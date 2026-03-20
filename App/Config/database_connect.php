<?php
class Database {
    private $servername = "127.0.0.1";
    private $username = "root";
    private $password = "";
    private $dbname = "dripnstyle";
    private $port = 3306;

    public function connect() {
        $conn = new mysqli(
            $this->servername,
            $this->username,
            $this->password,
            $this->dbname,
            $this->port
        );

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        return $conn;
    }
}
?>