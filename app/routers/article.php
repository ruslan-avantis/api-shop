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
use ApiShop\Resources\Menu;
use ApiShop\Resources\Template;
use ApiShop\Model\SessionUser;
use RouterDb\Db;
use RouterDb\Router;
 
$config = (new Settings())->get();
$article_category_router = $config['routers']['article_category'];
$article_router = $config['routers']['article'];
 
$app->get($article_category_router.'{alias:[a-z0-9_-]+}.html', function (Request $request, Response $response, array $args) {
 
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
 
    // Подключаем плагины
    $utility = new Utility();
 
    // Получаем alias из url
    if ($request->getAttribute('alias')) {
        $alias = $utility->clean($request->getAttribute('alias'));
    } else {
        $alias = null;
    }
 
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
 
    $title = "";
    $keywords = "";
    $description = "";
    $og_url = "";
    $og_title = "";
    $og_description = "";
 
    $render = "404";
    $content = "";
    
    // Меню
    $menu = (new Menu())->get();
 
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
        // Отдаем чистые данные
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
 
            $render = "article";
 
        } else {
            $render = "404";
        }
    } else {
        $render = "404";
    }
 
    // Запись в лог
    $this->logger->info("article - ".$alias);
 
    return $this->view->render($render.'.html', [
        "template" => $template,
        "head" => [
            "page" => "article",
            "title" => $title,
            "keywords" => $keywords,
            "description" => $description,
            "og_url" => $og_url,
            "og_title" => $og_title,
            "og_description" => $og_description,
            "host" => $host,
            "path" => $path
        ],
        "site" => $site_config,
        "routers" => $routers,
        "config" => $config['settings']['site'],
        "language" => $language,
        "token" => $session->token,
        "session" => $sessionUser,
        "content" => $content,
        "menu" => $menu,
    ]);
 
});

$app->get($article_router.'{alias:[a-z0-9_-]+}.html', function (Request $request, Response $response, array $args) {
 
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
 
    // Подключаем плагины
    $utility = new Utility();
 
    // Получаем alias из url
    if ($request->getAttribute('alias')) {
        $alias = $utility->clean($request->getAttribute('alias'));
    } else {
        $alias = null;
    }
 
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
 
    $title = "";
    $keywords = "";
    $description = "";
    $og_url = "";
    $og_title = "";
    $og_description = "";
 
    $render = "404";
    $content = "";
    
    // Меню
    $menu = (new Menu())->get();
 
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
        // Отдаем чистые данные
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
 
            $render = "article";
 
        } else {
            $render = "404";
        }
    } else {
        $render = "404";
    }
 
    // Запись в лог
    $this->logger->info("article - ".$alias);
 
    return $this->view->render($render.'.html', [
        "template" => $template,
        "head" => [
            "page" => "article",
            "title" => $title,
            "keywords" => $keywords,
            "description" => $description,
            "og_url" => $og_url,
            "og_title" => $og_title,
            "og_description" => $og_description,
            "host" => $host,
            "path" => $path
        ],
        "site" => $site_config,
        "routers" => $routers,
        "config" => $config['settings']['site'],
        "language" => $language,
        "token" => $session->token,
        "session" => $sessionUser,
        "content" => $content,
        "menu" => $menu,
    ]);
 
});