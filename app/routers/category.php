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
use RouterDb\Db;
use RouterDb\Router;
use ApiShop\Config\Settings;
use ApiShop\Utilities\Utility;
use ApiShop\Resources\Language;
use ApiShop\Resources\Site;
use ApiShop\Resources\Menu;
use ApiShop\Resources\Template;
use ApiShop\Model\SessionUser;
use ApiShop\Model\Filter;
use ApiShop\Model\Pagination;
 
$config = (new Settings())->get();
$category_router = $config['routers']['category'];
 
$app->get($category_router.'[/{category:[a-z0-9_-]+}]', function (Request $request, Response $response, array $args) {
 
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    // Подключаем плагины
    $utility = new Utility();
    // Получаем конфигурацию \ApiShop\Config\Settings
    $config = (new Settings())->get();
    $routers = $config['routers'];
    
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
    // Подключаем мультиязычность
 
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
 
    // Получаем category из url
    if ($request->getAttribute('category')) {
        $category_alias = $utility->clean($request->getAttribute('category'));
    } else {
        $category_alias = null;
    }
 
    $title = $language['402'];
    $keywords = $language['402'];
    $description = $language['402'];
    $robots = $config['settings']['site']['robots'];
    $og_title = $language['402'];
    $og_description = $language['402'];
    $og_image = $config['settings']['site']['og_image'];
    $og_type = $config['settings']['site']['og_type'];
    $og_locale = $config['settings']['site']['og_locale'];
    $og_url = $config['settings']['site']['og_url'];
 
    $category = '';
    $render = 'category';
    $products_template = 'helper/products.html';
    $products_limit = 15;
    $products_order = "ASC";
    $products_sort = "price";
    $image_width = 360;
    $image_height = 360;
 
    if (isset($category_alias)) {
        // Ресурс (таблица) к которому обращаемся
        $category_resource = "category";
        // Отдаем роутеру RouterDb конфигурацию.
        $router = new Router($config);
        // Получаем название базы для указанного ресурса
        $category_db = $router->ping($category_resource);
        // Подключаемся к базе
        $db = new Db($category_db, $config);
        // Отправляем запрос и получаем данные
        $resp = $db->get($category_resource, ['alias' => $category_alias]);
 
        if (isset($resp["headers"]["code"])) {
            if ($resp["headers"]["code"] == 200 || $resp["headers"]["code"] == '200') {
                $cat = $resp['body']['items']['0']['item'];
                if(is_object($cat)) {
                    $category = (array)$cat;
                } elseif (is_array($cat)) {
                    $category = $cat;
                }
                $title = $category['seo_title'] ? $category['seo_title'] : $category['title'];
                $keywords = $category['seo_keywords'] ? $category['seo_keywords'] : $category['title'];
                $description = $category['seo_description'] ? $category['seo_description'] : $category['title'];
                $og_title = $category['og_title'] ? $category['og_title'] : $category['title'];
                $og_description = $category['og_description'] ? $category['og_description'] : $category['title'];
                $og_image = $category['og_image'] ? $category['og_image'] : '';
                $og_type = $category['og_type'] ? $category['og_type'] : '';
                $robots = $category['robots'] ? $category['robots'] : 'index, follow';
                $render = $category['categories_template'] ? $category['categories_template'] : 'category';
                $products_template = $category['products_template'] ? 'helper/'.$category['products_template'].'.html' : 'helper/products.html';
                $products_limit = $category['products_limit'] ? $category['products_limit'] : 15;
                $products_order = $category['products_order'] ? $category['products_order'] : "ASC";
                $products_sort = $category['products_sort'] ? $category['products_sort'] : "price";
                $image_width = $category['image_width'] ? $category['image_width'] : 360;
                $image_height = $category['image_height'] ? $category['image_height'] : 360;
            }
        }
 
        if (isset($category['product_type'])) {
            //$product_type = explode(',', str_replace(array('"', "'", " "), '', $category['product_type']));
            $product_type = $category['product_type'];
        } else {
            $product_type = null;
        }
    
    }
 


    // Получаем массив параметров uri
    $queryParams = $request->getQueryParams();
    $arr = array();
    $arr['state'] = 1;
    $arr['offset'] = 0;
    $arr['limit'] = $products_limit;
    $arr['order'] = $products_order;
    $arr['sort'] = $products_sort;
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
    $sortArr = [
        "name" => $language["51"],
        "type" => $language["46"],
        "brand" => $language["47"],
        "serie" => $language["48"],
        "articul" => $language["49"],
        "price" => $language["112"]
    ];
    $sortArray = $filter->sort($sortArr);

    // Ресурс (таблица) к которому обращаемся
    $resource = "price";
    // Отдаем роутеру RouterDb конфигурацию.
    $router = new Router($config);
    // Получаем название базы для указанного ресурса
    $name_db = $router->ping($resource);
    // Подключаемся к базе
    $db = new Db($name_db, $config);
 
    if (isset($product_type)) {
        $arrPlus['type'] = $product_type;
    }
    $arrPlus['relations'] = "image,description";
    $newArr = $arr + $arrPlus;
 
    // Отправляем запрос и получаем данные
    $response = $db->get($resource, $newArr);

    $count = 0;
    if (isset($response["response"]['total'])) {
        $count = $response["response"]['total'];
    }
    $paginator = $filter->paginator($count);
 
    // Если ответ не пустой
    if (count($response["body"]['items']) >= 1) {
        // Отдаем пагинатору колличество
        foreach($response["body"]['items'] as $item)
        {
            // Обрабатываем картинки
            $product['no_image'] = $utility->get_image(null, '/images/no_image.png', $image_width, $image_height);
            $image_1 = '';
            $image_1 = (isset($item['item']['image']['0']['image_path'])) ? $utility->clean($item['item']['image']['0']['image_path']) : null;
            if (isset($image_1)) {$product['image']['1'] = $utility->get_image($item['item']['product_id'], $image_1, $image_width, $image_height);}
            $image_2 = '';
            $image_2 = (isset($item['item']['image']['1']['image_path'])) ? $utility->clean($item['item']['image']['1']['image_path']) : null;
            if (isset($image_2)) {$product['image']['2'] = $utility->get_image($item['item']['product_id'], $image_2, $image_width, $image_height);}

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
    $this->logger->info($render."");
 
    $head = [
        "page" => $render,
        "title" => $title,
        "keywords" => $keywords,
        "description" => $description,
        "robots" => $robots,
        "og_title" => $og_title,
        "og_description" => $og_description,
        "og_image" => $og_image,
        "og_type" => $og_type,
        "og_locale" => $og_locale,
        "og_url" => $og_url,
        "host" => $host,
        "path" => $path
    ];
 
    return $this->view->render($render.'.html', [
        "template" => $template,
        "products_template" => $products_template,
        "head" => $head,
        "site" => $site_config,
        "routers" => $routers,
        "config" => $config['settings']['site'],
        "language" => $language,
        "token" => $session->token,
        "session" => $sessionUser,
        "content" => $content,
        "menu" => $menu,
        "products" => $products,
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
 