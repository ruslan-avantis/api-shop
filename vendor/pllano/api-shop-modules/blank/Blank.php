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

namespace Pllano\ApiShop\Modules;

use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};
use Psr\Container\ContainerInterface as Container;

class Blank
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
        // Конфигурация пакета
        $moduleArr['config'] = $this->modulVal;
        $layout['content']['modules'][$this->modulKey]['content']['layout'] = $this->modulVal['view'];
        $content['content']['modules'][$this->modulKey] = $moduleArr;
        $return = array_replace_recursive($layout, $content);
        return $return;
    }

    public function post(Request $request)
    {
        $callback = ['status' => 404, 'title' => '', 'text' => ''];
        // Выводим json
        return json_encode($callback);
    }

}
 