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
 
namespace ApiShop\Adapter\Cache;
 
use Slim\Http\Request;
use Slim\Http\Response;
 
use ApiShop\Config\Settings;
 
class JsonDB {
 
    private $config;
    private $client;
    private $cache_lifetime = 360;
    private $url;
 
    public function __construct(array $store)
    {
        $config = (new Settings())->get();
        $this->cache_lifetime = $config['cache']['cache_lifetime'];
        $this->config = $config['cache']['jsondb'];
        if(isset($store['url'])) { $this->url = $store['url']; }
    }
 
    public function getItem($cacheKey)
    {
        return null;
    }
 
    public function save()
    {
        return null;
    }
 
    public function set()
    {
        return null;
    }
 
    public function expiresAfter()
    {
        return null;
    }
 
}
 