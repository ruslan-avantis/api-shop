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
        $cookie_key = $config['key']['cookie'];
 
        $callbackStatus = 400;
        $callbackTitle = 'Соообщение системы';
        $callbackText = '';
        
        // Чистим данные на всякий случай пришедшие через POST
        $post_email = filter_var($post['email'], FILTER_SANITIZE_STRING);
        $post_phone = filter_var($post['phone'], FILTER_SANITIZE_STRING);
        $post_password = filter_var($post['password'], FILTER_SANITIZE_STRING);
        $post_iname = filter_var($post['iname'], FILTER_SANITIZE_STRING);
        $post_fname = filter_var($post['fname'], FILTER_SANITIZE_STRING);
 
        $email = $utility->clean($post_email);
        $new_phone = $utility->phone_clean($post_phone);
        $password = $utility->clean($post_password);
        $iname = $utility->clean($post_iname);
        $fname = $utility->clean($post_fname);
 
        $pattern = "/^[\+0-9\-\(\)\s]*$/";
        if(preg_match($pattern, $new_phone)) {
            $phone = $new_phone;
            if(!empty($phone) && !empty($email) && !empty($iname) && !empty($fname)) {
                $email_validate = filter_var($email, FILTER_VALIDATE_EMAIL);
                if($utility->check_length($phone, 8, 25) && $email_validate) {
                    // Проверяем наличие пользователя
                    $user_search = (new User())->getEmailPhone($email, $phone);
                    if ($user_search == null) {
                        // Чистим сессию на всякий случай
                        //$session->clear();
                        // Создаем новую cookie
                        $cookie = $utility->random_token();
                        // Генерируем identificator
                        $identificator = $config['vendor']['crypto']['crypt']::encrypt($cookie, $cookie_key);
 
                        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
                        // Записываем пользователю новый cookie
                        if ($config['settings']['site']['cookie_httponly'] == '1'){
                            setcookie($config['settings']['session']['name'], $identificator, time()+60*60*24*365, '/', $domain, 1, true);
                            } else {
                            setcookie($config['settings']['session']['name'], $identificator, time()+60*60*24*365, '/', $domain);
                        }
                        // Пишем в сессию identificator cookie
 
                        $arr["role_id"] = 1;
                        $arr["password"] = password_hash($password, PASSWORD_DEFAULT);
                        $arr["phone"] = strval($phone);
                        $arr["email"] = $email;
                        $arr["language"] = $session->language;
                        $arr["ticketed"] = 1;
                        $arr["admin_access"] = 0;
                        $arr["iname"] = $iname;
                        $arr["fname"] = $fname;
                        $arr["cookie"] = $cookie;
                        $arr["created"] = $today;
                        $arr["authorized"] = $today;
                        $arr["alias"] = $utility->random_alias_id();
                        $arr["state"] = 1;
                        $arr["score"] = 1;
 
                        // Ресурс (таблица) к которому обращаемся
                        $resource = "user";
                        // Отдаем роутеру RouterDb конфигурацию.
                        $router = new Router($config);
                        // Получаем название базы для указанного ресурса
                        $name_db = $router->ping($resource);
                        // Подключаемся к базе
                        $db = new Db($name_db, $config);
                        // Отправляем запрос и получаем данные
                        $user_id = $db->post($resource, $arr);
                        
                        if ($user_id >= 1) {
                            // Обновляем данные в сессии
                            $session->authorize = 1;
                            $session->cookie = $identificator;
                            $session->user_id = $user_id;
                            $session->phone = $config['vendor']['crypto']['crypt']::encrypt($phone, $session_key);
                            $session->email = $config['vendor']['crypto']['crypt']::encrypt($email, $session_key);
                            $session->iname = $config['vendor']['crypto']['crypt']::encrypt($iname, $session_key);
                            $session->fname = $config['vendor']['crypto']['crypt']::encrypt($fname, $session_key);
 
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
        return ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
    }
 
}
 