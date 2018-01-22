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
use ApiShop\Resources\Template;
use ApiShop\Model\SessionUser;
use RouterDb\Db;
use RouterDb\Router;
 
$app->get('/{alias:[a-z0-9_-]+}.html', function (Request $request, Response $response, array $args) {
 
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
 
    if ($site_template != null) {
        $json_dir = $config["settings"]["themes"]["dir"].'/'.$config["settings"]["themes"]["templates"].'/'.$site_template.'/config/';
 
        if (file_exists($json_dir."".$alias.".json")) {
            $json = json_decode(file_get_contents($json_dir."".$alias.".json", true));
		    if(is_object($json["0"])) {
                $article = (array)$json["0"];
            } elseif (is_array($json["0"])) {
                $article = $json["0"];
            }
        } else {
            $article = null;
        }
    } else {
        $article = null;
    }
 
    if ($article == null) {
        // Ресурс (таблица) к которому обращаемся
        $resource = "article";
        // Отдаем роутеру RouterDb конфигурацию.
        $router = new Router($config);
        // Получаем название базы для указанного ресурса
        $name_db = $router->ping($resource);
        // Подключаемся к базе
        $db = new Db($name_db, $config);
        // Отправляем запрос и получаем данные
        $resp = $db->get($resource, ["state" => 1, "alias" => $alias]);
 
        if (isset($resp["headers"]["code"])) {
			if ($resp["headers"]["code"] == 200 || $resp["headers"]["code"] == "200") {
		        // Отдаем чистые данные
				if(is_object($resp["body"]["items"]["0"]["item"])) {
                    $article = (array)$resp["body"]["items"]["0"]["item"];
                } elseif (is_array($resp["body"]["items"]["0"]["item"])) {
                    $article = $resp["body"]["items"]["0"]["item"];
                }	
			} else {
		        $article = null;
		    }
		} else {
		    $article = null;
		}
    }
 
    if (isset($article["alias"])) {
        $content = $article["text_".$lang] ? $article["text_".$lang] : "";
        $page = $article["alias"] ? $article["alias"] : "article";
        $title = $article["seo_title"] ? $article["seo_title"].' | '.$host : $host;
        $keywords = $article["seo_keywords"] ? $article["seo_keywords"] : $host;
        $description = $article["seo_description"] ? $article["seo_description"] : $host;
        $og_url = $article["og_url"] ? $article["og_url"] : $site_config["http_protocol"].'://'.$host.''.$path;
        $og_title = $article["og_title"] ? $article["og_title"] : $host;
        $og_description = $article["og_description"] ? $article["og_description"] : $host;
	} else {
        $content = "";
        $page = "";
        $title = "";
        $keywords = "";
        $description = "";
        $og_url = "";
        $og_title = "";
        $og_description = "";
	}

    // Запись в лог
    $this->logger->info("article - ".$alias);
 
    return $this->view->render('article.html', [
        "template" => $template,
        "head" => [
            "page" => $page,
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
        "config" => $config['settings']['site'],
        "language" => $language,
        "token" => $session->token,
        "session" => $sessionUser,
        "content" => $content
    ]);
 
});