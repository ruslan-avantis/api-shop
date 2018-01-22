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
use Adbar\Session;
use Defuse\Crypto\Crypto;
use Sinergi\BrowserDetector\Language as Langs;
use ApiShop\Config\Settings;
use ApiShop\Utilities\Utility;
use ApiShop\Resources\Install;
use ApiShop\Resources\Language;
use ApiShop\Resources\Site;
use ApiShop\Resources\Template;
use ApiShop\Model\SessionUser;
use ApiShop\Model\Filter;
use ApiShop\Model\Pagination;
use RouterDb\Db;
use RouterDb\Router;
use ApiShop\Admin\Control;
use ApiShop\Admin\AdminDatabase;
 
// Главная страница админ панели
$app->get('/admin', function (Request $request, Response $response, array $args) {
 
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
 
    $config = (new Settings())->get();
    $admin_config = $config['admin'];
    $template = $admin_config["template"];
 
    // Подключаем плагины
    $utility = new Utility();
 
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token = Crypto::encrypt($token, $token_key);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser())->get();
    
    $templates_list = (new Install())->templates_list($config['seller']['store']);
    //print_r($templates_list);
 
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    // Подключаем определение языка в браузере
    $langs = new Langs();
    // Получаем массив данных из таблицы language на языке из $session->language
    if (isset($getParams['lang'])) {
        if ($getParams['lang'] == "ru" || $getParams['lang'] == "ua" || $getParams['lang'] == "en" || $getParams['lang'] == "de") {
            $lang = $getParams['lang'];
            $session->language = $getParams['lang'];
        } elseif (isset($session->language)) {
            $lang = $session->language;
        } else {
            $lang = $langs->getLanguage();
        }
    } elseif (isset($session->language)) {
        $lang = $session->language;
    } else {
        $lang = $langs->getLanguage();
    }
    // Подключаем мультиязычность
    $language = (new Language())->get($lang);
 
    // Подключаем конфигурацию сайта
    $site = new Site();
    $site->get();
    // Получаем название активного шаблона
    $site_template = $site->template();
 
    $adminDatabase = new AdminDatabase();
    $adminDd = $adminDatabase->list();
 
    $content = "";
    $render = "index";
 
    if (isset($session->authorize)) {
        if ($session->role_id == 100) {
            // Подключаем класс
            $index = new \ApiShop\Admin\Index();
            // Получаем массив с настройками шаблона
            $content = $index->get();
        } else {
            $render = "404";
        }
    } else {
        $session->authorize = null;
        $render = "404";
    }
 
    // Запись в лог
    $this->logger->info("admin/".$render);
 
    return $this->admin->render($render.'.html', [
        "template" => $template,
        "site_template" => $site_template,
        "head" => [
            "page" => $render,
            "title" => $language["709"],
            "keywords" => $language["709"],
            "description" => $language["709"],
            "host" => $host,
            "path" => $path
        ],
        "config" => $config,
        "language" => $language,
        "token" => $session->token,
        "session" => $sessionUser,
        "db" => $adminDd,
        "content" => $content
    ]);
 
});
 
// Активировать заказ
$app->post('/admin/order-activate', function (Request $request, Response $response, array $args) {
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
        
        if (isset($post['alias'])) {
            $alias = filter_var($post['alias'], FILTER_SANITIZE_STRING);

 
            $callback = array(
                'status' => 200,
                'title' => "Информация",
                'text' => "Все ок"
            );
        } else {
            $callback = array(
                'status' => 400,
                'title' => "Соообщение системы",
                'text' => "Не определен alias заказа"
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
 
// Купить и установить шаблон
$app->post('/admin/template-buy', function (Request $request, Response $response, array $args) {
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
        
        if (isset($post['alias'])) {
            $alias = filter_var($post['alias'], FILTER_SANITIZE_STRING);

 
            $callback = array(
                'status' => 200,
                'title' => "Информация",
                'text' => "Все ок"
            );
        } else {
            $callback = array(
                'status' => 400,
                'title' => "Соообщение системы",
                'text' => "Не определен alias шаблона"
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
 
// Установить шаблон
$app->post('/admin/template-install', function (Request $request, Response $response, array $args) {
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
        if (isset($post['alias'])) {
 
            $dir = '';
            $uri = '';
            $name = '';
 
            $alias = filter_var($post['alias'], FILTER_SANITIZE_STRING);
 
            $templates_list = (new Install())->templates_list($config['seller']['store']);
 
            if (count($templates_list) >= 1) {
                foreach($templates_list as $value)
                {
                    if ($value['item']["alias"] == $alias) {
                        $dir = $value['item']['dir'];
                        $uri = $value['item']['uri'];
                        $name = $value['item']['dir'];
 
                        if(isset($dir) && isset($uri)) {
                            $template_dir = $config["settings"]["themes"]["dir"].'/'.$config["settings"]["themes"]["templates"].'/'.$dir;
                            if (!file_exists($template_dir)) {
                                mkdir($template_dir, 0777, true);
                                file_put_contents($template_dir.'/template.zip', file_get_contents($uri));
                                $zip = new ZipArchive;
                                if ($zip->open($template_dir.'/template.zip') === true) {
                                    $zip->extractTo($template_dir);
                                    $zip->close();
									
                                    if (file_exists($template_dir."/template.zip")) {
                                        unlink($template_dir."/template.zip");
                                    }
 
                                    // Активируем шаблон
                                    // Подключаем класс
                                    $settings = new \ApiShop\Admin\Config();
                                    // Получаем массив
                                    $arrJson = $settings->get();
                                    $paramPost['settings']['themes']['template'] = $name;
                                    // Соеденяем массивы
                                    $newArr = array_replace_recursive($arrJson, $paramPost);
                                    // Сохраняем в файл
                                    $settings->put($newArr);
                                }
                                $callback = array('status' => 200);
 
                            } else {
                                $callback = array(
                                    'status' => 400,
                                    'title' => "Соообщение системы",
                                    'text' => "Папка шаблона уже существует. Удалите или переименуйте папку."
                                );
                            }
                        } else {
                            $callback = array(
                                'status' => 400,
                                'title' => "Соообщение системы",
                                'text' => "Шаблон не найден"
                            );
                        }
                    }

                }
            }
        } else {
            $callback = array(
                'status' => 400,
                'title' => "Соообщение системы",
                'text' => "Не определен alias шаблона"
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
 
// Активировать шаблон
$app->post('/admin/template-activate', function (Request $request, Response $response, array $args) {
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
        
        if (isset($post['name'])) {
            $name = filter_var($post['name'], FILTER_SANITIZE_STRING);
            $alias = filter_var($post['alias'], FILTER_SANITIZE_STRING);
 
            // Подключаем класс
            $settings = new \ApiShop\Admin\Config();
            // Получаем массив
            $arrJson = $settings->get();
            //print_r($content);
            $paramPost['settings']['themes']['template'] = $name;
            // Соеденяем массивы
            $newArr = array_replace_recursive($arrJson, $paramPost);
            // Сохраняем в файл
            $settings->put($newArr);
 
            $callback = array(
                'status' => 200,
                'title' => "Информация",
                'text' => "Все ок"
            );
        } else {
            $callback = array(
                'status' => 400,
                'title' => "Соообщение системы",
                'text' => "Не определено название шаблона"
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
 
// Удалить шаблон
$app->post('/admin/template-delete', function (Request $request, Response $response, array $args) {
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
        
        if (isset($post['name'])) {
            $name = filter_var($post['name'], FILTER_SANITIZE_STRING);
 
            $directory = $config["settings"]["themes"]["dir"].'/'.$config["settings"]["themes"]["templates"].'/'.$name;
            // Подключаем класс
            $admin = new \ApiShop\Admin\Control();
            // Получаем массив
            $admin->delete($directory);
 
            $callback = array('status' => 200);
 
        } else {
            $callback = array(
                'status' => 400,
                'title' => "Соообщение системы",
                'text' => "Не определено название шаблона"
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
 
// Список шаблонов
$app->get('/admin/template', function (Request $request, Response $response, array $args) {
 
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
 
    $config = (new Settings())->get();
    $admin_config = $config['admin'];
    $template = $admin_config["template"];
 
    // Подключаем плагины
    $utility = new Utility();
 
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token = Crypto::encrypt($token, $token_key);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser())->get();
 
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    // Подключаем определение языка в браузере
    $langs = new Langs();
    // Получаем массив данных из таблицы language на языке из $session->language
    if (isset($getParams['lang'])) {
        if ($getParams['lang'] == "ru" || $getParams['lang'] == "ua" || $getParams['lang'] == "en" || $getParams['lang'] == "de") {
            $lang = $getParams['lang'];
            $session->language = $getParams['lang'];
        } elseif (isset($session->language)) {
            $lang = $session->language;
        } else {
            $lang = $langs->getLanguage();
        }
    } elseif (isset($session->language)) {
        $lang = $session->language;
    } else {
        $lang = $langs->getLanguage();
    }
    // Подключаем мультиязычность
    $language = (new Language())->get($lang);
 
    // Подключаем конфигурацию сайта
    $site = new Site();
    $site->get();
    // Получаем название активного шаблона
    $site_template = $site->template();
    
    $adminDatabase = new AdminDatabase();
    $adminDd = $adminDatabase->list();
 
    $content = "";
    $api = "";
    $render = "templates";
 
    if (isset($session->authorize)) {
        if ($session->role_id == 100) {
            // Подключаем класс
            $templates = new \ApiShop\Admin\Template();
            // Получаем массив с настройками шаблона
            $content = $templates->get();
            $api = (new Install())->templates_list($config['seller']['store']);
        } else {
            $render = "404";
        }
    } else {
        $session->authorize = null;
        $render = "404";
    }
 
    // Запись в лог
    $this->logger->info("admin/".$render);
 
    return $this->admin->render($render.'.html', [
        "template" => $template,
        "site_template" => $site_template,
        "head" => [
            "page" => $render,
            "title" => $language["815"],
            "keywords" => $language["815"],
            "description" => $language["815"],
            "host" => $host,
            "path" => $path
        ],
        "config" => $config,
        "language" => $language,
        "token" => $session->token,
        "session" => $sessionUser,
        "db" => $adminDd,
        "content" => $content,
        "api" => $api
    ]);
 
});
 
// Страница шаблона
$app->get('/admin/template/{name:[a-z0-9_-]+}', function (Request $request, Response $response, array $args) {
 
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
 
    $config = (new Settings())->get();
    $admin_config = $config['admin'];
    $template = $admin_config["template"];
 
    // Подключаем плагины
    $utility = new Utility();
 
    // Получаем name из url
    if ($request->getAttribute('name')) {
        $name = $utility->clean($request->getAttribute('name'));
    } else {
        $name = null;
    }
 
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token = Crypto::encrypt($token, $token_key);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser())->get();
 
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    // Подключаем определение языка в браузере
    $langs = new Langs();
    // Получаем массив данных из таблицы language на языке из $session->language
    if (isset($getParams['lang'])) {
        if ($getParams['lang'] == "ru" || $getParams['lang'] == "ua" || $getParams['lang'] == "en" || $getParams['lang'] == "de") {
            $lang = $getParams['lang'];
            $session->language = $getParams['lang'];
        } elseif (isset($session->language)) {
            $lang = $session->language;
        } else {
            $lang = $langs->getLanguage();
        }
    } elseif (isset($session->language)) {
        $lang = $session->language;
    } else {
        $lang = $langs->getLanguage();
    }
    // Подключаем мультиязычность
    $language = (new Language())->get($lang);
 
    $adminDatabase = new AdminDatabase();
    $adminDd = $adminDatabase->list();
 
    $content = "";
    $render = "template";
 
    if (isset($session->authorize) && isset($name)) {
        if ($session->role_id) {
            // Подключаем класс
            $templates = new \ApiShop\Admin\Template($name);
            $content = $templates->getOne();
        } else {
            $render = "404";
        }
    } else {
        $session->authorize = null;
        $render = "404";
    }
 
    // Запись в лог
    $this->logger->info("admin/".$render);
 
    return $this->admin->render($render.'.html', [
        "template" => $template,
        "head" => [
            "page" => $render,
            "title" => $language["814"].': '.$render.' - '. $language["709"],
            "keywords" => $language["814"].': '.$render.' - '. $language["709"],
            "description" => $language["814"].': '.$render.' - '. $language["709"],
            "host" => $host,
            "path" => $path
        ],
        "config" => $admin_config,
        "language" => $language,
        "token" => $session->token,
        "session" => $sessionUser,
        "db" => $adminDd,
        "content" => $content
    ]);
 
});
 
// Редактируем настройки шаблона
$app->post('/admin/template/{name:[a-z0-9_-]+}', function (Request $request, Response $response, array $args) {
 
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
 
    $config = (new Settings())->get();
    $admin_config = $config['admin'];
    $template = $admin_config["template"];
 
    // Подключаем плагины
    $utility = new Utility();
 
    // Получаем name из url
    if ($request->getAttribute('name')) {
        $name = $utility->clean($request->getAttribute('name'));
    } else {
        $name = null;
    }
 
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token = Crypto::encrypt($token, $token_key);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser())->get();
 
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    // Подключаем определение языка в браузере
    $langs = new Langs();
    // Получаем массив данных из таблицы language на языке из $session->language
    if (isset($getParams['lang'])) {
        if ($getParams['lang'] == "ru" || $getParams['lang'] == "ua" || $getParams['lang'] == "en" || $getParams['lang'] == "de") {
            $lang = $getParams['lang'];
            $session->language = $getParams['lang'];
        } elseif (isset($session->language)) {
            $lang = $session->language;
        } else {
            $lang = $langs->getLanguage();
        }
    } elseif (isset($session->language)) {
        $lang = $session->language;
    } else {
        $lang = $langs->getLanguage();
    }
    // Подключаем мультиязычность
    $language = (new Language())->get($lang);
 
    $adminDatabase = new AdminDatabase();
    $adminDd = $adminDatabase->list();
 
    $content = "";
    $render = "template";
 
    if (isset($session->authorize) && isset($name)) {
        if ($session->role_id) {
            // Подключаем класс
            $templates = new \ApiShop\Admin\Template($name);
            // Получаем массив
            $arrJson = $templates->getOne();
 
            //print_r($content);
            // Массив из POST
            $paramPost = $request->getParsedBody();
            // Соеденяем массивы
            $newArr = array_replace_recursive($arrJson, $paramPost);
            // Сохраняем в файл
            $templates->put($newArr);
            $content = $templates->getOne();
            
        } else {
            $render = "404";
        }
    } else {
        $session->authorize = null;
        $render = "404";
    }
 
    // Запись в лог
    $this->logger->info("admin/".$render);
 
    return $this->admin->render($render.'.html', [
        "template" => $template,
        "head" => [
            "page" => $render,
            "title" => $language["814"].': '.$render.' - '. $language["709"],
            "keywords" => $language["814"].': '.$render.' - '. $language["709"],
            "description" => $language["814"].': '.$render.' - '. $language["709"],
            "host" => $host,
            "path" => $path
        ],
        "config" => $admin_config,
        "language" => $language,
        "token" => $session->token,
        "session" => $sessionUser,
        "db" => $adminDd,
        "content" => $content
    ]);
 
});
 
// Список шаблонов
$app->get('/admin/plugins', function (Request $request, Response $response, array $args) {
 
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
 
    $config = (new Settings())->get();
    $admin_config = $config['admin'];
    $template = $admin_config["template"];
 
    // Подключаем плагины
    $utility = new Utility();
 
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token = Crypto::encrypt($token, $token_key);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser())->get();
 
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    // Подключаем определение языка в браузере
    $langs = new Langs();
    // Получаем массив данных из таблицы language на языке из $session->language
    if (isset($getParams['lang'])) {
        if ($getParams['lang'] == "ru" || $getParams['lang'] == "ua" || $getParams['lang'] == "en" || $getParams['lang'] == "de") {
            $lang = $getParams['lang'];
            $session->language = $getParams['lang'];
        } elseif (isset($session->language)) {
            $lang = $session->language;
        } else {
            $lang = $langs->getLanguage();
        }
    } elseif (isset($session->language)) {
        $lang = $session->language;
    } else {
        $lang = $langs->getLanguage();
    }
    // Подключаем мультиязычность
    $language = (new Language())->get($lang);
 
    // Подключаем конфигурацию сайта
    $site = new Site();
    $site->get();
    // Получаем название активного шаблона
    $site_template = $site->template();
    
    $adminDatabase = new AdminDatabase();
    $adminDd = $adminDatabase->list();
 
    $content = "";
    $api = "";
    $render = "plugins";
 
    if (isset($session->authorize)) {
        if ($session->role_id == 100) {
            // Подключаем класс
            $templates = new \ApiShop\Admin\Template();
            // Получаем массив с настройками шаблона
            $content = $templates->get();
            $api = (new Install())->templates_list($config['seller']['store']);
        } else {
            $render = "404";
        }
    } else {
        $session->authorize = null;
        $render = "404";
    }
 
    // Запись в лог
    $this->logger->info("admin/".$render);
 
    return $this->admin->render($render.'.html', [
        "template" => $template,
        "site_template" => $site_template,
        "head" => [
            "page" => $render,
            "title" => $language["815"],
            "keywords" => $language["815"],
            "description" => $language["815"],
            "host" => $host,
            "path" => $path
        ],
        "config" => $config,
        "language" => $language,
        "token" => $session->token,
        "session" => $sessionUser,
        "db" => $adminDd,
        "content" => $content,
        "api" => $api
    ]);
 
});
 
// Глобальные настройки
$app->get('/admin/config', function (Request $request, Response $response, array $args) {
 
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
 
    $config = (new Settings())->get();
    $admin_config = $config['admin'];
    $template = $admin_config["template"];
 
    // Подключаем плагины
    $utility = new Utility();
 
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token = Crypto::encrypt($token, $token_key);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser())->get();
 
    $langs = new Langs();
    // Получаем массив данных из таблицы language на языке из $session->language
    if (isset($session->language)) {
        $lang = $session->language;
    } elseif ($langs->getLanguage()) {
        $lang = $langs->getLanguage();
    } else {
        $lang = $admin_config["language"];
    }
    // Подключаем мультиязычность
    $language = (new Language())->get($lang);
    // Подключаем конфигурацию сайта
    $site = new Site();
    $site->get();
    // Получаем название активного шаблона
    $site_template = $site->template();
    
    $adminDatabase = new AdminDatabase();
    $adminDd = $adminDatabase->list();
 
    $content = "";
    $render = "config";
 
    if (isset($session->authorize)) {
        if ($session->role_id == 100) {
            // Подключаем класс
            $settings = new \ApiShop\Admin\Config();
            // Получаем массив с настройками шаблона
            $content = $settings->get();
            //print_r($content);
        } else {
            $render = "404";
        }
    } else {
        $session->authorize = null;
        $render = "404";
    }
 
    // Запись в лог
    $this->logger->info("admin/".$render);
 
    return $this->admin->render($render.'.html', [
        "template" => $template,
        "head" => [
            "page" => $render,
            "title" => $language["709"],
            "keywords" => $language["709"],
            "description" => $language["709"],
            "host" => $host,
            "path" => $path
        ],
        "config" => $config,
        "language" => $language,
        "token" => $session->token,
        "session" => $sessionUser,
        "db" => $adminDd,
        "content" => $content
    ]);
 
});
 
// Редактируем глобальные настройки
$app->post('/admin/config', function (Request $request, Response $response, array $args) {
 
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
 
    $config = (new Settings())->get();
    $admin_config = $config['admin'];
    $template = $admin_config["template"];
 
    // Подключаем плагины
    $utility = new Utility();
 
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token = Crypto::encrypt($token, $token_key);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser())->get();
 
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    // Подключаем определение языка в браузере
    $langs = new Langs();
    // Получаем массив данных из таблицы language на языке из $session->language
    if (isset($getParams['lang'])) {
        if ($getParams['lang'] == "ru" || $getParams['lang'] == "ua" || $getParams['lang'] == "en" || $getParams['lang'] == "de") {
            $lang = $getParams['lang'];
            $session->language = $getParams['lang'];
        } elseif (isset($session->language)) {
            $lang = $session->language;
        } else {
            $lang = $langs->getLanguage();
        }
    } elseif (isset($session->language)) {
        $lang = $session->language;
    } else {
        $lang = $langs->getLanguage();
    }
    // Подключаем мультиязычность
    $language = (new Language())->get($lang);
 
    // Подключаем конфигурацию сайта
    $site = new Site();
    $site->get();
    // Получаем название активного шаблона
    $site_template = $site->template();
    
    $adminDatabase = new AdminDatabase();
    $adminDd = $adminDatabase->list();
 
    $content = "";
    $render = "config";
 
    if (isset($session->authorize)) {
        if ($session->role_id == 100) {
            // Подключаем класс
            $settings = new \ApiShop\Admin\Config();
            // Получаем массив
            $arrJson = $settings->get();
            //print_r($content);
            // Массив из POST
            $paramPost = $request->getParsedBody();
            // Соеденяем массивы
            $newArr = array_replace_recursive($arrJson, $paramPost);
            // Сохраняем в файл
            $settings->put($newArr);
            $content = $settings->get();
        } else {
            $render = "404";
        }
    } else {
        $session->authorize = null;
        $render = "404";
    }
 
    // Запись в лог
    $this->logger->info("admin/".$render);
 
    return $this->admin->render($render.'.html', [
        "template" => $template,
        "head" => [
            "page" => $render,
            "title" => $language["709"],
            "keywords" => $language["709"],
            "description" => $language["709"],
            "host" => $host,
            "path" => $path
        ],
        "config" => $config,
        "language" => $language,
        "token" => $session->token,
        "session" => $sessionUser,
        "db" => $adminDd,
        "content" => $content
    ]);
 
});
 
// Страница ресурса (таблицы)
$app->get('/admin/db/{resource:[a-z0-9_-]+}[/{id:[0-9_]+}]', function (Request $request, Response $response, array $args) {
 
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
 
    $config = (new Settings())->get();
    $admin_config = $config['admin'];
    $template = $admin_config["template"];
 
    // Подключаем плагины
    $utility = new Utility();
 
    // Получаем resource из url
    if ($request->getAttribute('resource')) {
        $resource = $utility->clean($request->getAttribute('resource'));
    } else {
        $resource = null;
    }
 
    // Получаем resource из url
    if ($request->getAttribute('id')) {
        $id = $utility->clean($request->getAttribute('id'));
    } else {
        $id = null;
    }
 
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token = Crypto::encrypt($token, $token_key);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser())->get();
 
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    // Подключаем определение языка в браузере
    $langs = new Langs();
    // Получаем массив данных из таблицы language на языке из $session->language
    if (isset($getParams['lang'])) {
        if ($getParams['lang'] == "ru" || $getParams['lang'] == "ua" || $getParams['lang'] == "en" || $getParams['lang'] == "de") {
            $lang = $getParams['lang'];
            $session->language = $getParams['lang'];
        } elseif (isset($session->language)) {
            $lang = $session->language;
        } else {
            $lang = $langs->getLanguage();
        }
    } elseif (isset($session->language)) {
        $lang = $session->language;
    } else {
        $lang = $langs->getLanguage();
    }
    // Подключаем мультиязычность
    $language = (new Language())->get($lang);
 
    $adminDatabase = new AdminDatabase();
    $adminDd = $adminDatabase->list();
 
    $content = "";
    if (isset($id)) {
        $render = "db-id";
    } else {
        $render = "db";
    }
    
    $name_db = null;
 
    if (isset($session->authorize) && isset($resource)) {
        if ($session->role_id) {
 
            // Получаем массив параметров uri
            $queryParams = $request->getQueryParams();
            $arr = array();
            $arr['state'] = 1;
            $arr['offset'] = 0;
            $arr['limit'] = 30;
            $arr['order'] = "ASC";
            if (count($queryParams) >= 1) {
                foreach($queryParams as $key => $value)
                {
                    if (isset($key) && isset($value)) {
                        $arr[$key] = $utility->clean($value);
                    }
                }
            }

            // Собираем полученные параметры в url и отдаем шаблону
            $get_array = http_build_query($arr);
            // Вытягиваем URL_PATH для правильного формирования юрл
            //$url_path = parse_url($request->getUri(), PHP_URL_PATH);
            $url_path = $path;
            // Подключаем сортировки
            $filter = new Filter($url_path, $arr);
            $orderArray = $filter->order();
            $limitArray = $filter->limit();
            // Формируем массив по которому будем сортировать
            
            $resourceDd = $adminDatabase->getOne($resource);
            
            $arrs["id"] = "id";
            $resourceArray = $arrs + $resourceDd; 
            
            $content_key = array_keys($resourceArray);
            
            //print_r($content_key);
            
            foreach($resourceArray as $key => $value)
            {
                $sortArr[$key] = $key;
            }
 
            $sortArray = $filter->sort($sortArr);
 
            // Отдаем роутеру RouterDb конфигурацию.
            $router = new Router($config);
            // Получаем название базы для указанного ресурса
            $name_db = $router->ping($resource);
            // Подключаемся к базе
            $db = new Db($name_db, $config);
            // Отправляем запрос и получаем данные
            $resp = $db->get($resource, $arr);
 
            $count = 0;
            if (isset($resp["response"]['total'])) {
                $count = $resp["response"]['total'];
            }
            $paginator = $filter->paginator($count);
            // Если ответ не пустой
            if (count($resp["body"]['items']) >= 1) {
                $content = '';
                // Отдаем пагинатору колличество
                foreach($resp["body"]['items'] as $item)
                {
                    
                    foreach($item["item"] as $key => $value)
                    {
                        if ($value == ''){$value = "--";}
                        $contentArr[$key] = $utility->clean($value);
                    }
                    $content["items"][] = $contentArr;
                }
            } else {
                $content = null;
            }
        } else {
            $render = "404";
        }
    } else {
        $session->authorize = null;
        $render = "404";
    }
 
    // Запись в лог
    $this->logger->info("admin/".$render);
 
    return $this->admin->render($render.'.html', [
        "template" => $template,
        "resource" => $resource,
        "head" => [
            "page" => $render,
            "title" => $language["814"].': '.$render.' - '. $language["709"],
            "keywords" => $language["814"].': '.$render.' - '. $language["709"],
            "description" => $language["814"].': '.$render.' - '. $language["709"],
            "host" => $host,
            "path" => $path
        ],
        "config" => $config,
        "language" => $language,
        "token" => $session->token,
        "session" => $sessionUser,
        "db_type" => $name_db,
        "db" => $adminDd,
        "content" => $content,
        "content_key" => $content_key,
        "paginator" => $paginator,
        "order" => $orderArray,
        "sort" => $sortArray,
        "limit" => $limitArray,
        "param" => $arr,
        "total" => $count,
        "url_param" => $get_array,
        "url" => $url_path
    ]);
 
});
 
// Глобально
$app->get('/admin/{resource:[a-z0-9_-]+}[/{id:[a-z0-9_]+}]', function (Request $request, Response $response, array $args) {
 
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
 
    $config = (new Settings())->get();
    $admin_config = $config['admin'];
    $template = $admin_config["template"];
 
    // Подключаем плагины
    $utility = new Utility();
 
    // Получаем resource из url
    if ($request->getAttribute('resource')) {
        $resource = $utility->clean($request->getAttribute('resource'));
    } else {
        $resource = null;
    }
 
    // Получаем id из url
    if ($request->getAttribute('id')) {
        $id = $utility->clean($request->getAttribute('id'));
    } else {
        $id = null;
    }
 
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token = Crypto::encrypt($token, $token_key);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser())->get();
 
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    // Подключаем определение языка в браузере
    $langs = new Langs();
    // Получаем массив данных из таблицы language на языке из $session->language
    if (isset($getParams['lang'])) {
        if ($getParams['lang'] == "ru" || $getParams['lang'] == "ua" || $getParams['lang'] == "en" || $getParams['lang'] == "de") {
            $lang = $getParams['lang'];
            $session->language = $getParams['lang'];
        } elseif (isset($session->language)) {
            $lang = $session->language;
        } else {
            $lang = $langs->getLanguage();
        }
    } elseif (isset($session->language)) {
        $lang = $session->language;
    } else {
        $lang = $langs->getLanguage();
    }
    // Подключаем мультиязычность
    $language = (new Language())->get($lang);
 
    $adminDatabase = new AdminDatabase();
    $adminDd = $adminDatabase->list();
 
    $content = "";
    $title = "";
    $keywords = "";
    $description = "";
 
    $control = new Control();
    $test = $control->test($resource);
    if ($test === true) {
        
        $site = new Site();
        $site_config = $site->get();
        $site_template = $site->template();
        
        //$param = $request->getParsedBody();
        $param = $request->getQueryParams();
        
        $render = $resource;
        if (isset($session->authorize)) {
            if ($session->role_id != 100) {
                $render = "404";
            } else {
                if(stristr($resource, '_') === FALSE) {
                    $resourceName = "\\ApiShop\\Admin\\".ucfirst($resource);
                } else {
                    $resourceNew = (str_replace(" ", "", ucwords(str_replace("_", " ", $resource))));
                    $resourceName = "\\ApiShop\\Admin\\".$resourceNew;
                }
                // Подключаем класс
                $resourceClass = new $resourceName($site_template);
                // Отправляем запрос
                $get = $resourceClass->get($resource, $param, $id);
                
                if ($resource == "settings") {
                    $content = $get;
                } else {
                    $content = $get["body"]["items"];
                }
            }
        } else {
            $session->authorize = null;
            $render = "404";
        }
    } else {
        $render = "404";
    }
 
    // Запись в лог
    $this->logger->info("admin - ".$render);
 
    return $this->admin->render($render.'.html', [
        "template" => $template,
        "head" => [
            "page" => $render,
            "title" => $title,
            "keywords" => $keywords,
            "description" => $description,
            "host" => $host,
            "path" => $path
        ],
        "config" => $config,
        "language" => $language,
        "token" => $session->token,
        "session" => $sessionUser,
        "db" => $adminDd,
        "content" => $content
    ]);
 
});
 