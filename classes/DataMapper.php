<?php

defined('BASE_URL') OR exit('No direct script access allowed');

class DataMapper extends Mapper
{
    public function save($form_name, array $data) {
        $keys = array_keys($data);
        $sql = "INSERT INTO data_".$form_name."(".implode(',', $keys).") values (:".implode(',:', $keys).")";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute($data);
        if(!$result) {
            throw new Exception("could not save record");
        }
    }
}
