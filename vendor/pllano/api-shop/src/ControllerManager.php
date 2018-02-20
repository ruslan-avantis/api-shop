<?php /**
    * This file is part of the {API}$hop
    *
    * @license http://opensource.org/licenses/MIT
    * @link https://github.com/pllano/api-shop
    * @version 1.1.1
    * @package pllano.api-shop
    *
    * For the full copyright and license information, please view the LICENSE
    * file that was distributed with this source code.
*/
 
namespace ApiShop;
 
use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};
use ApiShop\Model\{User, Install, SessionUser, Language, Site, Template, Security};
use ApiShop\Utilities\Utility;
use ApiShop\Adapter\Menu;
use Pllano\Caching\Cache;
 
class ControllerManager
{
    private $config = [];
    private $package = [];
    protected $logger;
    protected $view;
    protected $route;
    protected $query;
 
    function __construct($query, $route, $config, $package, $view, $logger)
    {
        $this->config = $config;
        $this->package = $package;
        $this->logger = $logger;
        $this->view = $view;
        $this->route = $route;
        $this->query = $query;
    }
 
    public function get(Request $request, Response $response, array $args)
    {
        // $getScheme = $request->getUri()->getScheme(); // Работает
        // $getParams = $request->getQueryParams(); // Работает
        // $getQuery = $request->getUri()->getQuery(); // Работает
        // $getHost = $request->getUri()->getHost(); // Работает
        // $getPath = $request->getUri()->getPath(); // Работает
        // $routess = $request->getAttribute('route'); // Работает - Отдает огромный массив информации
        // $getMethod = $request->getMethod();
        // $getParsedBody = $request->getParsedBody();
 
        // Конфигурация
        $config = $this->config;
        // Передаем данные Hooks для обработки ожидающим классам
        // Default Pllano\Hooks\Hook
        $hook = new $config['vendor']['hooks']['hook']($config, $this->query, $this->route, 'site');
        $hook->http($request, $response, $args);
        $request = $hook->request();
        $args = $hook->args();
        // true - Если все хуки отказались подменять контент
        if($hook->state() === true) {
            // Подключаем утилиты
            $utility = new Utility();
 
            // Получаем параметры из URL
            $host = $request->getUri()->getHost();
            $path = '';
            if($request->getUri()->getPath() != '/') {
               $path = $request->getUri()->getPath();
            }
            $params = '';
            // Параметры из URL
            $params_query = str_replace('q=/', '', $request->getUri()->getQuery());
            
            if ($params_query) {
                $params = '/'.$params_query;
            }
 
            // Подключаем сессию, берет название класса из конфигурации
            // Default Adbar\Session
            $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
            // Данные пользователя из сессии
            $sessionUser =(new SessionUser($config))->get();
            // Подключаем временное хранилище
            // Default Adbar\Session
            $sessionTemp = new $config['vendor']['session']['session']("_temp");
            // Генерируем токен. Читаем ключ. Записываем токен в сессию.
            // Default Defuse\Crypto\Crypto
            $session->token = $config['vendor']['crypto']['crypt']::encrypt($utility->random_token(), $config['key']['token']);
 
            // Подключаем мультиязычность
            $languages = new Language($request, $config);
            $language = $languages->get();
            $lang = $languages->lang();
            // Настройки сайта
            $site = new Site($config);
            $siteConfig = $site->get();
            // Получаем название шаблона
            // Конфигурация шаблона
            $templateConfig = new Template($site->template());
            $template = $templateConfig->get();
 
            // layout по умолчанию 404
            $render = $template['layouts']['404'] ? $template['layouts']['404'] : '404.html';
 
            // Конфигурация роутинга
            $routers = $config['routers'];
 
            $admin_uri = '/_';
            if(!empty($session->admin_uri)) {
                $admin_uri = '/'.$session->admin_uri;
            }
            $post_id = '/_';
            if(!empty($session->post_id)) {
                $post_id = '/'.$session->post_id;
            }
            
            //print("<br>query: {$host}{$params}/{$lang}/{$this->route}<br>");
 
            // Заголовки по умолчанию из конфигурации
            $headArr = explode(',', str_replace([" ", "'"], "", $config['settings']['seo']['head']));
            $head = ["page" => $this->route, "host" => $host, "path" => $path, "scheme" => $config["server"]["scheme"].'://'];
            foreach($headArr as $headKey => $headVal)
            {
                $head_arr[$headVal] = $config['settings']['site'][$headVal];
                $head = array_replace_recursive($head, $head_arr);
            }
 
            if ($config["settings"]["install"]["status"] != null) {
                $pluginsArr = [];
                $arr = [];
                $cache = new Cache($config);
                if ($cache->run($host.''.$params.'/'.$lang.'/'.$this->route) === null) {
                    $dataArr = [
                        "head" => $head,
                        "routers" => $routers,
                        "site" => $siteConfig,
                        "config" => $config['settings']['site'],
                        "template" => $template
                    ];
                    $mods = explode(',', str_replace([" ", "'"], "", $config['routers']['site'][$this->route]['blocks']));
                    foreach($mods as $key => $block)
                    {
                        $modules = new $config['vendor']['modules']['manager']($config, $this->package, $template, $block, $this->route, $lang, $language);
                        $arr = $modules->get($request, $response, $args);
                        $dataArr = array_replace_recursive($dataArr, $arr);
                    }
                    if ((int)$cache->state() == 1) {
                        $cache->set($dataArr);
                    }
                } else {
                    $dataArr = $cache->get();
                }
 
                // Определяем layout
                // Модули могут поменять layout
                if (isset($dataArr['content']['modules'][$this->route]['content']['layout'])) {
                    $render = $dataArr['content']['modules'][$this->route]['content']['layout'];
                } elseif (isset($dataArr['content'])) {
                    $render = $template['layouts']['layout'];
                }
 
                // Массив данных который нельзя кешировать
                $userArr = [
                    "language" => $language,
                    "token" => $session->token,
                    "post_id" => $post_id,
                    "admin_uri" => $admin_uri,
                    "session" => $sessionUser
                ];
                // Формируем данные для шаблонизатора. Склеиваем два массива.
                $data = array_replace_recursive($userArr, $dataArr);
                
                //print_r($data);
 
            } else {
			    $render = "index.html";
                // Если ключа доступа у нет, значит сайт еще не активирован
                $content = '';
                // $session->install = null;
                if (isset($session->install)) {
                    if ($session->install == 1) {
                        $render = "stores.html";
                        $content = (new Install($config))->stores_list();
                    } elseif ($session->install == 2) {
                        $render = "templates.html";
						
						$install_store = $session->install_store ?? null; // php7
 
                        $content = (new Install($config))->templates_list($install_store);
                    } elseif ($session->install == 3) {
                        $render = "welcome.html";
                    } elseif ($session->install == 10) {
                        $render = "templates.html";
                        $content = (new Install($config))->templates_list(null);
                    } elseif ($session->install == 11) {
                        $render = "key.html";
                    }
                }
 
                $data = [
                    "head" => $head,
                    "template" => "install",
                    "routers" => $routers,
                    "config" => $config['settings']['site'],
                    "language" => $language,
                    "token" => $session->token,
                    "session" => $sessionUser,
                    "session_temp" => $sessionTemp,
                    "content" => $content
                ];
            }
 
            // Передаем данные Hooks для обработки ожидающим классам
            $hook->get($render, $data);
            // Запись в лог
            $this->logger->info($hook->logger());
 
        }
		if ($config['settings']["install"]["status"] != null) {
			return $this->view->render($response, $hook->render(), $hook->view());
		} else {
		    return $this->view->render($render, $data);
		}
 
    }
 
    public function post(Request $request, Response $response, array $args)
    {
        $config = $this->config;
        $method = $request->getMethod();
        $post = $request->getParsedBody();
 
        $today = date("Y-m-d H:i:s");
        // Получаем данные отправленные нам через POST
 
        // Передаем данные Hooks для обработки ожидающим классам
        $hook = new $config['vendor']['hooks']['hook']($config);
        $hook->http($request, $response, $args, $method, 'site');
        $request = $hook->request();
        $args = $hook->args();
 
        // Подключаем утилиты
        $utility = new Utility();
        // Подключаем сессию, берет название класса из конфигурации
        $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
        // Читаем ключи
        $token_key = $config['key']['token'];
        // Подключаем систему безопасности
        $security = new Security();
 
        // Настройки сайта
        $site = new Site($config);
        $siteConfig = $site->get();
        // Получаем название шаблона
        // Конфигурация шаблона
        $templateConfig = new Template($site->template());
        $template = $templateConfig->get();
        // Подключаем мультиязычность
        $languages = new Language($request, $config);
        $language = $languages->get();
        $lang = $languages->lang();
 
        try {
            // Получаем токен из сессии
            $token = $config['vendor']['crypto']['crypt']::decrypt($session->token, $token_key);
        } catch (\Exception $ex) {
            $token = 0;
            // Сообщение об Атаке или подборе токена
            $security->token($request, $response);
        }
        try {
            // Получаем токен из POST
            $post_csrf = $config['vendor']['crypto']['crypt']::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
            // Чистим данные на всякий случай пришедшие через POST
            $csrf = $utility->clean($post_csrf);
        } catch (\Exception $ex) {
            $csrf = 1;
            // Сообщение об Атаке или подборе csrf
            $security->csrf($request, $response);
        }
 
        $callbackStatus = 400;
        $callbackTitle = 'Соообщение системы';
        $callbackText = 'Действие запрещено !';
        $callback = ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');
 
        if ($csrf == $token) {
            $mods = explode(',', str_replace([" ", "'"], "", $config['routers']['site'][$this->route]['blocks']));
            foreach($mods as $key => $block)
            {
                $modules = new $config['vendor']['modules']['manager']($config, $template, $block, $this->route, $lang, $language);
                $callback = $modules->post($request, $response, $args);
            }
        } else {
            $callbackText = 'Перегрузите страницу';
            $callback = ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
        }
 
        // Подменяем заголовки
        $response = $hook->response();
        // Выводим json
        echo json_encode($callback, JSON_PRETTY_PRINT);
 
    }
 
}
 