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

namespace Pllano\ApiShop\Modules\Blank;

use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};
use Pllano\Interfaces\ModuleInterface;
use Pllano\Core\Module;

class ModuleBlank extends Module implements ModuleInterface
{

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
 