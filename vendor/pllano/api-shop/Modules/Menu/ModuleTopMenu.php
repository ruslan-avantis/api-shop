<?php 
/**
 * Pllano {API}$hop (https://pllano.com)
 *
 * @link https://github.com/pllano/api-shop
 * @version 1.2.1
 * @copyright Copyright (c) 2017-2018 PLLANO
 * @license http://opensource.org/licenses/MIT (MIT License)
 */
namespace Pllano\ApiShop\Modules\Menu;

use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};
use Psr\Container\ContainerInterface as Container;
use Pllano\Core\Interfaces\ModuleInterface;
use Pllano\Core\Module;

class ModuleTopMenu extends Module implements ModuleInterface
{

    public function __construct(Container $app, string $route = null, string $block = null, string $modulKey = null, array $modulVal = [])
    {
		parent::__construct($app, $route, $block, $modulKey, $modulVal);
		//$this->connectContainer();
		$this->connectDatabases();
        $this->_table = 'category';
        $this->_idField = 'category_id';
    }
	
    public function get(Request $request)
    {
        $return = [];
		// Конфигурация пакета
        $moduleArr = [];
        $moduleArr['config'] = $this->modulVal;

        $menuArr = [];
        // Отдаем роутеру RouterDb конфигурацию
        $this->routerDb->setConfig([], 'Apis');
        // Пингуем для ресурса указанную и доступную базу данных
        $this->_database = $this->routerDb->ping($this->_table);
        // Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
        $this->db = $this->routerDb->run($this->_database);
        // Массив c запросом
        $query = ["menu" => 1, "state" => 1, "sort" => "sort", "order" => "ASC", "offset" => 0, "limit" => 5];
        // Отправляем запрос к БД в формате адаптера. В этом случае Apis
        $menuArr = $this->db->get($this->_table, $query);

        if (isset($menuArr['headers']['code']) && (int)$menuArr['headers']['code'] == 200) {
            $resp = [];
            $item = [];
            $submenu = [];
            foreach($menuArr['body']['items'] as $value)
            {
                $item['submenu'] = null;
				$val = $value['item'];
                if ($val->parent_id == 0) {
                    $item['alias'] = $val->alias;
                    $item['title'] = $val->title;
                    $item['title_ru'] = $val->title_ru;
                    $item['title_ua'] = $val->title_ua;
                    $item['title_en'] = $val->title_en;
                    $item['title_de'] = $val->title_de;
                    $item['url'] = $val->url;

					$parent = $this->db->get($this->_table, ["menu" => 1, "state" => 1, "parent_id" => $val->id]);
                    
                    if (isset($parent['headers']['code']) && (int)$parent['headers']['code'] == 200) {
						$submenu['subsubmenu'] = null;
						
						foreach($parent['body']['items'] as $subvalue)
						{
							$sub = $subvalue['item'];
							$submenu['alias'] = $sub->alias;
							$submenu['title'] = $sub->title;
							$submenu['title_ru'] = $sub->title_ru;
							$submenu['title_ua'] = $sub->title_ua;
							$submenu['title_en'] = $sub->title_en;
							$submenu['title_de'] = $sub->title_de;
							$submenu['url'] = $sub->url;
							
							$subparent = $this->db->get($this->_table, ["menu" => 1, "state" => 1, "parent_id" => $sub->id]);
							if (isset($subparent['headers']['code']) && (int)$subparent['headers']['code'] == 200) {
								$submenu['subsubmenu'] = [];
								$subsubmenu = [];
								foreach($subparent['body']['items'] as $subsubvalue)
								{
									$subsub = $subsubvalue['item'];
									$subsubmenu['alias'] = $subsub->alias;
									$subsubmenu['title'] = $subsub->title;
									$subsubmenu['title_ru'] = $subsub->title_ru;
									$subsubmenu['title_ua'] = $subsub->title_ua;
									$subsubmenu['title_en'] = $subsub->title_en;
									$subsubmenu['title_de'] = $subsub->title_de;
									$subsubmenu['url'] = $subsub->url;
									$submenu['subsubmenu'][] = $subsubmenu;
								}
							}
							$item['submenu'][] = $submenu;
						}
					}
                    $resp[] = $item;
				}
			}
            $content['content'] = $resp;
            $return['content']['modules'][$this->modulKey] = array_replace_recursive($content, $moduleArr);
		}
        return $return;
	}
	
	public function post(Request $request)
    {
		return null;
	}
}
 