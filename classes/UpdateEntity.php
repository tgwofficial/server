<?php

class UpdateEntity
{
    protected $id;
    protected $update_id;
    protected $form_name;
    protected $data;
    protected $user_id;
    protected $server_timestamp;

	public function __construct(array $data) {
        if(isset($data['id'])) {
            $this->id = $data['id'];
        }
        $this->update_id = $data['update_id'];
        $this->form_name = $data['form_name'];
        $this->data = $data['data'];
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

    public function getUserId() {
        return $this->user_id;
    }

    public function getServerTimestamp() {
        return $this->server_timestamp;
    }

    public function toArray(){
        return ["update_id"=>$this->getUpdateId(),"form_name"=>$this->getFormName(),"data"=>$this->getData(),"user_id"=>$this->getUserId()];
    }

}
