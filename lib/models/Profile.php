<?php

class Profile
{
    /**
     * @var int the profile's ID
     */
    private $id;
    /**
     * @var int the location's ID
     * @see Location
     */
    private $location_id;
    /**
     * @var string the user's first name
     */
    private $first_name;
    /**
     * @var string the user's last name
     */
    private $last_name;
    /**
     * @var string the user's telephone number
     */
    private $tel;

    /**
     * Profile constructor.
     * @param int $id
     * @param int $location_id
     * @param string $first_name
     * @param string $last_name
     * @param string $tel
     */
    public function __construct($id, $location_id, $first_name, $last_name, $tel)
    {
        $this->id = require_non_empty($id, "profile_id");
        $this->location_id = require_non_empty($location_id, "location_id");
        $this->first_name = require_non_empty($first_name, "first_name");
        $this->last_name = require_non_empty($last_name, "last_name");
        $this->tel = require_non_empty($tel, "tel");
    }

    /**
     * Create a new profile and save it into the database.
     * @param Location $location user's location
     * @param string $first_name user's first name
     * @param string $last_name user's last name
     * @param string $tel user's telephone number
     * @return Profile profile object
     */
    public static function create($location, $first_name, $last_name, $tel)
    {
        global $db;

        $stmt = $db->prepare("INSERT INTO profiles(location_id, first_name, last_name, tel) VALUES (:location_id, :first_name, :last_name, :tel)");
        $stmt->bindValue(":location_id", $location->getId(), PDO::PARAM_INT);
        $stmt->bindValue(":first_name", $first_name, PDO::PARAM_STR);
        $stmt->bindValue(":last_name", $last_name, PDO::PARAM_STR);
        $stmt->bindValue(":tel", $tel, PDO::PARAM_STR);
        $stmt->execute();
        $stmt->closeCursor();

        $pid = (int)$db->lastInsertId();

        return new Profile($pid, $location->getId(), $first_name, $last_name, $tel);
    }

    /**
     * Get a profile from the database.
     * @param int $profile_id
     * @return Profile profile by ID
     */
    public static function getById($profile_id)
    {
        global $db;

        $stmt = $db->prepare("SELECT id, location_id, first_name, last_name, tel FROM profiles WHERE id = :profile_id");
        $stmt->bindValue(":profile_id", $profile_id, PDO::PARAM_INT);
        $stmt->execute();

        $profile = $stmt->fetch(PDO::FETCH_ASSOC);

        return new Profile($profile['id'], $profile['location_id'], $profile['first_name'], $profile['last_name'], $profile['tel']);
    }

    /**
     * @return int profile's ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int location's ID
     */
    public function getLocationId()
    {
        return $this->location_id;
    }

    /**
     * @return string user's first name
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @return string user's second name
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @return string user's telephone number
     */
    public function getTel()
    {
        return $this->tel;
    }
}