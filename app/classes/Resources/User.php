<?php
/**
 * This file is part of the API SHOP
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/pllano/api-shop
 * @version 1.0.1
 * @package pllano.api-shop
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ApiShop\Resources;

use Adbar\Session;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException as CryptoEx;

use ApiShop\Config\Settings;
use ApiShop\Utilities\Utility;
use ApiShop\Database\Router;
use ApiShop\Database\Ping;
use ApiShop\Model\SessionUser;

class User {
 
    protected $db;
 
    function __construct()
    {
        list($x1,$x2) = explode('.',strrev($_SERVER['HTTP_HOST']));
        $xdomain = $x1.'.'.$x2;
        $this->domain =  strrev($xdomain);
    }
 
    // Запускаем сессию пользоваетеля
    public function run()
    {
        // Подключаем конфиг \ApiShop\Config\Settings
        $config = (new Settings())->get();
        // Подключаем сессию \Adbar\Session
        $session = new Session($config['settings']['session']['name']);
        // Читаем ключи
        $session_key = $config['key']['session'];
        $cookie_key = $config['key']['cookie'];
        // Читаем печеньку у юзера в браузере
        $read_code = isset($_COOKIE[$config['settings']['session']['name']]) ? $_COOKIE[$config['settings']['session']['name']] : null;
        // Если печеньки нет создаем новую
        if ($read_code) {
            try {
                $cookie = Crypto::decrypt($_COOKIE[$config['settings']['session']['name']], $cookie_key);
                $identificator = $_COOKIE[$config['settings']['session']['name']];
            } catch (CryptoEx $ex) {
                $identificator = null;
            }
        } else {
            $identificator = null;
        }
        // Если cookie нет создаем новую
        if ($identificator === null) {
            // Чистим сессию на всякий случай
            $session->clear();
            // Подключаем утилиту
            $utility = new Utility();
            // Генерируем identificator
            $identificator = Crypto::encrypt($utility->random_token(), $cookie_key);
            // Записываем пользователю новый cookie
            setcookie($config['settings']['session']['name'], $identificator, time() + 3600 * 24 * 31, '/', $this->domain, 1, true);
            // Пишем в сессию identificator cookie
            $session->cookie = $identificator;
        }
        // Если cookie есть и дешифровка не дала ошибки
        if ($identificator != null) {
            // Пишем в сессию identificator cookie
            $session->cookie = $identificator;
            // cookie в расшифрованном виде для записи в базу
            $cookie = Crypto::decrypt($identificator, $cookie_key);
            //print_r($cookie);
            // Подключаем ApiShop\Database\Router - Универсальный класс работы с базами данных
            $database = new Router((new Ping("user"))->get());
            // Запрашиваем в базе данные пользователя по cookie
            $response = $database->get("user", ["cookie" => $cookie]);
            // Если нашли пользователя в базе и получили его id
            if ($response != null && isset($response['items']['0']['item']['user_id'])) {
                $user_id = $response['items']['0']['item']['user_id'];
                $database->put("user", ["cookie" => $cookie], $user_id);
                //print_r($response['items']['0']['item']['user_id']);
                // Пишем данные из базы в сессию
                $session->id = Crypto::encrypt($user_id, $session_key);
                $session->user_id = Crypto::encrypt($user_id, $session_key);
                //$session->cart_id = Crypto::encrypt($response['items']['item']['0']['cart_id'], $session_key);
                $authorize = ($response['items']['0']['item']['authorized'] == '0000-00-00 00:00:00') ? 0 : 1;
                // Записываем authorize в сессию
                $session->authorize = $authorize;
                // Если пользователь есть мы на всякий случай обновим данные в его сессии
                $sessionUser = new SessionUser();
                $sessionUser->checking();
            } else {
                // Если нет то обнуляем данные в сессии на всякий случай
                $session->id = null;
                $session->user_id = null;
                //$session->cart_id = null;
                $session->authorize = null;
            }
        }
    }

    // Авторизвация
    public function checkLogin($email, $phone, $password)
    {
        // Подключаем ApiShop\Database\Router - Универсальный класс работы с базами данных
        $database = new Router((new Ping("user"))->get());
        // Ищем пользователя по email и phone
        $response = $database->get("user", ["phone" => $phone, "email" => $email]);
        if ($response != null) {
            // Если все ок читаем пароль
            if (password_verify($password, $response['items']["0"]['item']["password"])) {
                // Если все ок - отдаем user_id
                return $response['items']["0"]['item']["user_id"];
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
 
    // Получить данные пользователя по cookie
    public function getUserCode()
    {
        // Получаем конфигурацию \ApiShop\Config\Settings
        $config = (new Settings())->get();
        // Читаем ключи
        $cookie_key = $config['key']['cookie'];
        // Подключаем сессию
        $session = new Session($config['settings']['session']['name']);
        // Читаем печеньку у юзера в браузере
        $session_code = isset($_COOKIE[$config['settings']['session']['name']]) ? $_COOKIE[$config['settings']['session']['name']] : null;
        if ($session_code) {
            try {
                $cookie = Crypto::decrypt($session_code, $cookie_key);
            } catch (CryptoEx $ex) {
                $cookie = null;
            }
            if ($cookie != null) {
                // Подключаем ApiShop\Database\Router - Универсальный класс работы с базами данных
                $database = new Router((new Ping("user"))->get());
                // Запрашиваем в базе данные пользователя по cookie
                $response = $database->get("user", ["cookie" => $cookie]);
                if ($response != null) {
                    // Возвращаем ответ на запрос
                    return $response;
                } else {
                    return null;
                }
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
 
    // Обновляем cookie в базе
    public function putUserCode($user_id, $cookie)
    {
        $today = date("Y-m-d H:i:s");
        // Получаем конфигурацию \ApiShop\Config\Settings
        $config = (new Settings())->get();
        // Читаем ключи шифрования
        $cookie_key = $config['key']['cookie'];
        $new_cookie = Crypto::encrypt($cookie, $cookie_key);
        // Перезаписываем cookie у пользователя
        setcookie($config['settings']['session']['name'], $new_cookie, time() + 3600 * 24 * 31, '/', $this->domain, 1, true);
        // Подключаем ApiShop\Database\Router - Универсальный класс работы с базами данных
        $database = new Router((new Ping("user"))->get());
        // Обновляем в базе cookie
        $response = $database->put("user", ["cookie" => $cookie, "authorized" => $today], $user_id);
        if ($response != null) {
            // Если все ок возвращаем 1
            return 1;
        } else {
            return null;
        }
    }
 
    // Проверяем наличие пользователя по email и phone
    public function getEmailPhone($email, $phone)
    {
        $database = new Router((new Ping("user"))->get());
        $arrUser["email"] = $email;
        $arrUser["phone"] = $phone;
        $response = $database->get("user", $arrUser);
        
        //return $response['items']["0"]['item']["user_id"];
        
        if(isset($response['items']["0"]['item']["user_id"])){
            return $response['items']["0"]['item']["user_id"];
        } else {
            return null;
        }
    }

}
 