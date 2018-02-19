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
 
use Psr\Http\Message\ServerRequestInterface as Request;
use Pllano\RouterDb\Db;
use Pllano\RouterDb\Router;
use Pllano\Caching\Cache;
 
class Language
{
    private $language = "en";
    private $resource = "language";
    private $config;
    protected $request;
    private $cache_lifetime = 30*24*60*60;
 
    function __construct(Request $request, $config)
    {
        $this->config = $config;
        $this->request = $request;
        $getParams = $this->request->getQueryParams();
        
        // Подключаем сессию, берет название класса из конфигурации
        $session = new $this->config['vendor']['session']['session']($this->config['settings']['session']['name']);
 
        // Подключаем определение языка в браузере
        $langs = new $this->config['vendor']['detector']['language']();
        // Получаем массив данных из таблицы language на языке из $session->language
        if (isset($getParams['lang'])) {
            if ($getParams['lang'] == "ru" || $getParams['lang'] == "ua" || $getParams['lang'] == "en" || $getParams['lang'] == "de") {
                $this->language = $getParams['lang'];
                $session->language = $getParams['lang'];
            } elseif (isset($session->language)) {
                $this->language = $session->language;
            } else {
                $this->language = $langs->getLanguage();
            }
        } elseif (isset($session->language)) {
            $this->language = $session->language;
        } else {
            $this->language = $langs->getLanguage();
        }
    }
 
    // Ресурс language доступен только на чтение
    public function get()
    {
        $host = $this->request->getUri()->getHost();
        $cache = new Cache($this->config);
        $cache_run = $cache->run($host.'/'.$this->resource.'/'.$this->language, $this->cache_lifetime);
        if ($cache_run === null) {
            // Отдаем роутеру RouterDb конфигурацию.
            $router = new Router($this->config);
            // Получаем название базы для указанного ресурса
            $db_name = $router->ping($this->resource);
            // Подключаемся к базе
            $db = new Db($db_name, $this->config);
            // Отправляем запрос и получаем данные
            $resp = $db->get($this->resource);
            if ($resp != null) {
                foreach($resp['body']['items'] as $value)
                {
                    $array = (array)$value['item'];
                    $arr[$array["id"]] = $array[$this->language];
                }
                if ($cache->state() == 1) {
                    $cache->set($arr);
                }
 
                return $arr;
 
            } else {
                return $cache->get();
            }
        } else {
            return $cache->get();
        }
 
    }
    
    public function lang()
    {
        return $this->language;
    }
 
    public function setResource($resource)
    {
        $this->resource = $resource;
    }
 
    public function setLanguage($language)
    {
        $this->language = $language;
    }
 
    public function cache_lifetime($cache_lifetime)
    {
        $this->cache_lifetime = $cache_lifetime;
    }
 
}
 