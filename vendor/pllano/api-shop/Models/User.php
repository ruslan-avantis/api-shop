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
use Psr\Container\ContainerInterface as Container;

class User
{
    
    private $app;
    private $config;
    private $session;

    function __construct(Container $app)
    {
        $this->app = $app;
        $this->config = $app->get('config');
        $this->session = $app->get('session');
    }

    // Запускаем сессию пользоваетеля
    public function run()
    {
        // Подключаем сессию
        $session = $this->session;
        $session_name = $this->config['settings']['session']['name'];
        // Читаем ключи
        $session_key = $this->config['key']['session'];
        $cookie_key = $this->config['key']['cookie'];
        $crypt = $this->config['vendor']['crypto']['crypt'];

        $get_cookie = get_cookie($session_name);
        if ($get_cookie != null) {
            try {
                $cookie = $crypt::decrypt($get_cookie, $cookie_key);
            } catch (\Exception $ex) {
                $cookie = null;
            }

            if ($cookie != null) {
                // Ресурс (таблица) к которому обращаемся
                $resource = "user";
                // Отдаем роутеру RouterDb конфигурацию
                $routerDb = new RouterDb($this->config, 'Apis');
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
                if (isset($responseArr["headers"]["code"]) && (int)$responseArr["headers"]["code"] == 200) {
                        if(is_object($responseArr["body"]["items"]["0"]["item"])) {
                            $user = (array)$responseArr["body"]["items"]["0"]["item"];
                        } elseif (is_array($responseArr["body"]["items"]["0"]["item"])) {
                            $user = $responseArr["body"]["items"]["0"]["item"];
                        }
 
                        if ($user["state"] == 1) {
                            $session->authorize = 1;
                            $session->role_id = $user["role_id"];
                            if($session->role_id == 100) {
								if(!isset($session->admin_uri)) {
                                    $session->admin_uri = random_alias_id();
								}
                            }
                            $session->user_id = $user["id"];
                            $session->iname = $crypt::encrypt($user["iname"], $session_key);
                            $session->fname = $crypt::encrypt($user["fname"], $session_key);
                            $session->phone = $crypt::encrypt($user["phone"], $session_key);
                            $session->email = $crypt::encrypt($user["email"], $session_key);
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
            if ($get_cookie === null) {
                // Чистим сессию на всякий случай
                $session->clear();
                // Генерируем identificator
                $get_cookie = $crypt::encrypt(random_token(), $cookie_key);
                // Записываем пользователю новый cookie
                set_cookie($session_name, $get_cookie, 60*60*24*365);
                // Пишем в сессию get_cookie cookie
                $session->cookie = $get_cookie;
            }
        }
        
        if (!isset($session->language)) {
            $langs = new $this->config['vendor']['detector']['language']();
            if ($langs->getLanguage()) {
                $session->language = $langs->getLanguage();
            }
        }
    }

    // Авторизвация
    public function checkLogin($email, $phone, $password)
    {
        // Ресурс (таблица) к которому обращаемся
        $resource = "user";
        // Отдаем роутеру RouterDb конфигурацию
        $routerDb = new RouterDb($this->config, 'Apis');
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
        // Подключаем сессию
        $session = $this->session;
        $session_name = $this->config['settings']['session']['name'];
        // Генерируем новый cookie
        $cookie = random_token();

        // Ресурс (таблица) к которому обращаемся
        $resource = "user";
        // Отдаем роутеру RouterDb конфигурацию
        $routerDb = new RouterDb($this->config, 'Apis');
        // Пингуем для ресурса указанную и доступную базу данных
        // Подключаемся к БД через выбранный Adapter: Sql, Pdo или Apis (По умолчанию Pdo)
        $db = $routerDb->run($routerDb->ping($resource));
        // Массив c запросом
        $query = [
            "cookie" => $cookie, 
            "authorized" => today()
        ];
        // Отправляем запрос к БД в формате адаптера. В этом случае Apis
        $responseArr = $db->put($resource, $query, $user_id);

        // Если удалось обновить cookie в базе перезапишем везде
        if (isset($responseArr["headers"]["code"])) {
            if ($responseArr["headers"]["code"] == 202 || $responseArr["headers"]["code"] == "202") {
                // Читаем ключи шифрования
                $cookie_key = $this->config['key']['cookie'];
                $crypt = $this->config['vendor']['crypto']['crypt'];
                // Шифруем cookie
                $new_cookie = $crypt::encrypt($cookie, $cookie_key);
                // Перезаписываем cookie в сессии
                $session->cookie = $new_cookie;
                // Перезаписываем cookie в базе
                set_cookie($session_name, $new_cookie, 60*60*24*365);
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
        // Ресурс (таблица) к которому обращаемся
        $resource = "user";
        // Отдаем роутеру RouterDb конфигурацию
        $routerDb = new RouterDb($this->config, 'Apis');
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
 