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
    
    public function post(Request $request)
    {
        // Подключаем сессию
        $session = $app->get('session');
        // Получаем данные отправленные нам через POST
        $post = $request->getParsedBody();
        // Читаем ключи
        $cookie_key = $this->config['key']['cookie'];
        // Чистим данные на всякий случай пришедшие через POST
        $post_email = filter_var($post['email'], FILTER_SANITIZE_STRING);
        $post_phone = filter_var($post['phone'], FILTER_SANITIZE_STRING);
        $post_password = filter_var($post['password'], FILTER_SANITIZE_STRING);
        $post_iname = filter_var($post['iname'], FILTER_SANITIZE_STRING);
        $post_fname = filter_var($post['fname'], FILTER_SANITIZE_STRING);

        $email = clean($post_email);
        $new_phone = phone_clean($post_phone);
        $password = clean($post_password);
        $iname = clean($post_iname);
        $fname = clean($post_fname);

        $callbackStatus = 400;
        $callbackTitle = 'Соообщение системы';
        $callbackText = '';

		$pattern = "/^[\+0-9\-\(\)\s]*$/";

        if(preg_match($pattern, $new_phone)) {
            $phone = $new_phone;
            if(!empty($phone) && !empty($email) && !empty($iname) && !empty($fname)) {
                $email_validate = filter_var($email, FILTER_VALIDATE_EMAIL);
                if(check_length($phone, 8, 25) && $email_validate) {
                    // Проверяем наличие пользователя
                    $user_search = (new User())->getEmailPhone($email, $phone);
                    if ($user_search == null) {
                        // Чистим сессию на всякий случай
                        //$session->clear();
                        // Создаем новую cookie
                        $cookie = random_token();
                        // Генерируем identificator
						$crypt = $this->config['vendor']['crypto']['crypt'];
                        $identificator = $crypt::encrypt($cookie, $cookie_key);
                        // Записываем пользователю новый cookie
                        if ($this->config['settings']['site']['cookie_httponly'] == '1'){
                            setcookie($this->config['settings']['session']['name'], $identificator, time()+60*60*24*365, '/', domain(), 1, true);
                        } else {
                            setcookie($this->config['settings']['session']['name'], $identificator, time()+60*60*24*365, '/', domain());
                        }
                        // Пишем в сессию identificator cookie
 
                        $query["role_id"] = 1;
                        $query["password"] = password_hash($password, PASSWORD_DEFAULT);
                        $query["phone"] = strval($phone);
                        $query["email"] = $email;
                        $query["language"] = $session->language;
                        $query["ticketed"] = 1;
                        $query["admin_access"] = 0;
                        $query["iname"] = $iname;
                        $query["fname"] = $fname;
                        $query["cookie"] = $cookie;
                        $query["created"] = $today;
                        $query["authorized"] = $today;
                        $query["alias"] = random_alias_id();
                        $query["state"] = 1;
                        $query["score"] = 1;
 
                        // Ресурс (таблица) к которому обращаемся
                        $resource = "user";
						// Отдаем роутеру RouterDb конфигурацию
                        $routerDb = new RouterDb($this->config, 'Apis');
                        // Пингуем для ресурса указанную и доступную базу данных
                        // Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
                        $db = $routerDb->run($routerDb->ping($resource));
                        // Отправляем запрос к БД в формате адаптера. В этом случае Apis
                        $user_id = $db->post($resource, $query);

                        if ($user_id >= 1) {
                            // Обновляем данные в сессии
                            $session->authorize = 1;
                            $session->cookie = $identificator;
                            $session->user_id = $user_id;
                            $session->phone = $crypt::encrypt($phone, $session_key);
                            $session->email = $crypt::encrypt($email, $session_key);
                            $session->iname = $crypt::encrypt($iname, $session_key);
                            $session->fname = $crypt::encrypt($fname, $session_key);
 
                            $callbackStatus = 200;
                        } else {
                            $callbackText = 'Ошибка';
                        }
                    } else {
                        $callbackText = 'Пользователь уже существует';
                    }
                } else {
                    $callbackText = 'Введите правильные данные !';
                }
            } else {
                $callbackText = 'Заполните пустые поля';
            }
        } else {
            $callbackText = 'Номер телефона не валиден';
        }
        $callback = [
		    'status' => $callbackStatus,
			'title' => $callbackTitle,
			'text' => $callbackText
		];
        return $callback;
    }

}
 