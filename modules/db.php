<?php

class DB {

    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $db_name = "aj_quote";
    public $conn;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        // Create connection
        $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->db_name);

        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }
}

?>