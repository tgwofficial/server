<?php

defined('BASE_URL') OR exit('No direct script access allowed');

class LocationMapper extends Mapper
{
	public function getUsers() {
        $sql = "SELECT *
            from location";
        $stmt = $this->db->query($sql);

        $results = [];
        while($row = $stmt->fetch()) {
            $results[] = new LocationEntity($row);
        }
        return $results;
    }

    public function getLocationById($id) {
        $sql = "SELECT *
            from location
			where location_id = :location_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["location_id" => $id]);

        return new LocationEntity($stmt->fetch());
    }

    public function save(LocationEntity $location) {
        $sql = "INSERT INTO location
            (name, parent_location, location_tag_id, uuid) values
            (:name, :parent_location, :location_tag_id, :uuid)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "name" => $location->getName(),
			"parent_location" => $location->getParentLocation(),
			"location_tag_id" => $location->getLocationTagId(),
			"uuid" => $location->getUuid(),
        ]);

        if(!$result) {
            throw new Exception("could not save record");
        }
    }

}
