<?php

defined('BASE_URL') OR exit('No direct script access allowed');

class UserMapper extends Mapper
{
	public function getUsers() {
        $sql = "SELECT *
            from user";
        $stmt = $this->db->query($sql);

        $results = [];
        while($row = $stmt->fetch()) {
            $results[] = new UserEntity($row);
        }
        return $results;
    }

    public function getUserById($id) {
        $sql = "SELECT *
            from user where user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["user_id" => $id]);

        return new UserEntity($stmt->fetch());
    }

    public function getUserByUsername($username) {
        $sql = "SELECT *
            from user where username = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["username" => $username]);

        return new UserEntity($stmt->fetch());
    }

    public function save(UserEntity $user) {
        $sql = "INSERT INTO user
            (username, password, fullname, contact, address, location_id, uuid) values
            (:username, :password, :fullname, :contact, :address, :location_id, :uuid)";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "username" => $user->getUsername(),
			"password" => $user->getPassword(),
			"fullname" => $user->getFullname(),
			"contact" => $user->getContact(),
			"address" => $user->getAddress(),
			"location_id" => $user->getLocationId(),
			"uuid" => $user->getUuid(),
        ]);

        if(!$result) {
            throw new Exception("could not save record");
        }
    }
}
