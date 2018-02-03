<?php
/**
* This file is part of the REST API SHOP library
*
* @license http://opensource.org/licenses/MIT
* @link https://github.com/API-Shop/api-shop
* @version 1.0
* @package api-shop.api-shop
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/
 
use Slim\Http\Request;
use Slim\Http\Response;
 
use RouterDb\Db;
use RouterDb\Router;
 
use ApiShop\Config\Settings;
use ApiShop\Utilities\Utility;
use ApiShop\Hooks\Hook;
 
use ApiShop\Model\Security;
use ApiShop\Model\SessionUser;
use ApiShop\Resources\Language;
use ApiShop\Resources\Template;
use ApiShop\Resources\Site;
use ApiShop\Resources\User;
 
$config = (new Settings())->get();
$sign_in = $config['routers']['sign_in'];
$sign_up = $config['routers']['sign_up'];
$logout = $config['routers']['logout'];
$login = $config['routers']['login'];
$check_in = $config['routers']['check_in'];
 
// Страница авторизации
$app->get($sign_in, function (Request $request, Response $response, array $args) {
 
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new Hook();
    $hook->http($request, $response, $args, 'GET', 'site');
    $request = $hook->request();
    $args = $hook->args();
 
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Получаем конфигурацию
    $config = (new Settings())->get();
    // Конфигурация роутинга
    $routers = $config['routers'];
    // Подключаем плагины
    $utility = new Utility();
    // Настройки сайта
    $site = new Site();
    $site_config = $site->get();
    // Получаем название шаблона
    $site_template = $site->template();
    // Конфигурация шаблона
    $templateConfig = new Template($site_template);
    $template = $templateConfig->get();
    // Подключаем мультиязычность
    $languages = new Language($request, $config);
    $language = $languages->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']($config['settings']['session']['name']);
    // Данные пользователя из сессии
    $user_data =(new SessionUser())->get();
    // Подключаем временное хранилище
    $session_temp = new $config['vendor']['session']("_temp");
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token = $config['vendor']['crypto']::encrypt($token, $token_key);
    // Что бы не давало ошибку присваиваем пустое значение
    $content = '';
 
    // Заголовки
    $head = [
        "page" => 'sign-in',
        "title" => "",
        "keywords" => "",
        "description" => "",
        "og_title" => "",
        "og_description" => "",
        "host" => $host,
        "path" => $path
    ];
 
    // Контент и конфигурации
    $view = [
        "head" => $head,
        "pages" => ["page" => 'sign-in'],
        "site" => $site_config,
        "routers" => $routers,
        "config" => $config['settings']['site'],
        "language" => $language,
        "template" => $template,
        "token" => $session->token,
        "session" => $user_data,
        "session_temp" => $session_temp,
        "content" => $content
    ];
 
    // Получаем название шаблона для рендера
    $render = $template['layouts']['sign_in'] ? $template['layouts']['sign_in'] : 'sign-in.html';
 
    // Передаем данные Hooks для обработки ожидающим классам
    $hook->get($view, $render);
    // Отдаем данные шаблонизатору
    return $this->view->render($hook->render(), $hook->view());
 
});
 
// Страница регистрации
$app->get($sign_up, function (Request $request, Response $response, array $args) {
 
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new Hook();
    $hook->http($request, $response, $args, 'GET', 'site');
    $request = $hook->request();
    $args = $hook->args();
 
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Получаем конфигурацию
    $config = (new Settings())->get();
    // Конфигурация роутинга
    $routers = $config['routers'];
    // Подключаем плагины
    $utility = new Utility();
    // Настройки сайта
    $site = new Site();
    $site_config = $site->get();
    // Получаем название шаблона
    $site_template = $site->template();
    // Конфигурация шаблона
    $templateConfig = new Template($site_template);
    $template = $templateConfig->get();
    // Подключаем мультиязычность
    $languages = new Language($request, $config);
    $language = $languages->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']($config['settings']['session']['name']);
    // Данные пользователя из сессии
    $user_data =(new SessionUser())->get();
    // Подключаем временное хранилище
    $session_temp = new $config['vendor']['session']("_temp");
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token = $config['vendor']['crypto']::encrypt($token, $token_key);
    // Что бы не давало ошибку присваиваем пустое значение
    $content = '';
    
    $head = [
        "page" => 'sign-in',
        "title" => "",
        "keywords" => "",
        "description" => "",
        "og_title" => "",
        "og_description" => "",
        "host" => $host,
        "path" => $path
    ];
    
    $view = [
        "head" => $head,
        "pages" => ["page" => 'sign-up'],
        "site" => $site_config,
        "routers" => $routers,
        "config" => $config['settings']['site'],
        "template" => $template,
        "language" => $language,
        "token" => $session->token,
        "session" => $user_data,
        "session_temp" => $session_temp,
        "content" => $content
    ];
 
    // Получаем название шаблона для рендера
    $render = $template['layouts']['sign_up'] ? $template['layouts']['sign_up'] : 'sign-up.html';
 
    // Передаем данные Hooks для обработки ожидающим классам
    $hook->get($view, $render);
    // Отдаем данные шаблонизатору
    return $this->view->render($hook->render(), $hook->view());
 
});
 
// Выйти
$app->post($logout, function (Request $request, Response $response, array $args) {
 
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new Hook();
    $hook->http($request, $response, $args, 'POST', 'site');
    $request = $hook->request();
    $args = $hook->args();
 
    $today = date("Y-m-d H:i:s");
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
    // Подключаем конфиг Settings\Config
    $config = (new Settings())->get();
    // Подключаем плагины
    $utility = new Utility();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Подключаем систему безопасности
    $security = new Security();
 
    try {
        // Получаем токен из сессии
        $token = $config['vendor']['crypto']::decrypt($session->token, $token_key);
    } catch (\Exception $ex) {
        $token = 0;
        // Сообщение об Атаке или подборе токена
        $security->token($request, $response);
    }
    try {
        // Получаем токен из POST
        $post_csrf = $config['vendor']['crypto']::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
        // Чистим данные на всякий случай пришедшие через POST
        $csrf = $utility->clean($post_csrf);
    } catch (\Exception $ex) {
        $csrf = 1;
        // Сообщение об Атаке или подборе csrf
        $security->csrf($request, $response);
    }
 
    $callbackStatus = 400;
    $callbackTitle = 'Соообщение системы';
    $callbackText = '';
 
    // Проверка токена - Если токен не совпадает то ничего не делаем. Можем записать в лог или написать письмо админу
    if ($csrf == $token) {
 
        $session->authorize = null;
        $session->cookie = '';
        unset($session->authorize); // удаляем сесию
        unset($session->id); // удаляем сесию
        unset($session->cookie); // удаляем сесию
        $session->destroy();
 
        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
        if ($config['settings']['site']['cookie_httponly'] == '1'){
            setcookie($config['settings']['session']['name'], null, time() - ( 3600 * 24 * 31 ), '/', $domain, 1, true);
        } else {
            setcookie($config['settings']['session']['name'], null, time() - ( 3600 * 24 * 31 ), '/', $domain);
        }
 
        $callbackStatus = 200;
        $callbackTitle = 'Информация';
        $callbackText = 'Вы вышли из системы';
 
    } else {
        $callbackText = 'Действие заблокировано';
    }
 
    $callback = ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
    // Выводим заголовки
    $response->withStatus(200);
    $response->withHeader('Content-type', 'application/json');
    // Подменяем заголовки
    $response = $hook->response();
    // Выводим json
    echo json_encode($hook->callback($callback));
 
});
  
// Авторизация
$app->post($login, function (Request $request, Response $response, array $args) {
 
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new Hook();
    $hook->http($request, $response, $args, 'POST', 'site');
    $request = $hook->request();
    $args = $hook->args();
 
    $today = date("Y-m-d H:i:s");
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
    // Подключаем конфиг Settings\Config
    $config = (new Settings())->get();
    // Подключаем плагины
    $utility = new Utility();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    $session_key = $config['key']['session'];
    // Подключаем систему безопасности
    $security = new Security();
 
    try {
        // Получаем токен из сессии
        $token = $config['vendor']['crypto']::decrypt($session->token, $token_key);
    } catch (\Exception $ex) {
        $token = 0;
        // Сообщение об Атаке или подборе токена
        $security->token($request, $response);
    }
    try {
        // Получаем токен из POST
        $post_csrf = $config['vendor']['crypto']::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
        // Чистим данные на всякий случай пришедшие через POST
        $csrf = $utility->clean($post_csrf);
    } catch (\Exception $ex) {
        $csrf = 1;
        // Сообщение об Атаке или подборе csrf
        $security->csrf($request, $response);
    }
 
    // Чистим данные на всякий случай пришедшие через POST
    $email = filter_var($utility->clean($post['email']), FILTER_SANITIZE_STRING);
    $new_phone = filter_var($utility->phone_clean($post['phone']), FILTER_SANITIZE_STRING);
    $password = filter_var($utility->clean($post['password']), FILTER_SANITIZE_STRING);
 
    $callbackStatus = 400;
    $callbackTitle = 'Соообщение системы';
    $callbackText = '';
 
    // Проверка токена - Если токен не совпадает то ничего не делаем. Можем записать в лог или написать письмо админу
    if ($csrf == $token) {
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
                                    $session->user_id = $user["id"];
                                    $session->iname = $config['vendor']['crypto']::encrypt($user["iname"], $session_key);
                                    $session->fname = $config['vendor']['crypto']::encrypt($user["fname"], $session_key);
                                    $session->phone = $config['vendor']['crypto']::encrypt($user["phone"], $session_key);
                                    $session->email = $config['vendor']['crypto']::encrypt($user["email"], $session_key);
                            
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
    } else {
        $callbackText = 'Перегрузите страницу';
    }
 
    $callback = ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
    // Выводим заголовки
    $response->withStatus(200);
    $response->withHeader('Content-type', 'application/json');
    // Подменяем заголовки
    $response = $hook->response();
    // Выводим json
    echo json_encode($hook->callback($callback));
 
});
 
// Регистрация
$app->post($check_in, function (Request $request, Response $response, array $args) {
 
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
    $post_email = filter_var($post['email'], FILTER_SANITIZE_STRING);
    $post_phone = filter_var($post['phone'], FILTER_SANITIZE_STRING);
    $post_password = filter_var($post['password'], FILTER_SANITIZE_STRING);
    $post_iname = filter_var($post['iname'], FILTER_SANITIZE_STRING);
    $post_fname = filter_var($post['fname'], FILTER_SANITIZE_STRING);
 
    $callbackStatus = 400;
    $callbackTitle = 'Соообщение системы';
    $callbackText = '';
 
    // Проверка токена - Если токен не совпадает то ничего не делаем. Можем записать в лог или написать письмо админу
    if ($csrf == $token) {
        $email = $utility->clean($post_email);
        $new_phone = $utility->phone_clean($post_phone);
        $password = $utility->clean($post_password);
        $iname = $utility->clean($post_iname);
        $fname = $utility->clean($post_fname);
 
        $pattern = "/^[\+0-9\-\(\)\s]*$/";
        if(preg_match($pattern, $new_phone)) {
            $phone = $new_phone;
        } else {
            $callback = array(
                'status' => 400,
                'title' => "Сообщение системы",
                'text' => "Номер телефона не валиден"
            );
            // Выводим заголовки
            $response->withStatus(200);
            $response->withHeader('Content-type', 'application/json');
            // Выводим json
            echo json_encode($callback);
        }
        
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
                    $identificator = $config['vendor']['crypto']::encrypt($cookie, $cookie_key);
 
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
                        $session->phone = $config['vendor']['crypto']::encrypt($phone, $session_key);
                        $session->email = $config['vendor']['crypto']::encrypt($email, $session_key);
                        $session->iname = $config['vendor']['crypto']::encrypt($iname, $session_key);
                        $session->fname = $config['vendor']['crypto']::encrypt($fname, $session_key);

                        $callback = ['status' => 200];
                        // Выводим заголовки
                        $response->withStatus(200);
                        $response->withHeader('Content-type', 'application/json');
                        // Выводим json
                        echo json_encode($callback);
                    } else {
                        $callback = array(
                            'status' => 400,
                            'title' => "Сообщение системы",
                            'text' => "Ошибка"
                        );
                        // Выводим заголовки
                        $response->withStatus(200);
                        $response->withHeader('Content-type', 'application/json');
                        // Выводим json
                        echo json_encode($callback);
                    }
                } else {
                    $callback = array(
                        'status' => 400,
                        'title' => "Сообщение системы",
                        'text' => "Пользователь уже существует"
                    );
                    // Выводим заголовки
                    $response->withStatus(200);
                    $response->withHeader('Content-type', 'application/json');
                    // Выводим json
                    echo json_encode($callback);
                }
            } else {
                $callback = array(
                    'status' => 400,
                    'title' => "Сообщение системы",
                    'text' => "Введите правильные данные !"
                );
                // Выводим заголовки
                $response->withStatus(200);
                $response->withHeader('Content-type', 'application/json');
                // Выводим json
                echo json_encode($callback);
            }
        } else {
            $callback = array(
                'status' => 400,
                'title' => "Сообщение системы",
                'text' => "Заполните пустые поля"
            );
            // Выводим заголовки
            $response->withStatus(200);
            $response->withHeader('Content-type', 'application/json');
            // Выводим json
            echo json_encode($callback);
        }
        //print_r($callback);
    } else {
        $callback = array(
            'status' => 400,
            'title' => "Сообщение системы безопасности",
            'text' => "Перегрузите страницу"
        );
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
        // Выводим json
        echo json_encode($callback);
    }
 
    $callback = ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
    // Выводим заголовки
    $response->withStatus(200);
    $response->withHeader('Content-type', 'application/json');
    // Подменяем заголовки
    $response = $hook->response();
    // Выводим json
    echo json_encode($hook->callback($callback));
 
});
 