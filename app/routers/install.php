<?php 
 
use Slim\Http\Request;
use Slim\Http\Response;
 
use Pllano\RouterDb\Db;
use Pllano\RouterDb\Router;
 
use ApiShop\Config\Settings;
use ApiShop\Utilities\Utility;
use ApiShop\Model\Security;
use ApiShop\Model\User;
use ApiShop\Model\Install;
 
// Активация с помощью public_key
$app->post('/install-api-key', function (Request $request, Response $response, array $args) {
    // Подключаем конфиг Settings\Config
    $config = (new Settings())->get();
    // Подключаем плагины
    $utility = new Utility();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
 
    try {
        // Получаем токен из сессии
        $token = $config['vendor']['crypto']::decrypt($session->token, $token_key);
    } catch (\Exception $ex) {
        (new Security())->token();
        // Сообщение об Атаке или подборе токена
    }
    try {
        // Получаем токен из POST
        $post_csrf = $config['vendor']['crypto']::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
    } catch (\Exception $ex) {
        (new Security())->csrf();
        // Сообщение об Атаке или подборе csrf
    }
 
    // Чистим данные на всякий случай пришедшие через POST
    $csrf = $utility->clean($post_csrf);
    // Проверка токена - Если токен не совпадает то ничего не делаем. Можем записать в лог или написать письмо админу
    if ($csrf == $token) {
 
        $public_key = filter_var($post['public_key'], FILTER_SANITIZE_STRING);
        if (isset($public_key)) {
 
            $session->install = null;
 
            $dir_name = __DIR__ .'/../config'.$config["db"]["json"]["dir_name"];
            $rep = 'https://raw.githubusercontent.com/pllano/structure-db/master/db.json';
            file_put_contents($dir_name.'core/db.json', file_get_contents($rep));
            // Скачиваем демо данные
            $db = json_decode(file_get_contents($dir_name.'core/db.json'), true);
 
            if (count($db) >= 1) {
                foreach($db as $value)
                {
                    if (isset($value['demo_data'])) {
                        $data = $value['demo_data'];
                        $table = $value['table'];
                        $get_config = file_get_contents($data.'/'.$table.'.config.json');
                        $put_config = $dir_name.''.$table.'.config.json';
                        file_put_contents($put_config, $get_config);
                        $get_data = file_get_contents($data.'/'.$table.'.data.json');
                        $put_data = $dir_name.''.$table.'.data.json';
                        file_put_contents($put_data, $get_data);
                    }
                }
            }
 
            $api = null;
            $httpClient = new $config['vendor']['http_client']();
            $httpBody = $httpClient->request('GET', $config["db"]["pllanoapi"]["url"].'api?public_key='.$public_key);
            $resp = $httpBody->getBody();
            $output = $utility->clean_json($resp);
            $records = json_decode($output, true);
            if (isset($records['headers']['code'])) {
                if(is_object($records["body"]["items"]["0"]["item"])) {
                    $api = (array)$records["body"]["items"]["0"]["item"];
                } elseif (is_array($records["body"]["items"]["0"]["item"])) {
                    $api = $records["body"]["items"]["0"]["item"];
                }
                if (isset($api['alias'])) {
                    $paramPost['seller']['alias'] = $api['alias'];
                    $paramPost['seller']['download_dir'] = $api['download_dir'];
                    $paramPost['seller']['download_alias'] = $api['download_alias'];
                    $paramPost['seller']['terms_of_delivery'] = $api['terms_of_delivery'];
                    $paramPost['seller']['currency_code'] = $api['currency_code'];
                    $paramPost['seller']['private_key'] = $api['private_key'];
                }
            }
 
            $paramPost['seller']['public_key'] = $public_key;
            $paramPost['db']['pllanoapi']['public_key'] = $public_key;
            $paramPost['db']['api']['public_key'] = $public_key;
 
            if (isset($session->template)) {
                $template = $session->template;
            } else {
                $template = 'mini-mo';
            }
            $paramPost['settings']['themes']['template'] = $template;
 
            // Подключаем класс файла конфигурации
            $settingsAdmin = new \ApiShop\Admin\Config();
            // Получаем массив конфигурации
            $arrJson = $settingsAdmin->get();
            // Соеденяем два массива
            $newArr = array_replace_recursive($arrJson, $paramPost);
            // Сохраняем
            $settingsAdmin->put($newArr);
 
            $callback = ['status' => 200];
 
        } else {
            $callback = array(
                'status' => 400,
                'title' => "Соообщение системы",
                'text' => "Не валидный public_key"
            );
        }
 
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
        // Выводим json
        echo json_encode($callback);
    } else {
        $callback = array(
            'status' => 400,
            'title' => "Ошибка",
            'text' => "Ошибка"
        );
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
        // Выводим json
        echo json_encode($callback);
    }
});
 
// Записать в сессию
$app->post('/install-key', function (Request $request, Response $response, array $args) {
    // Подключаем конфиг Settings\Config
    $config = (new Settings())->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    
    try {
        // Получаем токен из сессии
        $token = $config['vendor']['crypto']::decrypt($session->token, $token_key);
    } catch (\Exception $ex) {
        (new Security())->token();
        // Сообщение об Атаке или подборе токена
    }
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
    try {
        // Получаем токен из POST
        $post_csrf = $config['vendor']['crypto']::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
    } catch (\Exception $ex) {
        (new Security())->csrf();
        // Сообщение об Атаке или подборе csrf
    }
    // Подключаем плагины
    $utility = new Utility();
    // Чистим данные на всякий случай пришедшие через POST
    $csrf = $utility->clean($post_csrf);
    // Проверка токена - Если токен не совпадает то ничего не делаем. Можем записать в лог или написать письмо админу
    if ($csrf == $token) {
        
        $session->install = 10;
            
        $callback = array(
            'status' => 200,
            'title' => "Информация",
            'text' => "Все ок"
        );
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
        // Выводим json
        echo json_encode($callback);
    } else {
        $callback = array(
            'status' => 200,
            'title' => "Ошибка",
            'text' => "Ошибка"
        );
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
        // Выводим json
        echo json_encode($callback);
    }
});

// Записать в сессию
$app->post('/install-no-key', function (Request $request, Response $response, array $args) {
    // Подключаем конфиг Settings\Config
    $config = (new Settings())->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    
    try {
        // Получаем токен из сессии
        $token = $config['vendor']['crypto']::decrypt($session->token, $token_key);
    } catch (\Exception $ex) {
        (new Security())->token();
        // Сообщение об Атаке или подборе токена
    }
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
    try {
        // Получаем токен из POST
        $post_csrf = $config['vendor']['crypto']::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
    } catch (\Exception $ex) {
        (new Security())->csrf();
        // Сообщение об Атаке или подборе csrf
    }
    // Подключаем плагины
    $utility = new Utility();
    // Чистим данные на всякий случай пришедшие через POST
    $csrf = $utility->clean($post_csrf);
    // Проверка токена - Если токен не совпадает то ничего не делаем. Можем записать в лог или написать письмо админу
    if ($csrf == $token) {
        
        $session->install = 0;
            
        $callback = array(
            'status' => 200,
            'title' => "Информация",
            'text' => "Все ок"
        );
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
        // Выводим json
        echo json_encode($callback);
    } else {
        $callback = array(
            'status' => 200,
            'title' => "Ошибка",
            'text' => "Ошибка"
        );
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
        // Выводим json
        echo json_encode($callback);
    }
});
 
// Записать выбранный магазин в сессию
$app->post('/install-store', function (Request $request, Response $response, array $args) {
    // Подключаем конфиг Settings\Config
    $config = (new Settings())->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    
    try {
        // Получаем токен из сессии
        $token = $config['vendor']['crypto']::decrypt($session->token, $token_key);
    } catch (\Exception $ex) {
        (new Security())->token();
        // Сообщение об Атаке или подборе токена
    }
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
    try {
        // Получаем токен из POST
        $post_csrf = $config['vendor']['crypto']::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
    } catch (\Exception $ex) {
        (new Security())->csrf();
        // Сообщение об Атаке или подборе csrf
    }
    // Подключаем плагины
    $utility = new Utility();
    // Чистим данные на всякий случай пришедшие через POST
    $csrf = $utility->clean($post_csrf);
    // Проверка токена - Если токен не совпадает то ничего не делаем. Можем записать в лог или написать письмо админу
    if ($csrf == $token) {
        $id = filter_var($post['id'], FILTER_SANITIZE_STRING);
        if ($id) {
            $session->install = 2;
            $session->install_store = $id;
        }
 
        $callback = array(
            'status' => 200,
            'title' => "Информация",
            'text' => "Все ок"
        );
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
        // Выводим json
        echo json_encode($callback);
    } else {
        $callback = array(
            'status' => 200,
            'title' => "Ошибка",
            'text' => "Ошибка"
        );
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
        // Выводим json
        echo json_encode($callback);
    }
});
 
// Записать выбранный шаблон в сессию
$app->post('/install-template', function (Request $request, Response $response, array $args) {
    // Подключаем конфиг Settings\Config
    $config = (new Settings())->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    
    try {
        // Получаем токен из сессии
        $token = $config['vendor']['crypto']::decrypt($session->token, $token_key);
    } catch (\Exception $ex) {
        (new Security())->token();
        // Сообщение об Атаке или подборе токена
    }
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
    try {
        // Получаем токен из POST
        $post_csrf = $config['vendor']['crypto']::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
    } catch (\Exception $ex) {
        (new Security())->csrf();
        // Сообщение об Атаке или подборе csrf
    }
    // Подключаем плагины
    $utility = new Utility();
    // Чистим данные на всякий случай пришедшие через POST
    $csrf = $utility->clean($post_csrf);
    // Проверка токена - Если токен не совпадает то ничего не делаем. Можем записать в лог или написать письмо админу
    if ($csrf == $token) {
        $id = filter_var($post['id'], FILTER_SANITIZE_STRING);
        $uri = filter_var($post['uri'], FILTER_SANITIZE_STRING);
        $dir = filter_var($post['dir'], FILTER_SANITIZE_STRING);
        $host = filter_var($post['host'], FILTER_SANITIZE_STRING);
        if ($id && $uri && $dir && $host) {
            $template_dir = $config["settings"]["themes"]["dir"]."/".$config["settings"]["themes"]["templates"]."/".$dir;
            if (!file_exists($template_dir)) {
                mkdir($template_dir, 0777, true);
                file_put_contents($template_dir."/template.zip", file_get_contents($uri));
                $zip = new ZipArchive;
                if ($zip->open($template_dir."/template.zip") === true) {
                    $zip->extractTo($template_dir);
                    $zip->close();
 
                    // Обновляем название шаблона в настройках базы db
                    // Подключаемся к базе
                    $db = new Db("json", $config);
                    // Обновляем название шаблона в базе
                    $db->put("db", ["template" => $dir], 1);
 
                    // Если пользователь авторизован как администратор, и у него в сессии есть site_id
                    if (isset($session->authorize) && isset($session->role_id) && isset($session->site_id)) {
                        if ($session->authorize == 1 && $session->role_id == 100) {
                            // Обновляем название шаблона в ресурсе site
                            // Ресурс (таблица) к которому обращаемся
                            $resource = "site";
                            // Отдаем роутеру RouterDb конфигурацию.
                            $router = new Router($config);
                            // Получаем название базы для указанного ресурса
                            $name_db = $router->ping($resource);
                            // Подключаемся к базе
                            $db = new Db($name_db, $config);
                            // Отправляем запрос и получаем данные
                            $response = $db->put($resource, ["template" => $dir], $session->site_id);
                        }
                    }
 
                    $session->template = $dir;
                }
 
                if (file_exists($template_dir."/template.zip")) {
                    unlink($template_dir."/template.zip");
                }
 
                if ($session->install == 2) {
                    $session->install = 3;
                } elseif ($session->install == 10) {
                    $session->install = 11;
                } else {
                    $session->install = null;
                }
 
                $session->install_template = $id;
                $session->install_uri = $uri;
                $session->install_dir = $dir;
                $session->install_host = $host;
 
                $callback = array(
                    'status' => 200,
                    'title' => "Информация",
                    'text' => "Все ок"
                );
 
            } else {
 
                // Обновляем название шаблона в базе
                // Подключаемся к базе
                $db = new Db("json", $config);
                // Обновляем название шаблона в базе
                $db->put("db", ["template" => $dir], 1);
 
                $session->template = $dir;
 
                if ($session->install == 2) {
                    $session->install = 3;
                } elseif ($session->install == 10) {
                    $session->install = 11;
                } else {
                    $session->install = null;
                }
 
                $callback = array(
                    'status' => 200,
                    'title' => "Информация",
                    'text' => "Этот шаблоу уже установлен"
                );
 
            }
        } else {
            $callback = array(
                'status' => 400,
                'title' => "Информация",
                'text' => "Ошибка"
            );
        }
 
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
        // Выводим json
        echo json_encode($callback);
    } else {
        $callback = array(
            'status' => 400,
            'title' => "Ошибка",
            'text' => "Ошибка"
        );
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
        // Выводим json
        echo json_encode($callback);
    }
});
 
// Регистрация продавца
$app->post('/register-in-seller', function (Request $request, Response $response, array $args) {
    $today = date("Y-m-d H:i:s");
    // Подключаем конфиг Settings\Config
    $config = (new Settings())->get();
    // Отдаем роутеру RouterDb конфигурацию.
    $router = new Router($config);
    // Читаем ключи
    $session_key = $config['key']['session'];
    $cookie_key = $config['key']['cookie'];
    $token_key = $config['key']['token'];
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']($config['settings']['session']['name']);
    try {
        // Получаем токен из сессии
        $token = $config['vendor']['crypto']::decrypt($session->token, $token_key);
    } catch (\Exception $ex) {
        (new Security())->token();
        $token = null;
    }
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
    $post_email = filter_var($post['email'], FILTER_SANITIZE_STRING);
    $post_phone = filter_var($post['phone'], FILTER_SANITIZE_STRING);
    $post_password = filter_var($post['password'], FILTER_SANITIZE_STRING);
    $post_iname = filter_var($post['iname'], FILTER_SANITIZE_STRING);
    $post_fname = filter_var($post['fname'], FILTER_SANITIZE_STRING);
    $post_host = filter_var($post['host'], FILTER_SANITIZE_STRING);
    try {
        // Получаем токен из POST
        $post_csrf = $config['vendor']['crypto']::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
    } catch (\Exception $ex) {
        (new Security())->csrf();
        // Сообщение об Атаке или подборе csrf
    }
    // Подключаем плагины
    $utility = new Utility();
    // Чистим данные на всякий случай пришедшие через POST
    $csrf = $utility->clean($post_csrf);
    // Проверка токена - Если токен не совпадает то ничего не делаем. Можем записать в лог или написать письмо админу
    if ($csrf == $token) {
        $email = $utility->clean($post_email);
        $new_phone = $utility->phone_clean($post_phone);
        $password = $utility->clean($post_password);
        $iname = $utility->clean($post_iname);
        $fname = $utility->clean($post_fname);
        $host = $utility->clean($post_host);
 
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
            return false;
        }
 
        if(!empty($phone) && !empty($email) && !empty($iname) && !empty($fname) && !empty($host)) {
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
                    // Записываем пользователю новый cookie
                    $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
 
                    if ($config['settings']['site']['cookie_httponly'] === true){
                        setcookie($config['settings']['session']['name'], $identificator, time()+60*60*24*365, '/', $domain, 1, true);
                    } else {
                        setcookie($config['settings']['session']['name'], $identificator, time()+60*60*24*365, '/', $domain);
                    }
                    
                    $session->host = $host;
                    
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
 
                    $install["password"] = $password_hash;
                    $install["phone"] = $phone;
                    $install["email"] = $email;
                    $install["language"] = "ru";
                    $install["template"] = $config['settings']['themes']['template'];
                    $install["iname"] = $iname;
                    $install["fname"] = $fname;
                    $install["host"] = $host;
                    if (isset($session->template)) {
                        if ($session->template != null) {
                            $install["template"] = $session->template;
                        }
                    }
                    if (isset($_SERVER['SERVER_NAME'])) {
                            $install["server"] = $_SERVER['SERVER_NAME'];
                            $session->server = $_SERVER['SERVER_NAME'];
                    }
                    if (isset($_SERVER['SERVER_ADDR'])) {
                            $install["ip"] = $_SERVER['SERVER_ADDR'];
                            $session->ip = $_SERVER['SERVER_ADDR'];
                    }
 
                    // Регистрируем магазин и продавца на платформе
                    $resource = "install";
                    // Получаем название базы для указанного ресурса
                    $name_db = $router->ping($resource);
                    // Подключаемся к базе
                    $db = new Db($name_db, $config);
                    // Отправляем запрос и получаем данные
                    $records = $db->post($resource, $install);
                    
                    //file_put_contents(__DIR__ . "/records.json", json_encode($records));
                    
                    if (isset($records["headers"]["code"])) {
                        if ($records["headers"]["code"] == 201 || $records["headers"]["code"] == "201") {
 
                            if (isset($records["response"]["public_key"])) {
 
                                $public_key = $records["response"]["public_key"];
                                $private_key = $records["response"]["private_key"];
                                $site_id = $records["response"]["id"];
 
                                $session->private_key = $private_key;
                                $session->public_key = $public_key;
                                $session->site_id = intval($site_id);
                                $session->install = 1;
 
                                $arr["role_id"] = intval(100);
                                $arr["password"] = $password_hash;
                                $arr["phone"] = strval($phone);
                                $arr["email"] = $email;
                                $arr["language"] = "ru";
                                $arr["ticketed"] = 1;
                                $arr["admin_access"] = 1;
                                $arr["iname"] = $iname;
                                $arr["fname"] = $fname;
                                $arr["cookie"] = $cookie;
                                $arr["created"] = $today;
                                $arr["authorized"] = $today;
                                $arr["alias"] = $utility->random_alias_id();
                                $arr["state"] = 1;
                                $arr["score"] = 1;
 
                                //file_put_contents(__DIR__ . "/arr.json", json_encode($arr));
 
                                // Ресурс (таблица) к которому обращаемся
                                $resource = "user";
                                // Получаем название базы для указанного ресурса
                                $name_db = $router->ping($resource);
                                // Подключаемся к базе
                                $db = new Db($name_db, $config);
                                // Отправляем запрос и получаем данные
                                $user = $db->post($resource, $arr);
                        
                                if (isset($records["headers"]["code"])) {
                                    if ($records["headers"]["code"] == 201 || $records["headers"]["code"] == "201") {
 
                                        $user_id = $user["response"]["id"];
 
                                        //file_put_contents(__DIR__ . "/user_id.json", json_encode($user));
 
                                        if ($user_id >= 1) {
                                            // Обновляем данные в сессии
                                            $session->authorize = 1;
                                            $session->role_id = intval(100);
                                            $session->cookie = $identificator;
                                            $session->platform_user_id = intval($user_id);
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
                                            //return false;
 
                                        } else {
                                            $callback = array(
                                                'status' => 400,
                                                'title' => "Сообщение системы",
                                                'text' => "Не могу создать пользователя. Нет id"
                                            );
                                            // Выводим заголовки
                                            $response->withStatus(200);
                                            $response->withHeader('Content-type', 'application/json');
                                            // Выводим json
                                            echo json_encode($callback);
                                            return false;
                                        }
 
                                    } else {
                                        $callback = array(
                                            'status' => 400,
                                            'title' => "Сообщение системы",
                                            'text' => "Не могу создать пользователя"
                                        );
                                        // Выводим заголовки
                                        $response->withStatus(200);
                                        $response->withHeader('Content-type', 'application/json');
                                        // Выводим json
                                        echo json_encode($callback);
                                        return false;
                                    }
 
                                } else {
                                    $callback = array(
                                        'status' => 400,
                                        'title' => "Сообщение системы",
                                        'text' => "Не могу создать пользователя. В данных есть ошибка"
                                    );
                                    // Выводим заголовки
                                    $response->withStatus(200);
                                    $response->withHeader('Content-type', 'application/json');
                                    // Выводим json
                                    echo json_encode($callback);
                                    return false;
                                }
 
                            } else {
                                $callback = array(
                                    'status' => 400,
                                    'title' => "Сообщение системы",
                                    'text' => "Не могу получить public_key -1"
                                );
                                // Выводим заголовки
                                $response->withStatus(200);
                                $response->withHeader('Content-type', 'application/json');
                                // Выводим json
                                echo json_encode($callback);
                                return false;
                            }
 
                        } else {
                            $callback = array(
                                'status' => 400,
                                'title' => "Сообщение системы",
                                'text' => "Не могу получить public_key -2"
                            );
                            // Выводим заголовки
                            $response->withStatus(200);
                            $response->withHeader('Content-type', 'application/json');
                            // Выводим json
                            echo json_encode($callback);
                            return false;
                        }
 
                    } else {
                        $callback = array(
                            'status' => 400,
                            'title' => "Сообщение системы",
                            'text' => "Не могу получить public_key -3"
                        );
                        // Выводим заголовки
                        $response->withStatus(200);
                        $response->withHeader('Content-type', 'application/json');
                        // Выводим json
                        echo json_encode($callback);
                        return false;
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
                    return false;
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
                return false;
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
            return false;
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
        return false;
    } 
});
 
// Запуск магазина
$app->post('/start-shop', function (Request $request, Response $response, array $args) {
    // Подключаем конфиг Settings\Config
    $config = (new Settings())->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    
    try {
        // Получаем токен из сессии
        $token = $config['vendor']['crypto']::decrypt($session->token, $token_key);
    } catch (\Exception $ex) {
        (new Security())->token();
        // Сообщение об Атаке или подборе токена
    }
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
    try {
        // Получаем токен из POST
        $post_csrf = $config['vendor']['crypto']::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
    } catch (\Exception $ex) {
        (new Security())->csrf();
        // Сообщение об Атаке или подборе csrf
    }
    // Подключаем плагины
    $utility = new Utility();
    // Чистим данные на всякий случай пришедшие через POST
    $csrf = $utility->clean($post_csrf);
    // Проверка токена - Если токен не совпадает то ничего не делаем. Можем записать в лог или написать письмо админу
    if ($csrf == $token) {
        if (isset($session->install_store) && isset($session->public_key) && isset($session->template) && isset($session->site_id)) {
 
            $putArr["public_key"] = $session->public_key;
            $putArr["template"] = $session->template;
            $putArr["host"] = $session->host;
            $putArr["store"] = $session->install_store;
            $putArr["user_id"] = $session->platform_user_id;
 
            // Регистрируем магазин и продавца на платформе
            $resource = "install";
            // Отдаем роутеру RouterDb конфигурацию.
            $router = new Router($config);
            // Получаем название базы для указанного ресурса
            $name_db = $router->ping($resource);
            // Подключаемся к базе
            $db = new Db($name_db, $config);
            // Отправляем запрос и получаем данные
            $records = $db->put($resource, $putArr);
            
            //file_put_contents(__DIR__ . "/put-records.json", json_encode($records));
 
            if (isset($records["headers"]["code"])) {
                if ($records["headers"]["code"] == 202 || $records["headers"]["code"] == "202") {
 
                    $public_key = $session->public_key;
                    $template = $session->template;
 
                    $dir_name = __DIR__ .'/../config'.$config["db"]["json"]["dir_name"];
                    $rep = 'https://raw.githubusercontent.com/pllano/structure-db/master/db.json';
                    file_put_contents($dir_name.'core/db.json', file_get_contents($rep));
                    // Скачиваем демо данные
                    $db = json_decode(file_get_contents($dir_name.'core/db.json'), true);
 
                    if (count($db) >= 1) {
                        foreach($db as $value)
                        {
                            if (isset($value['demo_data'])) {
                                $data = $value['demo_data'];
                                $table = $value['table'];
                                $get_config = file_get_contents($data.'/'.$table.'.config.json');
                                $put_config = $dir_name.''.$table.'.config.json';
                                file_put_contents($put_config, $get_config);
                                $get_data = file_get_contents($data.'/'.$table.'.data.json');
                                $put_data = $dir_name.''.$table.'.data.json';
                                file_put_contents($put_data, $get_data);
                            }
                        }
                    }
 
                    // Подключаем класс
                    $settingsAdmin = new \ApiShop\Admin\Config();
                    // Получаем массив
                    $arrJson = $settingsAdmin->get();
                    $paramPost = array();
                    $paramPost['seller']['public_key'] = $public_key;
                    $paramPost['db']['pllanoapi']['public_key'] = $public_key;
                    $paramPost['db']['api']['public_key'] = $public_key;
                    $paramPost['settings']['themes']['template'] = $template;
                    // Соеденяем массивы
                    $newArr = array_replace_recursive($arrJson, $paramPost);
                    // Сохраняем в файл
                    $settingsAdmin->put($newArr);
 
                    $session->install = null;
                    $session->install_store = null;
                    $session->template = null;
                    $session->private_key = null;
                    $session->public_key = null;
                    $session->platform_user_id = null;
 
                    $callback = ['status' => 200];
 
                    // Выводим заголовки
                    $response->withStatus(200);
                    $response->withHeader('Content-type', 'application/json');
                    // Выводим json
                    echo json_encode($callback);
                    return;
 
                } else {
                    $callback = array(
                        'status' => 400,
                        'title' => "Ошибка",
                        'text' => "Ошибка"
                    );
                    // Выводим заголовки
                    $response->withStatus(200);
                    $response->withHeader('Content-type', 'application/json');
                    // Выводим json
                    echo json_encode($callback);
                    return;
                }
            } else {
                $callback = array(
                    'status' => 400,
                    'title' => "Ошибка",
                    'text' => "Ошибка"
                );
                // Выводим заголовки
                $response->withStatus(200);
                $response->withHeader('Content-type', 'application/json');
                // Выводим json
                echo json_encode($callback);
                return;
            }
        } else {
            $callback = array(
                'status' => 400,
                'title' => "Ошибка",
                'text' => "Ошибка"
            );
            // Выводим заголовки
            $response->withStatus(200);
            $response->withHeader('Content-type', 'application/json');
            // Выводим json
            echo json_encode($callback);
            return;
        }
    } else {
        $callback = array(
            'status' => 400,
            'title' => "Ошибка",
            'text' => "Ошибка"
        );
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
        // Выводим json
        echo json_encode($callback);
        return;
    }
 
});
 