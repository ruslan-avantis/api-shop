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
use ApiShop\Images\Image;
use ApiShop\Resources\Language;
use ApiShop\Resources\Site;
use ApiShop\Resources\Template;
use ApiShop\Model\SessionUser;
 
$config = (new Settings())->get();
$product_router = $config['routers']['product'];
$product_alias = $config['routers']['product_alias'];
$product_name = $config['routers']['product_name'];
$product_quick_view_router = $config['routers']['product_quick_view'];
 
$app->get($product_router.''.$product_alias.''.$product_name, function (Request $request, Response $response, array $args) {
 
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new Hook();
    $hook->http($request, $response, $args, 'GET');
    $request = $hook->request();
    $args = $hook->args();
 
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
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Получаем конфигурацию
    $config = (new Settings())->get();
    // Конфигурация роутинга
    $routers = $config['routers'];
    // Настройки сайта
    $site = new Site();
    $site_config = $site->get();
    // Получаем название шаблона
    $site_template = $site->template();
    // Конфигурация шаблона
    $templateConfig = new Template($site_template);
    $template = $templateConfig->get();
    // Подключаем мультиязычность
    $language = (new Language($getParams))->get();
    // Меню, берет название класса из конфигурации
    $menu = (new $config['vendor']['menu']())->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']($config['settings']['session']['name']);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser())->get();
    // Подключаем временное хранилище
    $session_temp = new $config['vendor']['session']("_temp");
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token = $config['vendor']['crypto']::encrypt($token, $token_key);
    // Обработка картинок
    $image = new Image();
 
    // Шаблон по умолчанию 404
    $render = $template['layouts']['404'] ? $template['layouts']['404'] : '404.html';
    // Контент по умолчанию
    $content = '';
    // Заголовки по умолчанию из конфигурации
    $title = $config['settings']['site']['title'];
    $keywords = $config['settings']['site']['keywords'];
    $description = $config['settings']['site']['description'];
    $robots = $config['settings']['site']['robots'];
    $og_title = $config['settings']['site']['og_title'];
    $og_description = $config['settings']['site']['og_description'];
    $og_image = $config['settings']['site']['og_image'];
    $og_type = $config['settings']['site']['og_type'];
    $og_locale = $config['settings']['site']['og_locale'];
    $og_url = $config['settings']['site']['og_url'];
 
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
        $resp = $db->get($resource, [], $alias);
 
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
            $protocol_uri = 'https://'.$host;
        } else {
            $protocol_uri = 'http://'.$host;
        }
 
        // Если ответ не пустой
        // Обрабатываем картинки
        foreach($resp["body"]['items']['0']['item']['image'] as $value)
        {
            $img = '';
            $img = (isset($value['image_path'])) ? $value['image_path'] : null;
            if (isset($img)) {
                $images[] = $image->get($resp["body"]['items']['0']['item']['product_id'], $img, $template["products"]["image_big_width"], $template["products"]["image_big_height"]);
            } else {
                $product['images'] = $image->get(null, $protocol_uri.'/images/no_image.png', $template["products"]["image_big_width"], $template["products"]["image_big_height"]);
            }
        }
        $product['images'] = $images;
 
        // Формируем URL страницы товара
        $path_url = pathinfo($resp["body"]['items']['0']['item']['url']);
        $basename = $path_url['basename'];
        $baseurl = str_replace('-'.$resp["body"]['items']['0']['item']['product_id'].'.html', '', $basename);
        $product['url'] = '/product/'.$resp["body"]['items']['0']['item']['id'].'/'.$baseurl.'.html';
 
        $product['name'] = (isset($resp["body"]['items']['0']['item']['name'])) ? $utility->clean($resp["body"]['items']['0']['item']['name']) : '';
        
        $product['description'] = (isset($resp["body"]['items']['0']['item']['description']['text'])) ? $utility->clean($resp["body"]['items']['0']['item']['description']['text']) : '';
        $product['type'] = (isset($resp["body"]['items']['0']['item']['type'])) ? $utility->clean($resp["body"]['items']['0']['item']['type']) : '';
        $product['brand'] = (isset($resp["body"]['items']['0']['item']['brand'])) ? $utility->clean($resp["body"]['items']['0']['item']['brand']) : '';
        $product['serie'] = (isset($resp["body"]['items']['0']['item']['serie'])) ? $utility->clean($resp["body"]['items']['0']['item']['serie']) : '';
        $product['articul'] = (isset($resp["body"]['items']['0']['item']['articul'])) ? $utility->clean($resp["body"]['items']['0']['item']['articul']) : '';
        if ($resp["body"]['items']['0']['item']['serie'] && $resp["body"]['items']['0']['item']['articul']) {$product['name'] = $resp["body"]['items']['0']['item']['serie'].' '.$resp["body"]['items']['0']['item']['articul'];}
        $product['oldprice'] = (isset($resp["body"]['items']['0']['item']['oldprice'])) ? $utility->clean($resp["body"]['items']['0']['item']['oldprice']) : '';
        $product['price'] = (isset($resp["body"]['items']['0']['item']['price'])) ? $utility->clean($resp["body"]['items']['0']['item']['price']) : '';
        $product['available'] = (isset($resp["body"]['items']['0']['item']['available'])) ? $utility->clean($resp["body"]['items']['0']['item']['available']) : '';
        $product['product_id'] = (isset($resp["body"]['items']['0']['item']['product_id'])) ? $utility->clean($resp["body"]['items']['0']['item']['product_id']) : '';
 
        if (isset($resp["body"]['items']['0']['item']['action_date'])) {
            $date = $resp["body"]['items']['0']['item']['action_date'];
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
        if (isset($resp["body"]['items']['0']['item']['template'])){
            $themes_dir = $config["settings"]["themes"]["dir"];
            $templates_dir = $config["settings"]["themes"]["templates"];
            $template_name = $config["settings"]["themes"]["template"];
            $templates_test = $themes_dir.'/'.$templates_dir.'/'.$template_name.'/layouts/'.$resp["body"]['items']['0']['item']['template'].'.html';
            if (file_exists($templates_test)) {
                $render = $resp["body"]['items']['0']['item']['template'] ? $resp["body"]['items']['0']['item']['template'] : $template['layouts']['product'];
            } else {
                $render = $template['layouts']['product'] ? $template['layouts']['product'] : 'product.html';
            }
        } else {
            $render = $template['layouts']['product'] ? $template['layouts']['product'] : 'product.html';
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
        
        $view = [
            "head" => $page,
            "routers" => $routers,
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
        ];
 
        
    
    }
 
    // Передаем данные Hooks для обработки ожидающим классам
    $hook->get($view, $render);
    // Отдаем данные шаблонизатору
    return $this->view->render($hook->render(), $hook->view());
 
});
 
$app->get($product_quick_view_router.''.$product_alias.''.$product_name, function (Request $request, Response $response, array $args) {
 
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new Hook();
    $hook->http($request, $response, $args, 'GET');
    $request = $hook->request();
    $args = $hook->args();
 
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
 
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
 
    // Подключаем мультиязычность
    $language = (new Language($getParams))->get();
 
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token = $config['vendor']['crypto']::encrypt($token, $token_key);
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
 
        foreach($resp["body"]['items']['0']['item']['image'] as $value)
        {
            $image = '';
            $image = (isset($value['image_path'])) ? $utility->clean($value['image_path']) : null;
            if (isset($image)) {$images[]  = $utility->get_image($resp["body"]['items']['0']['item']['product_id'], $image, 800, 800);}
        }
        $product['image'] = $images;
 
        // Формируем URL страницы товара
        $path_url = pathinfo($resp["body"]['items']['0']['item']['url']);
        $basename = $path_url['basename'];
        $baseurl = str_replace('-'.$resp["body"]['items']['0']['item']['product_id'].'.html', '', $basename);
        $product['url'] = '/product/'.$resp["body"]['items']['0']['item']['id'].'/'.$baseurl.'.html';
 
        $product['name'] = (isset($resp["body"]['items']['0']['item']['name'])) ? $utility->clean($resp["body"]['items']['0']['item']['name']) : '';
        
        $product['description'] = (isset($resp["body"]['items']['0']['item']['description']['text'])) ? $utility->clean($resp["body"]['items']['0']['item']['description']['text']) : '';
        $product['type'] = (isset($resp["body"]['items']['0']['item']['type'])) ? $utility->clean($resp["body"]['items']['0']['item']['type']) : '';
        $product['brand'] = (isset($resp["body"]['items']['0']['item']['brand'])) ? $utility->clean($resp["body"]['items']['0']['item']['brand']) : '';
        $product['serie'] = (isset($resp["body"]['items']['0']['item']['serie'])) ? $utility->clean($resp["body"]['items']['0']['item']['serie']) : '';
        $product['articul'] = (isset($resp["body"]['items']['0']['item']['articul'])) ? $utility->clean($resp["body"]['items']['0']['item']['articul']) : '';
        if ($resp["body"]['items']['0']['item']['serie'] && $resp["body"]['items']['0']['item']['articul']) {$product['name'] = $resp["body"]['items']['0']['item']['serie'].' '.$resp["body"]['items']['0']['item']['articul'];}
        $product['oldprice'] = (isset($resp["body"]['items']['0']['item']['oldprice'])) ? $utility->clean($resp["body"]['items']['0']['item']['oldprice']) : '';
        $product['price'] = (isset($resp["body"]['items']['0']['item']['price'])) ? $utility->clean($resp["body"]['items']['0']['item']['price']) : '';
        $product['available'] = (isset($resp["body"]['items']['0']['item']['available'])) ? $utility->clean($resp["body"]['items']['0']['item']['available']) : '';
        $product['product_id'] = (isset($resp["body"]['items']['0']['item']['product_id'])) ? $utility->clean($resp["body"]['items']['0']['item']['product_id']) : '';
 
        if (isset($resp["body"]['items']['0']['item']['action_date'])) {
            $date = $resp["body"]['items']['0']['item']['action_date'];
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
        
        $view = [
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
        ];
 
        $render = $template['layouts']['product-quick-view'] ? $template['layouts']['product-quick-view'] : 'product-quick-view.html';
 
    }
 
    // Передаем данные Hooks для обработки ожидающим классам
    $hook->get($view, $render);
    // Отдаем данные шаблонизатору
    return $this->view->render($hook->render(), $hook->view());
 
});
 