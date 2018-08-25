<?php

defined('BASE_URL') OR exit('No direct script access allowed');

class LocationTagEntity
{
    protected $location_tag_id;
    protected $name;
    protected $description;
    protected $uuid;

	public function __construct(array $data) {
		if(isset($data['location_tag_id'])) {
            $this->id = $data['location_tag_id'];
        }
        $this->name = $data['name'];
		if(isset($data['description'])) {
            $this->description = $data['description'];
        }
		if(isset($data['uuid'])) {
            $this->uuid = $data['uuid'];
        }else{
            $this->uuid = UUID::v4();
        }
	}

    public function getLocationTagId() {
        return $this->location_tag_id;
    }

    public function getName() {
        return $this->name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getUuid() {
        return $this->uuid;
    }

}
