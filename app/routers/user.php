<?php 
    
use Slim\Http\Request;
use Slim\Http\Response;
use Adbar\Session;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException as CryptoEx;
use ApiShop\Config\Settings;
use ApiShop\Utilities\Utility;
use ApiShop\Model\Security;
use ApiShop\Model\SessionUser;
use ApiShop\Resources\Language;
use ApiShop\Resources\Site;
use ApiShop\Resources\User;
use ApiShop\Database\Router;
use ApiShop\Database\Ping;
 
// Страница авторизации
$app->get('/sign-in', function (Request $request, Response $response, array $args) {
    // Получаем конфигурацию \ApiShop\Config\Settings
    $config = (new Settings())->get();
    $site = new Site();
    $site_config = $site->get();
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Подключаем временное хранилище
    $session_temp = new Session("_temp");
    // Читаем ключи
    $session_key = $config['key']['session'];
    $token_key = $config['key']['token'];
    // Получаем массив данных из таблицы language на языке из $session->language
    if (isset($session->language)) {
        $lang = $session->language;
    } else {
        $lang = $site_config["language"];
    }
    // Подключаем мультиязычность
    $language = (new Language())->get($lang);
    //print_r($language);
    // Подключаем плагины
    $utility = new Utility();
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token = Crypto::encrypt($token, $token_key);
    // Если запись об авторизации есть расшифровываем
    if ($session->authorize) {
        $authorize = $session->authorize;
    } else {
        $session->authorize = 0;
        $authorize = 0;
    }
    // Отдаем информацию для шаблонизатора
    // Информацию о странице
    $page = ["page" => 'sign-in'];
    // Данные пользователя из сессии
    $session_user_data =(new SessionUser())->get();
    // Что бы не давало ошибку присваиваем пустое значение
    $content = '';
    // print_r($content);
 
    return $this->twig->render('sign-in.html', [
        "template" => $site->template(),
        "pages" => $page,
        "site" => $site_config,
        "config" => $config['settings']['site'],
        "language" => $language,
        "token" => $session->token,
        "session" => $session_user_data,
        "session_temp" => $session_temp,
        "content" => $content
    ]);
});
 
// Страница регистрации
$app->get('/sign-up', function (Request $request, Response $response, array $args) {
    // Получаем конфигурацию \ApiShop\Config\Settings
    $config = (new Settings())->get();
    $site = new Site();
    $site_config = $site->get();
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Подключаем временное хранилище
    $session_temp = new Session("_temp");
    // Читаем ключи
    $session_key = $config['key']['session'];
    $token_key = $config['key']['token'];
    // Получаем массив данных из таблицы language на языке из $session->language
    if (isset($session->language)) {
        $lang = $session->language;
    } else {
        $lang = $site_config["language"];
    }
    // Подключаем мультиязычность
    $language = (new Language())->get($lang);
    //print_r($language);
    // Подключаем плагины
    $utility = new Utility();
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token = Crypto::encrypt($token, $token_key);
    // Если запись об авторизации есть расшифровываем
    if ($session->authorize) {
        $authorize = $session->authorize;
    } else {
        $session->authorize = 0;
        $authorize = 0;
    }
    // Отдаем информацию для шаблонизатора
    // Информацию о странице
    $page = ["page" => 'sign-up'];
    // Данные пользователя из сессии
    $session_user_data =(new SessionUser())->get();
    // Что бы не давало ошибку присваиваем пустое значение
    $content = '';
    // print_r($content);
 
    return $this->twig->render('sign-up.html', [
        "template" => $site->template(),
        "pages" => $page,
        "site" => $site_config,
        "config" => $config['settings']['site'],
        "language" => $language,
        "token" => $session->token,
        "session" => $session_user_data,
        "session_temp" => $session_temp,
        "content" => $content
    ]);
});
 
// Выйти
$app->post('/logout', function (Request $request, Response $response, array $args) {
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
        $session->authorize = null;
        $session->cookie = '';
        unset($session->authorize); // удаляем сесию
        unset($session->id); // удаляем сесию
        unset($session->cookie); // удаляем сесию
        list($x1,$x2) = explode('.',strrev($_SERVER['HTTP_HOST']));
        $xdomain = $x1.'.'.$x2;
        $domain =  strrev($xdomain);
        setcookie($config['settings']['session']['name'], null, time() - ( 3600 * 24 * 31 ), '/', $domain, 1, true);
        $session->destroy();
        $callback = array(
            'status' => "OK",
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
            'status' => "OK",
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
$app->post('/login', function (Request $request, Response $response, array $args) {
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
            'status' => "NO",
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
    $email = $utility->clean($post_email);
    $new_phone = $utility->phone_clean($post_phone);
    $password = $utility->clean($post_password);
    // Проверка токена - Если токен не совпадает то ничего не делаем. Можем записать в лог или написать письмо админу
    if ($csrf == $token) {
        $pattern = "/^[\+0-9\-\(\)\s]*$/";
        if(preg_match($pattern, $new_phone)) {
            $phone = $new_phone;
        } else {
            $callback = array(
                'status' => "NO",
                'title' => "Сообщение системы",
                'text' => "Номер телефона не валиден"
            );
            // Выводим заголовки
            $response->withStatus(200);
            $response->withHeader('Content-type', 'application/json');
            // Выводим json
            echo json_encode($callback);
        }
        if(!empty($phone) && !empty($email)) {
            $email_validate = filter_var($email, FILTER_VALIDATE_EMAIL);
            if($utility->check_length($phone, 8, 25) && $email_validate) {
                $session->phone = Crypto::encrypt($phone, $session_key);
                $session->email = Crypto::encrypt($email, $session_key);
                $user = new User();
                //check for correct email and password
                $user_id = $user->checkLogin($email, $phone, $password);
                if ($user_id != 0) {
                    $database = new Router((new Ping("user"))->get());
                    $user_data = $database->get("user", ["user_id" => $user_id]);
                    $session->language = $user_data['items']['0']['item']["language"];
                    $session->user_id = Crypto::encrypt($user_id, $session_key);
                    $session->phone = Crypto::encrypt($user_data['items']['0']['item']["phone"], $session_key);
                    $session->email = Crypto::encrypt($user_data['items']['0']['item']["email"], $session_key);
                    $session->iname = Crypto::encrypt($user_data['items']['0']['item']["iname"], $session_key);
                    $session->fname = Crypto::encrypt($user_data['items']['0']['item']["fname"], $session_key);
                    $authorize = 1;
                    // Записываем authorize в сессию
                    $session->authorize = Crypto::encrypt($authorize, $session_key);
                    $cookie = Crypto::decrypt($session->cookie, $cookie_key);
                    $user->putUserCode($user_id, $cookie);
                    $callback = array(
                        'status' => "OK",
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
                        'status' => "NO",
                        'title' => "Сообщение системы",
                        'text' => "Login failed. Incorrect credentials"
                    );
                    // Выводим заголовки
                    $response->withStatus(200);
                    $response->withHeader('Content-type', 'application/json');
                    // Выводим json
                    echo json_encode($callback);
                }
            } else {
                $callback = array(
                    'status' => "NO",
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
                'status' => "NO",
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
            'status' => "NO",
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
 
// Регистрация
$app->post('/check-in', function (Request $request, Response $response, array $args) {
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
            'status' => "NO",
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
                'status' => "NO",
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
                    list($x1,$x2) = explode('.',strrev($_SERVER['HTTP_HOST']));
                    $xdomain = $x1.'.'.$x2;
                    $domain =  strrev($xdomain);
                    setcookie($config['settings']['session']['name'], $identificator, time() + 3600 * 24 * 31, '/', $domain, 1, true);
                    // Пишем в сессию identificator cookie
 
                    $database = new Router((new Ping("user"))->get());
                    $arr["role_id"] = 1;
                    $arr["password"] = password_hash($password, PASSWORD_DEFAULT);
                    $arr["phone"] = $phone;
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
                    $user_id = $database->post("user", $arr);
 
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
                            'status' => "OK",
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
                            'status' => "NO",
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
                        'status' => "NO",
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
                    'status' => "NO",
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
                'status' => "NO",
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
            'status' => "NO",
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
 
    