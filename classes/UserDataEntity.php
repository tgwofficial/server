<?php

defined('BASE_URL') OR exit('No direct script access allowed');

class UserDataEntity
{
    protected $user_id;
    protected $fullname;
    protected $contact;
    protected $address;

	public function __construct(array $data) {
		$this->id = $data['user_id'];
		if(isset($data['fullname'])) {
            $this->fullname = $data['fullname'];
        }
		if(isset($data['contact'])) {
            $this->contact = $data['contact'];
        }
		if(isset($data['address'])) {
            $this->address = $data['address'];
        }
	}

    public function getUserId() {
        return $this->user_id;
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

}
