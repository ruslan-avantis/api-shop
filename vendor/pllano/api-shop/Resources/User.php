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
use Sinergi\BrowserDetector\Language as Langs;
use RouterDb\Db;
use RouterDb\Router;
use ApiShop\Config\Settings;
use ApiShop\Utilities\Utility;
use ApiShop\Model\SessionUser;

class User {

    // Запускаем сессию пользоваетеля
    public function run()
    {
        // Подключаем конфиг \ApiShop\Config\Settings
        $config = (new Settings())->get();
        // Подключаем сессию \Adbar\Session
        $session = new Session($config['settings']['session']['name']);
        // Получаем массив данных из таблицы language на языке из $session->language
        $langs = new Langs();
        // Перезаписываем язык в сессии
        if (isset($session->language)) {
            $lang = $session->language;
        } elseif ($langs->getLanguage()) {
            $lang = $langs->getLanguage();
        } else {
            $lang = $site_config["language"];
        }
        $session->language = $lang;
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
            $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
            if ($config['settings']['site']['cookie_httponly'] === true) {
                setcookie($config['settings']['session']['name'], $identificator, time()+60*60*24*365, '/', $domain, 1, true);
            } else {
                setcookie($config['settings']['session']['name'], $identificator, time()+60*60*24*365, '/', $domain);
            }
            // Пишем в сессию identificator cookie
            $session->cookie = $identificator;
        }
        // Если cookie есть и дешифровка не дала ошибки
        if ($identificator != null) {
            // Пишем в сессию identificator cookie
            $session->cookie = $identificator;
            // cookie в расшифрованном виде для записи в базу
            $cookie = Crypto::decrypt($identificator, $cookie_key);
  
            // Ресурс (таблица) к которому обращаемся
            $resource = "user";
            // Отдаем роутеру RouterDb конфигурацию.
            $router = new Router($config);
            // Получаем название базы для указанного ресурса
            $name_db = $router->ping($resource);
            // Подключаемся к базе
            $db = new Db($name_db, $config);
            // Отправляем запрос и получаем данные
            $response = $db->get($resource, ["cookie" => $cookie]);
 
            // Если нашли пользователя в базе и получили его id
            if ($response != null && isset($response["body"]["items"]['0']["item"]['user_id'])) {
 
                $user_id = $response["body"]["items"]['0']["item"]['user_id'];
                $db->put($resource, ["cookie" => $cookie], $user_id);
 
                // Пишем данные из базы в сессию
                $session->id = Crypto::encrypt($user_id, $session_key);
                $session->user_id = Crypto::encrypt($user_id, $session_key);
                //$session->cart_id = Crypto::encrypt($response["items"]["item"]['0']['cart_id'], $session_key);
                $authorize = ($response["body"]["items"]['0']["item"]['authorized'] == '0000-00-00 00:00:00') ? 0 : 1;
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
        // Подключаем конфиг \ApiShop\Config\Settings
        $config = (new Settings())->get();
 
        // Ресурс (таблица) к которому обращаемся
        $resource = "user";
        // Отдаем роутеру RouterDb конфигурацию.
        $router = new Router($config);
        // Получаем название базы для указанного ресурса
        $name_db = $router->ping($resource);
        // Подключаемся к базе
        $db = new Db($name_db, $config);
        // Отправляем запрос и получаем данные
        $response = $db->get($resource, ["phone" => $phone, "email" => $email]);
        if (isset($response["headers"]["code"])) {
            $item = (array)$response["body"]["items"]["0"]["item"];
            // Если все ок читаем пароль
            if (password_verify($password, $item["password"])) {
                // Если все ок - отдаем user_id
                return $item["user_id"];
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
 
                // Ресурс (таблица) к которому обращаемся
                $resource = "user";
                // Отдаем роутеру RouterDb конфигурацию.
                $router = new Router($config);
                // Получаем название базы для указанного ресурса
                $name_db = $router->ping($resource);
                // Подключаемся к базе
                $db = new Db($name_db, $config);
                // Отправляем запрос и получаем данные
                $response = $db->get($resource, ["cookie" => $cookie]);
 
                if (isset($response["headers"]["code"])) {
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
        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
        if ($config['settings']['site']['cookie_httponly'] === true) {
            setcookie($config['settings']['session']['name'], $new_cookie, time()+60*60*24*365, '/', $domain, 1, true);
        } else {
            setcookie($config['settings']['session']['name'], $new_cookie, time()+60*60*24*365, '/', $domain);
        }
        // Ресурс (таблица) к которому обращаемся
        $resource = "user";
        // Отдаем роутеру RouterDb конфигурацию.
        $router = new Router($config);
        // Получаем название базы для указанного ресурса
        $name_db = $router->ping($resource);
 
        // Подключаемся к базе
        $db = new Db($name_db, $config);
        // Отправляем запрос и получаем данные
        $response = $db->put($resource, ["cookie" => $cookie, "authorized" => $today], $user_id);
 
        if (isset($response["headers"]["code"])) {
            // Если все ок возвращаем 1
            return 1;
        } else {
            return null;
        }
    }
 
    // Проверяем наличие пользователя по email и phone
    public function getEmailPhone($email, $phone)
    {
        // Получаем конфигурацию \ApiShop\Config\Settings
        $config = (new Settings())->get();
 
        $arrUser["email"] = $email;
        $arrUser["phone"] = $phone;
 
        // Ресурс (таблица) к которому обращаемся
        $resource = "user";
        // Отдаем роутеру RouterDb конфигурацию.
        $router = new Router($config);
        // Получаем название базы для указанного ресурса
        $name_db = $router->ping($resource);
        // Подключаемся к базе
        $db = new Db($name_db, $config);
        // Отправляем запрос и получаем данные
        $response = $db->get($resource, $arrUser);
        
        if (isset($response["headers"]["code"])) {
            $item = (array)$response["body"]["items"]["0"]["item"];
            if(isset($item["user_id"])){
                return $item["user_id"];
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

}
 