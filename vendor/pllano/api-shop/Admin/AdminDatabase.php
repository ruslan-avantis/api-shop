<?php
 
namespace Pllano\ApiShop\Admin;
 
class AdminDatabase {
    
    private $config;
 
    function __construct($config)
    {
        $this->config = $config;
    }
 
    // Получаем список таблиц из json.db
    public function list() {
        if (isset($this->config["db"]["json"]["dir"])) {
            $json_dir = $this->config["db"]["json"]["dir"].'/core/';
            if (file_exists($json_dir."db.json")) {
                $json = json_decode(file_get_contents($json_dir."db.json"), true);
                if (count($json) >= 1) {
                    foreach($json as $value)
                    {
                        $table["name"] = $value["table"];
                        $table["schema"] = $value["schema"];
                        $resp["table"][] = $table;
                    }
                }
                return $resp;
            } else {
                return null;
            }
        } else {
            return false;
        }
    }
 
    // Получаем список таблиц из json.db
    public function getOne($db) {
        if (isset($this->config["db"]["json"]["dir"])) {
            $json_dir = $this->config["db"]["json"]["dir"].'/core/';
            if (file_exists($json_dir."db.json")) {
                $json = json_decode(file_get_contents($json_dir."db.json"), true);
                if (count($json) >= 1) {
                    foreach($json as $value)
                    {
                        if ($value["table"] == $db) {
                                $resp = $value["schema"];
                        }
                    }
                }
                return $resp;
            } else {
                return null;
            }
        } else {
            return false;
        }
    }

}
 