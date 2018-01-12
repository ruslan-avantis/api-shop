<?php 
 
use Slim\Http\Request;
use Slim\Http\Response;
use Adbar\Session;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException as CryptoEx;
use Sinergi\BrowserDetector\Language as Langs;
use RouterDb\Db;
use RouterDb\Router;
use ApiShop\Config\Settings;
use ApiShop\Utilities\Utility;
use ApiShop\Model\Security;
use ApiShop\Resources\Language;
use ApiShop\Resources\User;
use ApiShop\Resources\Install;
 
// Записать выбранный магазин в сессию
$app->post('/check-api-key', function (Request $request, Response $response, array $args) {
    // Подключаем конфиг Settings\Config
    $config = (new Settings())->get();
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    
    try {
        // Получаем токен из сессии
        $token = Crypto::decrypt($session->token, $token_key);
    } catch (CryptoEx $ex) {
        (new Security())->token();
        // Сообщение об Атаке или подборе токена
    }
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
    try {
        // Получаем токен из POST
        $post_csrf = Crypto::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
    } catch (CryptoEx $ex) {
        (new Security())->csrf();
        // Сообщение об Атаке или подборе csrf
    }
    // Подключаем плагины
    $utility = new Utility();
    // Чистим данные на всякий случай пришедшие через POST
    $csrf = $utility->clean($post_csrf);
    // Проверка токена - Если токен не совпадает то ничего не делаем. Можем записать в лог или написать письмо админу
    if ($csrf == $token) {
        $public_key = filter_var($post['public_key'], FILTER_SANITIZE_STRING);
        if (strlen($public_key) == $config["settings"]["install"]["strlen"]) {
 
            $session->install = null;
 
            // Подключаемся к базе json
            $db = new Db("json", $config);
            // Обновляем public_key в базе
            $db->put("db", ["public_key" => $public_key], 1);
 
            // Сохраняем резервный public_key
            if (!file_exists($config["settings"]["install"]["file"])) {
                file_put_contents($config["settings"]["install"]["file"], $public_key);
            }
 
            $callback = array(
                'status' => 200,
                'title' => "Информация",
                'text' => "Все ок"
            );
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
            'text' => "Что то не так"
        );
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
        // Выводим json
        echo json_encode($callback);
    }
});
 
// Записать в сессию
$app->post('/check-key', function (Request $request, Response $response, array $args) {
    // Подключаем конфиг Settings\Config
    $config = (new Settings())->get();
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    
    try {
        // Получаем токен из сессии
        $token = Crypto::decrypt($session->token, $token_key);
    } catch (CryptoEx $ex) {
        (new Security())->token();
        // Сообщение об Атаке или подборе токена
    }
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
    try {
        // Получаем токен из POST
        $post_csrf = Crypto::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
    } catch (CryptoEx $ex) {
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
            'text' => "Что то не так"
        );
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
        // Выводим json
        echo json_encode($callback);
    }
});

// Записать в сессию
$app->post('/check-no-key', function (Request $request, Response $response, array $args) {
    // Подключаем конфиг Settings\Config
    $config = (new Settings())->get();
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    
    try {
        // Получаем токен из сессии
        $token = Crypto::decrypt($session->token, $token_key);
    } catch (CryptoEx $ex) {
        (new Security())->token();
        // Сообщение об Атаке или подборе токена
    }
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
    try {
        // Получаем токен из POST
        $post_csrf = Crypto::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
    } catch (CryptoEx $ex) {
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
            'text' => "Что то не так"
        );
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
        // Выводим json
        echo json_encode($callback);
    }
});
 
// Записать выбранный магазин в сессию
$app->post('/check-store', function (Request $request, Response $response, array $args) {
    // Подключаем конфиг Settings\Config
    $config = (new Settings())->get();
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    
    try {
        // Получаем токен из сессии
        $token = Crypto::decrypt($session->token, $token_key);
    } catch (CryptoEx $ex) {
        (new Security())->token();
        // Сообщение об Атаке или подборе токена
    }
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
    try {
        // Получаем токен из POST
        $post_csrf = Crypto::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
    } catch (CryptoEx $ex) {
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
            'text' => "Что то не так"
        );
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
        // Выводим json
        echo json_encode($callback);
    }
});
  
// Записать выбранный шаблон в сессию
$app->post('/check-template', function (Request $request, Response $response, array $args) {
    // Подключаем конфиг Settings\Config
    $config = (new Settings())->get();
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    
    try {
        // Получаем токен из сессии
        $token = Crypto::decrypt($session->token, $token_key);
    } catch (CryptoEx $ex) {
        (new Security())->token();
        // Сообщение об Атаке или подборе токена
    }
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
    try {
        // Получаем токен из POST
        $post_csrf = Crypto::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
    } catch (CryptoEx $ex) {
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
 
                    // Обновляем название шаблона в базе
                    // Подключаемся к базе
                    $db = new Db("json", $config);
                    // Обновляем название шаблона в базе
                    $db->put("db", ["template" => $dir], 1);
                    
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
                'text' => "Что то не так"
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
            'text' => "Что то не так"
        );
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
        // Выводим json
        echo json_encode($callback);
    }
});
 
// Регистрация продавца
$app->post('/check-in-seller', function (Request $request, Response $response, array $args) {
    $today = date("Y-m-d H:i:s");
    // Подключаем конфиг Settings\Config
    $config = (new Settings())->get();
    // Читаем ключи
    $session_key = $config['key']['session'];
    $cookie_key = $config['key']['cookie'];
    $token_key = $config['key']['token'];
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    try {
        // Получаем токен из сессии
        $token = Crypto::decrypt($session->token, $token_key);
    } catch (CryptoEx $ex) {
        (new Security())->token();
        // Сообщение об Атаке или подмене сессии
        $callback = array(
            'status' => 400,
            'title' => "Сообщение системы",
            'text' => "Вы не прошли проверку системы безопасности !<br>У вас осталась одна попытка :)"
        );
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
        // Выводим json
        echo json_encode($callback);
    }
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
    $post_email = filter_var($post['email'], FILTER_SANITIZE_STRING);
    $post_phone = filter_var($post['phone'], FILTER_SANITIZE_STRING);
    $post_password = filter_var($post['password'], FILTER_SANITIZE_STRING);
    $post_iname = filter_var($post['iname'], FILTER_SANITIZE_STRING);
    $post_fname = filter_var($post['fname'], FILTER_SANITIZE_STRING);
    try {
        // Получаем токен из POST
        $post_csrf = Crypto::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
    } catch (CryptoEx $ex) {
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
                    $session->clear();
                    // Создаем новую cookie
                    $cookie = $utility->random_token();
                    // Генерируем identificator
                    $identificator = Crypto::encrypt($cookie, $cookie_key);
                    // Записываем пользователю новый cookie
                    $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
                    if ($config['settings']['site']['cookie_httponly'] === true){
                        setcookie($config['settings']['session']['name'], $identificator, time()+60*60*24*365, '/', $domain, 1, true);
                    } else {
                        setcookie($config['settings']['session']['name'], $identificator, time()+60*60*24*365, '/', $domain);
                    }
                    // Пишем в сессию identificator cookie
 
                    $arr["role_id"] = 100;
                    $arr["password"] = password_hash($password, PASSWORD_DEFAULT);
                    $arr["phone"] = $phone;
                    $arr["email"] = $email;
                    $arr["language"] = $session->language;
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
                        $session->user_id = Crypto::encrypt($user_id, $session_key);
                        $session->phone = Crypto::encrypt($phone, $session_key);
                        $session->email = Crypto::encrypt($email, $session_key);
                        $session->iname = Crypto::encrypt($iname, $session_key);
                        $session->fname = Crypto::encrypt($fname, $session_key);

                        $callback = array(
                            'status' => 200,
                            'title' => "Сообщение системы",
                            'text' => "Урааааааа"
                        );
                        // Выводим заголовки
                        $response->withStatus(200);
                        $response->withHeader('Content-type', 'application/json');
                        // Выводим json
                        echo json_encode($callback);
                    } else {
                        $callback = array(
                            'status' => 400,
                            'title' => "Сообщение системы",
                            'text' => "Что то не так"
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
});
 