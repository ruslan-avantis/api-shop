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

namespace Pllano\ApiShop\Modules\Breadcrumbs;

use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};
use Psr\Container\ContainerInterface as Container;
use Pllano\Core\Interfaces\ModuleInterface;
use Pllano\Core\Module;

class ModuleBreadcrumbs extends Module implements ModuleInterface
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
        // Конфигурация пакета
        $moduleArr['config'] = $this->config['modules'][$this->route][$this->module];
        $layout['content']['modules'][$this->module]['content']['layout'] = $moduleArr['config']['view'];
        $content['content']['modules'][$this->module] = $moduleArr;
        $return = array_replace_recursive($layout, $content);
        return $return;
    }

    public function post(Request $request)
    {
		return null;
	}

}
 