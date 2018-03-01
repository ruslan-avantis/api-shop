<?php
/**
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

namespace Pllano\ApiShop\Models;

use Pllano\RouterDb\Router as RouterDb;
use Pllano\ApiShop\Utilities\Utility;

class User {

    // Запускаем сессию пользоваетеля
    public function run($config = [])
    {
        // Подключаем утилиты
        $utility = new Utility();
        // Подключаем сессию
        $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
        // Читаем ключи
        $session_key = $config['key']['session'];
        $cookie_key = $config['key']['cookie'];
 
        // Читаем печеньку у юзера в браузере
        $identificator = isset($_COOKIE[$config['settings']['session']['name']]) ? $_COOKIE[$config['settings']['session']['name']] : null;
 
        if ($identificator != null) {
            try {
                $cookie = $config['vendor']['crypto']['crypt']::decrypt($identificator, $cookie_key);
            } catch (\Exception $ex) {
                $cookie = null;
            }
 
            if ($cookie != null) {
                // Ресурс (таблица) к которому обращаемся
                $resource = "user";
                // Отдаем роутеру RouterDb конфигурацию
                $routerDb = new RouterDb($config, 'Apis');
                // Пингуем для ресурса указанную и доступную базу данных
				// Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
				$db = $routerDb->run($routerDb->ping($resource));
                // Массив для запроса
				$query = [
				    "cookie" => $cookie, 
					"state" => 1
				];
				// Отправляем запрос к БД в формате адаптера. В этом случае Apis
                $responseArr = $db->get($resource, $query);

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
							$crypt = $config['vendor']['crypto']['crypt']::encrypt;
                            $session->iname = $crypt($user["iname"], $session_key);
                            $session->fname = $crypt($user["fname"], $session_key);
                            $session->phone = $crypt($user["phone"], $session_key);
                            $session->email = $crypt($user["email"], $session_key);
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
                $identificator = $config['vendor']['crypto']['crypt']::encrypt($utility->random_token(), $cookie_key);
                // Записываем пользователю новый cookie
                $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : 'localhost';
                if ($config['settings']['site']['cookie_httponly'] == '1') {
                    setcookie($config['settings']['session']['name'], $identificator, time()+60*60*24*365, '/', $domain, 1, true);
                } else {
                    setcookie($config['settings']['session']['name'], $identificator, time()+60*60*24*365, '/', $domain);
                }
                // Пишем в сессию identificator cookie
                $session->cookie = $identificator;
            }
        }
        
        if (!isset($session->language)) {
            $langs = new $config['vendor']['detector']['language']();
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
        // Отдаем роутеру RouterDb конфигурацию
        $routerDb = new RouterDb($config);
        // Пингуем для ресурса указанную и доступную базу данных
        // Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
        $db = $routerDb->run($routerDb->ping($resource));
        // Массив для запроса
        $query = [
				    "phone" => $phone, 
					"email" => $email
        ];
        // Отправляем запрос к БД в формате адаптера. В этом случае Apis
        $responseArr = $db->get($resource, $query);

        if (isset($responseArr["headers"]["code"])) {
            $item = (array)$responseArr["body"]["items"]["0"]["item"];
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
        $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
 
        // Текущая дата
        $today = date("Y-m-d H:i:s");
        // Подключаем утилиту
        $utility = new Utility();
        // Генерируем новый cookie
        $cookie = $utility->random_token();
 
        // Ресурс (таблица) к которому обращаемся
        $resource = "user";
		// Отдаем роутеру RouterDb конфигурацию
        $routerDb = new RouterDb($config);
        // Пингуем для ресурса указанную и доступную базу данных
        // Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
        $db = $routerDb->run($routerDb->ping($resource));
        // Массив c запросом
        $query = [
		    "cookie" => $cookie, 
		    "authorized" => $today
        ];
        // Отправляем запрос к БД в формате адаптера. В этом случае Apis
        $responseArr = $db->put($resource, $query, $user_id);

        // Если удалось обновить cookie в базе перезапишем везде
        if (isset($responseArr["headers"]["code"])) {
            if ($responseArr["headers"]["code"] == 202 || $responseArr["headers"]["code"] == "202") {
                // Читаем ключи шифрования
                $cookie_key = $config['key']['cookie'];
                // Шифруем cookie
                $new_cookie = $config['vendor']['crypto']['crypt']::encrypt($cookie, $cookie_key);
 
                // Перезаписываем cookie в сессии
                $session->cookie = $new_cookie;
 
                // Перезаписываем cookie в базе
                $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : 'localhost';
                if ($config['settings']['site']['cookie_httponly'] == '1') {
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

        // Ресурс (таблица) к которому обращаемся
        $resource = "user";
		// Отдаем роутеру RouterDb конфигурацию
        $routerDb = new RouterDb($config);
        // Пингуем для ресурса указанную и доступную базу данных
        // Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
        $db = $routerDb->run($routerDb->ping($resource));
        // Массив c запросом
        $query["email"] = $email;
        $query["phone"] = $phone;
        // Отправляем запрос к БД в формате адаптера. В этом случае Apis
        $responseArr = $db->get($resource, $query);
        
        if (isset($responseArr["headers"]["code"])) {
            if ($responseArr["headers"]["code"] == 200 || $responseArr["headers"]["code"] == "200") {
                $item = (array)$responseArr["body"]["items"]["0"]["item"];
                if(isset($item["user_id"])){
                    return $item["user_id"];
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

}
 