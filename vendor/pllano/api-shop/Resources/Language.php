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
 
namespace ApiShop\Resources;
 
use Slim\Http\Request;
use Slim\Http\Response;
use RouterDb\Db;
use RouterDb\Router;
use ApiShop\Hooks\Hook;
use ApiShop\Adapter\Cache;
use ApiShop\Config\Settings;
 
class Language {

    private $db_name;
    private $language = "en";
    private $resource = "language";
    private $config;
    private $request;
 
    function __construct(Request $request, $config)
    {
        $this->request = $request;
        $this->config = $config;
        
        $getParams = $request->getQueryParams();
        // Подключаем сессию, берет название класса из конфигурации
        $session = new $this->config['vendor']['session']($this->config['settings']['session']['name']);
 
        // Подключаем определение языка в браузере
        $langs = new $this->config['vendor']['language_detector']();
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
 
        // Отдаем роутеру RouterDb конфигурацию.
        $router = new Router($this->config);
        // Получаем название базы для указанного ресурса
        $this->db_name = $router->ping($this->resource);
    }
 
    // Ресурс language доступен только на чтение
    public function get()
    {
        $cache = new Cache($this->config);
        if ($cache->run('language/'.$this->language, 30*24*60*60) === null) {
            // Подключаемся к базе
            $db = new Db($this->db_name, $this->config);
            // Отправляем запрос и получаем данные
            $resp = $db->get($this->resource);
            if ($resp != null) {
                foreach($resp['body']['items'] as $value)
                {
                    $array = (array)$value['item'];
                    $arr[$array["id"]] = $array[$this->language];
                }
                if ($cache->state() == '1') {
                    $cache->set($arr);
                }
 
                return $arr;
 
            } else {
                return null;
            }
        } else {
            return $cache->get();
        }
 
    }
    
    public function lang()
    {
        return $this->language;
    }
 
}
 