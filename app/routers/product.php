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
use RouterDb\Db;
use RouterDb\Router;
use ApiShop\Config\Settings;
use ApiShop\Utilities\Utility;
use ApiShop\Resources\Language;
use ApiShop\Resources\Site;
use ApiShop\Resources\Menu;
use ApiShop\Resources\Template;
use ApiShop\Model\SessionUser;
 
$config = (new Settings())->get();
$product_router = $config['routers']['product'];
$product_alias = $config['routers']['product_alias'];
$product_name = $config['routers']['product_name'];
$product_quick_view_router = $config['routers']['product_quick_view'];
 
$app->get($product_router.''.$product_alias.''.$product_name, function (Request $request, Response $response, array $args) {
 
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Подключаем плагины
    $utility = new Utility();
    // Получаем параметры из url
     $alias = null;
    // Получаем alias из url
    if ($request->getAttribute('alias')) {
        $alias = $utility->clean($request->getAttribute('alias'));
    }
     $name = null;
    // Получаем alias из url
    if ($request->getAttribute('name')) {
        $name = $utility->clean($request->getAttribute('name'));
    }
    // Получаем конфигурацию \ApiShop\Config\Settings
    $config = (new Settings())->get();
 
    $site = new Site();
    $site_config = $site->get();
    $site_template = $site->template();
 
    $templateConfig = new Template($site_template);
    $template = $templateConfig->get();
 
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
    // Читаем ключи
    $session_key = $config['key']['session'];
    $token_key = $config['key']['token'];
 
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
    // Меню
	$menu = (new Menu())->get();
    
    if ($alias != null) {
        
        // Ресурс (таблица) к которому обращаемся
        $resource = "price";
        // Отдаем роутеру RouterDb конфигурацию.
        $router = new Router($config);
        // Получаем название базы для указанного ресурса
        $name_db = $router->ping($resource);
        // Подключаемся к базе
        $db = new Db($name_db, $config);
        // Отправляем запрос и получаем данные
        $response = $db->get($resource, [], $alias);
 
        // Если ответ не пустой
        // Обрабатываем картинки
        $product['image']['no_image'] = $utility->get_image(null, '/images/no_image.png', 800, 800);
 
        foreach($response["body"]['items']['0']['item']['image'] as $value)
        {
            $image = '';
            $image = (isset($value['image_path'])) ? $utility->clean($value['image_path']) : null;
            if (isset($image)) {$images[]  = $utility->get_image($response["body"]['items']['0']['item']['product_id'], $image, 800, 800);}
        }
        $product['image'] = $images;
 
        // Формируем URL страницы товара
        $path_url = pathinfo($response["body"]['items']['0']['item']['url']);
        $basename = $path_url['basename'];
        $baseurl = str_replace('-'.$response["body"]['items']['0']['item']['product_id'].'.html', '', $basename);
        $product['url'] = '/product/'.$response["body"]['items']['0']['item']['id'].'/'.$baseurl.'.html';
 
        $product['name'] = (isset($response["body"]['items']['0']['item']['name'])) ? $utility->clean($response["body"]['items']['0']['item']['name']) : '';
        
        $product['description'] = (isset($response["body"]['items']['0']['item']['description']['text'])) ? $utility->clean($response["body"]['items']['0']['item']['description']['text']) : '';
        $product['type'] = (isset($response["body"]['items']['0']['item']['type'])) ? $utility->clean($response["body"]['items']['0']['item']['type']) : '';
        $product['brand'] = (isset($response["body"]['items']['0']['item']['brand'])) ? $utility->clean($response["body"]['items']['0']['item']['brand']) : '';
        $product['serie'] = (isset($response["body"]['items']['0']['item']['serie'])) ? $utility->clean($response["body"]['items']['0']['item']['serie']) : '';
        $product['articul'] = (isset($response["body"]['items']['0']['item']['articul'])) ? $utility->clean($response["body"]['items']['0']['item']['articul']) : '';
        if ($response["body"]['items']['0']['item']['serie'] && $response["body"]['items']['0']['item']['articul']) {$product['name'] = $response["body"]['items']['0']['item']['serie'].' '.$response["body"]['items']['0']['item']['articul'];}
        $product['oldprice'] = (isset($response["body"]['items']['0']['item']['oldprice'])) ? $utility->clean($response["body"]['items']['0']['item']['oldprice']) : '';
        $product['price'] = (isset($response["body"]['items']['0']['item']['price'])) ? $utility->clean($response["body"]['items']['0']['item']['price']) : '';
        $product['available'] = (isset($response["body"]['items']['0']['item']['available'])) ? $utility->clean($response["body"]['items']['0']['item']['available']) : '';
        $product['product_id'] = (isset($response["body"]['items']['0']['item']['product_id'])) ? $utility->clean($response["body"]['items']['0']['item']['product_id']) : '';
 
        if (isset($response["body"]['items']['0']['item']['action_date'])) {
            $date = $response["body"]['items']['0']['item']['action_date'];
        } else {
            $rand = rand(1000, 5000);
            $date = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +".$rand." minutes"));
        }
 
        $date = strtotime($date);
        $product['y'] = date("Y", $date);
        $product['m'] = date("m", $date);
        $product['d'] = date("d", $date);
        $product['h'] = date("H", $date);
        $product['i'] = date("i", $date);
        $product['s'] = date("s", $date);
        
        // Каждый товар может иметь свой уникальный шаблон
        // Если шаблон товара не установлен берем по умолчанию
        if (isset($response["body"]['items']['0']['item']['template'])){
            $themes_dir = $config["settings"]["themes"]["dir"];
            $templates_dir = $config["settings"]["themes"]["templates"];
            $template_name = $config["settings"]["themes"]["template"];
            $templates_test = $themes_dir.'/'.$templates_dir.'/'.$template_name.'/layouts/'.$response["body"]['items']['0']['item']['template'].'.html';
            if (file_exists($templates_test)) {
                $template_product = $response["body"]['items']['0']['item']['template'];
            } else {
                $template_product = 'product';
            }
        } else {
            $template_product = 'product';
        }

        // Запись в лог
        $this->logger->info("product");
        // Информация для head
        $page = [
            "page" => 'product',
            "title" => $product['name'],
            "keywords" => $product['name'],
            "description" => $product['name'],
            "og_title" => $product['name'],
            "og_description" => $product['name'],
            "host" => $host,
            "path" => $path
        ];
 
        return $this->view->render($template_product.'.html', [
            "head" => $page,
            "site" => $site_config,
            "config" => $config['settings']['site'],
            "language" => $language,
            "template" => $template,
            "token" => $session->token,
            "session" => $sessionUser,
            "content" => $content,
			"menu" => $menu,
            "product" => $product,
            "session_id" => $session->id
        ]);
    
    } else {
        return $this->view->render('404.html', ["template" => $site->template(), "language" => $language]);
    }
    
});
 
$app->get($product_quick_view_router.'{alias:[a-z0-9_]+}/{name}.html', function (Request $request, Response $response, array $args) {
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Подключаем плагины
    $utility = new Utility();
    // Получаем параметры из url
     $alias = null;
    // Получаем alias из url
    if ($request->getAttribute('alias')) {
        $alias = $utility->clean($request->getAttribute('alias'));
    }
     $name = null;
    // Получаем alias из url
    if ($request->getAttribute('name')) {
        $name = $utility->clean($request->getAttribute('name'));
    }
    // Получаем конфигурацию \ApiShop\Config\Settings
    $config = (new Settings())->get();
 
    $site = new Site();
    $site_config = $site->get();
    $site_template = $site->template();
 
    $templateConfig = new Template($site_template);
    $template = $templateConfig->get();
 
    // Подключаем сессию
    $session = new Session($config['settings']['session']['name']);
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
    // Меню
	$menu = (new Menu())->get();
    
    if ($alias != null) {
        
        // Ресурс (таблица) к которому обращаемся
        $resource = "price";
        // Отдаем роутеру RouterDb конфигурацию.
        $router = new Router($config);
        // Получаем название базы для указанного ресурса
        $name_db = $router->ping($resource);
        // Подключаемся к базе
        $db = new Db($name_db, $config);
        // Отправляем запрос и получаем данные
        $response = $db->get($resource, [], $alias);
 
        // Если ответ не пустой
        // Обрабатываем картинки
        $product['image']['no_image'] = $utility->get_image(null, '/images/no_image.png', 800, 800);
 
        foreach($response["body"]['items']['0']['item']['image'] as $value)
        {
            $image = '';
            $image = (isset($value['image_path'])) ? $utility->clean($value['image_path']) : null;
            if (isset($image)) {$images[]  = $utility->get_image($response["body"]['items']['0']['item']['product_id'], $image, 800, 800);}
        }
        $product['image'] = $images;
 
        // Формируем URL страницы товара
        $path_url = pathinfo($response["body"]['items']['0']['item']['url']);
        $basename = $path_url['basename'];
        $baseurl = str_replace('-'.$response["body"]['items']['0']['item']['product_id'].'.html', '', $basename);
        $product['url'] = '/product/'.$response["body"]['items']['0']['item']['id'].'/'.$baseurl.'.html';
 
        $product['name'] = (isset($response["body"]['items']['0']['item']['name'])) ? $utility->clean($response["body"]['items']['0']['item']['name']) : '';
        
        $product['description'] = (isset($response["body"]['items']['0']['item']['description']['text'])) ? $utility->clean($response["body"]['items']['0']['item']['description']['text']) : '';
        $product['type'] = (isset($response["body"]['items']['0']['item']['type'])) ? $utility->clean($response["body"]['items']['0']['item']['type']) : '';
        $product['brand'] = (isset($response["body"]['items']['0']['item']['brand'])) ? $utility->clean($response["body"]['items']['0']['item']['brand']) : '';
        $product['serie'] = (isset($response["body"]['items']['0']['item']['serie'])) ? $utility->clean($response["body"]['items']['0']['item']['serie']) : '';
        $product['articul'] = (isset($response["body"]['items']['0']['item']['articul'])) ? $utility->clean($response["body"]['items']['0']['item']['articul']) : '';
        if ($response["body"]['items']['0']['item']['serie'] && $response["body"]['items']['0']['item']['articul']) {$product['name'] = $response["body"]['items']['0']['item']['serie'].' '.$response["body"]['items']['0']['item']['articul'];}
        $product['oldprice'] = (isset($response["body"]['items']['0']['item']['oldprice'])) ? $utility->clean($response["body"]['items']['0']['item']['oldprice']) : '';
        $product['price'] = (isset($response["body"]['items']['0']['item']['price'])) ? $utility->clean($response["body"]['items']['0']['item']['price']) : '';
        $product['available'] = (isset($response["body"]['items']['0']['item']['available'])) ? $utility->clean($response["body"]['items']['0']['item']['available']) : '';
        $product['product_id'] = (isset($response["body"]['items']['0']['item']['product_id'])) ? $utility->clean($response["body"]['items']['0']['item']['product_id']) : '';
 
        if (isset($response["body"]['items']['0']['item']['action_date'])) {
            $date = $response["body"]['items']['0']['item']['action_date'];
        } else {
            $rand = rand(1000, 5000);
            $date = date("Y-m-d H:i:s",strtotime(date("Y-m-d H:i:s")." +".$rand." minutes"));
        }
 
        $date = strtotime($date);
        $product['y'] = date("Y", $date);
        $product['m'] = date("m", $date);
        $product['d'] = date("d", $date);
        $product['h'] = date("H", $date);
        $product['i'] = date("i", $date);
        $product['s'] = date("s", $date);
 
        // Запись в лог
        $this->logger->info("product");
        // Информация для head
        $page = [
            "page" => 'product',
            "title" => $product['name'],
            "keywords" => $product['name'],
            "description" => $product['name'],
            "og_title" => $product['name'],
            "og_description" => $product['name'],
            "host" => $host,
            "path" => $path
        ];
 
        return $this->view->render('product-quick-view.html', [
            "head" => $page,
            "site" => $site_config,
            "config" => $config['settings']['site'],
            "language" => $language,
            "template" => $template,
            "token" => $session->token,
            "session" => $sessionUser,
            "content" => $content,
			"menu" => $menu,
            "product" => $product,
            "session_id" => $session->id
        ]);
    
    } else {
        return $this->view->render('404.html', ["template" => $site->template(), "language" => $language]);
    }
    
});
 