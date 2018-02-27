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
 
class Install {
 
    private $config;
 
    function __construct($config)
    {
        $this->config = $config;
    }

    public function stores_list()
    {
        // Ресурс к которому обращаемся
        $resource = "stores_list";

        // Отдаем роутеру RouterDb конфигурацию
        $routerDb = new RouterDb($this->config, 'APIS');
        // Пингуем для ресурса указанную и доступную базу данных
        // Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
        $db = $routerDb->run($routerDb->ping($resource));
        // Отправляем запрос к БД в формате адаптера. В этом случае Apis
        $responseArr = $db->get($resource);

        return $responseArr["body"]["items"];
    }
 
    public function templates_list($store = null)
    {
        // Ресурс к которому обращаемся
        $resource = "templates_list";
        // Отдаем роутеру RouterDb конфигурацию
        $routerDb = new RouterDb($this->config, 'APIS');
        // Пингуем для ресурса указанную и доступную базу данных
        // Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
        $db = $routerDb->run($routerDb->ping($resource));
        // Отправляем запрос к БД в формате адаптера. В этом случае Apis
        $responseArr = $db->get($resource);

        if (isset($store)) {
			$responseArr = $db->get($resource, ["store_id" => $store]);
        } else {
            $responseArr = $db->get($resource);
        }
        return $responseArr["body"]["items"];
    }
 
}
 