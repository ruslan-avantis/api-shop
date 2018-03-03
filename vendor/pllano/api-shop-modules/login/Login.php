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
use Pllano\RouterDb\Router as RouterDb;
use Pllano\ApiShop\Models\User;

class Login
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

    public function post(Request $request): array
    {
        // Получаем данные отправленные нам через POST
        $post = $request->getParsedBody();
        $callbackStatus = 400;
        $callbackTitle = 'Соообщение системы';
        $callbackText = '';

		//$post['email'] = 'admin@pllano.com';
		//$post['phone'] = '380670010011';
		//$post['password'] = 'admin@pllano.com';

        if(!empty($post['email']) && !empty($post['phone']) && !empty($post['password'])) {
            if(check_phone($post['phone']) && validate_email(sanitize($post['email']))) {
                $phone = sanitize($post['phone']);

                $user = new User($this->app);
                //check for correct email and password
                $user_id = $user->checkLogin(sanitize($post['email']), $phone, sanitize($post['password']));

                if ($user_id != 0) {

                    $cookie = $user->putUserCode($user_id);
                    if($cookie == 1) {

                        // Ресурс (таблица) к которому обращаемся
                        $resource = "user";
                        // Отдаем роутеру RouterDb конфигурацию
                        $routerDb = new RouterDb($this->config, 'Apis');
                        // Пингуем для ресурса указанную и доступную базу данных
                        // Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
                        $db = $routerDb->run($routerDb->ping($resource));
                        // Отправляем запрос к БД в формате адаптера. В этом случае Apis
                        $respArr = $db->get($resource, [], $user_id);

                        if (isset($respArr["headers"]["code"]) && (int)$respArr["headers"]["code"] == 200) {

							// Подключаем сессию
                            $session = $this->app->get('session');

                            if(is_object($respArr["body"]["items"]["0"]["item"])) {
                                $user = (array)$respArr["body"]["items"]["0"]["item"];
                            } elseif (is_array($respArr["body"]["items"]["0"]["item"])) {
                                $user = $respArr["body"]["items"]["0"]["item"];
                            }

                            if ($user["state"] == 1) {

                                // Читаем ключи
                                $session_key = $this->config['key']['session'];
								$crypt = $this->config['vendor']['crypto']['crypt'];

                                $session->authorize = 1;
                                $session->role_id = $user["role_id"];
                                if($session->role_id == 100) {
                                    $session->admin_uri = random_alias_id();
                                }
                                $session->user_id = $user["id"];
                                $session->iname = $crypt::encrypt($user["iname"], $session_key);
                                $session->fname = $crypt::encrypt($user["fname"], $session_key);
                                $session->phone = $crypt::encrypt($user["phone"], $session_key);
                                $session->email = $crypt::encrypt($user["email"], $session_key);

                                $callbackStatus = 200;

                            } else {
                                $session->authorize = null;
                                $session->role_id = null;
                                $session->user_id = null;
                                unset($session->authorize); // удаляем authorize
                                unset($session->role_id); // удаляем role_id
                                unset($session->user_id); // удаляем role_id
                                $callbackText = 'Ваш аккаунт заблокирован';
                            }
						}
					} else {
						$callbackText = 'Ошибка cookie';
					}
				} else {
					$callbackText = 'Login failed. Incorrect credentials';
				}
			} else {
				$callbackText = 'Данные не прошли валидацию !';
			}
		} else {
			$callbackText = 'Заполните пустые поля';
		}

		$callback = [
		    'status' => $callbackStatus,
		    'title' => $callbackTitle,
		    'text' => $callbackText
		];
		return $callback;
	}

}
 