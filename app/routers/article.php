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
 
use Pllano\RouterDb\Db;
use Pllano\RouterDb\Router;
 
use Pllano\Hooks\Hook;
use Pllano\Caching\Cache;
 
use ApiShop\Config\Settings;
use ApiShop\Utilities\Utility;
use ApiShop\Adapter\Menu;
use ApiShop\Resources\Language;
use ApiShop\Resources\Site;
use ApiShop\Resources\Template;
use ApiShop\Model\SessionUser;
 
$config = (new Settings())->get();
$category = $config['routers']['article_category'];
$article = $config['routers']['article'];
 
$app->get($category.'{alias:[a-z0-9_-]+}.html', function (Request $request, Response $response, array $args) {
 
    // Получаем конфигурацию
    $config = (new Settings())->get();
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new Hook($config);
    $hook->http($request, $response, $args, 'GET', 'site');
    $request = $hook->request();
    $args = $hook->args();
 
    // Подключаем плагины
    $utility = new Utility();
    // Получаем alias из url
    if ($request->getAttribute('alias')) {
        $alias = $utility->clean($request->getAttribute('alias'));
    } else {
        $alias = null;
    }
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Конфигурация роутинга
    $routers = $config['routers'];
    // Настройки сайта
    $site = new Site($config);
    $site_config = $site->get();
    // Получаем название шаблона
    $site_template = $site->template();
    // Конфигурация шаблона
    $templateConfig = new Template($site_template);
    $template = $templateConfig->get();
    // Меню, берет название класса из конфигурации
    $menu = (new Menu())->get();
    // Подключаем мультиязычность
    $languages = new Language($request, $config);
    $language = $languages->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']($config['settings']['session']['name']);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser($config))->get();
    // Подключаем временное хранилище
    $session_temp = new $config['vendor']['session']("_temp");
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token = $config['vendor']['crypto']::encrypt($token, $token_key);
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
 
    if (isset($alias)) {
        // Ресурс (таблица) к которому обращаемся
        $resource = "article_category";
        // Отдаем роутеру RouterDb конфигурацию.
        $router = new Router($config);
        // Получаем название базы для указанного ресурса
        $name_db = $router->ping($resource);
        // Подключаемся к базе
        $db = new Db($name_db, $config);

        // Отправляем запрос и получаем данные
        $resp = $db->get($resource, ["alias" => $alias]);
 
        if (isset($resp["headers"]["code"])) {
            if ($resp["headers"]["code"] == 200 || $resp["headers"]["code"] == "200") {
 
                // Если данные в виде объекта переводим в массив
                if(is_object($resp["body"]["items"]["0"]["item"])) {
                    $content = (array)$resp["body"]["items"]["0"]["item"];
                } elseif (is_array($resp["body"]["items"]["0"]["item"])) {
                    $content = $resp["body"]["items"]["0"]["item"];
                }
 
                $content["text"] = htmlspecialchars_decode($content["text"]);
                $content["text_ru"] = htmlspecialchars_decode($content["text_ru"]);
                $content["text_ua"] = htmlspecialchars_decode($content["text_ua"]);
                $content["text_en"] = htmlspecialchars_decode($content["text_en"]);
                $content["text_de"] = htmlspecialchars_decode($content["text_de"]);
 
                $title = $content["seo_title"];
                $keywords = $content["seo_keywords"];
                $description = $content["seo_description"];
                $og_url = $content["og_url"];
                $og_title = $content["og_title"];
                $og_description = $content["og_description"];
 
                if (isset($content['layouts'])) {
                    $render = $content['layouts'] ? $content['layouts'] : $template['layouts']['article'];
                } else {
                    $render = $template['layouts']['article'] ? $template['layouts']['article'] : 'article.html';
                }
 
            }
        }
    }
    
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
        "head" => $head,
        "routers" => $routers,
        "site" => $site_config,
        "config" => $config['settings']['site'],
        "language" => $language,
        "template" => $template,
        "token" => $session->token,
        "session" => $sessionUser,
        "menu" => $menu,
        "content" => $content
    ];
 
    // Запись в лог
    $this->logger->info("article - ".$alias);
 
    // Передаем данные Hooks для обработки ожидающим классам
    $hook->get($view, $render);
    // Отдаем данные шаблонизатору
    return $this->view->render($hook->render(), $hook->view());
 
});

$app->get($article.'{alias:[a-z0-9_-]+}.html', function (Request $request, Response $response, array $args) {
 
    // Получаем конфигурацию
    $config = (new Settings())->get();
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new Hook($config);
    $hook->http($request, $response, $args, 'GET', 'site');
    $request = $hook->request();
    $args = $hook->args();
 
    // Подключаем плагины
    $utility = new Utility();
    // Получаем alias из url
    if ($request->getAttribute('alias')) {
        $alias = $utility->clean($request->getAttribute('alias'));
    } else {
        $alias = null;
    }
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Конфигурация роутинга
    $routers = $config['routers'];
    // Настройки сайта
    $site = new Site($config);
    $site_config = $site->get();
    // Получаем название шаблона
    $site_template = $site->template();
    // Конфигурация шаблона
    $templateConfig = new Template($site_template);
    $template = $templateConfig->get();
    // Меню, берет название класса из конфигурации
    $menu = (new Menu())->get();
    // Подключаем мультиязычность
    $languages = new Language($request, $config);
    $language = $languages->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']($config['settings']['session']['name']);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser($config))->get();
    // Подключаем временное хранилище
    $session_temp = new $config['vendor']['session']("_temp");
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token = $config['vendor']['crypto']::encrypt($token, $token_key);
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
 
    if (isset($alias)) {
        $render = $template['layouts']['article'] ? $template['layouts']['article'] : 'article.html';
        // Если cache->run дает null работаем без кеша
        $cache = new Cache($config);
        if ($cache->run($host.'/site'.$path.'/'.$languages->lang()) === null) {
            // Ресурс (таблица) к которому обращаемся
            $resource = "article";
            // Отдаем роутеру RouterDb конфигурацию.
            $router = new Router($config);
            // Получаем название базы для указанного ресурса
            $name_db = $router->ping($resource);
            // Подключаемся к базе
            $db = new Db($name_db, $config);
            // Отправляем запрос и получаем данные
            $resp = $db->get($resource, ["alias" => $alias]);
 
            if (isset($resp["headers"]["code"])) {
                if ($resp["headers"]["code"] == 200 || $resp["headers"]["code"] == "200") {
                    // Если данные в виде объекта переводим в массив
                    if(is_object($resp["body"]["items"]["0"]["item"])) {
                        $content = (array)$resp["body"]["items"]["0"]["item"];
                    } elseif (is_array($resp["body"]["items"]["0"]["item"])) {
                        $content = $resp["body"]["items"]["0"]["item"];
                    }
 
                    $content["text"] = htmlspecialchars_decode($content["text"]);
                    $content["text_ru"] = htmlspecialchars_decode($content["text_ru"]);
                    $content["text_ua"] = htmlspecialchars_decode($content["text_ua"]);
                    $content["text_en"] = htmlspecialchars_decode($content["text_en"]);
                    $content["text_de"] = htmlspecialchars_decode($content["text_de"]);
 
                    $title = $content["seo_title"];
                    $keywords = $content["seo_keywords"];
                    $description = $content["seo_description"];
                    $og_url = $content["og_url"];
                    $og_title = $content["og_title"];
                    $og_description = $content["og_description"];
 
                    if (isset($content['layouts'])) {
                        $render = $content['layouts'] ? $content['layouts'] : $template['layouts']['article'];
                    }
                }
            }
            if ($cache->state() == '1') {
                $cache->set($content);
            }
        } else {
            $content = $cache->get();
        }
    }
 
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
        "head" => $head,
        "routers" => $routers,
        "site" => $site_config,
        "config" => $config['settings']['site'],
        "language" => $language,
        "template" => $template,
        "token" => $session->token,
        "session" => $sessionUser,
        "menu" => $menu,
        "content" => $content
    ];
 
    // Передаем данные Hooks для обработки ожидающим классам
    $hook->get($view, $render);
    // Запись в лог
    $this->logger->info($hook->logger());
    // Отдаем данные шаблонизатору
    return $this->view->render($hook->render(), $hook->view());
 
});