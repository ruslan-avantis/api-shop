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
use RouterDb\Db;
use RouterDb\Router;
use ApiShop\Config\Settings;
use ApiShop\Utilities\Utility;
use ApiShop\Resources\Language;
use ApiShop\Resources\Site;
use ApiShop\Model\SessionUser;

// Product Routes Controllers

$app->get('/product/{alias:[a-z0-9_]+}/{name}.html', function (Request $request, Response $response, array $args) {
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
    // print_r($content);
	
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
 
		//print_r($response);
        // Если ответ не пустой
        // Обрабатываем картинки
        $product['image']['no_image'] = $utility->get_image(null, '/images/no_image.png', 360, 360);
        $image_1 = '';
        $image_1 = (isset($response["body"]['items']['item']['image']['1'])) ? $utility->clean($response["body"]['items']['item']['image']['1']) : null;
        if (isset($image_1)) {$product['image']['1'] = $utility->get_image($response["body"]['items']['item']['product_id'], $image_1, 360, 360);}
        $image_2 = '';
        $image_2 = (isset($response["body"]['items']['item']['image']['2'])) ? $utility->clean($response["body"]['items']['item']['image']['2']) : null;
        if (isset($image_2)) {$product['image']['2'] = $utility->get_image($response["body"]['items']['item']['product_id'], $image_2, 360, 360);}
 
        // Формируем URL страницы товара
        $path_url = pathinfo($response["body"]['items']['item']['url']);
        $basename = $path_url['basename'];
        $baseurl = str_replace('-'.$response["body"]['items']['item']['product_id'].'.html', '', $basename);
        $product['url'] = '/product/'.$response["body"]['items']['item']['id'].'/'.$baseurl.'.html';
 
        $product['name'] = (isset($response["body"]['items']['item']['name'])) ? $utility->clean($response["body"]['items']['item']['name']) : '';
        $product['type'] = (isset($response["body"]['items']['item']['type'])) ? $utility->clean($response["body"]['items']['item']['type']) : '';
        $product['brand'] = (isset($response["body"]['items']['item']['brand'])) ? $utility->clean($response["body"]['items']['item']['brand']) : '';
        $product['serie'] = (isset($response["body"]['items']['item']['serie'])) ? $utility->clean($response["body"]['items']['item']['serie']) : '';
        $product['articul'] = (isset($response["body"]['items']['item']['articul'])) ? $utility->clean($response["body"]['items']['item']['articul']) : '';
        if ($response["body"]['items']['item']['serie'] && $response["body"]['items']['item']['articul']) {$product['name'] = $response["body"]['items']['item']['serie'].' '.$response["body"]['items']['item']['articul'];}
        $product['oldprice'] = (isset($response["body"]['items']['item']['oldprice'])) ? $utility->clean($response["body"]['items']['item']['oldprice']) : '';
        $product['price'] = (isset($response["body"]['items']['item']['price'])) ? $utility->clean($response["body"]['items']['item']['price']) : '';
        $product['available'] = (isset($response["body"]['items']['item']['available'])) ? $utility->clean($response["body"]['items']['item']['available']) : '';
		$product['product_id'] = (isset($response["body"]['items']['item']['product_id'])) ? $utility->clean($response["body"]['items']['item']['product_id']) : '';
 
        if (isset($response["body"]['items']['item']['action_date'])) {
            $date = $response["body"]['items']['item']['action_date'];
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
		if (isset($response["body"]['items']['item']['template'])){
			$themes_dir = $config["settings"]["themes"]["dir"];
			$templates_dir = $config["settings"]["themes"]["templates"];
			$template_name = $config["settings"]["themes"]["template"];
			$templates_test = $themes_dir.'/'.$templates_dir.'/'.$template_name.'/layouts/'.$response["body"]['items']['item']['template'].'.html';
			if (file_exists($templates_test)) {
			    $template = $response["body"]['items']['item']['template'];
			} else {
			    $template = 'product';
			}
		} else {
		    $template = 'product';
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
 
        return $this->twig->render($template.'.html', [
            "template" => $site->template(),
            "head" => $page,
            "site" => $site_config,
            "config" => $config['settings']['site'],
            "language" => $language,
            "token" => $session->token,
            "session" => $sessionUser,
            "content" => $content,
            "product" => $product,
			"session_id" => $session->id
        ]);
	
	} else {
	    return $this->twig->render('404.html', ["template" => $site->template(), "language" => $language]);
	}
	
});
 