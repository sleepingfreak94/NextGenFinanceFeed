<?php
class Database
{
    private $host = "127.0.0.1";
    private $username = "root";
    private $password = "";
    private $database = "nextgen_finance_feed";
    public $conn;

    public function __construct()
    {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->database);
        if ($this->conn->connect_error) {
            die("Database Connection Failed: " . $this->conn->connect_error);
        }
    }
}
