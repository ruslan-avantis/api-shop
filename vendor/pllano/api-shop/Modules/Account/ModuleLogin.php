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
use Pllano\Interfaces\ModuleInterface;
use Pllano\Core\Module;
use Pllano\Core\Models\ModelUser;

class ModuleLogin extends Module implements ModuleInterface
{

    public function __construct(Container $app, $route = null, $block = null, $modulKey = null, $modulVal = [])
    {
		parent::__construct($app, $route, $block, $modulKey, $modulVal);
		$this->connectContainer();
		$this->connectDatabases();
        $this->_table = 'user';
        //$this->_idField = 'id';
    }

    public function get(Request $request)
    {
		return null;
	}

    public function post(Request $request)
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

                $user = new ModelUser($this->app);
                //check for correct email and password
                $user_id = $user->checkLogin(sanitize($post['email']), $phone, sanitize($post['password']));

                if ($user_id != 0) {

                    $cookie = $user->putUserCode($user_id);
                    if($cookie == 1) {

                        $responseArr = [];
                        // Отдаем роутеру RouterDb конфигурацию
                        $this->routerDb->setConfig([], 'Pllano', 'Apis');
                        // Пингуем для ресурса указанную и доступную базу данных
                        $this->_database = $this->routerDb->ping($this->_table);
                        // Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
                        $this->db = $this->routerDb->run($this->_database);
                        // Отправляем запрос к БД в формате адаптера. В этом случае Apis
                        $responseArr = $this->db->get($this->_table, [], $user_id);

                        // Подключаем сессию
                        $session = $this->app->get('session');

                        if(is_object($responseArr["0"])) {
                            $user = (array)$responseArr["0"];
                        } elseif (is_array($responseArr["0"])) {
                            $user = $responseArr["0"];
                        }

                        if ($user["state"] == 1) {

                            // Читаем ключи
                            $session_key = $this->config['key']['session'];
                            $crypt = $this->config['vendor']['crypto']['crypt'];

                            $this->session->authorize = 1;
                            $this->session->role_id = $user["role_id"];
                            if($this->session->role_id == 100) {
                                $this->session->admin_uri = random_alias_id();
                            }
                            $this->session->user_id = $user["id"];
                            $this->session->iname = $crypt::encrypt($user["iname"], $session_key);
                            $this->session->fname = $crypt::encrypt($user["fname"], $session_key);
                            $this->session->phone = $crypt::encrypt($user["phone"], $session_key);
                            $this->session->email = $crypt::encrypt($user["email"], $session_key);

                            $callbackStatus = 200;

                        } else {
                            $this->session->authorize = null;
                            $this->session->role_id = null;
                            $this->session->user_id = null;
                            unset($this->session->authorize); // удаляем authorize
                            unset($this->session->role_id); // удаляем role_id
                            unset($this->session->user_id); // удаляем role_id
                            $callbackText = 'Ваш аккаунт заблокирован';
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
 