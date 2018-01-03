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

//use ApiShop\Database\Router;
//use ApiShop\Database\Ping;

$app->get('/', function (Request $request, Response $response, array $args) {
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
    if ($session->authorize) {
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
 
    // Эти параметры должны браться из конфигурации сайта получаемого через API
    $arr = array(
        "limit" => 9,
        "sort" => 'price',
        "order" => 'ASC'
    );

    // Ресурс (таблица) к которому обращаемся
    $resource = "price";
    // Отдаем роутеру RouterDb конфигурацию.
    $router = new Router($config);
    // Получаем название базы для указанного ресурса
    $name_db = $router->get($resource);
    // Подключаемся к базе
    $db = new Db($name_db, $config);
    // Отправляем запрос и получаем данные
    $response = $db->get($resource, $arr);
 
    // Если ответ не пустой
    if (count($response["body"]['items']) >= 1) {
        foreach($response["body"]['items'] as $item)
        {
            // Обрабатываем картинки
            $product['image']['no_image'] = $utility->get_image(null, 'https://life24.com.ua/images/no_image.png', 360, 360);
            $image_1 = '';
            $image_1 = (isset($item['item']['image']['1'])) ? $utility->clean($item['item']['image']['1']) : null;
            if (isset($image_1)) {$product['image']['1'] = $utility->get_image($item['item']['product_id'], $image_1, 360, 360);}
            $image_2 = '';
            $image_2 = (isset($item['item']['image']['2'])) ? $utility->clean($item['item']['image']['2']) : null;
            if (isset($image_2)) {$product['image']['2'] = $utility->get_image($item['item']['product_id'], $image_2, 360, 360);}

            $path_url = pathinfo($item['item']['url']);
            $basename = $path_url['basename']; // lib.inc.php
            $baseurl = str_replace('-'.$item['item']['product_id'].'.html', '', $basename);

            $product['url'] = '/product/'.$item['item']['id'].'/'.$baseurl.'.html';
            $product['name'] = (isset($item['item']['name'])) ? $utility->clean($item['item']['name']) : '';
            $product['type'] = (isset($item['item']['type'])) ? $utility->clean($item['item']['type']) : '';
            $product['brand'] = (isset($item['item']['brand'])) ? $utility->clean($item['item']['brand']) : '';
            $product['serie'] = (isset($item['item']['serie'])) ? $utility->clean($item['item']['serie']) : '';
            $product['articul'] = (isset($item['item']['articul'])) ? $utility->clean($item['item']['articul']) : '';
            if ($item['item']['serie'] && $item['item']['articul']) {$product['name'] = $item['item']['serie'].' '.$item['item']['articul'];}
            $product['oldprice'] = (isset($item['item']['oldprice'])) ? $utility->clean($item['item']['oldprice']) : '';
            $product['price'] = (isset($item['item']['price'])) ? $utility->clean($item['item']['price']) : '';
            $product['available'] = (isset($item['item']['available'])) ? $utility->clean($item['item']['available']) : '';
            $product['product_id'] = (isset($item['item']['product_id'])) ? $utility->clean($item['item']['product_id']) : '';

            $rand = rand(1000, 5000);
            $date = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +".$rand." minutes"));
            $date = strtotime($date);
            $product['y'] = date("Y", $date);
            $product['m'] = date("m", $date);
            $product['d'] = date("d", $date);
            $product['h'] = date("H", $date);
            $product['i'] = date("i", $date);
            $product['s'] = date("s", $date);
            // Отдаем данные шаблонизатору 
            $products['product'][] = $product;
        }
    } else {
        $products = null;
    }
 
    // Запись в лог
    $this->logger->info("home");
    
    $head = [
            "page" => 'home',
            "title" => "",
            "keywords" => "",
            "description" => "",
            "og_title" => "",
            "og_description" => "",
            "host" => $host,
            "path" => $path
    ];
 
    return $this->twig->render('index.html', [
        "template" => $site->template(),
        "head" => $head,
        "site" => $site_config,
        "config" => $config['settings']['site'],
        "language" => $language,
        "token" => $session->token,
        "session" => $sessionUser,
        "content" => $content,
        "products" => $products
    ]);
 
});
 
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
    if ($session->authorize) {
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
    if ($session->authorize) {
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
    if ($session->authorize) {
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
 