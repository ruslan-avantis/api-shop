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
 
namespace ApiShop\Controller;
 
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
 
use Pllano\RouterDb\Db;
use Pllano\RouterDb\Router;
use Pllano\Caching\Cache;
 
use ApiShop\Utilities\Utility;
use ApiShop\Adapter\Menu;
 
use ApiShop\Model\SessionUser;
use ApiShop\Model\Language;
use ApiShop\Model\Site;
use ApiShop\Model\Template;
 
use ApiShop\Model\User;
use ApiShop\Model\Products;
 
use ApiShop\Model\Install;
 
class Index
{
 
    private $config = [];
    protected $logger;
    protected $view;
 
    function __construct($config, $view, $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->view = $view;
    }
 
    public function get(Request $request, Response $response, array $args)
    {
        // Конфигурация
        $config = $this->config;
        
        // Передаем данные Hooks для обработки ожидающим классам
        $hook = new $config['vendor']['hooks']['hook']($config);
        $hook->http($request, $response, $args, 'GET', 'site');
        $request = $hook->request();
        $args = $hook->args();
        if($hook->state() === true) {
            
            // Подключаем плагины
            $utility = new Utility();
            // Получаем параметры из URL
            $host = $request->getUri()->getHost();
            $path = $request->getUri()->getPath();
            // Конфигурация роутинга
            $routers = $config['routers'];
            // Подключаем мультиязычность
            $languages = new Language($request, $config);
            $language = $languages->get();
            // Меню, берет название класса из конфигурации
            $menu = (new Menu($config))->get();
            // Подключаем сессию, берет название класса из конфигурации
            $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
            // Данные пользователя из сессии
            $sessionUser =(new SessionUser($config))->get();
            // Подключаем временное хранилище
            $sessionTemp = new $config['vendor']['session']['session']("_temp");
            
            // Генерируем токен. Читаем ключ. Записываем токен в сессию.
            $session->token = $config['vendor']['crypto']['crypt']::encrypt($utility->random_token(), $config['key']['token']);
            
            // Контент по умолчанию
            $content = [];
            $render = '';
            
            $post_id = '/_';
            $admin_uri = '/_';
            if(!empty($session->admin_uri)) {
                $admin_uri = '/'.$session->admin_uri;
            }
            if(!empty($session->post_id)) {
                $post_id = '/'.$session->post_id;
            }
            
            if ($config["settings"]["install"]["status"] != null) {
                // Настройки сайта
                $site = new Site($config);
                $site_config = $site->get();
                // Получаем название шаблона
                $site_template = $site->template();
                // Конфигурация шаблона
                $templateConfig = new Template($site_template);
                $template = $templateConfig->get();
                // Шаблон по умолчанию 404
                $render = $template['layouts']['404'] ? $template['layouts']['404'] : '404.html';
            }
            
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
            
            $head = [
                "page" => 'home',
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
            
            if ($config["settings"]["install"]["status"] != null) {
                
                $templateConfig = new Template($config["settings"]["themes"]["template"]);
                $template = $templateConfig->get();
                
                $cache = new Cache($config);
                if ($cache->run($host.'/'.$languages->lang().'/'.$path) === null) {
                    
                    // Эти параметры должны браться из конфигурации шаблона
                    $arr = [
                        "limit" => $template['products']['home']['limit'],
                        "sort" => $template['products']['home']['sort'],
                        "order" => $template['products']['home']['order'],
                        "relations" => $template['products']['home']['relations'],
                        "state_seller" => 1
                    ];
                    // Получаем список товаров
                    $productsList = new $config['vendor']['products']['home']($config);
                    $content['products'] = $productsList->get($arr, $template, $host);
                    if ((int)$cache->state() == 1) {
                        $cache->set($content['products']);
                    }
                } else {
                    $content['products'] = $cache->get();
                }
                
                if (count($content['products']) >= 1) {
                    $render = $template['layouts']['index'] ? $template['layouts']['index'] : 'index.html';
                }
                
                $data = [
                    "head" => $head,
                    "routers" => $routers,
                    "site" => $site_config,
                    "config" => $config['settings']['site'],
                    "language" => $language,
                    "template" => $template,
                    "token" => $session->token,
                    "post_id" => $post_id,
                    "admin_uri" => $admin_uri,
                    "session" => $sessionUser,
                    "menu" => $menu,
                    "content" => $content
                ];
                
                } else {
                // Если ключа доступа у нет, значит сайт еще не активирован
                $content = '';
                $render = 'index.html';
                // $session->install = null;
                if (isset($session->install)) {
                    if ($session->install == 1) {
                        $render = "stores.html";
                        $content = (new Install($config))->stores_list();
                    } elseif ($session->install == 2) {
                        $render = "templates.html";
                        if (isset($session->install_store)) {
                            $install_store = $session->install_store;
                        } else {
                            $install_store = null;
                        }
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
            $hook->get($data, $render);
            // Запись в лог
            $this->logger->info($hook->logger());
            
        }
        
        // Отдаем данные шаблонизатору
        return $this->view->render($response, $hook->render(), $hook->view());
        
    }
    
}
