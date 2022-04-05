<?php
class Database {
//$conn_string = "host=localhost port=1768 dbname=vk_api_phone_numbers user=postgres password=adu202121";
    // укажите свои учетные данные базы данных
    private $host = "localhost";
    private $port = "1768";
    private $db_name = "vk_api_phone_numbers";
    private $username = "postgres";
    private $password = "adu202121";
    public $conn;

    // получаем соединение с БД
    public function getConnection(){

        $this->conn = null;

        try {
            $this->conn = pg_connect("host=". $this->host ." port=".$this->port." dbname=".$this->db_name." user=".$this->username." password=".$this->password);
        } catch(Exception $e){
            echo "Connection error: " . $e->getMessage();
        }

        return $this->conn;
    }
}