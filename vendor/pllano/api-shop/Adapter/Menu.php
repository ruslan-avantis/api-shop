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
 
namespace ApiShop\Adapter;
 
use ApiShop\Config\Settings;
 
class Menu {
 
    private $resource = "category";
    private $vendor = null;
    private $config;
 
    function __construct()
    {
        $this->config = (new Settings())->get();
        if(isset($this->config['vendor']['menu'])) {
            $this->vendor = $this->config['vendor']['menu'];
        }
    }
 
    public function get()
    {
        $menu = new $this->vendor();
        return $menu->get();
    }
 
    public function setResource($resource = null)
    {
        if(isset($resource)) {
            $this->resource = $resource;
        }
    }
 
    public function getResource()
    {
            return $this->resource;
    }
 
    public function setVendor($vendor = null)
    {
        if(isset($vendor)) {
            $this->vendor = $vendor;
        }
    }
 
    public function getVendor()
    {
            return $this->vendor;
    }
 
}
 