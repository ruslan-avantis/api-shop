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

namespace ApiShop\Modules;
 
use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};
 
class Blank
{
    private $config;
    private $package;
    private $template;
    private $route;
    private $block;
    private $module;
    private $lang = null;
    private $language = null;
 
    function __construct($config = [], $package = [], $template = [], $module, $block, $route, $lang = null, $language = null)
    {
        $this->config = $config;
        $this->package = $package;
        $this->template = $template;
        $this->route = $route;
        $this->block = $block;
        $this->module = $module;
        if (isset($lang)) {
            $this->lang = $lang;
        }
        if (isset($language)) {
            $this->language = $language;
        }
    }
 
    public function get(Request $request, Response $response, array $args)
    {
        // Конфигурация пакета
        $moduleArr['config'] = $this->config['modules'][$this->route][$this->module];
        //$moduleArr = $template['modules'][$this->route][$this->module];
        $layout['content']['modules'][$this->module]['content']['layout'] = $moduleArr['config']['view'];
        $content['content']['modules'][$this->module] = $moduleArr;
        $return = array_replace_recursive($layout, $content);
        return $return;
    }
 
    public function post(Request $request, Response $response, array $args)
    {
        $callback = ['status' => 404, 'title' => '', 'text' => ''];
        // Выводим заголовки
        $response->withStatus(404);
        $response->withHeader('Content-type', 'application/json');
        // Выводим json
        echo json_encode($callback);
    }
 
}
 