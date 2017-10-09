<?php

/**
 * A listing represents an item that someone wants to trade on the site (either obtain it from other
 * users or exchange it for other user's listings).
 *
 * @see User
 * @see Location
 */
class Listing
{
    /**
     * @var int listing id
     */
    private $id;
    /**
     * @var int type of listing, one of TYPE_OFFER or TYPE_WISH
     */
    private $type;
    /**
     * @var int id of the user that posted the listing
     * @see User
     */
    private $user_id;
    /**
     * @var string listing title
     */
    private $title;
    /**
     * @var string url-friendly slug for listing
     */
    private $slug;
    /**
     * @var string long description of the listing
     */
    private $description;
    /**
     * @var int listing visiblity status, one of STATUS_AVAILABLE or STATUS_GONE (if it was already traded)
     */
    private $status;
    /**
     * @var DateTime when the listing was added
     */
    private $added;
    /**
     * @var int id of the Location where the listing is available
     * @see Location
     */
    private $location_id;

    /**
     * A listing for an item that an user wants to give in exchange for other items.
     * @see Listing::getType()
     */
    const TYPE_OFFER = 1;
    /**
     * A listing for an item that an user wants but does not have, and is willing to trade other items for it.
     * @see Listing::getType()
     */
    const TYPE_WISH = 2;

    /**
     * A listing that is valid and displayed.
     * @see Listing::getStatus()
     */
    const STATUS_AVAILABLE = 1;
    /**
     * A listing that is no longer valid because the user already traded the item away, or already obtained it.
     * @see Listing::getStatus()
     */
    const STATUS_GONE = 2;

    /**
     * Listing constructor.
     * @param int $id
     * @param int $type
     * @param int $user_id
     * @param string $title
     * @param string $slug
     * @param string $description
     * @param int $status
     * @param DateTime $added
     * @param int $location_id
     */
    private function __construct($id, $type, $user_id, $title, $slug, $description, $status, DateTime $added, $location_id)
    {
        $this->id = require_non_empty($id, "listing_id");
        $this->type = require_non_empty($type, "type");
        $this->user_id = require_non_empty($user_id, "user_id");
        $this->title = require_non_empty($title, "title");
        $this->slug = require_non_empty($slug, "slug");
        $this->description = require_non_null($description, "description");
        $this->status = require_non_empty($status, "status");
        $this->added = require_non_null($added, "added");
        $this->location_id = require_non_empty($location_id, "location_id");
    }

    /**
     * @param array $l array result fetched with PDO::FETCH_ASSOC
     * @return Listing Listing object
     */
    public static function makeFromPDO($l)
    {
        $added = DateTime::createFromFormat('Y-m-d H:i:s', $l['added']);
        return new Listing($l['id'], $l['type'], $l['user_id'], $l['title'], $l['slug'],
            $l['description'], $l['status'], $added, $l['location_id']);
    }

    public static function getById($listing_id) {
        global $db;

        $stmt = $db->prepare("SELECT id, type, user_id, title, slug, description, status, added, location_id
                             FROM listings WHERE id = :listing_id");
        $stmt->bindValue(":listing_id", $listing_id, PDO::PARAM_INT);
        $stmt->execute();

        return self::makeFromPDO(require_fetch_one($stmt, "Listing", "id", $listing_id));
    }

    /**
     * @return Tag[] array of this listing's tags
     */
    public function getTags()
    {
        global $db;

        $stmt = $db->prepare("SELECT id, name
                             FROM tags INNER JOIN listing_tags ON tags.id = listing_tags.tag_id
                             WHERE listing_tags.listing_id = :listing_id");
        $stmt->bindValue(":listing_id", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        $result = [];
        $tags = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($tags as $tag) {
            $result[] = Tag::makeFromPDO($tag);
        }

        return $result;
    }

    /**
     * @return Image[] array of this listing's images
     */
    public function getImages()
    {
        global $db;

        $stmt = $db->prepare("SELECT id, path, listing_id FROM images WHERE images.listing_id = :listing_id");
        $stmt->bindValue(":listing_id", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        $result = [];
        $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($images as $image) {
            $result[] = Image::makeFromPDO($image);
        }

        return $result;
    }

    /**
     * @return int listing unique ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int type of listing, one of TYPE_ constants
     * @see Listing::TYPE_OFFER, Listing::TYPE_WISH
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return User the user that posted this listing
     */
    public function getUser()
    {
        return User::getById($this->user_id);
    }

    /**
     * @return string listing title
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string url-friendly slug
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return string listing long description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return int listing visiblity status, one of STATUS_ constants
     * @see Listing::STATUS_AVAILABLE, Listing::STATUS_GONE
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return DateTime when the listing was added
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * @return Location location where this listing is available
     */
    public function getLocation()
    {
        return Location::getById($this->location_id);
    }
}
