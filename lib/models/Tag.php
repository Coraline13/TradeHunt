<?php

class Tag
{
    /**
     * @var int the tag's ID
     */
    private $id;
    /**
     * @var string the tag's name
     */
    private $name;

    /**
     * Tag constructor.
     * @param int $id
     * @param string $name
     */
    private function __construct($id, $name)
    {
        $this->id = require_non_empty($id, "tag_id");
        $this->name = require_non_empty($name, "name");
    }

    /**
     * @param array $t array result fetched with PDO::FETCH_ASSOC
     * @return Tag Tag object
     */
    public static function makeFromPDO($t) {
        return new Tag($t['id'], $t['name']);
    }

    /**
     * @param string $name the tag's name
     * @return Tag tag object
     */
    public static function create($name)
    {
        global $db;

        $stmt = $db->prepare("INSERT INTO tags(name) VALUES (:name)");
        $stmt->bindValue(":name", $name, PDO::PARAM_STR);
        $stmt->execute();
        $stmt->closeCursor();

        $tid = (int)$db->lastInsertId();

        return new Tag($tid, $name);
    }

    /**
     * @return Listing[] array of all listings with this tag
     */
    public function getListings()
    {
        global $db;

        $stmt = $db->prepare("SELECT l.id, l.type, l.user_id, l.title, l.slug, l.description, l.status, l.added, l.location_id
                             FROM listings as l INNER JOIN listing_tags ON l.id = listing_tags.listing_id
                             WHERE listing_tags.tag_id = :tag_id");
        $stmt->bindValue(":tag_id", $this->id, PDO::PARAM_INT);
        $stmt->execute();

        return fetch_all_and_make($stmt, 'Listing');
    }

    /**
     * @param string $tag_name the tag's name
     * @return Tag tag by ID
     */
    public static function getByName($tag_name)
    {
        global $db;

        $stmt = $db->prepare("SELECT id, name FROM tags WHERE name = :tag_name");
        $stmt->bindValue(":tag_name", $tag_name, PDO::PARAM_STR);
        $stmt->execute();

        return self::makeFromPDO(require_fetch_one($stmt, "Tag", "name", $tag_name));
    }

    /**
     * @return Tag[] array of all tags
     */
    public static function getAll()
    {
        global $db;

        $stmt = $db->prepare("SELECT id, name FROM tags");
        $stmt->execute();

        return fetch_all_and_make($stmt, 'Tag');
    }

    /**
     * @return int the tag's unique ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string the tag's unique name
     */
    public function getName()
    {
        return $this->name;
    }
}