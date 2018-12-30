<?php

defined('BASE_URL') OR exit('No direct script access allowed');

class UpdateEntity
{
    protected $id;
    protected $update_id;
    protected $form_name;
    protected $data;
    protected $location_id;
    protected $desa;
    protected $dusun;
    protected $user_id;
    protected $server_timestamp;

	public function __construct(array $data) {
        if(isset($data['id'])) {
            $this->id = $data['id'];
        }
        $this->update_id = $data['update_id'];
        $this->form_name = $data['form_name'];
        $this->data = $data['data'];
        $this->location_id = $data['location_id'];
        $this->desa = $data['desa'];
        $this->dusun = $data['dusun'];
        $this->user_id = $data['user_id'];
        if(isset($data['server_timestamp'])) {
            $this->server_timestamp = $data['server_timestamp'];
        }else{
            $this->server_timestamp = date("Y-m-d H:i:s");
        }
    }

    public function getId() {
        return $this->id;
    }

    public function getUpdateId() {
        return $this->update_id;
    }

    public function getFormName() {
        return $this->form_name;
    }

    public function getData() {
        return $this->data;
    }

    public function getLocationId() {
        return $this->location_id;
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function getServerTimestamp() {
        return $this->server_timestamp;
    }

    public function getDesa() {
        return $this->desa;
    }

    public function getDusun() {
        return $this->dusun;
    }

    public function toArray(){
        return ["update_id"=>$this->getUpdateId(),"form_name"=>$this->getFormName(),"data"=>$this->getData(),"location_id"=>$this->getLocationId(),"desa"=>$this->getDesa(),"dusun"=>$this->getDusun(),"user_id"=>$this->getUserId()];
    }

}
