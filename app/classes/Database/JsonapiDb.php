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

use ApiShop\Config\Settings;
use ApiShop\Utilities\Utility;
use GuzzleHttp\Client as Guzzle;

class JsonapiDb
{
    
    private $settings = null;
    private $resource = null;
    private $url = null;
    private $auth = null;
    private $public_key = null;
 
    public function __construct()
    {
        $this->settings = (new Settings())->get();
        $this->auth = $this->settings["db"]["jsonapi"]["auth"];
        $this->url = $this->settings["db"]["jsonapi"]["url"];
        $this->public_key = $this->settings["db"]["jsonapi"]["public_key"];
    }
 
    public function get($resource = null, array $arr = array(), $id = null)
    {
        $array = http_build_query($arr);
        $public_key = "?";
        if ($this->auth == "QueryKeyAuth") {
            $public_key = "?public_key=".$this->public_key;
        }
        $this->resource = $resource;
        $resource_id = "";
        if ($id >= 1) {$resource_id = "/".$id;}
 
        $response = (new Guzzle())->request("GET", $this->url."".$resource."".$resource_id."".$public_key."&".$array);
        $resp = $response->getBody();
        $output = (new Utility())->clean_json($resp);
        $records = json_decode($output, true);
 
        if (isset($records["headers"]["code"])) {
            if ($records["headers"]["code"] == 200 || $records["headers"]["code"] == "200") {
                return $records["body"];
            }
        }
    }
 
    // Создаем одну запись
    public function post($resource = null, array $arr = array())
    {
        
    }
 
    // Обновляем
    public function put($resource = null, array $arr = array(), $id = null)
    {
        
    }
 
    // Удаляем
    public function delete($resource = null, array $arr = array(), $id = null)
    {
        
    }
 
}
 