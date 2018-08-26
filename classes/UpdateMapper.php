<?php

defined('BASE_URL') OR exit('No direct script access allowed');

class UpdateMapper extends Mapper
{
	public function getUpdates($update_id) {
        $sql = "SELECT *
            from updates WHERE update_id > $update_id";
        $stmt = $this->db->query($sql);

        $results = [];
        while($row = $stmt->fetch()) {
            $results[] = new UpdateEntity($row);
        }
        return $results;
    }

	public function getBatchUpdates($update_id,$batch) {
        $sql = "SELECT *
            from updates WHERE update_id > $update_id LIMIT $batch";
        $stmt = $this->db->query($sql);

        $results = [];
        while($row = $stmt->fetch()) {
			$update = new UpdateEntity($row);
            $results[] = $update->toArray();
        }
        return $results;
    }

	public function getBatchUpdatesByLocationId($location_id,$update_id,$batch) {
        $sql = "SELECT *
            from updates WHERE update_id > $update_id AND location_id = '$location_id' LIMIT $batch";
        $stmt = $this->db->query($sql);

        $results = [];
        while($row = $stmt->fetch()) {
			$update = new UpdateEntity($row);
            $results[] = $update->toArray();
        }
        return $results;
    }

    public function getUpdateById($id) {
        $sql = "SELECT *
            from updates where update_id = :update_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["update_id" => $id]);

        return new UpdateEntity($stmt->fetch());
    }

    public function save(UpdateEntity $update) {
        $sql = "INSERT INTO updates
            (update_id, form_name, data, user_id) values
            (:update_id, :form_name, :data, :user_id)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "update_id" => $update->getUpdateId(),
			"form_name" => $update->getFormName(),
			"data" => json_encode($update->getData()),
			"location_id" => $update->getLocationId(),
			"user_id" => $update->getUserId(),
        ]);

        if(!$result) {
            throw new Exception("could not save record");
        }
    }

	public function saveBatch(UpdateEntity $updates) {

	}
}
