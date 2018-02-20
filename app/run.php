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
 
use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};
 
/**
    * API Shop дает полную свободу с выбора классов обработки страниц
    * При установке пекетов или шаблонов вы можете перезаписать в конфиге класс и функцию обработки
    * Вы можете использовать контроллеры по умолчанию и вносить изменения с помощью \Pllano\Hooks\Hook
    * Вы можете использовать ApiShop\Adapter\ и менять vendor в конфигурации
*/
 
$container = $app->getContainer();
 
// Подключаем файл конфигурации системы
require __DIR__ . '/settings.php';
// Получаем конфигурацию
 
// Конфигурация доступна внутри и вне роутеров
// Получить внутри роутера $name = $this->config['name']; всю = $this->config
// Получить вне роутеров $name = $config['name']; всю = $config
$container['config'] = \ApiShop\Config\Settings::get();
$config = $container['config'];
 
// Создаем контейнер с конфигурацией пакетов
$container['package'] = $package;
 
// Для POST запросов вначале url генерируем post_id
// Если у пользователя нет сессии он не сможет отправлять POST запросы
$session = new $config['vendor']['session']['session']($config['settings']['session']['name']);
$post_id = '/_'; if(isset($session->post_id)){$post_id = '/'.$session->post_id;}
 
// Получаем конфигурацию роутеров
$router = $config['routers']['site'];
 
// Run User Session
// Запускаем сессию пользователя
(new \ApiShop\Model\User())->run();
 
// Если одина из баз json запускаем jsonDB
if ($config["db"]["master"] == "json" || $config["db"]["slave"] == "json") {
    // Запускаем jsonDB\Db
    $jsonDb = new \jsonDB\Db($config['db']['json']['dir']);
    $jsonDb->run();
}
 
// monolog
$container['logger'] = function ($c)
{
    $settings = $c['config']['settings']["logger"];
    $logger = new \Monolog\Logger($settings['name']);
    $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
    
};
 
// Register \Pllano\Adapter\TemplateEngine
$container['view'] = function ($apishop)
{
	$view = '';
	if ($apishop['config']['settings']["install"]["status"] != null) {
        // Получаем название шаблона из конфигурации
        $template = $apishop['config']['template']['front_end']['themes']["template"]; // По умолчанию mini-mo
        $site = new \ApiShop\Model\Site($apishop['config']);
        $site->get();
        // Получаем название шаблона из конфигурации сайта
        if ($site->template()) {$template = $site->template();}
        $view = new $apishop['config']['vendor']['templates']['template_engine']($apishop['config'], $template);
	} else {
        $loader = new \Twig_Loader_Filesystem($apishop['config']['settings']['themes']['front_end_dir']."/".$apishop['config']['template']['front_end']['themes']['templates']."/install");
        $view = new \Twig_Environment($loader, ['cache' => false, 'strict_variables' => false]);
    }
	return $view;
};
 
// Register Original Twig View Admin Panel
$container['admin'] = function ($config)
{
    // Получаем название шаблона
    $template = $config['config']['admin']['template'];
    $loader = new \Twig_Loader_Filesystem($config['config']['settings']['themes']['dir']."/".$config['config']['settings']['themes']['templates']."/".$template."/layouts");
    $admin = new \Twig_Environment($loader, ['cache' => false, 'strict_variables' => false]);
    return $admin;
};
 
// GET - Главная
$app->get($router['index']['route'], function (Request $req, Response $res, $args = []) {
    $controller = $this->config['vendor']['controllers']['controller'];
    $function = strtolower($req->getMethod());
    $class = new $controller($req->getMethod(), 'index', $this->config, $this->package, $this->view, $this->logger);
    return $class->$function($req, $res, $args);
}); 
 
// GET - Статьи
$app->get($router['article']['route'], function (Request $req, Response $res, $args = []) {
    $controller = $this->config['vendor']['controllers']['controller'];
    $function = strtolower($req->getMethod());
    $class = new $controller($req->getMethod(), 'article', $this->config, $this->package, $this->view, $this->logger);
    return $class->$function($req, $res, $args);
});
 
// GET - Категории статей
$app->get($router['article_category']['route'], function (Request $req, Response $res, $args = []) {
    $controller = $this->config['vendor']['controllers']['controller'];
    $function = strtolower($req->getMethod());
    $class = new $controller($req->getMethod(), 'article_category', $this->config, $this->package, $this->view, $this->logger);
    return $class->$function($req, $res, $args);
}); 
 
// GET - Категории товаров
$app->get($router['category']['route'], function (Request $req, Response $res, $args = []) {
    $controller = $this->config['vendor']['controllers']['controller'];
    $function = strtolower($req->getMethod());
    $class = new $controller($req->getMethod(), 'category', $this->config, $this->package, $this->view, $this->logger);
    return $class->$function($req, $res, $args);
});
 
// GET - Страница товара
$app->get($router['product']['route'], function (Request $req, Response $res, $args = []) {
    $controller = $this->config['vendor']['controllers']['controller'];
    $function = strtolower($req->getMethod());
    $class = new $controller($req->getMethod(), 'product', $this->config, $this->package, $this->view, $this->logger);
    return $class->$function($req, $res, $args);
});
 
// GET - Страница товара quick_view
$app->get($router['quick_view']['route'], function (Request $req, Response $res, $args = []) {
    $controller = $this->config['vendor']['controllers']['controller'];
    $function = strtolower($req->getMethod());
    $class = new $controller($req->getMethod(), 'quick_view', $this->config, $this->package, $this->view, $this->logger);
    return $class->$function($req, $res, $args);
});
 
// GET - Получить локализацию
$app->map(['GET', 'POST'], $post_id.$router['language']['route'], function (Request $req, Response $res, $args = []) {
    $router = $this->config['routers']['site']['language'];
    $controller = $router['controller'];
    $function = strtolower($req->getMethod());
    $class = new $controller($req->getMethod(), 'language', $this->config, $this->package, $this->view, $this->logger);
    return $class->$function($req, $res, $args);
});
 
// GET - запросы к корзине
$app->map(['GET', 'POST'], $router['cart']['route'].'{function:[a-z0-9_-]+}', function (Request $req, Response $res, $args = []) {
    $router = $this->config['routers']['site']['cart'];
    $controller = $router['controller'];
    if ($req->getAttribute('function') && method_exists($controller,'get_'.str_replace("-", "_", $req->getAttribute('function')))) {
        $function = 'get_'.str_replace("-", "_", $req->getAttribute('function'));
    } else {
        $controller = $this->config['routers']['site']['error']['controller'];
        $function = strtolower($req->getMethod());
    }
    $class = new $controller($req->getMethod(), 'cart', $this->config, $this->package, $this->view, $this->logger);
    return $class->$function($req, $res, $args);
});
 
// GET - Страница авторизации. Войти в систему
$app->get($router['sign_in']['route'], function (Request $req, Response $res, $args = []) {
    $controller = $this->config['vendor']['controllers']['controller'];
    $function = strtolower($req->getMethod());
    $class = new $controller($req->getMethod(), 'sign_in', $this->config, $this->package, $this->view, $this->logger);
    return $class->$function($req, $res, $args);
});

// GET - Страница регистрации. Зарегистрироваться
$app->get($router['sign_up']['route'], function (Request $req, Response $res, $args = []) {
    $controller = $this->config['vendor']['controllers']['controller'];
    $function = strtolower($req->getMethod());
    $class = new $controller($req->getMethod(), 'sign_up', $this->config, $this->package, $this->view, $this->logger);
    return $class->$function($req, $res, $args);
});
 
// POST - Авторизоваться
$app->map(['GET', 'POST'], $post_id.$router['login']['route'], function (Request $req, Response $res, $args = []) {
    $controller = $this->config['vendor']['controllers']['controller'];
    $class = new $controller($req->getMethod(), 'login', $this->config, $this->package, $this->view, $this->logger);
    return $class->post($req, $res, $args);
});
 
// POST - Зарегистрироваться
$app->post($post_id.$router['check_in']['route'], function (Request $req, Response $res, $args = []) {
    $router = $this->config['routers']['site']['check_in'];
    $controller = $router['controller'];
    $class = new $controller($req->getMethod(), 'check_in', $this->config, $this->package, $this->view, $this->logger);
    return $class->check_in($req, $res, $args);
});
 
// POST - Выйти
$app->post($post_id.$router['logout']['route'], function (Request $req, Response $res, $args = []) {
    $controller = $this->config['vendor']['controllers']['controller'];
    $class = new $controller($req->getMethod(), 'logout', $this->config, $this->package, $this->view, $this->logger);
    return $class->post($req, $res, $args);
});

// Automatically register routers
// Автоматическое подключение роутеров
$routers = glob(__DIR__ . '/routers/*.php');
foreach ($routers as $router) {
    require $router;
}
 
// Если решить эту задачку получим крутое подключение роутринга
// Сейчас подключает но только один роутер
// Думаю стоит взять юрл и по регулярке сравнить с конфигом если совпало выполнить
// Возможно просто не вижу очевидного :) буду очень благодарен за дельные советы
/* 
foreach ($router as $key => $val)
{
    if($key == 'index' || $key == '_article') {
 
        $rep = $container->get('settings');
        $rep->replace(['keys' => $key]);
        
        $return = '';
 
        $app->get($val['route'], function (Request $req, Response $res, $args = []) {
 
            print("<br>{$this->settings['keys']}<br>");
 
            $router = $this->config['routers']['site'][$this->settings['keys']];
            
            $controller = $router['controller'];
 
            print("<br>{$router['controller']}<br>");
 
            $function = strtolower($req->getMethod());
            $class = new $controller($req->getMethod(), $key, $this->config, $this->package, $this->view, $this->logger);
            
            $return = $class->$function($req, $res, $args);
 
            return $return;
 
        });
    }
}
*/
 
 