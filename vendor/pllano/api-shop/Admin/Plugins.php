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
 
class Plugins {
 
    private $plugin = null;
    private $config;
 
    function __construct()
    {
        $config = (new Settings())->get();
        $this->config = $config;
    }
 
    public function get()
    {
        $vendor_dir = $this->config["admin"]["vendor"].'/';
        if (file_exists($vendor_dir."auto_require.json")) {
            return json_decode(file_get_contents($vendor_dir."auto_require.json"), true);
        } else {
            return null;
        }
    }
 
    public function put($arr)
    {
        $newArr = json_encode($arr);
        $vendor_dir = $this->config["admin"]["vendor"].'/';
        file_put_contents($vendor_dir."auto_require.json", $newArr);
        return true;
    }
    
    public function delete($arr)
    {
        $newArr = json_encode($arr);
        $vendor_dir = $this->config["admin"]["vendor"].'/';
        file_put_contents($vendor_dir."auto_require.json", $newArr);
        return true;
    }
 
}
 