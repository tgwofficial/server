<?php

defined('BASE_URL') OR exit('No direct script access allowed');

class UserLocationEntity
{
    protected $location_id;
    protected $user_id;

	public function __construct(array $data) {
		$this->location_id = $data['location_id'];
		$this->user_id = $data['user_id'];
	}

    public function getLocationId() {
        return $this->location_id;
    }

    public function getUserId() {
        return $this->user_id;
    }

}
