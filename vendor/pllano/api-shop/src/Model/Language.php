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
use Pllano\RouterDb\Router as RouterDb;
use Pllano\Caching\Cache;
 
class Language
{
    private $language = "en";
    private $resource = "language";
    private $config;
	private $session;
    protected $request;
    private $cache_lifetime = 30*24*60*60;
 
    function __construct(array $config = [], $session)
    {
        $this->config = $config;
		$this->session = $session;
    }
 
    // Ресурс language доступен только на чтение
    public function get(Request $request)
    {
        $session = $this->session;
		$this->request = $request;
        $getParams = $this->request->getQueryParams();

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

        $host = $this->request->getUri()->getHost();
        $cache = new Cache($this->config);
        $cache_run = $cache->run($host.'/'.$this->resource.'/'.$this->language, $this->cache_lifetime);
        if ($cache_run === null) {
			
            // Отдаем роутеру RouterDb конфигурацию
            $routerDb = new RouterDb($this->config, 'APIS');
            // Пингуем для ресурса указанную и доступную базу данных
            // Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
            $db = $routerDb->run($routerDb->ping($this->resource));
            // Отправляем запрос к БД в формате адаптера. В этом случае Apis
            $responseArr = $db->get($this->resource);

            if ($responseArr != null) {
                foreach($responseArr['body']['items'] as $value)
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
 