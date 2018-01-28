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
 
namespace ApiShop\Admin;
 
use ApiShop\Config\Settings;
 
use RouterDb\Db;
use RouterDb\Router;
 
class Index {
 
    private $config;
 
    function __construct()
    {
        // Подключаем конфиг Settings\Config
        $config = (new Settings())->get();
        $this->config = $config;
    }
 
    public function get()
    {
        // Получаем список виджетов для вывода на главную
        $resource_list = explode(',', str_replace(array('"', "'", " "), '', $this->config['admin']['index_widget']));
        foreach($resource_list as $resource)
        {
            if($resource == 'templates'){
                $resp["templates"] = array();
                $templates = array();
                $directory = $this->config["settings"]["themes"]["dir"]."/".$this->config["settings"]["themes"]["templates"];
                $scanned = array_diff(scandir($directory), array('..', '.'));
                if (count($scanned) >= 1) {
                    foreach($scanned as $dir)
                    {
                        if (is_dir($directory.'/'.$dir)) {
                        $json_dir = $this->config["settings"]["themes"]["dir"].'/'.$this->config["settings"]["themes"]["templates"].'/'.$dir.'/config/';
                            if (file_exists($json_dir."config.json")) {
                                $json = json_decode(file_get_contents($json_dir."config.json"), true);
                                $template = $json;
                                $templates["alias"] = $template["alias"];
                                $templates["name"] = $template["name"];
                                $templates["dir"] = $dir;
                                $templates["version"] = $template["version"];
                                $templates["url"] = $template["url"];
                                if(isset($template["demo"])){
                                    $templates["demo"] = $template["demo"];
                                } else {
                                    $templates["demo"] = "https://".$dir.".pllano.com/";
                                }
                                $resp["templates"][] = $templates;
                            }
                        }
                   }
                }
            } else {
                // Отдаем роутеру RouterDb конфигурацию.
                $router = new Router($this->config);
                // Получаем название базы для указанного ресурса
                $name_db = $router->ping($resource);
                // Подключаемся к базе
                $db = new Db($name_db, $this->config);
                // Получаем массив
                $resourceGet = $db->get($resource, [
                    "offset" => 0,
                    "limit" => 5,
                    "sort" => "id",
                    "order" => "DESC"
                ]);
                // Убираем body items
                $resp[$resource] = $resourceGet['body']['items'];
            }
        }

        return $resp;

    }
 
}
 