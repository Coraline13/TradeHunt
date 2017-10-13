<?php

class Location
{
    /**
     * @var int the location's ID
     */
    private $id;
    /**
     * @var string the country name
     */
    private $country;
    /**
     * @var string the city name
     */
    private $city;

    /**
     * Location constructor.
     * @param int $id
     * @param string $country
     * @param string $city
     */
    private function __construct($id, $country, $city)
    {
        $this->id = (int)require_non_empty($id, "location_id");
        $this->country = require_non_empty($country, "country");
        $this->city = require_non_empty($city, "city");
    }

    /**
     * @param array $l array result fetched with PDO::FETCH_ASSOC
     * @return Location Location object
     */
    public static function makeFromPDO($l) {
        return new Location($l['id'], $l['country'], $l['city']);
    }

    /**
     * Get a location from the database.
     * @param int $location_id
     * @return Location location by ID
     */
    public static function getById($location_id)
    {
        global $db;

        $stmt = $db->prepare("SELECT id, country, city FROM locations WHERE id = :location_id");
        $stmt->bindValue(":location_id", $location_id, PDO::PARAM_INT);
        $stmt->execute();

        return self::makeFromPDO(require_fetch_one($stmt, "Location", "id", $location_id));
    }

    /**
     * Get the list of all locations.
     * @return Location[] array of Location objects
     */
    public static function getAll()
    {
        global $db;

        $stmt = $db->prepare("SELECT id, country, city FROM locations");
        $stmt->execute();

        return fetch_all_and_make($stmt, 'Location');
    }

    /**
     * @return int the location's ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string the country name
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return string the city name
     */
    public function getCity()
    {
        return $this->city;
    }
}