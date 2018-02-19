<?php /**
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
 
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
 
use Pllano\RouterDb\Db;
use Pllano\RouterDb\Router;
use Pllano\Hooks\Hook;
 
use ApiShop\Utilities\Utility;
 
use ApiShop\Model\Install;
use ApiShop\Model\Language;
use ApiShop\Model\Site;
use ApiShop\Model\Template;
use ApiShop\Model\SessionUser;
use ApiShop\Model\Filter;
use ApiShop\Model\Pagination;
use ApiShop\Model\Security;
 
use ApiShop\Admin\Control;
use ApiShop\Admin\AdminDatabase;
use ApiShop\Admin\Resources;
use ApiShop\Admin\Packages;
 
$admin_router = $config['routers']['admin']['all']['route'];
$admin_index = $config['routers']['admin']['index']['route'];
 
$session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
 
$admin_uri = '/0';
$post_id = '/0';
 
if(isset($session->admin_uri)) {
    $admin_uri = '/'.$session->admin_uri;
}
if(isset($session->post_id)) {
    $post_id = '/'.$session->post_id.'/';
}

// Главная страница админ панели
$app->get($admin_uri.$admin_index.'', function (Request $request, Response $response, array $args) {
    
    // Получаем конфигурацию
    $config = $this->config;
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new $config['vendor']['hooks']['hook']($config);
    $hook->http($request, $response, $args, 'GET', 'admin');
    $request = $hook->request();
    $args = $hook->args();
    
    // Подключаем плагины
    $utility = new Utility();
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Конфигурация роутинга
    $routers = $config['routers'];
    // Конфигурация шаблона
    $templateConfig = new Template($config['admin']['template']);
    $template = $templateConfig->get();
    // Подключаем мультиязычность
    $languages = new Language($request, $config);
    $language = $languages->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser($config))->get();
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token_admin = $config['vendor']['crypto']['crypt']::encrypt($token, $token_key);
    // Шаблон по умолчанию 404
    $render = $template['layouts']['404'] ? $template['layouts']['404'] : '404.html';
    // Контент по умолчанию
    $content = '';
    
    $post_id = '/_';
    $admin_uri = '/_';
    if(!empty($session->admin_uri)) {
        $admin_uri = '/'.$session->admin_uri;
    }
    if(!empty($session->post_id)) {
        $post_id = '/'.$session->post_id;
    }
    
    // Заголовки по умолчанию из конфигурации
    $title = $language["709"].' - '.$config['settings']['site']['title'];
    $keywords = $config['settings']['site']['keywords'];
    $description = $config['settings']['site']['description'];
    $robots = $config['settings']['site']['robots'];
    $og_title = $config['settings']['site']['og_title'];
    $og_description = $config['settings']['site']['og_description'];
    $og_image = $config['settings']['site']['og_image'];
    $og_type = $config['settings']['site']['og_type'];
    $og_locale = $config['settings']['site']['og_locale'];
    $og_url = $config['settings']['site']['og_url'];
    
    if (isset($session->authorize)) {
        if ($session->role_id == 100) {
            // Подключаем класс
            $index = new \ApiShop\Admin\Index($config);
            // Получаем массив с настройками шаблона
            $content = $index->get();
            // Получаем название шаблона
            $render = $template['layouts']['index'] ? $template['layouts']['index'] : 'index.html';
        }
    } else {
        $session->authorize = null;
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
    "config" => $config,
    "language" => $language,
    "template" => $template,
    "token" => $session->token_admin,
    "admin_uri" => $admin_uri,
    "post_id" => $post_id,
    "session" => $sessionUser,
    "content" => $content
    ];
    
    // Передаем данные Hooks для обработки ожидающим классам
    $hook->get($render, $view);
    // Запись в лог
    $this->logger->info($hook->logger());
    // Отдаем данные шаблонизатору
    return $this->admin->render($hook->render(), $hook->view());
    
});

// Список items указанного resource
$app->get($admin_uri.$admin_router.'resource/{resource:[a-z0-9_-]+}[/{id:[a-z0-9_]+}]', function (Request $request, Response $response, array $args) {
    
    // Получаем конфигурацию
    $config = $this->config;
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new $config['vendor']['hooks']['hook']($config);
    $hook->http($request, $response, $args, 'GET', 'admin');
    $request = $hook->request();
    $args = $hook->args();
    
    // Подключаем плагины
    $utility = new Utility();
    // Получаем resource из url
    if ($request->getAttribute('resource')) {
        $resource = $utility->clean($request->getAttribute('resource'));
    } else {
        $resource = null;
    }
    // Получаем id из url
    if ($request->getAttribute('id')) {
        $id = $utility->clean($request->getAttribute('id'));
    } else {
        $id = null;
    }
    // Подключаем плагины
    $utility = new Utility();
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Конфигурация роутинга
    $routers = $config['routers'];
    // Конфигурация шаблона
    $templateConfig = new Template($config['admin']['template']);
    $template = $templateConfig->get();
    // Подключаем мультиязычность
    $languages = new Language($request, $config);
    $language = $languages->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser($config))->get();
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token_admin = $config['vendor']['crypto']['crypt']::encrypt($token, $token_key);
    // Шаблон по умолчанию 404
    $render = $template['layouts']['404'] ? $template['layouts']['404'] : '404.html';
    // Контент по умолчанию
    $content = '';
    
    $post_id = '/_';
    $admin_uri = '/_';
    if(!empty($session->admin_uri)) {
        $admin_uri = '/'.$session->admin_uri;
    }
    if(!empty($session->post_id)) {
        $post_id = '/'.$session->post_id;
    }
    
    // Заголовки по умолчанию из конфигурации
    $title = $language["709"].' - '.$config['settings']['site']['title'];
    $keywords = $config['settings']['site']['keywords'];
    $description = $config['settings']['site']['description'];
    $robots = $config['settings']['site']['robots'];
    $og_title = $config['settings']['site']['og_title'];
    $og_description = $config['settings']['site']['og_description'];
    $og_image = $config['settings']['site']['og_image'];
    $og_type = $config['settings']['site']['og_type'];
    $og_locale = $config['settings']['site']['og_locale'];
    $og_url = $config['settings']['site']['og_url'];
    
    $name_db = '';
    $type = 'get';
    
    if (isset($session->authorize) && isset($resource)) {
        if ($session->role_id == 100) {
            
            $resource_list = explode(',', str_replace(['"', "'", " "], '', $config['admin']['resource_list']));
            
            if (array_key_exists($resource, array_flip($resource_list))) {
                
                // Отдаем роутеру RouterDb конфигурацию.
                $router = new Router($config);
                // Получаем название базы для указанного ресурса
                $name_db = $router->ping($resource);
                // Подключаемся к базе
                $db = new Db($name_db, $config);
                
                if($id >= 1) {
                    $render = $resource.'_id.html';
                    $type = 'edit';
                    // Отправляем запрос и получаем данные
                    $resp = $db->get($resource, [], $id);
                    if (isset($resp["headers"]["code"])) {
                        if ($resp["headers"]["code"] == 200 || $resp["headers"]["code"] == '200') {
                            $content = $resp['body']['items']['0']['item'];
                            if($resource == 'article'){
                                $title = $content['seo_title'].'- API Shop';
                                $keywords = $content['seo_keywords'].'- API Shop';
                                $description = $content['seo_description'].'- API Shop';
                            }
                        }
                    }
                } else {
                    $render = $resource.'.html';
                    // Отправляем запрос и получаем данные
                    $resp = $db->get($resource);
                    if (isset($resp["headers"]["code"])) {
                        if ($resp["headers"]["code"] == 200 || $resp["headers"]["code"] == '200') {
                            $content = $resp['body']['items'];
                        }
                    }
                }
            }
        }
    } else {
        $session->authorize = null;
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
        "config" => $config,
        "language" => $language,
        "template" => $template,
        "token" => $session->token_admin,
        "admin_uri" => $admin_uri,
        "post_id" => $post_id,
        "session" => $sessionUser,
        "content" => $content,
        "editor" => $config['admin']['editor'],
        "name_db" => $name_db,
        "resource" => $resource,
        "type" => $type
    ];
    
    // Передаем данные Hooks для обработки ожидающим классам
    $hook->get($render, $view);
    // Запись в лог
    $this->logger->info($hook->logger());
    // Отдаем данные шаблонизатору
    return $this->admin->render($hook->render(), $hook->view());
    
});

// Содать запись в resource
$app->post($admin_uri.$admin_router.'resource-post', function (Request $request, Response $response, array $args) {
    
    // Подключаем конфиг Settings\Config
    $config = $this->config;
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new $config['vendor']['hooks']['hook']($config);
    $hook->http($request, $response, $args, 'POST', 'admin');
    $request = $hook->request();
    $args = $hook->args();
    
    $today = date("Y-m-d H:i:s");
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
    // Подключаем плагины
    $utility = new Utility();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Подключаем систему безопасности
    $security = new Security();
    
    $resource = null;
    if (isset($post['resource'])) {
        $resource = filter_var($post['resource'], FILTER_SANITIZE_STRING);
    }
    
    try {
        // Получаем токен из сессии
        $token = $config['vendor']['crypto']['crypt']::decrypt($session->token_admin, $token_key);
    } catch (\Exception $ex) {
        $token = 0;
        if (isset($session->authorize)) {
            if ($session->authorize != 1 || $session->role_id != 100) {
                // Сообщение об Атаке или подборе токена
                $security->token($request, $response);
            }
        } else {
            // Сообщение об Атаке или подборе токена
            $security->token($request, $response);
        }
    }
    
    try {
        // Получаем токен из POST
        $post_csrf = $config['vendor']['crypto']['crypt']::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
        // Чистим данные на всякий случай пришедшие через POST
        $csrf = $utility->clean($post_csrf);
    } catch (\Exception $ex) {
        $csrf = 1;
        if (isset($session->authorize)) {
            if ($session->authorize != 1 || $session->role_id != 100) {
                // Сообщение об Атаке или подборе csrf
                $security->csrf($request, $response);
            }
        } else {
            // Сообщение об Атаке или подборе csrf
            $security->csrf($request, $response);
        }
    }
    
    $callbackStatus = 400;
    $callbackTitle = 'Соообщение системы';
    $callbackText = '';
    
    // Проверка токена - Если токен не совпадает то ничего не делаем. Можем записать в лог или написать письмо админу
    if ($csrf == $token) {
        if (isset($session->authorize)) {
            if ($session->authorize == 1 || $session->role_id == 100) {
                if (isset($resource)) {
                    $resource_list = explode(',', str_replace(['"', "'", " "], '', $config['admin']['resource_list']));
                    if (array_key_exists($resource, array_flip($resource_list))) {
                        
                        $postArr = [];
                        $random_alias_id = $utility->random_alias_id();
                        
                        if ($resource == 'article') {
                            $postArr['title'] = 'New Article';
                            $postArr['text'] = '<div class="text-red font_56">New Text Article</div>';
                            $postArr['alias'] = 'alias-'.$random_alias_id;
                            $postArr['alias_id'] = $random_alias_id;
                            $postArr['created'] = $today;
                            $postArr['category_id'] = 0;
                            $postArr['state'] = 1;
                            } elseif ($resource == 'article_category' || $resource == 'category') {
                            $postArr['title'] = 'New Category';
                            $postArr['text'] = '<div class="text-red font_56">New Text Category</div>';
                            $postArr['alias'] = 'alias-'.$random_alias_id;
                            $postArr['parent_id'] = 0;
                            $postArr['alias_id'] = $random_alias_id;
                            $postArr['created'] = $today;
                            $postArr['state'] = 1;
                            } elseif ($resource == 'currency') {
                            $postArr['name'] = 'New Article';
                            $postArr['course'] = 'course';
                            $postArr['iso_code'] = 'iso_code';
                            $postArr['iso_code_num'] = 'iso_code_num';
                            $postArr['modified'] = $today;
                            $postArr['state'] = 1;
                            } elseif ($resource == 'user') {
                            $postArr['iname'] = 'New';
                            $postArr['fname'] = 'User';
                            $postArr['email'] = 'user.' . rand(0,9) . rand(0,9) . rand(0,9) .'@example.com';
                            $random_number = intval( rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) . rand(0,9) ); 
                            $postArr['phone'] = '38067'.$random_number;
                            $postArr['alias'] = $random_alias_id;
                            $postArr['language'] = 'ru';
                            $postArr['password'] = password_hash($random_alias_id, PASSWORD_DEFAULT);
                            $postArr['role_id'] = 1;
                            $postArr['state'] = 1;
                        }
                        
                        // Отдаем роутеру RouterDb конфигурацию.
                        $router = new Router($config);
                        // Получаем название базы для указанного ресурса
                        $name_db = $router->ping($resource);
                        // Подключаемся к базе
                        $db = new Db($name_db, $config);
                        
                        // Передаем данные Hooks для обработки ожидающим классам
                        $hook->post($resource, $name_db, 'POST', $postArr, null);
                        $hookState = $hook->state();
                        // Если Hook вернул true
                        if ($hookState == true) {
                            // Обновленные Hooks данные
                            $hookResource = $hook->resource();
                            $hookPostArr = $hook->postArr();
                            // Отправляем запрос в базу
                            $dbState = $db->post($resource, $postArr);
                            if ($dbState == true) {
                                // Ответ
                                $callbackStatus = 200;
                            } else {
                                $callbackText = 'Действие заблокировано - 2';
                            }
                        }
                        
                    } else {
                        $callbackText = 'Действие заблокировано - 1';
                    }
                } else {
                    $callbackText = 'Ошибка !';
                }
            } else {
                $callbackText = 'Вы не администратор';
            }
        } else {
            $callbackText = 'Вы не авторизованы';
        }
    } else {
        $callbackText = 'Обновите страницу';
    }
    
    $callback = ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
    // Выводим заголовки
    $response->withStatus(200);
    $response->withHeader('Content-type', 'application/json');
    // Подменяем заголовки
    $response = $hook->response();
    // Выводим json
    echo json_encode($hook->callback($callback));
    
});

// Удалить запись в resource
$app->post($admin_uri.$admin_router.'resource-delete', function (Request $request, Response $response, array $args) {
    
    // Подключаем конфиг Settings\Config
    $config = $this->config;
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new $config['vendor']['hooks']['hook']($config);
    $hook->http($request, $response, $args, 'POST', 'admin');
    $request = $hook->request();
    $args = $hook->args();
    
    // Подключаем плагины
    $utility = new Utility();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Подключаем систему безопасности
    $security = new Security();
    
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
    
    $resource = null;
    if (isset($post['resource'])) {
        $resource = filter_var($post['resource'], FILTER_SANITIZE_STRING);
    }
    
    $id = null;
    if (isset($post['id'])) {
        $id = intval($post['id']);
    }
    
    try {
        // Получаем токен из сессии
        $token = $config['vendor']['crypto']['crypt']::decrypt($session->token_admin, $token_key);
    } catch (\Exception $ex) {
        $token = 0;
        if (isset($session->authorize)) {
            if ($session->authorize != 1 || $session->role_id != 100) {
                // Сообщение об Атаке или подборе токена
                $security->token($request, $response);
            }
        } else {
            // Сообщение об Атаке или подборе токена
            $security->token($request, $response);
        }
    }
    
    try {
        // Получаем токен из POST
        $post_csrf = $config['vendor']['crypto']['crypt']::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
        // Чистим данные на всякий случай пришедшие через POST
        $csrf = $utility->clean($post_csrf);
    } catch (\Exception $ex) {
        $csrf = 1;
        if (isset($session->authorize)) {
            if ($session->authorize != 1 || $session->role_id != 100) {
                // Сообщение об Атаке или подборе csrf
                $security->csrf($request, $response);
            }
        } else {
            // Сообщение об Атаке или подборе csrf
            $security->csrf($request, $response);
        }
    }
    
    $callbackStatus = 400;
    $callbackTitle = 'Соообщение системы';
    $callbackText = '';
    
    // Проверка токена - Если токен не совпадает то ничего не делаем. Можем записать в лог или написать письмо админу
    if ($csrf == $token) {
        if (isset($session->authorize)) {
            if ($session->authorize == 1 || $session->role_id == 100) {
                if (isset($resource) && isset($id)) {
                    $resource_list = explode(',', str_replace(['"', "'", " "], '', $config['admin']['resource_list']));
                    if (array_key_exists($resource, array_flip($resource_list))) {
                        
                        // Отдаем роутеру RouterDb конфигурацию.
                        $router = new Router($config);
                        // Получаем название базы для указанного ресурса
                        $name_db = $router->ping($resource);
                        // Подключаемся к базе
                        $db = new Db($name_db, $config);
                        
                        // Передаем данные Hooks для обработки ожидающим классам
                        $hook->post($resource, $name_db, 'DELETE', [], $id);
                        $hookState = $hook->state();
                        // Если Hook вернул true
                        if ($hookState == true) {
                            // Обновленные Hooks данные
                            $hookResource = $hook->resource();
                            $hookId = $hook->id();
                            // Отправляем запрос в базу
                            $dbState = $db->delete($hookResource, [], $hookId);
                            if ($dbState == true) {
                                // Ответ
                                $callbackStatus = 200;
                            } else {
                                $callbackText = 'Действие заблокировано';
                            }
                        }
                    } else {
                        $callbackText = 'Действие заблокировано';
                    }
                } else {
                    $callbackText = 'Ошибка !';
                }
            } else {
                $callbackText = 'Вы не администратор';
            }
        } else {
            $callbackText = 'Вы не авторизованы';
        }
    } else {
        $callbackText = 'Обновите страницу';
    }
    
    $callback = ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
    // Выводим заголовки
    $response->withStatus(200);
    $response->withHeader('Content-type', 'application/json');
    // Подменяем заголовки
    $response = $hook->response();
    // Выводим json
    echo json_encode($hook->callback($callback));
    
});

// Редактируем запись в resource
$app->post($admin_uri.$admin_router.'resource-put/{resource:[a-z0-9_-]+}[/{id:[a-z0-9_]+}]', function (Request $request, Response $response, array $args) {
    
    // Подключаем конфиг Settings\Config
    $config = $this->config;
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new $config['vendor']['hooks']['hook']($config);
    $hook->http($request, $response, $args, 'POST', 'admin');
    $request = $hook->request();
    $args = $hook->args();
    
    // Подключаем плагины
    $utility = new Utility();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    
    // Получаем resource из url
    if ($request->getAttribute('resource')) {
        $resource_list = explode(',', str_replace(['"', "'", " "], '', $config['admin']['resource_list']));
        $resource = $utility->clean($request->getAttribute('resource'));
        if (array_key_exists($resource, array_flip($resource_list))) {
            $table = json_decode(file_get_contents($config["db"]["json"]["dir"].'/'.$resource.'.config.json'), true);
            // Получаем данные отправленные нам через POST
            $post = $request->getParsedBody();
            $post = (array)$post;
        } else {
            $resource = null;
        }
    } else {
        $resource = null;
    }
    
    // Получаем id из url
    if ($request->getAttribute('id')) {
        $id = intval($utility->clean($request->getAttribute('id')));
        } else {
        $id = null;
    }
    
    try {
        // Получаем токен из сессии
        $token = $config['vendor']['crypto']['crypt']::decrypt($session->token_admin, $token_key);
    } catch (\Exception $ex) {
        $token = 0;
        if (isset($session->authorize)) {
            if ($session->authorize != 1 || $session->role_id != 100) {
                // Сообщение об Атаке или подборе токена
                (new Security())->token($request, $response);
            }
        } else {
            // Сообщение об Атаке или подборе токена
            (new Security())->token($request, $response);
        }
    }
    
    try {
        // Получаем токен из POST
        $post_csrf = $config['vendor']['crypto']['crypt']::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
        // Чистим данные на всякий случай пришедшие через POST
        $csrf = $utility->clean($post_csrf);
    } catch (\Exception $ex) {
        $csrf = 1;
        if (isset($session->authorize)) {
            if ($session->authorize != 1 || $session->role_id != 100) {
                // Сообщение об Атаке или подборе csrf
                (new Security())->csrf($request, $response);
            }
        } else {
            // Сообщение об Атаке или подборе csrf
            (new Security())->csrf($request, $response);
        }
    }
    
    $callbackStatus = 400;
    $callbackTitle = 'Соообщение системы';
    $callbackText = '';
    
    // Проверка токена - Если токен не совпадает то ничего не делаем. Можем записать в лог или написать письмо админу
    if ($csrf == $token) {
        if (isset($session->authorize)) {
            if ($session->authorize == 1 && $session->role_id == 100) {
                if (isset($resource) && isset($id)) {
                    if (array_key_exists($resource, array_flip($resource_list))) {
                        $saveArr = [];
                        $resource_id = $resource."_id";
                        
                        foreach($post as $key => $value)
                        {
                            if (array_key_exists($key, $table["schema"]) && $value != "" && $key != "id") {
                                if($key == "text" || $key == "text_ru" || $key == "text_ua" || $key == "text_de" || $key == "text_en") {
                                    $saveArr[$key] = $utility->cleanText($value);
                                } elseif ($key == $resource_id) {
                                    $value = str_replace(['"', "'", " "], '', $value);
                                    $saveArr[$key] = intval($utility->clean($value));
                                } elseif ($key == "phone") {
                                    $value = str_replace(['"', "'", " "], '', $value);
                                    $saveArr[$key] = strval($utility->clean($value));
                                } elseif ($key == "password") {
                                    if(strlen($value) >= 55 && strlen($value) <= 65) {
                                        $saveArr[$key] = filter_var($value, FILTER_SANITIZE_STRING);
                                    } else {
                                        $saveArr[$key] = password_hash(filter_var($value, FILTER_SANITIZE_STRING), PASSWORD_DEFAULT);
                                    }
                                } else {
                                    if (is_numeric($utility->clean($value))) {
                                        $value = str_replace(['"', "'", " "], '', $value);
                                        $saveArr[$key] = intval($utility->clean($value));
                                        } elseif (is_float($utility->clean($value))) {
                                        $value = str_replace(['"', "'", " "], '', $value);
                                        $saveArr[$key] = float($utility->clean($value));
                                        } elseif (is_bool($utility->clean($value))) {
                                        $value = str_replace(['"', "'", " "], '', $value);
                                        $saveArr[$key] = boolval($utility->clean($value));
                                        } elseif (is_string($utility->clean($value))) {
                                        $saveArr[$key] = filter_var(strval($value), FILTER_SANITIZE_STRING);
                                    }
                                }
                            }
                        }
                        
                        // Отдаем роутеру RouterDb конфигурацию.
                        $router = new Router($config);
                        // Получаем название базы для указанного ресурса
                        $name_db = $router->ping($resource);
                        // Подключаемся к базе
                        $db = new Db($name_db, $config);
                        // Обновляем данные
                        $requestDb = $db->put($resource, $saveArr, $id);
                        
                        if ($requestDb == true) {
                            // Ответ
                            $callbackStatus = 200;
                        } else {
                            $callbackText = 'Действие заблокировано';
                        }
                    } else {
                        $callbackText = 'Действие заблокировано';
                    }
                } else {
                    $callbackText = 'Ошибка !';
                }
            } else {
                $callbackText = 'Вы не администратор';
            }
        } else {
            $callbackText = 'Вы не авторизованы';
        }
    } else {
        $callbackText = 'Обновите страницу';
    }
    
    $callback = ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
    // Выводим заголовки
    $response->withStatus(200);
    $response->withHeader('Content-type', 'application/json');
    // Подменяем заголовки
    $response = $hook->response();
    // Выводим json
    echo json_encode($hook->callback($callback));
    
});

// Активировать заказ
$app->post($admin_uri.$admin_router.'order-activate', function (Request $request, Response $response, array $args) {
    
    // Подключаем конфиг Settings\Config
    $config = $this->config;
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new $config['vendor']['hooks']['hook']($config);
    $hook->http($request, $response, $args, 'POST', 'admin');
    $request = $hook->request();
    $args = $hook->args();
    
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
    
    // Подключаем плагины
    $utility = new Utility();
    
    try {
        // Получаем токен из сессии
        $token = $config['vendor']['crypto']['crypt']::decrypt($session->token_admin, $token_key);
    } catch (\Exception $ex) {
        $token = 0;
        if (isset($session->authorize)) {
            if ($session->authorize != 1 || $session->role_id != 100) {
                // Сообщение об Атаке или подборе токена
                (new Security())->token($request, $response);
            }
        } else {
            // Сообщение об Атаке или подборе токена
            (new Security())->token($request, $response);
        }
    }
    
    try {
        // Получаем токен из POST
        $post_csrf = $config['vendor']['crypto']['crypt']::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
        // Чистим данные на всякий случай пришедшие через POST
        $csrf = $utility->clean($post_csrf);
    } catch (\Exception $ex) {
        $csrf = 1;
        if (isset($session->authorize)) {
            if ($session->authorize != 1 || $session->role_id != 100) {
                // Сообщение об Атаке или подборе csrf
                (new Security())->csrf($request, $response);
            }
        } else {
            // Сообщение об Атаке или подборе csrf
            (new Security())->csrf($request, $response);
        }
    }
    
    $callbackStatus = 400;
    $callbackTitle = 'Соообщение системы';
    $callbackText = '';
    
    // Проверка токена - Если токен не совпадает то ничего не делаем. Можем записать в лог или написать письмо админу
    if ($csrf == $token) {
        if (isset($session->authorize)) {
            if ($session->authorize == 1 || $session->role_id == 100) {
                if (isset($post['alias'])) {
                    $alias = filter_var($post['alias'], FILTER_SANITIZE_STRING);
                    
                    if ($alias == true) {
                        // Ответ
                        $callbackStatus = 200;
                    } else {
                        $callbackText = 'Действие заблокировано';
                    }
                } else {
                    $callbackText = 'Не определен alias заказа';
                }
            } else {
                $callbackText = 'Вы не являетесь администратором';
            }
        } else {
            $callbackText = 'Вы не авторизованы';
        }
    } else {
        $callbackText = 'Ошибка';
    }
    
    $callback = ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
    // Выводим заголовки
    $response->withStatus(200);
    $response->withHeader('Content-type', 'application/json');
    // Подменяем заголовки
    $response = $hook->response();
    // Выводим json
    echo json_encode($hook->callback($callback));
    
});

// Купить и установить шаблон
$app->post($admin_uri.$admin_router.'template-buy', function (Request $request, Response $response, array $args) {
    
    // Подключаем конфиг Settings\Config
    $config = $this->config;
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new $config['vendor']['hooks']['hook']($config);
    $hook->http($request, $response, $args, 'POST', 'admin');
    $request = $hook->request();
    $args = $hook->args();
    
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    
    try {
        // Получаем токен из сессии
        $token = $config['vendor']['crypto']['crypt']::decrypt($session->token_admin, $token_key);
    } catch (\Exception $ex) {
        (new Security())->token($request, $response);
        // Сообщение об Атаке или подборе токена
    }
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
    try {
        // Получаем токен из POST
        $post_csrf = $config['vendor']['crypto']['crypt']::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
        } catch (\Exception $ex) {
        (new Security())->csrf($request, $response);
        // Сообщение об Атаке или подборе csrf
    }
    // Подключаем плагины
    $utility = new Utility();
    // Чистим данные на всякий случай пришедшие через POST
    $csrf = $utility->clean($post_csrf);
    
    $callbackStatus = 400;
    $callbackTitle = 'Соообщение системы';
    $callbackText = '';
    
    // Проверка токена - Если токен не совпадает то ничего не делаем. Можем записать в лог или написать письмо админу
    if ($csrf == $token) {
        
        if (isset($post['alias'])) {
            $alias = filter_var($post['alias'], FILTER_SANITIZE_STRING);
            $callbackStatus = 200;
            } else {
            $callbackText = 'Ошибка !';
        }
        } else {
        $callbackText = 'Ошибка !';
    }
    
    $callback = ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
    // Выводим заголовки
    $response->withStatus(200);
    $response->withHeader('Content-type', 'application/json');
    // Подменяем заголовки
    $response = $hook->response();
    // Выводим json
    echo json_encode($hook->callback($callback));
    
});

// Установить шаблон
$app->post($admin_uri.$admin_router.'template-install', function (Request $request, Response $response, array $args) {
    
    // Подключаем конфиг Settings\Config
    $config = $this->config;
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new $config['vendor']['hooks']['hook']($config);
    $hook->http($request, $response, $args, 'POST', 'admin');
    $request = $hook->request();
    $args = $hook->args();
    
    // Подключаем плагины
    $utility = new Utility();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
    
    try {
        // Получаем токен из сессии
        $token = $config['vendor']['crypto']['crypt']::decrypt($session->token_admin, $token_key);
        } catch (\Exception $ex) {
        $token = 0;
        if (isset($session->authorize)) {
            if ($session->authorize != 1 || $session->role_id != 100) {
                // Сообщение об Атаке или подборе токена
                (new Security())->token($request, $response);
            }
            } else {
            // Сообщение об Атаке или подборе токена
            (new Security())->token($request, $response);
        }
    }
    
    try {
        // Получаем токен из POST
        $post_csrf = $config['vendor']['crypto']['crypt']::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
        // Чистим данные на всякий случай пришедшие через POST
        $csrf = $utility->clean($post_csrf);
        } catch (\Exception $ex) {
        $csrf = 1;
        if (isset($session->authorize)) {
            if ($session->authorize != 1 || $session->role_id != 100) {
                // Сообщение об Атаке или подборе csrf
                (new Security())->csrf($request, $response);
            }
            } else {
            // Сообщение об Атаке или подборе csrf
            (new Security())->csrf($request, $response);
        }
    }
    
    $callbackStatus = 400;
    $callbackTitle = 'Соообщение системы';
    $callbackText = '';
    
    // Проверка токена - Если токен не совпадает то ничего не делаем. Можем записать в лог или написать письмо админу
    if ($csrf == $token) {
        if (isset($post['alias'])) {
            
            $dir = null;
            $uri = null;
            $name = null;
            
            $alias = filter_var($post['alias'], FILTER_SANITIZE_STRING);
            
            $templates_list = (new Install($config))->templates_list($config['seller']['store']);
            
            if (count($templates_list) >= 1) {
                foreach($templates_list as $value)
                {
                    if ($value['item']["alias"] == $alias) {
                        
                        $dir = $value['item']['dir'];
                        $uri = $value['item']['uri'];
                        $name = $value['item']['dir'];
                        
                        if(isset($dir) && isset($uri) && isset($name)) {
                            // Подключаем глобальную конфигурацию
                            $glob_config = new \ApiShop\Admin\Config($config);
                            // Устанавливаем шаблон
                            $template_install = $glob_config->template_install($name, $dir, $uri);
                            
                            if ($template_install === true) {
                                $callbackStatus = 200;
                                }  else {
                                $callbackText = 'Ошибка !';
                            }
                            
                            } else {
                            $callbackText = 'Ошибка !';
                        }
                    }
                }
            }
            } else {
            $callbackText = 'Ошибка !';
        }
        } else {
        $callbackText = 'Ошибка !';
    }
    
    $callback = ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
    // Выводим заголовки
    $response->withStatus(200);
    $response->withHeader('Content-type', 'application/json');
    // Подменяем заголовки
    $response = $hook->response();
    // Выводим json
    echo json_encode($hook->callback($callback));
    
});

// Активировать шаблон
$app->post($admin_uri.$admin_router.'template-activate', function (Request $request, Response $response, array $args) {
    
    // Подключаем конфиг Settings\Config
    $config = $this->config;
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new $config['vendor']['hooks']['hook']($config);
    $hook->http($request, $response, $args, 'POST', 'admin');
    $request = $hook->request();
    $args = $hook->args();
    
    // Подключаем плагины
    $utility = new Utility();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
    
    try {
        // Получаем токен из сессии
        $token = $config['vendor']['crypto']['crypt']::decrypt($session->token_admin, $token_key);
        } catch (\Exception $ex) {
        $token = 0;
        if (isset($session->authorize)) {
            if ($session->authorize != 1 || $session->role_id != 100) {
                // Сообщение об Атаке или подборе токена
                (new Security())->token($request, $response);
            }
            } else {
            // Сообщение об Атаке или подборе токена
            (new Security())->token($request, $response);
        }
    }
    
    try {
        // Получаем токен из POST
        $post_csrf = $config['vendor']['crypto']['crypt']::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
        // Чистим данные на всякий случай пришедшие через POST
        $csrf = $utility->clean($post_csrf);
        } catch (\Exception $ex) {
        $csrf = 1;
        if (isset($session->authorize)) {
            if ($session->authorize != 1 || $session->role_id != 100) {
                // Сообщение об Атаке или подборе csrf
                (new Security())->csrf($request, $response);
            }
            } else {
            // Сообщение об Атаке или подборе csrf
            (new Security())->csrf($request, $response);
        }
    }
    
    $callbackStatus = 400;
    $callbackTitle = 'Соообщение системы';
    $callbackText = '';
    
    // Проверка токена - Если токен не совпадает то ничего не делаем. Можем записать в лог или написать письмо админу
    if ($csrf == $token) {
        if (isset($session->authorize)) {
            if ($session->authorize == 1 && $session->role_id == 100) {
                if (isset($post['dir'])) {
                    
                    $dir = filter_var($post['dir'], FILTER_SANITIZE_STRING);
                    $alias = filter_var($post['alias'], FILTER_SANITIZE_STRING);
                    
                    // Активируем шаблон
                    (new \ApiShop\Admin\Config($config))->template_activate($dir, $alias);
                    
                    $callbackStatus = 200;
                    
                    } else {
                    $callbackText = 'Не определено название шаблона';
                }
                } else {
                $callbackText = 'Вы не являетесь администратором';
            }
            } else {
            $callbackText = 'Вы не авторизованы';
        }
        } else {
        $callbackText = 'Ошибка !';
    }
    
    $callback = ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
    // Выводим заголовки
    $response->withStatus(200);
    $response->withHeader('Content-type', 'application/json');
    // Подменяем заголовки
    $response = $hook->response();
    // Выводим json
    echo json_encode($hook->callback($callback));
    
});

// Удалить шаблон
$app->post($admin_uri.$admin_router.'template-delete', function (Request $request, Response $response, array $args) {
    
    // Подключаем конфиг Settings\Config
    $config = $this->config;
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new $config['vendor']['hooks']['hook']($config);
    $hook->http($request, $response, $args, 'POST', 'admin');
    $request = $hook->request();
    $args = $hook->args();
    
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    
    try {
        // Получаем токен из сессии
        $token = $config['vendor']['crypto']['crypt']::decrypt($session->token_admin, $token_key);
        } catch (\Exception $ex) {
        (new Security())->token($request, $response);
        // Сообщение об Атаке или подборе токена
    }
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
    try {
        // Получаем токен из POST
        $post_csrf = $config['vendor']['crypto']['crypt']::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
        } catch (\Exception $ex) {
        (new Security())->csrf($request, $response);
        // Сообщение об Атаке или подборе csrf
    }
    // Подключаем плагины
    $utility = new Utility();
    // Чистим данные на всякий случай пришедшие через POST
    $csrf = $utility->clean($post_csrf);
    
    $callbackStatus = 400;
    $callbackTitle = 'Соообщение системы';
    $callbackText = '';
    
    // Проверка токена - Если токен не совпадает то ничего не делаем. Можем записать в лог или написать письмо админу
    if ($csrf == $token) {
        
        if (isset($post['dir'])) {
            $dir = filter_var($post['dir'], FILTER_SANITIZE_STRING);
            
            $directory = $config["settings"]["themes"]["dir"].'/'.$config["settings"]["themes"]["templates"].'/'.$dir;
            // Подключаем класс
            $admin = new \ApiShop\Admin\Control();
            // Получаем массив
            $admin->delete($directory);
            
            $callbackStatus = 200;
            
            } else {
            $callbackText = 'Ошибка !';
        }
        } else {
        $callbackText = 'Ошибка !';
    }
    
    $callback = ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
    // Выводим заголовки
    $response->withStatus(200);
    $response->withHeader('Content-type', 'application/json');
    // Подменяем заголовки
    $response = $hook->response();
    // Выводим json
    echo json_encode($hook->callback($callback));
    
});

// Список шаблонов
$app->get($admin_uri.$admin_router.'template', function (Request $request, Response $response, array $args) {
    
    // Получаем конфигурацию
    $config = $this->config;
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new $config['vendor']['hooks']['hook']($config);
    $hook->http($request, $response, $args, 'GET', 'admin');
    $request = $hook->request();
    $args = $hook->args();
    
    // Подключаем плагины
    $utility = new Utility();
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Конфигурация роутинга
    $routers = $config['routers'];
    // Конфигурация шаблона
    $templateConfig = new Template($config['admin']['template']);
    $template = $templateConfig->get();
    // Подключаем мультиязычность
    $languages = new Language($request, $config);
    $language = $languages->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser($config))->get();
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token_admin = $config['vendor']['crypto']['crypt']::encrypt($token, $token_key);
    // Шаблон по умолчанию 404
    $render = $template['layouts']['404'] ? $template['layouts']['404'] : '404.html';
    // Контент по умолчанию
    $content = '';
    
    $post_id = '/_';
    $admin_uri = '/_';
    if(!empty($session->admin_uri)) {
        $admin_uri = '/'.$session->admin_uri;
    }
    if(!empty($session->post_id)) {
        $post_id = '/'.$session->post_id;
    }
    
    // Заголовки по умолчанию из конфигурации
    $title = $language["815"].' - '.$config['settings']['site']['title'];
    $keywords = $config['settings']['site']['keywords'];
    $description = $config['settings']['site']['description'];
    $robots = $config['settings']['site']['robots'];
    $og_title = $config['settings']['site']['og_title'];
    $og_description = $config['settings']['site']['og_description'];
    $og_image = $config['settings']['site']['og_image'];
    $og_type = $config['settings']['site']['og_type'];
    $og_locale = $config['settings']['site']['og_locale'];
    $og_url = $config['settings']['site']['og_url'];
    
    $api = '';
    
    if (isset($session->authorize)) {
        if ($session->role_id == 100) {
            // Подключаем класс
            $templates = new \ApiShop\Admin\Template($config);
            // Получаем массив с настройками шаблона
            $content = $templates->get();
            $api = (new Install($config))->templates_list($config['seller']['store']);
            $render = $template['layouts']['templates'] ? $template['layouts']['templates'] : 'templates.html';
        }
    } else {
        $session->authorize = null;
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
        "config" => $config,
        "language" => $language,
        "template" => $template,
        "token" => $session->token_admin,
        "admin_uri" => $admin_uri,
        "post_id" => $post_id,
        "session" => $sessionUser,
        "content" => $content,
        "editor" => $config['admin']['editor'],
        "api" => $api
    ];
    
    // Передаем данные Hooks для обработки ожидающим классам
    $hook->get($render, $view);
    // Запись в лог
    $this->logger->info($hook->logger());
    // Отдаем данные шаблонизатору
    return $this->admin->render($hook->render(), $hook->view());
    
});

// Страница шаблона
$app->get($admin_uri.$admin_router.'template/{alias:[a-z0-9_-]+}', function (Request $request, Response $response, array $args) {
    
    // Получаем конфигурацию
    $config = $this->config;
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new $config['vendor']['hooks']['hook']($config);
    $hook->http($request, $response, $args, 'GET', 'admin');
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
    // Конфигурация шаблона
    $templateConfig = new Template($config['admin']['template']);
    $template = $templateConfig->get();
    // Подключаем мультиязычность
    $languages = new Language($request, $config);
    $language = $languages->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser($config))->get();
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token_admin = $config['vendor']['crypto']['crypt']::encrypt($token, $token_key);
    // Шаблон по умолчанию 404
    $render = $template['layouts']['404'] ? $template['layouts']['404'] : '404.html';
    // Контент по умолчанию
    $content = '';
    
    $post_id = '/_';
    $admin_uri = '/_';
    if(!empty($session->admin_uri)) {
        $admin_uri = '/'.$session->admin_uri;
    }
    if(!empty($session->post_id)) {
        $post_id = '/'.$session->post_id;
    }
    
    // Заголовки по умолчанию из конфигурации
    $title = $language["709"].' '.$language["814"].' - '.$config['settings']['site']['title'];
    $keywords = $config['settings']['site']['keywords'];
    $description = $config['settings']['site']['description'];
    $robots = $config['settings']['site']['robots'];
    $og_title = $config['settings']['site']['og_title'];
    $og_description = $config['settings']['site']['og_description'];
    $og_image = $config['settings']['site']['og_image'];
    $og_type = $config['settings']['site']['og_type'];
    $og_locale = $config['settings']['site']['og_locale'];
    $og_url = $config['settings']['site']['og_url'];
    
    if (isset($session->authorize) && isset($alias)) {
        if ($session->role_id) {
            // Подключаем класс
            $templates = new \ApiShop\Admin\Template($config, $alias);
            $content = $templates->getOne();
            $render = $template['layouts']['template'] ? $template['layouts']['template'] : 'template.html';
        }
    } else {
        $session->authorize = null;
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
        "config" => $config,
        "language" => $language,
        "template" => $template,
        "token" => $session->token_admin,
        "admin_uri" => $admin_uri,
        "post_id" => $post_id,
        "session" => $sessionUser,
        "content" => $content
    ];
    
    // Передаем данные Hooks для обработки ожидающим классам
    $hook->get($render, $view);
    // Запись в лог
    $this->logger->info($hook->logger());
    // Отдаем данные шаблонизатору
    return $this->admin->render($hook->render(), $hook->view());
    
});

// Редактируем настройки шаблона
$app->post($admin_uri.$admin_router.'template/{alias:[a-z0-9_-]+}', function (Request $request, Response $response, array $args) {
    
    // Получаем конфигурацию
    $config = $this->config;
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new $config['vendor']['hooks']['hook']($config);
    $hook->http($request, $response, $args, 'POST', 'admin');
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
    // Конфигурация шаблона
    $templateConfig = new Template($config['admin']['template']);
    $template = $templateConfig->get();
    // Подключаем мультиязычность
    $languages = new Language($request, $config);
    $language = $languages->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser($config))->get();
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token_admin = $config['vendor']['crypto']['crypt']::encrypt($token, $token_key);
    // Шаблон по умолчанию 404
    $render = $template['layouts']['404'] ? $template['layouts']['404'] : '404.html';
    // Контент по умолчанию
    $content = '';
    
    $post_id = '/_';
    $admin_uri = '/_';
    if(!empty($session->admin_uri)) {
        $admin_uri = '/'.$session->admin_uri;
    }
    if(!empty($session->post_id)) {
        $post_id = '/'.$session->post_id;
    }
    
    // Заголовки по умолчанию из конфигурации
    $title = $language["709"].' '.$language["814"].' - '.$config['settings']['site']['title'];
    $keywords = $config['settings']['site']['keywords'];
    $description = $config['settings']['site']['description'];
    $robots = $config['settings']['site']['robots'];
    $og_title = $config['settings']['site']['og_title'];
    $og_description = $config['settings']['site']['og_description'];
    $og_image = $config['settings']['site']['og_image'];
    $og_type = $config['settings']['site']['og_type'];
    $og_locale = $config['settings']['site']['og_locale'];
    $og_url = $config['settings']['site']['og_url'];
    
    if (isset($session->authorize) && isset($alias)) {
        if ($session->role_id) {
            // Подключаем класс
            $templates = new \ApiShop\Admin\Template($config, $alias);
            // Получаем массив
            $arrJson = $templates->getOne();
            //print_r($content);
            // Массив из POST
            $paramPost = $request->getParsedBody();
            // Соеденяем массивы
            $newArr = array_replace_recursive($arrJson, $paramPost);
            // Сохраняем в файл
            $templates->put($newArr);
            $content = $templates->getOne();
            
            $render = $template['layouts']['template'] ? $template['layouts']['template'] : 'template.html';
            
        }
    } else {
        $session->authorize = null;
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
    "config" => $config,
    "language" => $language,
    "template" => $template,
    "token" => $session->token_admin,
    "admin_uri" => $admin_uri,
    "post_id" => $post_id,
    "session" => $sessionUser,
    "content" => $content
    ];
    
    // Передаем данные Hooks для обработки ожидающим классам
    $hook->get($render, $view);
    // Запись в лог
    $this->logger->info($hook->logger());
    // Отдаем данные шаблонизатору
    return $this->admin->render($hook->render(), $hook->view());
    
});

// Станица пакета
$app->map(['GET', 'POST'], $admin_uri.$admin_router.'package/{vendor:[a-z0-9_-]+}.{package:[a-z0-9_-]+}', function (Request $request, Response $response, array $args) {
    // Получаем конфигурацию
    $config = $this->config;
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new $config['vendor']['hooks']['hook']($config);
    $hook->http($request, $response, $args, 'GET', 'admin');
    $request = $hook->request();
    $args = $hook->args();
    
    // Подключаем плагины
    $utility = new Utility();
    // Получаем vendor из url
    if ($request->getAttribute('vendor')) {
        $vendor = $utility->clean($request->getAttribute('vendor'));
    } else {
        $vendor = null;
    }
    if ($request->getAttribute('package')) {
        $package = $utility->clean($request->getAttribute('package'));
    } else {
        $package = null;
    }
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Конфигурация роутинга
    $routers = $config['routers'];
    // Конфигурация шаблона
    $templateConfig = new Template($config['admin']['template']);
    $template = $templateConfig->get();
    // Подключаем мультиязычность
    $languages = new Language($request, $config);
    $language = $languages->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser($config))->get();
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token_admin = $config['vendor']['crypto']['crypt']::encrypt($token, $token_key);
    // Шаблон по умолчанию 404
    $render = $template['layouts']['404'] ? $template['layouts']['404'] : '404.html';
    // Контент по умолчанию
    $content = '';
 
    $post_id = '/_';
    $admin_uri = '/_';
    if(!empty($session->admin_uri)) {
        $admin_uri = '/'.$session->admin_uri;
    }
    if(!empty($session->post_id)) {
        $post_id = '/'.$session->post_id;
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
 
    if (isset($session->authorize)) {
        if ($session->role_id == 100) {
            // Подключаем класс
            $packages = new Packages($config);
            if ($request->getMethod() == 'POST') {
                $paramPost = $request->getParsedBody();
                $packages->put($paramPost);
            }
            if (isset($vendor) && isset($package)) {
                // Получаем массив
                $content = $packages->getOne($vendor, $package);
            }
            $render = $template['layouts']['package'] ? $template['layouts']['package'] : 'package.html'; 
        }
    } else {
        $session->authorize = null;
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
        "config" => $config,
        "language" => $language,
        "template" => $template,
        "token" => $session->token_admin,
        "admin_uri" => $admin_uri,
        "post_id" => $post_id,
        "session" => $sessionUser,
        "content" => $content
    ];
 
    // Передаем данные Hooks для обработки ожидающим классам
    $hook->get($render, $view);
    // Запись в лог
    $this->logger->info($hook->logger());
    // Отдаем данные шаблонизатору
    return $this->admin->render($hook->render(), $hook->view());
 
});
 
// Изменение статуса пакета
$app->post($admin_uri.$admin_router.'package-{querys:[a-z0-9_-]+}/{vendor:[a-z0-9_-]+}.{package:[a-z0-9_-]+}', function (Request $request, Response $response, array $args) {
    
    // Подключаем конфиг Settings\Config
    $config = $this->config;
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new $config['vendor']['hooks']['hook']($config);
    $hook->http($request, $response, $args, 'POST', 'admin');
    $request = $hook->request();
    $args = $hook->args();
 
    // Подключаем плагины
    $utility = new Utility();
 
    if ($args['querys']) {
        $querys = $utility->clean($args['querys']);
    } else {
        $querys = null;
    }
    if ($args['vendor']) {
        $vendor = $utility->clean($args['vendor']);
    } else {
        $vendor = null;
    }
    if ($args['package']) {
        $package = $utility->clean($args['package']);
    } else {
        $package = null;
    }
    // Получаем данные отправленные нам через POST
    $post = $request->getParsedBody();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    // Читаем ключи
    $token_key = $config['key']['token'];
    
    try {
        // Получаем токен из сессии
        $token = $config['vendor']['crypto']['crypt']::decrypt($session->token_admin, $token_key);
        } catch (\Exception $ex) {
        $token = 0;
        if (isset($session->authorize)) {
            if ($session->authorize != 1 || $session->role_id != 100) {
                // Сообщение об Атаке или подборе токена
                (new Security())->token($request, $response);
            }
            } else {
            // Сообщение об Атаке или подборе токена
            (new Security())->token($request, $response);
        }
    }
    try {
        // Получаем токен из POST
        $post_csrf = $config['vendor']['crypto']['crypt']::decrypt(filter_var($post['csrf'], FILTER_SANITIZE_STRING), $token_key);
        // Чистим данные на всякий случай пришедшие через POST
        $csrf = $utility->clean($post_csrf);
        } catch (\Exception $ex) {
        $csrf = 1;
        if (isset($session->authorize)) {
            if ($session->authorize != 1 || $session->role_id != 100) {
                // Сообщение об Атаке или подборе csrf
                (new Security())->csrf($request, $response);
            }
            } else {
            // Сообщение об Атаке или подборе csrf
            (new Security())->csrf($request, $response);
        }
    }
    
    $callbackStatus = 400;
    $callbackTitle = 'Соообщение системы';
    $callbackText = '';
    
    // Проверка токена - Если токен не совпадает то ничего не делаем. Можем записать в лог или написать письмо админу
    if ($csrf == $token) {
        if (isset($session->authorize)) {
            if ($session->authorize == 1 && $session->role_id == 100) {
                if (isset($vendor) && isset($package) && isset($querys)) {
                    // Подключаем класс
                    $packages = new Packages($config);
                    if($querys == 'delete') {
                        $content = $packages->del($vendor, $package);
                    } elseif($querys == 'activate'){
                        $state = '1';
                        $content = $packages->state($vendor, $package, $state);
                    } else {
                        $state = '0';
                        $content = $packages->state($vendor, $package, $state);
                    }
                    
                    if($content == true){
                        $callbackStatus = 200;
                    } else {
                        $callbackText = 'Ошибка !';
                    }
                } else {
                    $callbackText = 'Ошибка !';
                }
            } else {
                $callbackText = 'Вы не администратор';
            }
        } else {
            $callbackText = 'Вы не авторизованы';
        }
    } else {
        $callbackText = 'Обновите страницу';
    }
    
    $callback = ['status' => $callbackStatus, 'title' => $callbackTitle, 'text' => $callbackText];
    // Выводим заголовки
    $response->withStatus(200);
    $response->withHeader('Content-type', 'application/json');
    // Подменяем заголовки
    $response = $hook->response();
    // Выводим json
    echo json_encode($hook->callback($callback));
    
});

// Список пакетов
$app->get($admin_uri.$admin_router.'packages', function (Request $request, Response $response, array $args) {
    
    // Получаем конфигурацию
    $config = $this->config;
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new $config['vendor']['hooks']['hook']($config);
    $hook->http($request, $response, $args, 'GET', 'admin');
    $request = $hook->request();
    $args = $hook->args();
    
    // Подключаем плагины
    $utility = new Utility();
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Конфигурация роутинга
    $routers = $config['routers'];
    // Конфигурация шаблона
    $templateConfig = new Template($config['admin']['template']);
    $template = $templateConfig->get();
    // Подключаем мультиязычность
    $languages = new Language($request, $config);
    $language = $languages->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser($config))->get();
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token_admin = $config['vendor']['crypto']['crypt']::encrypt($token, $token_key);
    // Шаблон по умолчанию 404
    $render = $template['layouts']['404'] ? $template['layouts']['404'] : '404.html';
    // Контент по умолчанию
    $content = '';
    
    $post_id = '/_';
    $admin_uri = '/_';
    if(!empty($session->admin_uri)) {
        $admin_uri = '/'.$session->admin_uri;
    }
    if(!empty($session->post_id)) {
        $post_id = '/'.$session->post_id;
    }
    
    // Заголовки по умолчанию из конфигурации
    $title = $language["709"].' '.$language["814"].' - '.$config['settings']['site']['title'];
    $keywords = $config['settings']['site']['keywords'];
    $description = $config['settings']['site']['description'];
    $robots = $config['settings']['site']['robots'];
    $og_title = $config['settings']['site']['og_title'];
    $og_description = $config['settings']['site']['og_description'];
    $og_image = $config['settings']['site']['og_image'];
    $og_type = $config['settings']['site']['og_type'];
    $og_locale = $config['settings']['site']['og_locale'];
    $og_url = $config['settings']['site']['og_url'];
    
    if (isset($session->authorize)) {
        if ($session->role_id == 100) {
            // Подключаем класс
            $packages = new Packages($config);
            // Получаем массив
            $content = $packages->get();
            $render = $template['layouts']['packages'] ? $template['layouts']['packages'] : 'packages.html';
            
        }
        } else {
        $session->authorize = null;
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
    "config" => $config,
    "language" => $language,
    "template" => $template,
    "token" => $session->token_admin,
    "admin_uri" => $admin_uri,
    "post_id" => $post_id,
    "session" => $sessionUser,
    "content" => $content
    ];
    
    // Передаем данные Hooks для обработки ожидающим классам
    $hook->get($render, $view);
    // Запись в лог
    $this->logger->info($hook->logger());
    // Отдаем данные шаблонизатору
    return $this->admin->render($hook->render(), $hook->view());
    
});

// Репозиторий
$app->get($admin_uri.$admin_router.'packages-install', function (Request $request, Response $response, array $args) {
    
    // Получаем конфигурацию
    $config = $this->config;
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new $config['vendor']['hooks']['hook']($config);
    $hook->http($request, $response, $args, 'GET', 'admin');
    $request = $hook->request();
    $args = $hook->args();
    
    // Подключаем плагины
    $utility = new Utility();
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Конфигурация роутинга
    $routers = $config['routers'];
    // Конфигурация шаблона
    $templateConfig = new Template($config['admin']['template']);
    $template = $templateConfig->get();
    // Подключаем мультиязычность
    $languages = new Language($request, $config);
    $language = $languages->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser($config))->get();
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token_admin = $config['vendor']['crypto']['crypt']::encrypt($token, $token_key);
    // Шаблон по умолчанию 404
    $render = $template['layouts']['404'] ? $template['layouts']['404'] : '404.html';
    // Контент по умолчанию
    $content = '';
    
    $post_id = '/_';
    $admin_uri = '/_';
    if(!empty($session->admin_uri)) {
        $admin_uri = '/'.$session->admin_uri;
    }
    if(!empty($session->post_id)) {
        $post_id = '/'.$session->post_id;
    }
    
    // Заголовки по умолчанию из конфигурации
    $title = $language["709"].' '.$language["814"].' - '.$config['settings']['site']['title'];
    $keywords = $config['settings']['site']['keywords'];
    $description = $config['settings']['site']['description'];
    $robots = $config['settings']['site']['robots'];
    $og_title = $config['settings']['site']['og_title'];
    $og_description = $config['settings']['site']['og_description'];
    $og_image = $config['settings']['site']['og_image'];
    $og_type = $config['settings']['site']['og_type'];
    $og_locale = $config['settings']['site']['og_locale'];
    $og_url = $config['settings']['site']['og_url'];
    
    if (isset($session->authorize)) {
        if ($session->role_id == 100) {
            // Подключаем класс
            $packages = new Packages($config);
            // Получаем массив
            $content = $packages->get();
            $render = $template['layouts']['packages'] ? $template['layouts']['packages'] : 'packages.html';
            
        }
        } else {
        $session->authorize = null;
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
    "config" => $config,
    "language" => $language,
    "template" => $template,
    "token" => $session->token_admin,
    "admin_uri" => $admin_uri,
    "post_id" => $post_id,
    "session" => $sessionUser,
    "content" => $content
    ];
    
    // Передаем данные Hooks для обработки ожидающим классам
    $hook->get($render, $view);
    // Запись в лог
    $this->logger->info($hook->logger());
    // Отдаем данные шаблонизатору
    return $this->admin->render($hook->render(), $hook->view());
    
});

// Страница установки из json файла
$app->get($admin_uri.$admin_router.'packages-install-json', function (Request $request, Response $response, array $args) {
    
    // Получаем конфигурацию
    $config = $this->config;
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new $config['vendor']['hooks']['hook']($config);
    $hook->http($request, $response, $args, 'GET', 'admin');
    $request = $hook->request();
    $args = $hook->args();
    
    // Подключаем плагины
    $utility = new Utility();
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Конфигурация роутинга
    $routers = $config['routers'];
    // Конфигурация шаблона
    $templateConfig = new Template($config['admin']['template']);
    $template = $templateConfig->get();
    // Подключаем мультиязычность
    $languages = new Language($request, $config);
    $language = $languages->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser($config))->get();
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token_admin = $config['vendor']['crypto']['crypt']::encrypt($token, $token_key);
    // Шаблон по умолчанию 404
    $render = $template['layouts']['404'] ? $template['layouts']['404'] : '404.html';
    // Контент по умолчанию
    $content = '';
    
    $post_id = '/_';
    $admin_uri = '/_';
    if(!empty($session->admin_uri)) {
        $admin_uri = '/'.$session->admin_uri;
    }
    if(!empty($session->post_id)) {
        $post_id = '/'.$session->post_id;
    }
    
    // Заголовки по умолчанию из конфигурации
    $title = $language["709"].' '.$language["814"].' - '.$config['settings']['site']['title'];
    $keywords = $config['settings']['site']['keywords'];
    $description = $config['settings']['site']['description'];
    $robots = $config['settings']['site']['robots'];
    $og_title = $config['settings']['site']['og_title'];
    $og_description = $config['settings']['site']['og_description'];
    $og_image = $config['settings']['site']['og_image'];
    $og_type = $config['settings']['site']['og_type'];
    $og_locale = $config['settings']['site']['og_locale'];
    $og_url = $config['settings']['site']['og_url'];
    
    if (isset($session->authorize)) {
        if ($session->role_id == 100) {
            // Подключаем класс
            $packages = new Packages($config);
            // Получаем массив
            $content = $packages->get();
            $render = $template['layouts']['packages'] ? $template['layouts']['packages'] : 'packages.html';
            
        }
        } else {
        $session->authorize = null;
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
    "config" => $config,
    "language" => $language,
    "template" => $template,
    "token" => $session->token_admin,
    "admin_uri" => $admin_uri,
    "post_id" => $post_id,
    "session" => $sessionUser,
    "content" => $content
    ];
    
    // Передаем данные Hooks для обработки ожидающим классам
    $hook->get($render, $view);
    // Запись в лог
    $this->logger->info($hook->logger());
    // Отдаем данные шаблонизатору
    return $this->admin->render($hook->render(), $hook->view());
    
});

// Глобальные настройки
$app->get($admin_uri.$admin_router.'config', function (Request $request, Response $response, array $args) {
    
    // Получаем конфигурацию
    $config = $this->config;
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new $config['vendor']['hooks']['hook']($config);
    $hook->http($request, $response, $args, 'GET', 'admin');
    $request = $hook->request();
    $args = $hook->args();
    
    // Подключаем плагины
    $utility = new Utility();
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Конфигурация роутинга
    $routers = $config['routers'];
    // Конфигурация шаблона
    $templateConfig = new Template($config['admin']['template']);
    $template = $templateConfig->get();
    // Подключаем мультиязычность
    $languages = new Language($request, $config);
    $language = $languages->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser($config))->get();
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token_admin = $config['vendor']['crypto']['crypt']::encrypt($token, $token_key);
    // Шаблон по умолчанию 404
    $render = $template['layouts']['404'] ? $template['layouts']['404'] : '404.html';
    // Контент по умолчанию
    $content = '';
    
    $post_id = '/_';
    $admin_uri = '/_';
    if(!empty($session->admin_uri)) {
        $admin_uri = '/'.$session->admin_uri;
    }
    if(!empty($session->post_id)) {
        $post_id = '/'.$session->post_id;
    }
    
    // Заголовки по умолчанию из конфигурации
    $title = $language["709"].' '.$language["814"].' - '.$config['settings']['site']['title'];
    $keywords = $config['settings']['site']['keywords'];
    $description = $config['settings']['site']['description'];
    $robots = $config['settings']['site']['robots'];
    $og_title = $config['settings']['site']['og_title'];
    $og_description = $config['settings']['site']['og_description'];
    $og_image = $config['settings']['site']['og_image'];
    $og_type = $config['settings']['site']['og_type'];
    $og_locale = $config['settings']['site']['og_locale'];
    $og_url = $config['settings']['site']['og_url'];
    
    if (isset($session->authorize)) {
        if ($session->role_id == 100) {
            // Подключаем класс
            $settings = new \ApiShop\Admin\Config($config);
            // Получаем массив с настройками шаблона
            $content = $settings->get();
            $render = $template['layouts']['config'] ? $template['layouts']['config'] : 'config.html';
        }
        } else {
        $session->authorize = null;
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
        "config" => $config,
        "language" => $language,
        "template" => $template,
        "token" => $session->token_admin,
        "admin_uri" => $admin_uri,
        "post_id" => $post_id,
        "session" => $sessionUser,
        "content" => $content,
        "type" => "edit",
        "editor" => $config['admin']['editor']
    ];
    
    // Передаем данные Hooks для обработки ожидающим классам
    $hook->get($render, $view);
    // Запись в лог
    $this->logger->info($hook->logger());
    // Отдаем данные шаблонизатору
    return $this->admin->render($hook->render(), $hook->view());
    
});

// Редактируем глобальные настройки
$app->post($admin_uri.$admin_router.'config', function (Request $request, Response $response, array $args) {
    
    // Получаем конфигурацию
    $config = $this->config;
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new $config['vendor']['hooks']['hook']($config);
    $hook->http($request, $response, $args, 'POST', 'admin');
    $request = $hook->request();
    $args = $hook->args();
    
    // Подключаем плагины
    $utility = new Utility();
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Конфигурация роутинга
    $routers = $config['routers'];
    // Конфигурация шаблона
    $templateConfig = new Template($config['admin']['template']);
    $template = $templateConfig->get();
    // Подключаем мультиязычность
    $languages = new Language($request, $config);
    $language = $languages->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser($config))->get();
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token_admin = $config['vendor']['crypto']['crypt']::encrypt($token, $token_key);
    // Шаблон по умолчанию 404
    $render = $template['layouts']['404'] ? $template['layouts']['404'] : '404.html';
    // Контент по умолчанию
    $content = '';
    
    $post_id = '/_';
    $admin_uri = '/_';
    if(!empty($session->admin_uri)) {
        $admin_uri = '/'.$session->admin_uri;
    }
    if(!empty($session->post_id)) {
        $post_id = '/'.$session->post_id;
    }
    
    // Заголовки по умолчанию из конфигурации
    $title = $language["709"].' '.$language["814"].' - '.$config['settings']['site']['title'];
    $keywords = $config['settings']['site']['keywords'];
    $description = $config['settings']['site']['description'];
    $robots = $config['settings']['site']['robots'];
    $og_title = $config['settings']['site']['og_title'];
    $og_description = $config['settings']['site']['og_description'];
    $og_image = $config['settings']['site']['og_image'];
    $og_type = $config['settings']['site']['og_type'];
    $og_locale = $config['settings']['site']['og_locale'];
    $og_url = $config['settings']['site']['og_url'];
    
    if (isset($session->authorize)) {
        if ($session->role_id == 100) {
            // Подключаем класс
            $settings = new \ApiShop\Admin\Config($config);
            // Массив из POST
            $paramPost = $request->getParsedBody();
            // Сохраняем в файл
            $settings->put($paramPost);
            // Получаем обновленные данные
            $content = $settings->get();
            
            $render = $template['layouts']['config'] ? $template['layouts']['config'] : 'config.html';
        }
        } else {
        $session->authorize = null;
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
        "config" => $config,
        "language" => $language,
        "template" => $template,
        "token" => $session->token_admin,
        "admin_uri" => $admin_uri,
        "post_id" => $post_id,
        "session" => $sessionUser,
        "content" => $content,
        "type" => "edit",
        "editor" => $config['admin']['editor']
    ];
    
    // Передаем данные Hooks для обработки ожидающим классам
    $hook->get($render, $view);
    // Запись в лог
    $this->logger->info($hook->logger());
    // Отдаем данные шаблонизатору
    return $this->admin->render($hook->render(), $hook->view());
    
});

// Список баз данных
$app->get($admin_uri.$admin_router.'db', function (Request $request, Response $response, array $args) {
    
    // Получаем конфигурацию
    $config = $this->config;
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new $config['vendor']['hooks']['hook']($config);
    $hook->http($request, $response, $args, 'GET', 'admin');
    $request = $hook->request();
    $args = $hook->args();
    
    // Подключаем плагины
    $utility = new Utility();
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Конфигурация роутинга
    $routers = $config['routers'];
    // Конфигурация шаблона
    $templateConfig = new Template($config['admin']['template']);
    $template = $templateConfig->get();
    // Подключаем мультиязычность
    $languages = new Language($request, $config);
    $language = $languages->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser($config))->get();
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token_admin = $config['vendor']['crypto']['crypt']::encrypt($token, $token_key);
    // Шаблон по умолчанию 404
    $render = $template['layouts']['404'] ? $template['layouts']['404'] : '404.html';
    // Контент по умолчанию
    $content = '';
    
    $post_id = '/_';
    $admin_uri = '/_';
    if(!empty($session->admin_uri)) {
        $admin_uri = '/'.$session->admin_uri;
    }
    if(!empty($session->post_id)) {
        $post_id = '/'.$session->post_id;
    }
    
    // Заголовки по умолчанию из конфигурации
    $title = $language["814"].' - '.$config['settings']['site']['title'];
    $keywords = $config['settings']['site']['keywords'];
    $description = $config['settings']['site']['description'];
    $robots = $config['settings']['site']['robots'];
    $og_title = $config['settings']['site']['og_title'];
    $og_description = $config['settings']['site']['og_description'];
    $og_image = $config['settings']['site']['og_image'];
    $og_type = $config['settings']['site']['og_type'];
    $og_locale = $config['settings']['site']['og_locale'];
    $og_url = $config['settings']['site']['og_url'];
    
    if (isset($session->authorize)) {
        if ($session->role_id) {
            $adminDatabase = new AdminDatabase();
            $content = $adminDatabase->list();
            $render = $template['layouts']['db'] ? $template['layouts']['db'] : 'db.html';
        }
        } else {
        $session->authorize = null;
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
    "config" => $config,
    "language" => $language,
    "template" => $template,
    "token" => $session->token_admin,
    "admin_uri" => $admin_uri,
    "post_id" => $post_id,
    "session" => $sessionUser,
    "content" => $content
    ];
    
    // Передаем данные Hooks для обработки ожидающим классам
    $hook->get($render, $view);
    // Запись в лог
    $this->logger->info($hook->logger());
    // Отдаем данные шаблонизатору
    return $this->admin->render($hook->render(), $hook->view());
    
});

// Страница таблицы (ресурса)
$app->get($admin_uri.$admin_router.'db/{resource:[a-z0-9_-]+}[/{id:[0-9_]+}]', function (Request $request, Response $response, array $args) {
    
    // Получаем конфигурацию
    $config = $this->config;
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new $config['vendor']['hooks']['hook']($config);
    $hook->http($request, $response, $args, 'GET', 'admin');
    $request = $hook->request();
    $args = $hook->args();
    
    // Подключаем плагины
    $utility = new Utility();
    // Получаем resource из url
    if ($request->getAttribute('resource')) {
        $resource = $utility->clean($request->getAttribute('resource'));
        } else {
        $resource = null;
    }
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Конфигурация роутинга
    $routers = $config['routers'];
    // Конфигурация шаблона
    $templateConfig = new Template($config['admin']['template']);
    $template = $templateConfig->get();
    // Подключаем мультиязычность
    $languages = new Language($request, $config);
    $language = $languages->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser($config))->get();
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token_admin = $config['vendor']['crypto']['crypt']::encrypt($token, $token_key);
    // Шаблон по умолчанию 404
    $render = $template['layouts']['404'] ? $template['layouts']['404'] : '404.html';
    // Контент по умолчанию
    $content = '';
    
    $post_id = '/_';
    $admin_uri = '/_';
    if(!empty($session->admin_uri)) {
        $admin_uri = '/'.$session->admin_uri;
    }
    if(!empty($session->post_id)) {
        $post_id = '/'.$session->post_id;
    }
    
    // Заголовки по умолчанию из конфигурации
    $title = $language["814"].' - '.$config['settings']['site']['title'];
    $keywords = $config['settings']['site']['keywords'];
    $description = $config['settings']['site']['description'];
    $robots = $config['settings']['site']['robots'];
    $og_title = $config['settings']['site']['og_title'];
    $og_description = $config['settings']['site']['og_description'];
    $og_image = $config['settings']['site']['og_image'];
    $og_type = $config['settings']['site']['og_type'];
    $og_locale = $config['settings']['site']['og_locale'];
    $og_url = $config['settings']['site']['og_url'];
    
    if (isset($id)) {
        $render = $template['layouts']['db_id'] ? $template['layouts']['db_id'] : 'db_id.html';
        } else {
        $render = $template['layouts']['db_item'] ? $template['layouts']['db_item'] : 'db_item.html';
    }
    
    $name_db = null;
    
    if (isset($session->authorize) && isset($resource)) {
        if ($session->role_id) {
            
            // Получаем массив параметров uri
            $queryParams = $request->getQueryParams();
            $arr = [];
            $arr['state'] = 1;
            $arr['offset'] = 0;
            $arr['limit'] = 30;
            $arr['order'] = "ASC";
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
            
            $resourceDd = $adminDatabase->getOne($resource);
            
            $arrs["id"] = "id";
            $resourceArray = $arrs + $resourceDd; 
            
            $content_key = array_keys($resourceArray);
            
            //print_r($content_key);
            
            foreach($resourceArray as $key => $value)
            {
                $sortArr[$key] = $key;
            }
            
            $sortArray = $filter->sort($sortArr);
            
            // Отдаем роутеру RouterDb конфигурацию.
            $router = new Router($config);
            // Получаем название базы для указанного ресурса
            $name_db = $router->ping($resource);
            // Подключаемся к базе
            $db = new Db($name_db, $config);
            // Отправляем запрос и получаем данные
            $resp = $db->get($resource);
            
            $count = 0;
            if (isset($resp["response"]['total'])) {
                $count = $resp["response"]['total'];
            }
            $paginator = $filter->paginator($count);
            // Если ответ не пустой
            if (count($resp["body"]['items']) >= 1) {
                $content = '';
                // Отдаем пагинатору колличество
                foreach($resp["body"]['items'] as $item)
                {
                    foreach($item["item"] as $key => $value)
                    {
                        if ($value == ''){$value = "--";}
                        $contentArr[$key] = $utility->clean($value);
                    }
                    $content["items"][] = $contentArr;
                }
                } else {
                $content = null;
            }
            } else {
            $render = "404";
        }
        } else {
        $session->authorize = null;
        $render = "404";
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
        "config" => $config,
        "language" => $language,
        "template" => $template,
        "token" => $session->token_admin,
        "admin_uri" => $admin_uri,
        "post_id" => $post_id,
        "session" => $sessionUser,
        "content" => $content,
        "content_key" => $content_key,
        "paginator" => $paginator,
        "order" => $orderArray,
        "sort" => $sortArray,
        "limit" => $limitArray,
        "param" => $arr,
        "total" => $count,
        "url_param" => $get_array,
        "url" => $url_path
    ];
    
    // Передаем данные Hooks для обработки ожидающим классам
    $hook->get($render, $view);
    // Запись в лог
    $this->logger->info($hook->logger());
    // Отдаем данные шаблонизатору
    return $this->admin->render($hook->render(), $hook->view());
    
});

// Глобально
$app->get($admin_uri.$admin_router.'_{resource:[a-z0-9_-]+}[/{id:[a-z0-9_]+}]', function (Request $request, Response $response, array $args) {
    
    // Получаем конфигурацию
    $config = $this->config;
    // Передаем данные Hooks для обработки ожидающим классам
    $hook = new $config['vendor']['hooks']['hook']($config);
    $hook->http($request, $response, $args, 'GET', 'admin');
    $request = $hook->request();
    $args = $hook->args();
    
    // Подключаем плагины
    $utility = new Utility();
    // Получаем resource из url
    if ($request->getAttribute('resource')) {
        $resource = $utility->clean($request->getAttribute('resource'));
        } else {
        $resource = null;
    }
    // Получаем id из url
    if ($request->getAttribute('id')) {
        $id = $utility->clean($request->getAttribute('id'));
        } else {
        $id = null;
    }
    // Получаем параметры из URL
    $getParams = $request->getQueryParams();
    $host = $request->getUri()->getHost();
    $path = $request->getUri()->getPath();
    // Конфигурация роутинга
    $routers = $config['routers'];
    // Конфигурация шаблона
    $templateConfig = new Template($config['admin']['template']);
    $template = $templateConfig->get();
    // Подключаем мультиязычность
    $languages = new Language($request, $config);
    $language = $languages->get();
    // Подключаем сессию, берет название класса из конфигурации
    $session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
    // Данные пользователя из сессии
    $sessionUser =(new SessionUser($config))->get();
    // Читаем ключи
    $token_key = $config['key']['token'];
    // Генерируем токен
    $token = $utility->random_token();
    // Записываем токен в сессию
    $session->token_admin = $config['vendor']['crypto']['crypt']::encrypt($token, $token_key);
    // Шаблон по умолчанию 404
    $render = $template['layouts']['404'] ? $template['layouts']['404'] : '404.html';
    // Контент по умолчанию
    $content = '';
    
    $post_id = '/_';
    $admin_uri = '/_';
    if(!empty($session->admin_uri)) {
        $admin_uri = '/'.$session->admin_uri;
    }
    if(!empty($session->post_id)) {
        $post_id = '/'.$session->post_id;
    }
    
    // Заголовки по умолчанию из конфигурации
    $title = $language["814"].' - '.$config['settings']['site']['title'];
    $keywords = $config['settings']['site']['keywords'];
    $description = $config['settings']['site']['description'];
    $robots = $config['settings']['site']['robots'];
    $og_title = $config['settings']['site']['og_title'];
    $og_description = $config['settings']['site']['og_description'];
    $og_image = $config['settings']['site']['og_image'];
    $og_type = $config['settings']['site']['og_type'];
    $og_locale = $config['settings']['site']['og_locale'];
    $og_url = $config['settings']['site']['og_url'];
    
    $control = new Control();
    $test = $control->test($resource);
    if ($test === true) {
        
        $site = new Site($config);
        $site_config = $site->get();
        $site_template = $site->template();
        
        $param = $request->getQueryParams();
        
        if (isset($session->authorize)) {
            if ($session->role_id == 100) {
                
                $render = $template['layouts'][$resource] ? $template['layouts'][$resource] : $resource.'.html';
                
                if(stristr($resource, '_') === FALSE) {
                    $resourceName = "\\ApiShop\\Admin\\".ucfirst($resource);
                    } else {
                    $resourceNew = (str_replace(" ", "", ucwords(str_replace("_", " ", $resource))));
                    $resourceName = "\\ApiShop\\Admin\\".$resourceNew;
                }
                // Подключаем класс
                $resourceClass = new $resourceName($site_template);
                // Отправляем запрос
                $get = $resourceClass->get($resource, $param, $id);
                
                if ($resource == "settings") {
                    $content = $get;
                    } else {
                    $content = $get["body"]["items"];
                }
            }
            } else {
            $session->authorize = null;
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
    "config" => $config,
    "language" => $language,
    "template" => $template,
    "token" => $session->token_admin,
    "admin_uri" => $admin_uri,
    "post_id" => $post_id,
    "session" => $sessionUser,
    "content" => $content
    ];
    
    // Передаем данные Hooks для обработки ожидающим классам
    $hook->get($render, $view);
    // Запись в лог
    $this->logger->info($hook->logger());
    // Отдаем данные шаблонизатору
    return $this->admin->render($hook->render(), $hook->view());
    
});
 