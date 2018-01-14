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
		// Читаем ключи
        $session_key = $config['key']['session'];
		$cookie_key = $config['key']['cookie'];
 
        // Читаем печеньку у юзера в браузере
        $identificator = isset($_COOKIE[$config['settings']['session']['name']]) ? $_COOKIE[$config['settings']['session']['name']] : null;
 
        if ($identificator != null) {
            try {
                $cookie = Crypto::decrypt($identificator, $cookie_key);
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
                $response = $db->get($resource, ["cookie" => $cookie, "state" => 1]);
 
				//print("<br>");
				//print_r($response);
				if (isset($response["headers"]["code"])) {
				    if ($response["headers"]["code"] == 200 || $response["headers"]["code"] == "200") {
 
				        if(is_object($response["body"]["items"]["0"]["item"])) {
				            $user = (array)$response["body"]["items"]["0"]["item"];
				        } elseif (is_array($response["body"]["items"]["0"]["item"])) {
				            $user = $response["body"]["items"]["0"]["item"];
				        }
 
				        if ($user["state"] == 1) {
				            $session->authorize = 1;
				            $session->role_id = $user["role_id"];
				            $session->user_id = $user["id"];
				            $session->iname = Crypto::encrypt($user["iname"], $session_key);
				            $session->fname = Crypto::encrypt($user["fname"], $session_key);
				            $session->phone = Crypto::encrypt($user["phone"], $session_key);
				            $session->email = Crypto::encrypt($user["email"], $session_key);
				        } else {
				            $session->authorize = null;
				            $session->role_id = null;
				            $session->user_id = null;
				            unset($session->authorize); // удаляем authorize
				            unset($session->role_id); // удаляем role_id
				            unset($session->user_id); // удаляем role_id
				            $session->destroy();
				            $session->clear();
				        }
				    }
				} else {
				    $session->authorize = null;
				    $session->role_id = null;
				    $session->user_id = null;
				    unset($session->authorize); // удаляем authorize
				    unset($session->role_id); // удаляем role_id
				    unset($session->user_id); // удаляем role_id
			    }
            }
        } else {
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
        }
		
        if (!isset($session->language)) {
			$langs = new Langs();
		    if ($langs->getLanguage()) {
                $session->language = $langs->getLanguage();
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
 
    // Обновляем cookie в базе
    public function putUserCode($user_id)
    {
        // Получаем конфигурацию \ApiShop\Config\Settings
        $config = (new Settings())->get();
        // Подключаем сессию
        $session = new Session($config['settings']['session']['name']);
 
        // Текущая дата
        $today = date("Y-m-d H:i:s");
        // Подключаем утилиту
        $utility = new Utility();
        // Генерируем новый cookie
        $cookie = $utility->random_token();
 
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
 
        // Если удалось обновить cookie в базе перезапишем везде
        if (isset($response["headers"]["code"])) {
            if ($response["headers"]["code"] == 202 || $response["headers"]["code"] == "202") {
                // Читаем ключи шифрования
                $cookie_key = $config['key']['cookie'];
                // Шифруем cookie
                $new_cookie = Crypto::encrypt($cookie, $cookie_key);
 
                // Перезаписываем cookie в сессии
                $session->cookie = $new_cookie;
 
                // Перезаписываем cookie в базе
                $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
                if ($config['settings']['site']['cookie_httponly'] === true) {
                    setcookie($config['settings']['session']['name'], $new_cookie, time()+60*60*24*365, '/', $domain, 1, true);
                } else {
                    setcookie($config['settings']['session']['name'], $new_cookie, time()+60*60*24*365, '/', $domain);
                }
 
                // Если все ок возвращаем 1
                return 1;
 
            } else {
                return null;
            }
 
        } else {
            // Если не удалось перезаписать в базе
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
 