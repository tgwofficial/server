<?php

defined('BASE_URL') OR exit('No direct script access allowed');

class LocationTagMapper extends Mapper
{
	public function getUsers() {
        $sql = "SELECT *
            from location_tag";
        $stmt = $this->db->query($sql);

        $results = [];
        while($row = $stmt->fetch()) {
            $results[] = new LocationTagEntity($row);
        }
        return $results;
    }

    public function getLocationTagById($id) {
        $sql = "SELECT *
            from location_tag
			where location_tag_id = :location_tag_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["location_tag_id" => $id]);

        return new LocationTagEntity($stmt->fetch());
    }

    public function save(LocationTagEntity $location_tag) {
        $sql = "INSERT INTO location_tag
            (name, description, uuid) values
            (:name, :description, :uuid)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "name" => $location_tag->getName(),
			"description" => $location_tag->getDescription(),
			"uuid" => $location_tag->getUuid(),
        ]);

        if(!$result) {
            throw new Exception("could not save record");
        }
    }

}
