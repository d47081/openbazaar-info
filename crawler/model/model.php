<?php

class Model {

    protected $db;

    public function __construct($database, $hostname, $port, $user, $password) {
        try {
            $this->db = new PDO('mysql:dbname=' . $database . ';host=' . $hostname . ';port=' . $port . ';charset=utf8', $user, $password, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            trigger_error($e->getMessage());
        }
    }
}
