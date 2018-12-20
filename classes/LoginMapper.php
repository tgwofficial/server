<?php

defined('BASE_URL') OR exit('No direct script access allowed');

class LoginMapper extends Mapper
{
	public function getLoginInfo($username) {
        $sql = "SELECT id,username,email,created_on,last_login,active,first_name,last_name,company,phone
            from users where username = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["username" => $username]);
        $user = $stmt->fetch();

        $sql = "SELECT *
            from user_map where user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["user_id" => $user['id']]);
        $user_map = $stmt->fetch();

        $sql = "SELECT *, name
            from users_groups LEFT JOIN groups ON users_groups.group_id=groups.id where user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["user_id" => $user['id']]);
        $users_groups = $stmt->fetch();
        $user['groups'] = $users_groups['name'];

        $res['user'] = $user;

        $sql = "SELECT *
            from location where location_id = :location_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["location_id" => $user_map['location_id']]);
        $loc = $stmt->fetch();
        $res['user_location'] = $loc;

        $locs = [];
        $locs[] = $loc;
        while($loc['parent_location']!=NULL){
            $loc = $this->getParentLocation($loc);
            $locs[] = $loc;
        }
        $locs = array_reverse($locs);
        
        if ($user['groups']=="bidan") {
            $childLocs = $this->getChildLocations($res['user_location']);
            foreach ($childLocs as $child) {
                $locs[] = $child;
            }
        }

        $res['locations_tree'] = $locs;        

        return $res;
    }

    private function getParentLocation($loc){
        $sql = "SELECT *
            from location where location_id = :parent_location";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["parent_location" => $loc['parent_location']]);
        $loc = $stmt->fetch();
        return $loc;
    }

    private function getChildLocations($loc){
        $sql = "SELECT *
            from location where parent_location = :location_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(["location_id" => $loc['location_id']]);
        $results = [];
        while($row = $stmt->fetch()) {
            $results[] = $row;
        }
        return $results;
    }
}