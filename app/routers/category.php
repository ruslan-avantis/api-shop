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
 
use RouterDb\Db;
use RouterDb\Router;
 
use ApiShop\Config\Settings;
use ApiShop\Utilities\Utility;
use ApiShop\Resources\Language;
use ApiShop\Resources\Site;
use ApiShop\Resources\Template;
use ApiShop\Model\SessionUser;
use ApiShop\Model\Filter;
use ApiShop\Model\Pagination;
 
$config = (new Settings())->get();
$category_router = $config['routers']['category'];
 
$app->get($category_router.'[/{category:[a-z0-9_-]+}]', function (Request $request, Response $response, array $args) {
 
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();

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
 
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']($config['settings']['session']['name']);
    // Читаем ключи
    $session_key = $config['key']['session'];
    $token_key = $config['key']['token'];
    // Подключаем мультиязычность
 
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
    // Меню, берет название класса из конфигурации
    $menu = (new $config['vendor']['menu']())->get();
 
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
    $render = $template['layouts']['category'] ? $template['layouts']['category'] : 'category.html';
    $products_template = $template['layouts']['helper']['products'] ? $template['layouts']['helper']['products'] : 'helper/products.html';
    $products_limit = $template['products']['category']['limit'];
    $products_order = $template['products']['category']['order'];
    $products_sort = $template['products']['category']['sort'];
 
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
                $products_template = $category['products_template'] ? 'helper/'.$category['products_template'].'.html' : $template['layouts']['helper']['products'];
                $products_limit = $category['products_limit'] ? $category['products_limit'] : $template['products']['category']['limit'];
                $products_order = $category['products_order'] ? $category['products_order'] : $template['products']['category']['order'];
                $products_sort = $category['products_sort'] ? $category['products_sort'] : $template['products']['category']['sort'];
                if (isset($category['categories_template'])) {
                    $render = $template['layouts']['category'] ? $category['categories_template'].'.html' : $template['layouts']['category'];
                }
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


 
    if (isset($product_type)) {
        $arrPlus['type'] = $product_type;
    }
    $arrPlus['relations'] = "image";
    $newArr = $arr + $arrPlus;
    
    // Получаем список товаров
    $productsList = new $config['vendor']['products_category']();
    $products = $productsList->get($newArr, $template);
    // Даем пагинатору колличество
    $count = $productsList->count();
    $paginator = $filter->paginator($count);
 
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
    
    $view = [
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
    ];
 
    // Запись в лог
    $this->logger->info($render);
 
    return $this->view->render($render, $view);
 
});
 