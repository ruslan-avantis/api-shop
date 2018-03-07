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
use Pllano\Core\Models\User;

class ModuleCheckIn extends Module implements ModuleInterface
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
        // Читаем ключи
        $cookie_key = $this->config['key']['cookie'];
        // Чистим данные на всякий случай пришедшие через POST
        $post_email = sanitize($post['email']);
        $post_phone = sanitize($post['phone']);
        $post_password = sanitize($post['password']);
        $post_iname = sanitize($post['iname']);
        $post_fname = sanitize($post['fname']);

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
                        //$this->session->clear();
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
                        $query["language"] = $this->session->language;
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

                        $responseArr = [];
                        // Отдаем роутеру RouterDb конфигурацию
                        $this->routerDb->setConfig([], 'Apis');
                        // Пингуем для ресурса указанную и доступную базу данных
                        $this->_database = $this->routerDb->ping($this->_table);
                        // Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
                        $this->db = $this->routerDb->run($this->_database);
                        // Отправляем запрос к БД в формате адаптера. В этом случае Apis
                        $user_id = $this->db->post($this->_table, $query);

                        if ($user_id >= 1) {
                            // Обновляем данные в сессии
                            $this->session->authorize = 1;
                            $this->session->cookie = $identificator;
                            $this->session->user_id = $user_id;
                            $this->session->phone = $crypt::encrypt($phone, $session_key);
                            $this->session->email = $crypt::encrypt($email, $session_key);
                            $this->session->iname = $crypt::encrypt($iname, $session_key);
                            $this->session->fname = $crypt::encrypt($fname, $session_key);
 
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
 