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

namespace ApiShop\Modules\Account;

use Psr\Http\Message\ServerRequestInterface as Request;
use Pllano\RouterDb\{Db, Router};
use ApiShop\Utilities\Utility;
use ApiShop\Model\User;

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
                            // Отдаем роутеру RouterDb конфигурацию.
                            $router = new Router($config);
                            // Получаем название базы для указанного ресурса
                            $name_db = $router->ping($resource);
                            // Подключаемся к базе
                            $db = new Db($name_db, $config);
                            // Отправляем запрос и получаем данные
                            $resp = $db->get($resource, [], $user_id);
                            
                            //print("<br>");
                            //print_r($resp);
                            if (isset($resp["headers"]["code"])) {
                                if ($resp["headers"]["code"] == 200 || $resp["headers"]["code"] == "200") {
                                    
                                    if(is_object($resp["body"]["items"]["0"]["item"])) {
                                        $user = (array)$resp["body"]["items"]["0"]["item"];
                                    } elseif (is_array($resp["body"]["items"]["0"]["item"])) {
                                        $user = $resp["body"]["items"]["0"]["item"];
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

