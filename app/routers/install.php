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
use ApiShop\Model\SessionUser;
use ApiShop\Resources\Language;
use ApiShop\Resources\Site;
use ApiShop\Resources\User;
  
// Выйти
$app->post('/check_store', function (Request $request, Response $response, array $args) {
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
            'text' => "Вы вышли из системы"
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
  
// Авторизация
$app->post('/check_template', function (Request $request, Response $response, array $args) {
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
            $session->install = 3;
			$session->install_template = $id;
		}
 
        $callback = array(
            'status' => 200,
            'title' => "Информация",
            'text' => "Вы вышли из системы"
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
 