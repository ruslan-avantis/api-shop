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
 
namespace ApiShop\Admin;
 
use ApiShop\Config\Settings;
 
class Config {
 
    private $config;
 
    function __construct()
    {
		// Подключаем конфиг Settings\Config
        $config = (new Settings())->get();
        $this->config = $config;
    }
 
    public function get()
    {
        if (isset($this->config["settings"]["json"])) {
            if (file_exists($this->config["settings"]["json"])) {
                return json_decode(file_get_contents($this->config["settings"]["json"]), true);
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
 
    public function put($arr)
    {
		$newArr = json_encode($arr);
		$newArr = str_replace('"1"', 1, $newArr);
		$newArr = str_replace('"0"', 0, $newArr);
		
		//if (is_numeric($value)) {$value = intval($value);}
		
		file_put_contents($this->config["settings"]["json"], $newArr);
        return true;
    }
 
}
 