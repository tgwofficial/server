<?php

defined('BASE_URL') OR exit('No direct script access allowed');

abstract class Mapper {
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

}
