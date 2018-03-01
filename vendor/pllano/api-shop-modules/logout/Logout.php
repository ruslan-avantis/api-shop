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

namespace Pllano\ApiShop\Modules\Account;
 
use Psr\Http\Message\ServerRequestInterface as Request;
 
class Logout
{
    private $config;
 
    function __construct($config = [], $package = [], $template = [], $module, $block, $route, $lang = null, $language = null)
    {
        $this->config = $config;
    }
 
    public function post(Request $request)
    {
        $config = $this->config;
        // Подключаем сессию
        $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
        $session->authorize = null;
        $session->post_id = null;
        $session->cookie = '';
        unset($session->authorize); // удаляем сесию
        unset($session->id); // удаляем сесию
        unset($session->cookie); // удаляем сесию
        $session->destroy();
 
        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
        if ($config['settings']['site']['cookie_httponly'] == '1'){
            setcookie($config['settings']['session']['name'], null, time() - ( 3600 * 24 * 31 ), '/', $domain, 1, true);
        } else {
            setcookie($config['settings']['session']['name'], null, time() - ( 3600 * 24 * 31 ), '/', $domain);
        }
 
        $callbackStatus = 200;
        $callbackTitle = 'Информация';
        $callbackText = 'Вы вышли из системы';
        $callback = ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
 
        return $callback;
    }
    
}
 
