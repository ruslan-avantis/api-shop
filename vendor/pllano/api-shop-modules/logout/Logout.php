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
 
use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};
use Psr\Container\ContainerInterface as Container;
 
class Logout
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

    public function post(Request $request)
    {
        // Подключаем сессию
        $session = $this->app->get('session');

        $session->authorize = null;
        $session->post_id = null;
        $session->cookie = '';
        unset($session->authorize); // удаляем сесию
        unset($session->id); // удаляем сесию
        unset($session->cookie); // удаляем сесию
        $session->destroy();

        if ($this->config['settings']['site']['cookie_httponly'] == '1'){
            setcookie($this->config['settings']['session']['name'], null, time() - ( 3600 * 24 * 31 ), '/', domain(), 1, true);
        } else {
            setcookie($this->config['settings']['session']['name'], null, time() - ( 3600 * 24 * 31 ), '/', domain());
        }

        $callbackStatus = 200;
        $callbackTitle = 'Информация';
        $callbackText = 'Вы вышли из системы';

		$callback = [
		    'status' => $callbackStatus,
			'title' => $callbackTitle,
			'text' => $callbackText
		];
        return $callback;
    }

}
 