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
 
use Slim\Http\Request;
use Slim\Http\Response;
use Adbar\Session;
use Defuse\Crypto\Crypto;
use Sinergi\BrowserDetector\Language as Langs;
use ApiShop\Config\Settings;
use ApiShop\Utilities\Utility;
use ApiShop\Resources\Language;
use ApiShop\Resources\Site;
use ApiShop\Model\SessionUser;
use RouterDb\Db;
use RouterDb\Router;
 
$app->get('/about-us.html', function (Request $request, Response $response, array $args) {
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Получаем конфигурацию \ApiShop\Config\Settings
    $config = (new Settings())->get();
    $site = new Site();
    $site_config = $site->get();
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Читаем ключи
    $session_key = $config['key']['session'];
    $token_key = $config['key']['token'];
    // Получаем массив данных из таблицы language на языке из $session->language
    $langs = new Langs();
    // Получаем массив данных из таблицы language на языке из $session->language
    if (isset($session->language)) {
        $lang = $session->language;
    } elseif ($langs->getLanguage()) {
        $lang = $langs->getLanguage();
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
    if (isset($session->authorize)) {
        $authorize = $session->authorize;
    } else {
        $session->authorize = 0;
        $authorize = 0;
    }
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser())->get();
    
    // Ресурс (таблица) к которому обращаемся
    $resource = "article";
    // Отдаем роутеру RouterDb конфигурацию.
    $router = new Router($config);
    // Получаем название базы для указанного ресурса
    $name_db = $router->ping($resource);
    // Подключаемся к базе
    $db = new Db($name_db, $config);
    // Отправляем запрос и получаем данные
    $response = $db->get($resource, ["state" => 1]);
    
    //print_r($response);
    
    // Что бы не давало ошибку присваиваем пустое значение
    $content = "";
 
    //print_r($db->last_id($resource));
 
    // Запись в лог
    $this->logger->info("about-us");
    
    $head = [
        "page" => 'about-us',
        "title" => $language['45'].' | '.$host,
        "keywords" => $language['45'],
        "description" => $language['45'].' | '.$host,
        "og_url" => $site_config["http_protocol"].'://'.$host.''.$path,
        "og_title" => $language['45'].' | '.$host,
        "og_description" => $language['45'].' | '.$host,
        "host" => $host,
        "path" => $path
    ];
 
    return $this->twig->render('about-us.html', [
        "template" => $site->template(),
        "head" => $head,
        "site" => $site_config,
        "config" => $config['settings']['site'],
        "language" => $language,
        "token" => $session->token,
        "session" => $sessionUser,
        "content" => $content
    ]);
 
});
 
$app->get('/contact.html', function (Request $request, Response $response, array $args) {
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Получаем конфигурацию \ApiShop\Config\Settings
    $config = (new Settings())->get();
    $site = new Site();
    $site_config = $site->get();
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Читаем ключи
    $session_key = $config['key']['session'];
    $token_key = $config['key']['token'];
    // Получаем массив данных из таблицы language на языке из $session->language
    $langs = new Langs();
    // Получаем массив данных из таблицы language на языке из $session->language
    if (isset($session->language)) {
        $lang = $session->language;
    } elseif ($langs->getLanguage()) {
        $lang = $langs->getLanguage();
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
    if (isset($session->authorize)) {
        $authorize = $session->authorize;
    } else {
        $session->authorize = 0;
        $authorize = 0;
    }
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser())->get();
    // Что бы не давало ошибку присваиваем пустое значение
    $content = '';
    // print_r($content);
 
    // Запись в лог
    $this->logger->info("contact");
    
    $head = [
        "page" => 'contact',
        "title" => $language['105'].' | '.$host,
        "keywords" => $language['105'],
        "description" => $language['105'].' | '.$host,
        "og_url" => $site_config["http_protocol"].'://'.$host.''.$path,
        "og_title" => $language['105'].' | '.$host,
        "og_description" => $language['105'].' | '.$host,
        "host" => $host,
        "path" => $path
    ];
 
    return $this->twig->render('contact.html', [
        "template" => $site->template(),
        "head" => $head,
        "site" => $site_config,
        "config" => $config['settings']['site'],
        "language" => $language,
        "token" => $session->token,
        "session" => $sessionUser,
        "content" => $content
    ]);
 
});
 
$app->get('/faq.html', function (Request $request, Response $response, array $args) {
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Получаем конфигурацию \ApiShop\Config\Settings
    $config = (new Settings())->get();
    $site = new Site();
    $site_config = $site->get();
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Читаем ключи
    $session_key = $config['key']['session'];
    $token_key = $config['key']['token'];
    // Получаем массив данных из таблицы language на языке из $session->language
    $langs = new Langs();
    // Получаем массив данных из таблицы language на языке из $session->language
    if (isset($session->language)) {
        $lang = $session->language;
    } elseif ($langs->getLanguage()) {
        $lang = $langs->getLanguage();
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
    if (isset($session->authorize)) {
        $authorize = $session->authorize;
    } else {
        $session->authorize = 0;
        $authorize = 0;
    }
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser())->get();
    // Что бы не давало ошибку присваиваем пустое значение
    $content = '';
    // print_r($content);
 
    // Запись в лог
    $this->logger->info("faq");
    
    $head = [
        "page" => 'faq',
        "title" => $language['311'].' | '.$host,
        "keywords" => $language['311'],
        "description" => $language['311'].' | '.$host,
        "og_url" => $site_config["http_protocol"].'://'.$host.''.$path,
        "og_title" => $language['311'].' | '.$host,
        "og_description" => $language['311'].' | '.$host,
        "host" => $host,
        "path" => $path
    ];
 
    return $this->twig->render('faq.html', [
        "template" => $site->template(),
        "head" => $head,
        "site" => $site_config,
        "config" => $config['settings']['site'],
        "language" => $language,
        "token" => $session->token,
        "session" => $sessionUser,
        "content" => $content
    ]);
 
});

$app->get('/imprint.html', function (Request $request, Response $response, array $args) {
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Получаем конфигурацию \ApiShop\Config\Settings
    $config = (new Settings())->get();
    $site = new Site();
    $site_config = $site->get();
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Читаем ключи
    $session_key = $config['key']['session'];
    $token_key = $config['key']['token'];
    // Получаем массив данных из таблицы language на языке из $session->language
    $langs = new Langs();
    // Получаем массив данных из таблицы language на языке из $session->language
    if (isset($session->language)) {
        $lang = $session->language;
    } elseif ($langs->getLanguage()) {
        $lang = $langs->getLanguage();
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
    if (isset($session->authorize)) {
        $authorize = $session->authorize;
    } else {
        $session->authorize = 0;
        $authorize = 0;
    }
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser())->get();
    // Что бы не давало ошибку присваиваем пустое значение
    $content = '';
 
    // Запись в лог
    $this->logger->info("imprint");
    
    $head = [
        "page" => 'imprint',
        "title" => $language['311'].' | '.$host,
        "keywords" => $language['311'],
        "description" => $language['311'].' | '.$host,
        "og_url" => $site_config["http_protocol"].'://'.$host.''.$path,
        "og_title" => $language['311'].' | '.$host,
        "og_description" => $language['311'].' | '.$host,
        "host" => $host,
        "path" => $path
    ];
 
    return $this->twig->render('imprint.html', [
        "template" => $site->template(),
        "head" => $head,
        "site" => $site_config,
        "config" => $config['settings']['site'],
        "language" => $language,
        "token" => $session->token,
        "session" => $sessionUser,
        "content" => $content
    ]);
 
});
 