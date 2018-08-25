<?php

defined('BASE_URL') OR exit('No direct script access allowed');

class LocationTagMapEntity
{
    protected $location_id;
    protected $location_tag_id;

	public function __construct(array $data) {
		$this->location_id = $data['location_id'];
		$this->location_tag_id = $data['location_tag_id'];
	}

    public function getLocationId() {
        return $this->location_id;
    }

    public function getLocationTagId() {
        return $this->location_tag_id;
    }

}
