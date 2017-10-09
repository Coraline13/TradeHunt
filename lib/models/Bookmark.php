<?php

class Bookmark
{
    /**
     * @var int the bookmark's ID
     */
    private $id;
    /**
     * @var int the user's ID
     */
    private $user_id;
    /**
     * @var int the listing's ID
     */
    private $listing_id;
    /**
     * @var DateTime the date when the listing was bookmarked
     */
    private $added;

    /**
     * Bookmark constructor.
     * @param int $id the bookmark's ID
     * @param int $user_id the user's ID
     * @param int $listing_id the listing's ID
     * @param DateTime $added the date when the listing was bookmarked
     */
    private function __construct($id, $user_id, $listing_id, DateTime $added)
    {
        $this->id = require_non_empty($id, "bookmark_id");
        $this->user_id = require_non_empty($user_id, "user_id");
        $this->listing_id = require_non_empty($listing_id, "listing_id");
        $this->added = require_non_null($added, "added");
    }

    /**
     * @param array $b array result fetched with PDO::FETCH_ASSOC
     * @return Bookmark Bookmark object
     */
    public static function makeFromPDO($b) {
        return new Bookmark($b['id'], $b['user_id'], $b['listing_id'], $b['added']);
    }

    /**
     * @param User $user current user
     * @param Listing $listing the listing to be bookmarked
     * @return Bookmark bookmark object
     */
    public static function create(User $user, Listing $listing)
    {
        global $db;

        $added = new DateTime();

        $stmt = $db->prepare("INSERT INTO bookmarks(user_id, listing_id, added) VALUES (:user_id, :listing_id, :added)");
        $stmt->bindValue(":user_id", $user->getId(), PDO::PARAM_INT);
        $stmt->bindValue(":listing_id", $listing->getId(), PDO::PARAM_INT);
        $stmt->bindValue(":added", $added->format('Y-m-d H:i:s'), PDO::PARAM_STR);
        $stmt->execute();
        $stmt->closeCursor();

        $bid = (int)$db->lastInsertId();

        return new Bookmark($bid, $user->getId(), $listing->getId(), $added);
    }

    /**
     * @return Listing the listing that was bookmarked
     */
    public function getListing()
    {
        return Listing::getById($this->listing_id);
    }
}
