<?php 
/**
 * Pllano {API}$hop (https://pllano.com)
 *
 * @link https://github.com/pllano/api-shop
 * @version 1.2.1
 * @copyright Copyright (c) 2017-2018 PLLANO
 * @license http://opensource.org/licenses/MIT (MIT License)
 */
namespace Pllano\ApiShop\Modules\Account;

use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};
use Psr\Container\ContainerInterface as Container;
use Pllano\Core\Interfaces\ModuleInterface;
use Pllano\Core\Module;

class ModuleLogout extends Module implements ModuleInterface
{
	
    public function get(Request $request)
    {
		return null;
	}

    public function post(Request $request)
    {
        $session_name = $this->config['settings']['session']['name'];
		// Подключаем сессию
        $session = $this->app->get('session');
        $session->authorize = null;
        $session->post_id = null;
        $session->cookie = '';
        unset($session->authorize); // удаляем сесию
        unset($session->id); // удаляем сесию
        unset($session->cookie); // удаляем сесию
        $session->destroy();
		
		clean_cookie($session_name, 60*60*24*365);

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
 