<?php

defined('BASE_URL') OR exit('No direct script access allowed');

class UserEntity
{
    protected $user_id;
    protected $username;
    protected $password;
    protected $uuid;

	public function __construct(array $data) {
		if(isset($data['user_id'])) {
            $this->id = $data['user_id'];
        }
        $this->username = $data['username'];
        $this->password = $data['password'];
		if(isset($data['uuid'])) {
            $this->uuid = $data['uuid'];
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

    public function getUuid() {
        return $this->uuid;
    }

}
