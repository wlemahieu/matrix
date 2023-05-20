<?php

class Controller {
    
    /**
     * @var null Database Connection
     */
    public $db = null;
    public $semaphore_db = null;
    public $ct_godaddy_db = null;

    // open all database connections
    function __construct() {
        $this->openDatabaseConnection();
    }

    // DB credentials reside in application/config/config.php
    private function openDatabaseConnection() {

        // define PDO connection options
        $options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING);

        // create an exceptions array for any broken DB connections we may run into
        $_SESSION['PDOException'] = array();

        // connect to Matrix DB
        try { 
            $this->db = new PDO(DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            $_SESSION['PDOException']['matrix'] = 1;
        }

        // connect to Semaphore DB (tickets)
        try { 
            $this->semaphore_db = new PDO(SEMAPHORE_DB_TYPE . ':host=' . SEMAPHORE_DB_HOST . ';dbname=' . SEMAPHORE_DB_NAME . ';charset=' . SEMAPHORE_DB_CHARSET, SEMAPHORE_DB_USER, SEMAPHORE_DB_PASS, $options);
        } catch (PDOException $e) {
            $_SESSION['PDOException']['semaphore'] = 1;
        }

        // connect to Cloudtech's DB (Gary's Box)
        try {
            $this->ct_godaddy_db = new PDO(CLOUDTECH_DB_TYPE . ':host=' . CLOUDTECH_DB_HOST . ';dbname=' . CLOUDTECH_DB_NAME . ';charset=' . CLOUDTECH_DB_CHARSET, CLOUDTECH_DB_USER, CLOUDTECH_DB_PASS, $options);
        } catch (PDOException $e) {
            $_SESSION['PDOException']['cloudtech'] = 1;
        }
    }
}