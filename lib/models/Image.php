<?php

class Image
{
    /**
     * @var int the image's ID
     */
    private $id;
    /**
     * @var string the image's path
     */
    private $path;
    /**
     * @var int the listing's ID
     */
    private $listing_id;

    /**
     * Image constructor.
     * @param int $id the image's ID
     * @param string $path the image's path
     * @param int $listing_id the listing's ID
     */
    public function __construct($id, $path, $listing_id)
    {
        $this->id = require_non_empty($id, "image_Id");
        $this->path = require_non_empty($path, "path");
        $this->listing_id = require_non_empty($listing_id, "listing_id");
    }

    /**
     * @param string $path the image's path
     * @param Listing $listing the listing containing this image
     * @return Image image object
     */
    public static function create($path, $listing)
    {
        global $db;

        $stmt = $db->prepare("INSERT INTO images(path, listing_id) VALUES (:path, :listing_id)");
        $stmt->bindValue(":path", $path, PDO::PARAM_STR);
        $stmt->bindValue(":listing_id", $listing->getId(), PDO::PARAM_INT);
        $stmt->execute();
        $stmt->closeCursor();

        $iid = (int)$db->lastInsertId();

        return new Image($iid, $path, $listing->getId());
    }

    /**
     * @return string the image's path
     */
    public function getPath()
    {
        return $this->path;
    }
}