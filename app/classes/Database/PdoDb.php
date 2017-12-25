<?php

namespace ApiShop\Database;

use ApiShop\Config\Settings;
use PDO;

class PdoDb {
    
    public $dbh; // handle of the db connexion
    private static $instance;
    private $settings = null;

    private function __construct() {
        $this->settings = (new Settings())->get();
        // building data source name from config
        $dsn = 'mysql:host='.$this->settings["db"]["mysql"]["host"].
               ';dbname='.$this->settings["db"]["mysql"]["dbname"].
               ';port='.$this->settings["db"]["mysql"]["port"].
               ';connect_timeout='.$this->settings["db"]["mysql"]["connect_timeout"].
               ';charset='.$this->settings["db"]["mysql"]["charset"];
        // getting DB user from config           
        $user = $this->settings["db"]["mysql"]["user"];
        // getting DB password from config
        $password = $this->settings["db"]["mysql"]["password"];

        $this->dbh = new PDO($dsn, $user, $password);   
    }

    public static function getInstance() {
        if (!isset(self::$instance))
        {
            $object = __CLASS__;
            self::$instance = new $object;
        }
        return self::$instance;
    }
    
    // others global functions
}