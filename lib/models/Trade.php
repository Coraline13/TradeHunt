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
        $this->id = $id;
        $this->recipient_id = $recipient_id;
        $this->sender_id = $sender_id;
        $this->message = $message;
        $this->status = $status;
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
}
