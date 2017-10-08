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
        $this->id = $id;
        $this->country = $country;
        $this->city = $city;
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

        $location = $stmt->fetch(PDO::FETCH_ASSOC);

        return new Location($location['id'], $location['country'], $location['city']);
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

        $result = [];
        $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($locations as $location) {
            $result[] = new Location($location['id'], $location['country'], $location['city']);
        }

        return $result;
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