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
use Psr\Container\ContainerInterface;
use ApiShop\Model\{User, Install, SessionUser, Language, Site, Template, Security};
use ApiShop\Utilities\Utility;
use ApiShop\Adapter\Menu;
use Pllano\Caching\Cache;
 
class ControllerManager
{

    private $config = [];
    private $package = [];
    private $session;
	private $languages;
	private $logger;
    private $template;
	private $view;
    private $route;
    private $query;
 
    function __construct($query, $route, $config, $package, $session, $languages, $template, $view, $logger)
    {
        $this->config = $config;
        $this->package = $package;
		$this->session = $session;
		$this->languages = $languages;
        $this->logger = $logger;
        $this->template = $template;
		$this->view = $view;
        $this->route = $route;
        $this->query = $query;
    }
    
/*     private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    } */
 
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

        // Передаем данные Hooks для обработки ожидающим классам
        // Default Pllano\Hooks\Hook
        $hook = new $this->config['vendor']['hooks']['hook']($this->config, $this->query, $this->route, 'site');
        $hook->http($request);
        $request = $hook->request();
 
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

            // Данные пользователя из сессии
            $sessionUser =(new SessionUser($this->config))->get();
            // Подключаем временное хранилище
            // Default Adbar\Session
            $sessionTemp = new $this->config['vendor']['session']['session']("_temp");
            // Генерируем токен. Читаем ключ. Записываем токен в сессию.
            // Default Defuse\Crypto\Crypto
            $this->session->token = $this->config['vendor']['crypto']['crypt']::encrypt($utility->random_token(), $this->config['key']['token']);

            $language = $this->languages->get($request);
            $lang = $this->languages->lang();

            // Настройки сайта
            $site = new Site($this->config);
            $siteConfig = $site->get();
            // Получаем название шаблона
            // Конфигурация шаблона
 
            // layout по умолчанию 404
            $render = $this->template['layouts']['404'] ? $this->template['layouts']['404'] : '404.html';

            // Конфигурация роутинга
            $routers = $this->config['routers'];

            $admin_uri = '/_';
            if(!empty($this->session->admin_uri)) {
                $admin_uri = '/'.$this->session->admin_uri;
            }
            $post_id = '/_';
            if(!empty($this->session->post_id)) {
                $post_id = '/'.$this->session->post_id;
            }

            // Заголовки по умолчанию из конфигурации
            $headArr = explode(',', str_replace([" ", "'"], "", $this->config['settings']['seo']['head']));
            $head = ["page" => $this->route, "host" => $host, "path" => $path, "scheme" => $this->config["server"]["scheme"].'://'];
            foreach($headArr as $headKey => $headVal)
            {
                $head_arr[$headVal] = $this->config['settings']['site'][$headVal];
                $head = array_replace_recursive($head, $head_arr);
            }

            if ($this->config["settings"]["install"]["status"] != null) {
                $pluginsArr = [];
                $dataArr = [];
                $arr = [];
                $cache = new Cache($this->config);
                if ($cache->run($host.''.$params.'/'.$lang.'/'.$this->route) === null) {
                    $dataArr = [
                        "head" => $head,
                        "routers" => $routers,
                        "site" => $siteConfig,
                        "config" => $this->config['settings']['site'],
                        "template" => $this->template
                    ];
                    $mods = explode(',', str_replace([" ", "'"], "", $this->config['routers']['site'][$this->route]['blocks']));
                    foreach($mods as $key => $block)
                    {
                        $modules = new $this->config['vendor']['modules']['manager']($this->config, $this->package, $this->template, $block, $this->route, $lang, $language);
                        $arr = $modules->get($request);
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
                $render = $dataArr['content']['modules'][$this->route]['content']['layout'] ?? $this->template['layouts']['layout'];

                if (isset($dataArr['content']['modules'][$this->route]['content']['layout'])) {
                    $render = $dataArr['content']['modules'][$this->route]['content']['layout'];
                } elseif (isset($dataArr['content'])) {
                    $render = $this->template['layouts']['layout'];
                }

                // Массив данных который нельзя кешировать
                $userArr = [
                    "language" => $language,
                    "token" => $this->session->token,
                    "post_id" => $post_id,
                    "admin_uri" => $admin_uri,
                    "session" => $sessionUser
                ];
                // Формируем данные для шаблонизатора. Склеиваем два массива.
                $data = array_replace_recursive($userArr, $dataArr);
                
                //print_r($data);
 
            } 
            else {
                $render = "index.html";
                // Если ключа доступа у нет, значит сайт еще не активирован
                $content = '';
                // $this->session->install = null;
                if (isset($this->session->install)) {
                    if ($this->session->install == 1) {
                        $render = "stores.html";
                        $content = (new Install($this->config))->stores_list();
                    } elseif ($this->session->install == 2) {
                        $render = "templates.html";
                        
                        $install_store = $this->session->install_store ?? null; // php7
 
                        $content = (new Install($this->config))->templates_list($install_store);
                    } elseif ($this->session->install == 3) {
                        $render = "welcome.html";
                    } elseif ($this->session->install == 10) {
                        $render = "templates.html";
                        $content = (new Install($this->config))->templates_list(null);
                    } elseif ($this->session->install == 11) {
                        $render = "key.html";
                    }
                }

                $data = [
                    "head" => $head,
                    "template" => "install",
                    "routers" => $routers,
                    "config" => $this->config['settings']['site'],
                    "language" => $language,
                    "token" => $this->session->token,
                    "post_id" => $post_id,
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
        if ($this->config['settings']["install"]["status"] != null) {
            return $this->view->render($hook->render(), $hook->view());
        } else {
            return $this->view->render($render, $data);
        }

    }
 
    public function post(Request $request, Response $response)
    {
        $method = $request->getMethod();
        $post = $request->getParsedBody();

        $today = date("Y-m-d H:i:s");
        // Получаем данные отправленные нам через POST

        // Передаем данные Hooks для обработки ожидающим классам
        $hook = new $this->config['vendor']['hooks']['hook']($this->config);
        $hook->http($request, $method, 'site');
        $request = $hook->request();

        // Подключаем утилиты
        $utility = new Utility();

        // Читаем ключи
        $token_key = $this->config['key']['token'];
        // Подключаем систему безопасности
        $security = new Security($this->config);

        // Настройки сайта
        $site = new Site($this->config);
        $siteConfig = $site->get();

        // Подключаем мультиязычность
        $language = $this->languages->get($request);
        $lang = $this->languages->lang();

        try {
            // Получаем токен из сессии
            $token = $this->config['vendor']['crypto']['crypt']::decrypt($this->session->token, $token_key);
        } catch (\Exception $ex) {
            $token = 0;
            // Сообщение об Атаке или подборе токена
            $security->token($request);
        }
        try {
            // Получаем токен из POST
            $post_csrf = $this->config['vendor']['crypto']['crypt']::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
            // Чистим данные на всякий случай пришедшие через POST
            $csrf = $utility->clean($post_csrf);
        } catch (\Exception $ex) {
            $csrf = 1;
            // Сообщение об Атаке или подборе csrf
            $security->csrf($request);
        }

        $callbackStatus = 400;
        $callbackTitle = 'Соообщение системы';
        $callbackText = 'Действие запрещено !';
        $callback = ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
        // Выводим заголовки
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');

        if ($csrf == $token) {
            $mods = explode(',', str_replace([" ", "'"], "", $this->config['routers']['site'][$this->route]['blocks']));
            foreach($mods as $key => $block)
            {
                $modules = new $this->config['vendor']['modules']['manager']($this->config, $this->template, $block, $this->route, $lang, $language);
                $callback = $modules->post($request);
            }
        } else {
            $callbackText = 'Перегрузите страницу';
            $callback = ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
        }

        // Выводим json
        return json_encode($callback, JSON_PRETTY_PRINT);

    }

}
 