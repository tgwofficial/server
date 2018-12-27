<?php

defined('BASE_URL') OR exit('No direct script access allowed');

class LocationMapper extends Mapper
{
	public function getLocations() {
        $sql = "SELECT *
            from location";
        $stmt = $this->db->query($sql);

        $results = [];
        while($row = $stmt->fetch()) {
            $loc = new LocationEntity($row);
            $results[] = $loc->toArray();
        }

        return $results;
    }

    public function getLocationById($id) {
        $sql = "SELECT *
            from location
			where location_id = :location_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["location_id" => $id]);

        $res = $stmt->fetch();
        if(!$res) return [];
        $loc = new LocationEntity($res);

        return $loc->toArray();
    }

    public function getChildLocationById($id) {
        $sql = "SELECT *
            from location
            where parent_location = :location_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["location_id" => $id]);

        $results = [];
        while($row = $stmt->fetch()) {
            $loc = new LocationEntity($row);
            $results[] = $loc->toArray();
        }

        return $results;
    }

    public function getLocationsWithNames() {
        $sql = "SELECT location.location_id,location.name,child.name as parent_location,location_tag.name as location_tag_id,location.uuid
            from location LEFT JOIN location as child ON child.parent_location=location.location_id
            LEFT JOIN location_tag ON location.location_tag_id=location_tag.location_tag_id";
        $stmt = $this->db->query($sql);

        $results = [];
        while($row = $stmt->fetch()) {
            $loc = new LocationEntity($row);
            $results[] = $loc->toArray();
        }

        return $results;
    }

    public function getLocationByIdWithNames($id) {
        $sql = "SELECT location.location_id,location.name,child.name as parent_location,location_tag.name as location_tag_id,location.uuid
            from location LEFT JOIN location as child ON child.parent_location=location.location_id
            LEFT JOIN location_tag ON location.location_tag_id=location_tag.location_tag_id
            where location.location_id = :location_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["location_id" => $id]);

        $res = $stmt->fetch();
        if(!$res) return [];
        $loc = new LocationEntity($res);

        return $loc->toArray();
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
