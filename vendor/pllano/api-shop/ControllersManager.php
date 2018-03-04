<?php /**
    * This file is part of the {API}$hop
    *
    * @license http://opensource.org/licenses/MIT
    * @link https://github.com/pllano/api-shop
    * @version 1.0.1
    * @package pllano.api-shop
    *
    * For the full copyright and license information, please view the LICENSE
    * file that was distributed with this source code.
*/

namespace Pllano\ApiShop;

use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};
use Psr\Container\ContainerInterface as Container;
use Pllano\ApiShop\Models\{
    Install, 
    SessionUser, 
    Site, 
    Security
};
use Pllano\Caching\Cache;

class ControllersManager
{

    private $app;
    private $config = [];
    private $time_start;
    private $package = [];
    private $session;
    private $languages;
    private $logger;
    private $template;
    private $view;
    private $route;
    private $query;

    function __construct(Container $app, $route)
    {
        $this->app = $app;
        $this->route = $route;
        $this->config = $app->get('config');
        $this->time_start = $app->get('time_start');
        $this->package = $app->get('package');
        $this->session = $app->get('session');
        $this->languages = $app->get('languages');
        $this->logger = $app->get('logger');
        $this->template = $app->get('template');
        $this->view = $app->get('view');
    }

    public function get(Request $request, Response $response, array $args)
    {
        // $getScheme			= $request->getUri()->getScheme(); // Работает
        // $getQuery			= $request->getUri()->getQuery(); // Работает
        // $getHost				= $request->getUri()->getHost(); // Работает
        // $getPath				= $request->getUri()->getPath(); // Работает
		// $getParams			= $request->getQueryParams(); // Работает
        // $getMethod			= $request->getMethod();
        // $getParsedBody		= $request->getParsedBody();

		$time_start = microtime_float();
        $this->query = $request->getMethod();

        // Передаем данные Hooks для обработки ожидающим классам
        // Default Pllano\Hooks\Hook
        $hook = new $this->config['vendor']['hooks']['hook']($this->config, $this->query, $this->route, 'site');
        $hook->http($request);
        $request = $hook->request();
        // true - Если все хуки отказались подменять контент
        if($hook->state() === true) {

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

            // Генерируем токен. Читаем ключ. Записываем токен в сессию.
            // Default Defuse\Crypto\Crypto
			$crypt = $this->config['vendor']['crypto']['crypt'];
            $this->session->token = $crypt::encrypt(random_token(), $this->config['key']['token']);

            $language = $this->languages->get($request);
            $lang = $this->languages->lang();

            // Настройки сайта
            $site = new Site($this->config);
            $siteConfig = $site->get();

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
                        $modules = new $this->config['vendor']['modules']['manager']($this->app, $this->route, $block);
                        $arr = $modules->get($request);
                        $dataArr = array_replace_recursive($dataArr, $arr);
                    }
                    if ((int)$cache->state() == 1) {
                        $cache->set($dataArr);
                    }
                } else {
                    $dataArr = $cache->get();
                }

                // Модули могут поменять layout
                $render = $dataArr['content']['modules'][$this->route]['content']['layout'] ?? $this->template['layouts']['layout'];

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
            } else {

				$sessionTemp = new $this->config['vendor']['session']['session']("_temp");
                $render = "index.html";
                // Если ключа доступа у нет, значит сайт еще не активирован
                $content = '';
                if (isset($this->session->install)) {
                    if ($this->session->install == 1) {
                        $render = "stores.html";
                        $content = (new Install($this->config))->stores_list();
                    } elseif ($this->session->install == 2) {
                        $render = "templates.html";
                        $install_store = $this->session->install_store ?? null;
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
        }

        $time = number_format(microtime_float() - $this->time_start, 4);
        $time_get_start = number_format(microtime_float() - $time_start, 4);
        if ($time >= 1) {
            // Запись в лог
            $this->logger->info("time", [
                "source" => "ControllerManager",
                "getMethod" => $request->getMethod(),
                "time" => $time,
                "time_start" => $this->time_start,
                "ControllerManagerStart" => $time_start,
                "uri" => escaped_url()
            ]);
        }
        if ($this->config['settings']["install"]["status"] != null) {
            return $response->write($this->view->render($hook->render(), $hook->view()));
        } else {
            return $response->write($this->view->render($render, $data));
        }

    }
 
    public function post(Request $request, Response $response, array $args)
    {
        $time_start = microtime_float();
        $method = $request->getMethod();
        $post = $request->getParsedBody();

        // Передаем данные Hooks для обработки ожидающим классам
        $hook = new $this->config['vendor']['hooks']['hook']($this->config);
        $hook->http($request, $method, 'site');
        $request = $hook->request();

        // Читаем ключи
        $token_key = $this->config['key']['token'];
        $crypt = $this->config['vendor']['crypto']['crypt'];

        // Подключаем систему безопасности
        $security = new Security($this->config);
        try {
            // Получаем токен из сессии
            $token = $crypt::decrypt($this->session->token, $token_key);
        } catch (\Exception $ex) {
            $token = 0;
            // Сообщение об Атаке или подборе токена
            $security->token($request);
        }
        try {
            // Получаем токен из POST
            $post_csrf = $crypt::decrypt(sanitize($post['csrf']), $token_key);
            // Чистим данные на всякий случай пришедшие через POST
            $csrf = clean($post_csrf);
        } catch (\Exception $ex) {
            $csrf = 1;
            // Сообщение об Атаке или подборе csrf
            $security->csrf($request);
        }

        $callbackStatus = 400;
        $callbackTitle = 'Соообщение системы';
        $callbackText = 'Действие запрещено !';
        $callback = ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
        $response->withStatus(200);
        $response->withHeader('Content-type', 'application/json');

        if ($csrf == $token) {
            $mods = explode(',', str_replace([" ", "'"], "", $this->config['routers']['site'][$this->route]['blocks']));
            foreach($mods as $key => $block)
            {
                $modules = new $this->config['vendor']['modules']['manager']($this->app, $this->route, $block);
                $callback = $modules->post($request);
            }
        } else {
            $callbackText = 'Перегрузите страницу';
            $callback = ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
        }

        $time = number_format(microtime_float() - $this->time_start, 4);
        $time_get_start = number_format(microtime_float() - $time_start, 4);
        if ($time >= 10) {
            // Запись в лог
            $this->logger->info("time", [
                "source" => "ControllerManager",
                "getMethod" => $request->getMethod(),
                "time" => $time,
                "time_start" => $this->time_start,
                "ControllerManagerStart" => $time_start,
                "uri" => escaped_url()
            ]);
        }
        return $response->write(json_encode($callback));
    }

}
 