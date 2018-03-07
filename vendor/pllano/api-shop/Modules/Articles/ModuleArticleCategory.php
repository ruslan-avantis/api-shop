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
use Pllano\Interfaces\ModuleInterface;
use Pllano\Core\Module;

class ModuleArticleCategory extends Module implements ModuleInterface
{

    public function __construct(Container $app, $route = null, $block = null, $modulKey = null, $modulVal = [])
    {
		parent::__construct($app, $route, $block, $modulKey, $modulVal);
		$this->connectContainer();
		$this->connectDatabases();
        $this->_table = 'article_category';
        $this->_idField = 'article_category_id';
    }

    public function get(Request $request)
    {
        // Получаем alias из url
        if ($request->getAttribute('alias')) {
            $alias = clean($request->getAttribute('alias'));
        } else {
            $alias = null;
        }

        $content = [];
        $contentArr = [];
        $heads = [];
        $return = [];
		$moduleArr = [];

        // Конфигурация пакета
        $moduleArr['config'] = $this->modulVal;

        $responseArr = [];
        // Отдаем роутеру RouterDb конфигурацию
        $this->routerDb->setConfig([], 'Apis');
        // Пингуем для ресурса указанную и доступную базу данных
        $this->_database = $this->routerDb->ping($this->_table);
        // Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
        $this->db = $this->routerDb->run($this->_database);
        // Массив c запросом
        $query = [
            "alias" => $alias,
			"state" => 1
        ];
        // Отправляем запрос к БД в формате адаптера. В этом случае Apis
        $responseArr = $this->db->get($this->_table, $query);

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

    public function post(Request $request)
    {
		return null;
	}

}
 