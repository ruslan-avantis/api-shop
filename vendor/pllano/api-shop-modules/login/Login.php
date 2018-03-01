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
use Pllano\RouterDb\Router as RouterDb;
use Pllano\ApiShop\Utilities\Utility;
use Pllano\ApiShop\Models\User;

class Login
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
        // Получаем данные отправленные нам через POST
        $post = $request->getParsedBody();
        // Подключаем утилиты
        $utility = new Utility();
        
        // Читаем ключи
        $session_key = $config['key']['session'];
 
        $callbackStatus = 400;
        $callbackTitle = 'Соообщение системы';
        $callbackText = '';
 
        // Чистим данные на всякий случай пришедшие через POST
        $email = filter_var($utility->clean($post['email']), FILTER_SANITIZE_STRING);
        $new_phone = filter_var($utility->phone_clean($post['phone']), FILTER_SANITIZE_STRING);
        $password = filter_var($utility->clean($post['password']), FILTER_SANITIZE_STRING);
 
        $pattern = "/^[\+0-9\-\(\)\s]*$/";
        if(preg_match($pattern, $new_phone)) {
            $phone = $new_phone;
            
            if(!empty($phone) && !empty($email)) {
                $email_validate = filter_var($email, FILTER_VALIDATE_EMAIL);
                if($utility->check_length($phone, 8, 25) && $email_validate) {
                    $user = new User();
                    //check for correct email and password
                    $user_id = $user->checkLogin($email, $phone, $password);
                    if ($user_id != 0) {
                        $cookie = $user->putUserCode($user_id);
                        if($cookie == 1) {
                            // Ресурс (таблица) к которому обращаемся
                            $resource = "user";
							
                            // Отдаем роутеру RouterDb конфигурацию
                            $routerDb = new RouterDb($config, 'Apis');
                            // Пингуем для ресурса указанную и доступную базу данных
                            // Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
                            $db = $routerDb->run($routerDb->ping($resource));
                            // Отправляем запрос к БД в формате адаптера. В этом случае Apis
                            $responseArr = $db->get($resource, [], $user_id);

                            //print("<br>");
                            //print_r($responseArr);
                            if (isset($responseArr["headers"]["code"])) {
                                if ($responseArr["headers"]["code"] == 200 || $responseArr["headers"]["code"] == "200") {
                                    
                                    if(is_object($responseArr["body"]["items"]["0"]["item"])) {
                                        $user = (array)$responseArr["body"]["items"]["0"]["item"];
                                    } elseif (is_array($responseArr["body"]["items"]["0"]["item"])) {
                                        $user = $responseArr["body"]["items"]["0"]["item"];
                                    }

                                    if ($user["state"] == 1) {
                                        
                                        $session->authorize = 1;
                                        $session->role_id = $user["role_id"];
                                        if($session->role_id == 100) {
                                            $session->admin_uri = $utility->random_alias_id();
                                        }
                                        $session->user_id = $user["id"];
                                        $session->iname = $config['vendor']['crypto']['crypt']::encrypt($user["iname"], $session_key);
                                        $session->fname = $config['vendor']['crypto']['crypt']::encrypt($user["fname"], $session_key);
                                        $session->phone = $config['vendor']['crypto']['crypt']::encrypt($user["phone"], $session_key);
                                        $session->email = $config['vendor']['crypto']['crypt']::encrypt($user["email"], $session_key);
                                        
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
                            }
                        } else {
                            $callbackText = 'Ошибка cookie';
                        }
                    } else {
                        $callbackText = 'Login failed. Incorrect credentials';
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
 
        return ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
    }
    
}

