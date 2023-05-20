<?php
class DB {
    function __construct($db) {
        try {
            $this->db = $db;
        } catch (PDOException $e) {
            exit('Database connection could not be established.');
        }
    }
}

class SemaphoreDB {
    function __construct($semaphore_db) {
        try {
            $this->semaphore_db = $semaphore_db;
        } catch (PDOException $e) {
            exit('Database connection could not be established.');
        }
    }
}