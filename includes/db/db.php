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
        // ✅ Force UTF-8 (utf8mb4) for this connection
        if (!$this->conn->set_charset("utf8mb4")) {
            die("Error loading character set utf8mb4: " . $this->conn->error);
        }
    }
}
