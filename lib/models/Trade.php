<?php

class Trade
{
    /**
     * @var int unique trade ID
     */
    private $id;
    /**
     * @var int user ID of the user that receives the trade offer
     */
    private $recipient_id;
    /**
     * @var int user ID of the user that sent/proposed the trade
     */
    private $sender_id;
    /**
     * @var string message attached by the sender
     */
    private $message;
    /**
     * @var integer trade status, one of STATUS_ constants
     */
    private $status;

    /**
     * A trade offer that was proposed by the sender, but not yet accepted or declined.
     * @see Trade::getStatus()
     */
    const STATUS_PROPOSED = 1;
    /**
     * A trade offer that was accepted by the receiver.
     * @see Trade::getStatus()
     */
    const STATUS_ACCEPTED = 2;
    /**
     * A trade offer that was decliend by the receiver.
     * @see Trade::getStatus()
     */
    const STATUS_DECLINED = 3;

    /**
     * Trade constructor.
     * @param int $id
     * @param int $recipient_id
     * @param int $sender_id
     * @param string $message
     * @param int $status
     */
    private function __construct($id, $recipient_id, $sender_id, $message, $status)
    {
        $this->id = require_non_empty($id, "trade_id");
        $this->recipient_id = require_non_empty($recipient_id, "recipient_id");
        $this->sender_id = require_non_empty($sender_id, "sender_id");
        $this->message = require_non_null($message, "message");
        $this->status = require_non_empty($status, "status");
        self::checkStatus($status);
    }

    private static function checkStatus($status)
    {
        if ($status != self::STATUS_PROPOSED && $status != self::STATUS_ACCEPTED && $status != self::STATUS_DECLINED) {
            throw new InvalidArgumentException("invalid status");
        }
    }

    /**
     * @param array $t array result fetched with PDO::FETCH_ASSOC
     * @return Trade Trade object
     */
    public static function makeFromPDO($t)
    {
        return new Trade($t['id'], $t['recipient_id'], $t['sender_id'], $t['message'], $t['status']);
    }

    /**
     * Create a new trade and insert it into the database.
     * @param User $sender the user that sent/proposed the trade
     * @param User $recipient the user that received the trade offer
     * @param string $message message attached by the sender
     * @return Trade Trade object
     */
    public static function create(User $sender, User $recipient, $message)
    {
        global $db;
        $status = self::STATUS_PROPOSED;

        $stmt = $db->prepare("INSERT INTO trades(recipient_id, sender_id, message, status)
                                        VALUES (:recipient_id, :sender_id, :message, :status)");
        $stmt->bindValue(":recipient_id", $recipient->getId(), PDO::PARAM_INT);
        $stmt->bindValue(":sender_id", $sender->getId(), PDO::PARAM_INT);
        $stmt->bindValue(":message", $message, PDO::PARAM_STR);
        $stmt->bindValue(":status", $status, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->closeCursor();

        $tid = (int)$db->lastInsertId();
        return new Trade($tid, $recipient->getId(), $sender->getId(), $message, $status);
    }

    /**
     * @return int the trade's unique ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User the user that received the trade offer
     */
    public function getRecipient()
    {
        return User::getById($this->recipient_id);
    }

    /**
     * @return User the user that sent/proposed the trade
     */
    public function getSender()
    {
        return User::getById($this->sender_id);
    }

    /**
     * @return string message attached by the sender
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return int trade status, one of STATUS_ constants
     * @see Trade::STATUS_PROPOSED, Trade::STATUS_ACCEPTED, Trade::STATUS_DECLINED
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get the listings associated with this trade.
     * @param bool $sender include listings belonging to trade sender
     * @param bool $recipient include listings belonging to trade receiver
     * @return Listing[] requested listings
     */
    public function getListings($sender = true, $recipient = true)
    {
        global $db;
        if (!$sender && !$recipient) {
            throw new InvalidArgumentException("cannot exclude both sender and receiver listings");
        }

        $stmt = $db->prepare("SELECT l.id, l.type, l.user_id, l.title, l.slug, l.description, l.status, l.added, l.location_id
                             FROM listings AS l INNER JOIN trade_offers ON l.id = trade_offers.trade_id
                             WHERE trade_offers.trade_id = :trade_id AND (listings.user_id = :sender_id OR listings.user_id = :recipient_id)
                             ORDER BY id ASC");
        $stmt->bindValue(":trade_id", $this->id, PDO::PARAM_INT);
        $stmt->bindValue(":sender_id", $sender ? $this->id : 0, PDO::PARAM_INT);
        $stmt->bindValue(":recipient_id", $recipient ? $this->id : 0, PDO::PARAM_INT);
        $stmt->execute();

        return fetch_all_and_make($stmt, 'Listing');
    }

    /**
     * Add a listing to this trade.
     * @param Listing $listing listing to be added
     */
    public function addListing(Listing $listing)
    {
        global $db;
        $stmt = $db->prepare("INSERT INTO trade_offers(trade_id, listing_id) VALUES (:trade_id, :listing_id)");
        $stmt->bindValue("trade_id", $this->id, PDO::PARAM_INT);
        $stmt->bindValue("listing_id", $listing->getId(), PDO::PARAM_INT);
        $stmt->execute();
        $stmt->closeCursor();
    }
}
