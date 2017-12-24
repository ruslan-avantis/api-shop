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

class ApiDb
{
 
    private $settings = null;
    private $public_key = null;
    private $resource = null;
    private $url = null;
    private $auth = null;
    private $api = null;
 
    public function __construct()
    {
        $this->settings = (new Settings())->get();
        $this->api = $this->settings["db"]["api"]["config"];
        $this->url = $this->settings["db"]["api"]["url"];
        $this->auth = $this->settings["db"]["api"]["auth"];
        if ($this->settings["db"]["api"]["public_key"] !== null) {
            $this->public_key = $this->settings["db"]["api"]["public_key"];
        }
    }
 
    public function get($resource = null, array $arr = array(), $id = null)
    {
        if ($this->api == true) {
            $public_key = "?";
            if ($this->auth == "QueryKeyAuth") {
                $public_key = "?public_key=".$this->public_key;
            }
            $this->resource = $resource;
            $resource_id = "";
            if ($id >= 1) {$resource_id = "/".$id;}
 
            $response = (new Guzzle())->request("GET", $this->url."".$resource."".$resource_id."".$public_key);
            $resp = $response->getBody();
            $output = (new Utility())->clean_json($resp);
            $records = json_decode($output, true);
 
            if (isset($records["header"]["code"])) {
                if ($records["header"]["code"] == 200 || $records["header"]["code"] == "200") {
                    return $records["body"];
                }
            }
        }
    }
 
}
 