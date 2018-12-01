<?php

defined('BASE_URL') OR exit('No direct script access allowed');

class LocationEntity
{
    protected $location_id;
    protected $name;
    protected $parent_location;
    protected $location_tag_id;
    protected $uuid;

	public function __construct(array $data) {
		if(isset($data['location_id'])) {
            $this->location_id = $data['location_id'];
        }
        $this->name = $data['name'];
		if(isset($data['parent_location'])) {
            $this->parent_location = $data['parent_location'];
        }
        $this->location_tag_id = $data['location_tag_id'];
		if(isset($data['uuid'])) {
            $this->uuid = $data['uuid'];
        }else{
            $this->uuid = UUID::v4();
        }
	}

    public function getLocationId() {
        return $this->location_id;
    }

    public function getName() {
        return $this->name;
    }

    public function getParentLocation() {
        return $this->parent_location;
    }

    public function getLocationTagId() {
        return $this->location_tag_id;
    }

    public function getUuid() {
        return $this->uuid;
    }

    public function toArray(){
        return ["location_id"=>$this->getLocationId(),"name"=>$this->getName(),"parent_location"=>$this->getParentLocation(),"location_tag_id"=>$this->getLocationTagId(),"uuid"=>$this->getUuid()];
    }

}
