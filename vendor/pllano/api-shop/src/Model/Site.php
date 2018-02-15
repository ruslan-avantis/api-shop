<?php
/**
 * This file is part of the {API}$hop
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/pllano/api-shop
 * @version 1.1.1
 * @package pllano.api-shop
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiShop\Model;
 
use Pllano\RouterDb\Db;
use Pllano\RouterDb\Router;
use Pllano\Caching\Cache;
 
class Site
{
    private $site = null;
    private $resource = "site";
    private $template;
    private $config = [];
    private $cache_lifetime = 30*24*60*60;
 
    function __construct($config)
    {
        $this->config = $config;
        $this->template = $this->config["settings"]["themes"]["template"];
    }

    public function get()
    {
        $config = $this->config;
        // Кеш
        $cache = new Cache($config);
        $cache_run = $cache->run($this->resource, $this->cache_lifetime);
        if ($cache_run === null) {
 
            // Отдаем роутеру RouterDb конфигурацию.
            $router = new Router($config);
            // Получаем название базы для указанного ресурса
            $db_name = $router->ping($this->resource);

            if ($db_name != null) {
                // Подключаемся к базе
                $db = new Db($db_name, $config);
                // Отправляем запрос и получаем данные
                $response = $db->get($this->resource);
 
                if ($response != null) {
                    $this->site = $response["body"]["items"]["0"]["item"];
                } else {
                    $this->site = null;
                }
 
                if ($cache->state() == 1) {
                    $cache->set($this->site);
                }
 
                return $this->site;
 
            } else {
                return $cache->get();
            }
        } else {
             return $cache->get();
        }
    
    }
 
    public function template()
    {
        return $this->template;
    }
 
    public function setResource($resource)
    {
        $this->resource = $resource;
    }
 
    public function cache_lifetime($cache_lifetime)
    {
        $this->cache_lifetime = $cache_lifetime;
    }
 
}
 