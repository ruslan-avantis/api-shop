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
 
use Pllano\RouterDb\Router as RouterDb;
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
        $this->template = $this->config["template"]["front_end"]["themes"]["template"];
    }

    public function get()
    {
        $config = $this->config;
        $cache = new Cache($this->config);
        $cache_run = $cache->run($this->resource, $this->cache_lifetime);
        if ($cache_run === null) {

			// Отдаем роутеру RouterDb конфигурацию
			$routerDb = new RouterDb($this->config, 'APIS');
			// Пингуем для ресурса указанную и доступную базу данных
			// Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
			$db = $routerDb->run($routerDb->ping($this->resource));
			// Отправляем запрос к БД в формате адаптера. В этом случае Apis
			$responseArr = $db->get($this->resource);

			$this->site = null;
			if(isset($responseArr["body"]["items"]["0"]["item"])) {
			    if ($responseArr != null) {
			        $this->site = $responseArr["body"]["items"]["0"]["item"];
			    }
			}
			if ($cache->state() == 1) {
			    $cache->set($this->site);
			}
			return $this->site;

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
 