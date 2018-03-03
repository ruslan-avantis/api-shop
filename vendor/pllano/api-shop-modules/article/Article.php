<?php /**
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

namespace Pllano\ApiShop\Modules\Articles;

use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};
use Psr\Container\ContainerInterface as Container;
use Pllano\RouterDb\Router as RouterDb;

class Article
{

    private $app;
	private $block;
    private $route;
    private $modulKey;
	private $modulVal;
	private $config;
    
    function __construct(Container $app, $route = null, $block = null, $modulKey = null, $modulVal = [])
    {
        $this->app = $app;
        $this->block = $block;
        $this->route = $route;
		$this->modulKey = $modulKey;
		$this->modulVal = $modulVal;
		$this->config = $app->get('config');
    }
    
    public function get(Request $request)
    {
        $host = $request->getUri()->getHost();
        // Получаем alias из url
        if ($request->getAttribute('alias')) {
            $alias = clean($request->getAttribute('alias'));
        } else {
            $alias = null;
        }
        // Конфигурация пакета
        $moduleArr['config'] = $this->modulVal;

        // Ресурс (таблица) к которому обращаемся
        $resource = "article";
        // Отдаем роутеру RouterDb конфигурацию
        $routerDb = new RouterDb($this->config, 'Apis');
        // Пингуем для ресурса указанную и доступную базу данных
        // Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
        $db = $routerDb->run($routerDb->ping($resource));
        // Массив для запроса
        $query = [
            "alias" => $alias
        ];
        // Отправляем запрос к БД в формате адаптера. В этом случае Apis
        $responseArr = $db->get($resource, $query);

        $content = [];
        $contentArr = [];
        $heads = [];
        $return = [];
 
        if (isset($responseArr["headers"]["code"])) {
            if ($responseArr["headers"]["code"] == 200 || $responseArr["headers"]["code"] == "200") {
                // Если данные в виде объекта переводим в массив
                if(is_object($responseArr["body"]["items"]["0"]["item"])) {
                    $arr = (array)$responseArr["body"]["items"]["0"]["item"];
                } elseif (is_array($responseArr["body"]["items"]["0"]["item"])) {
                    $arr = $responseArr["body"]["items"]["0"]["item"];
                }
 
                $arr["text"] = htmlspecialchars_decode($arr["text"]);
                $arr["text_ru"] = htmlspecialchars_decode($arr["text_ru"]);
                $arr["text_ua"] = htmlspecialchars_decode($arr["text_ua"]);
                $arr["text_en"] = htmlspecialchars_decode($arr["text_en"]);
                $arr["text_de"] = htmlspecialchars_decode($arr["text_de"]);
                $contentArr['content'] = $arr;

                $head["title"] = $arr["title"];
                $head["seo_title"] = $arr["seo_title"];
                $head["seo_keywords"] = $arr["seo_keywords"];
                $head["seo_description"] = $arr["seo_description"];
                $head["og_url"] = $arr["og_url"];
                $head["og_title"] = $arr["og_title"];
                $head["og_description"] = $arr["og_description"];
                $heads['head'] = $head;

            }
        }
 
        $content['content']['modules'][$this->modulKey] = $contentArr + $moduleArr;
        $return = array_replace_recursive($heads, $content);
        return $return;
    }
    
}
 