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
 
class Elasticsearch {
 
    private $config;
    private $client;
    private $cache_lifetime = 360;
    private $host;
    private $port;
    private $index = 'cache';
    private $type = 'api_shop';
    private $auth;
    private $user;
    private $password;
 
    public function __construct(array $store)
    {
        $config = (new Settings())->get();
        $this->cache_lifetime = $config['cache']['cache_lifetime'];
        $this->config = $config['cache']['elasticsearch'];
        if(isset($store['host'])) { $this->host = $store['host']; }
        if(isset($store['port'])) { $this->port = $store['port']; }
        if(isset($store['type'])) { $this->type = $store['type']; }
        if(isset($store['index'])) { $this->index = $store['index']; }
        if(isset($store['auth'])) { $this->auth = $store['auth']; }
        if(isset($store['user'])) { $this->user = $store['user']; }
        if(isset($store['password'])) { $this->password = $store['password']; }
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
 