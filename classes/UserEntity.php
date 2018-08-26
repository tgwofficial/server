<?php

defined('BASE_URL') OR exit('No direct script access allowed');

class UserEntity
{
    protected $user_id;
    protected $username;
    protected $password;
    protected $fullname;
    protected $contact;
    protected $address;
    protected $location_id;
    protected $uuid;

	public function __construct(array $data) {
		if(isset($data['user_id'])) {
            $this->user_id = $data['user_id'];
        }
        $this->username = $data['username'];
        $this->password = $data['password'];
        $this->fullname = $data['fullname'];
        if(isset($data['contact'])) {
            $this->contact = $data['contact'];
        }
        if(isset($data['address'])) {
            $this->address = $data['address'];
        }
        $this->location_id = $data['location_id'];
		if(isset($data['uuid'])) {
            $this->uuid = $data['uuid'];
        }else{
            $this->uuid = UUID::v4();
        }
	}

    public function getUserId() {
        return $this->user_id;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getFullname() {
        return $this->fullname;
    }

    public function getContact() {
        return $this->contact;
    }

    public function getAddress() {
        return $this->address;
    }

    public function getLocationId() {
        return $this->location_id;
    }

    public function getUuid() {
        return $this->uuid;
    }

}
