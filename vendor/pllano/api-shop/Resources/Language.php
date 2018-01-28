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
 
use RouterDb\Db;
use RouterDb\Router;
 
use ApiShop\Config\Settings;
 
class Language {

    private $db_name;
    private $language = "en";
    private $resource = "language";
    private $config;
 
    function __construct(array $getParams = array())
    {
        // Подключаем конфиг Settings\Config
        $config = (new Settings())->get();
        $this->config = $config;
 
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
        // Подключаемся к базе
        $db = new Db($this->db_name, $this->config);
        // Отправляем запрос и получаем данные
        $response = $db->get($this->resource);
 
        if ($response != null) {
            foreach($response['body']['items'] as $value)
            {
                $array = (array)$value['item'];
                $arr[$array["id"]] = $array[$this->language];
            }
            return $arr;
        } else {
            return null;
        }
    }
 
}
 