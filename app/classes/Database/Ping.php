<?php
/**
 * This file is part of the API SHOP
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/pllano/api-shop
 * @version 1.0.1
 * @package pllano.api-shop
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
namespace ApiShop\Database;

use GuzzleHttp\Client as Guzzle;
use ApiShop\Config\Settings;
use ApiShop\Utilities\Utility;
use jsonDB\Db;
use jsonDB\Database as jsonDb;
use jsonDB\Validate;
use jsonDB\dbException;

class Ping
{    
    private $resource = null;
    private $settings = null;
    private $db = null;
 
    public function __construct($resource = null)
    {
        $this->resource = $resource;
        $this->resourceTest();
    }
 
    public function resourceTest()
    {
        $this->settings = (new Settings())->get();
        $db = null;
		$resource = "site";
 
        if (isset($this->settings["resource"][$this->resource]["db"])) {
            $db = $this->settings["resource"][$this->resource]["db"];
        } else {
            $db = $this->settings["db"]["master"];
        }
        
        if ($db == "api") {
            try {
                $url = $this->settings["db"]["api"]["url"];
                $public_key = $this->settings["db"]["api"]["public_key"];

                if (isset($this->resource)) {
                    $resource = $this->resource;
                }
 
                $guzzle = new Guzzle();
                $response = $guzzle->request("GET", $url."".$resource."?public_key=".$public_key."&limit=1&offset=0");
                $output = $response->getBody();
 
                $output = (new Utility())->clean_json($output);
 
                $records = json_decode($output, true);
 
                if (isset($records["header"]["code"])) {
                    if ($records["header"]["code"] == 200 || $records["header"]["code"] == "200") {
                        if (count($records["body"]["items"]) >= 1) {
                            $this->db = "api";
                        }
                    }
                }
            
            } catch (dbException $e) {
                $db = $this->settings["db"]["slave"];
            }

        } elseif ($db == "json") {
            try {Validate::table($this->resource)->exists();
				$this->db = "json";
			} catch(dbException $e){
			    $this->db = $this->settings["db"]["slave"];
			}
			
        } elseif ($db == "mysql") {
            $this->db = "mysql";
        } elseif ($db == "elasticsearch") {
            $this->db = "elasticsearch";
        } else {
            $this->db = $this->settings["db"]["slave"];
        }
        
        print_r($this->db);
    }
    
    public function get()
    {
        return $this->db;
    }
}
 