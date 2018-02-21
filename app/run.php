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
 
/**
    * API Shop дает полную свободу с выбора классов обработки страниц
    * При установке пекетов или шаблонов вы можете перезаписать в конфиге класс и функцию обработки
    * Вы можете использовать контроллеры по умолчанию и вносить изменения с помощью \Pllano\Hooks\Hook
    * Вы можете использовать ApiShop\Adapter\ и менять vendor в конфигурации
*/
 
use Pimple\Container;
use Pimple\Psr11\Container as PsrContainer;
 
// Подключаем файл конфигурации системы
require __DIR__ . '/settings.php';
// Получаем конфигурацию
 
$container = new Container();
 
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

//print_r($router);
 
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
$container['view'] = function ($c)
{
	$view = '';
	if ($c['config']['settings']["install"]["status"] != null) {
        // Получаем название шаблона из конфигурации
        $template = $c['config']['template']['front_end']['themes']["template"]; // По умолчанию mini-mo
        $site = new \ApiShop\Model\Site($c['config']);
        $site->get();
        // Получаем название шаблона из конфигурации сайта
        if ($site->template()) {$template = $site->template();}
        $view = new $c['config']['vendor']['templates']['template_engine']($c['config'], $template);
	} else {
        $loader = new \Twig_Loader_Filesystem($c['config']['settings']['themes']['front_end_dir']."/".$c['config']['template']['front_end']['themes']['templates']."/install");
        $view = new \Twig_Environment($loader, ['cache' => false, 'strict_variables' => false]);
    }
	return $view;
};
 
// Register Original Twig View Admin Panel
$container['admin'] = function ($c)
{
    // Получаем название шаблона
    $template = $c['config']['admin']['template'];
    $loader = new \Twig_Loader_Filesystem($c['config']['settings']['themes']['dir']."/".$c['config']['settings']['themes']['templates']."/".$template."/layouts");
    $admin = new \Twig_Environment($loader, ['cache' => false, 'strict_variables' => false]);
    return $admin;
};
 
$app->setContainer(new PsrContainer($container));
 
//$controller = $config['vendor']['controllers']['controller'];
//$app->get($router['index']['route'], $controller.':get')->add(new $controller($container))->setName('index');
	
// GET - Главная
$app->get($router['index']['route'], function ($request, $response, $args) {
    $controller = $this->get('config')['vendor']['controllers']['controller'];
    $function = strtolower($request->getMethod());
    $class = new $controller($request->getMethod(), 'index', $this->get('config'), $this->get('package'), $this->get('view'), $this->get('logger'));
	return $response->write($class->$function($request, $response, $args));
});
 
// GET - Статьи
$app->get($router['article']['route'], function ($request, $response, $args) {
    $controller = $this->get('config')['vendor']['controllers']['controller'];
    $function = strtolower($request->getMethod());
    $class = new $controller($request->getMethod(), 'article', $this->get('config'), $this->get('package'), $this->get('view'), $this->get('logger'));
    return $response->write($class->$function($request, $response, $args));
});
 
// GET - Категории статей
$app->get($router['article_category']['route'], function ($request, $response, $args) {
    $controller = $this->get('config')['vendor']['controllers']['controller'];
    $function = strtolower($request->getMethod());
    $class = new $controller($request->getMethod(), 'article_category', $this->get('config'), $this->get('package'), $this->get('view'), $this->get('logger'));
    return $response->write($class->$function($request, $response, $args));
}); 
 
// GET - Категории товаров
$app->get($router['category']['route'], function ($request, $response, $args) {
    $controller = $this->get('config')['vendor']['controllers']['controller'];
    $function = strtolower($request->getMethod());
    $class = new $controller($request->getMethod(), 'category', $this->get('config'), $this->get('package'), $this->get('view'), $this->get('logger'));
    return $response->write($class->$function($request, $response, $args));
});
 
// GET - Страница товара
$app->get($router['product']['route'], function ($request, $response, $args) {
    $controller = $this->get('config')['vendor']['controllers']['controller'];
    $function = strtolower($request->getMethod());
    $class = new $controller($request->getMethod(), 'product', $this->get('config'), $this->get('package'), $this->get('view'), $this->get('logger'));
    return $response->write($class->$function($request, $response, $args));
});
 
// GET - Страница товара quick_view
$app->get($router['quick_view']['route'], function ($request, $response, $args) {
    $controller = $this->get('config')['vendor']['controllers']['controller'];
    $function = strtolower($request->getMethod());
    $class = new $controller($request->getMethod(), 'quick_view', $this->get('config'), $this->get('package'), $this->get('view'), $this->get('logger'));
    return $response->write($class->$function($request, $response, $args));
});
 
// GET - Получить локализацию
$app->map(['GET', 'POST'], $post_id.$router['language']['route'], function ($request, $response, $args) {
    $router = $this->get('config')['routers']['site']['language'];
    $controller = $router['controller'];
    $function = strtolower($request->getMethod());
    $class = new $controller($request->getMethod(), 'language', $this->get('config'), $this->get('package'), $this->get('view'), $this->get('logger'));
    return $response->write($class->$function($request, $response, $args));
});
 
// GET - запросы к корзине
$app->map(['GET', 'POST'], $router['cart']['route'].'{function:[a-z0-9_-]+}', function ($request, $response, $args) {
    $router = $this->get('config')['routers']['site']['cart'];
    $controller = $router['controller'];
    if ($request->getAttribute('function') && method_exists($controller,'get_'.str_replace("-", "_", $request->getAttribute('function')))) {
        $function = 'get_'.str_replace("-", "_", $request->getAttribute('function'));
    } else {
       $controller = $this->get('config')['routers']['site']['error']['controller'];
        $function = strtolower($request->getMethod());
    }
    $class = new $controller($request->getMethod(), 'cart', $this->get('config'), $this->get('package'), $this->get('view'), $this->get('logger'));
    return $response->write($class->$function($request, $response, $args));
});
 
// GET - Страница авторизации. Войти в систему
$app->get($router['sign_in']['route'], function ($request, $response, $args) {
    $controller = $this->get('config')['vendor']['controllers']['controller'];
    $function = strtolower($request->getMethod());
    $class = new $controller($request->getMethod(), 'sign_in', $this->get('config'), $this->get('package'), $this->get('view'), $this->get('logger'));
    return $response->write($class->$function($request, $response, $args));
});

// GET - Страница регистрации. Зарегистрироваться
$app->get($router['sign_up']['route'], function ($request, $response, $args) {
    $controller = $this->get('config')['vendor']['controllers']['controller'];
    $function = strtolower($request->getMethod());
    $class = new $controller($request->getMethod(), 'sign_up', $this->get('config'), $this->get('package'), $this->get('view'), $this->get('logger'));
    return $response->write($class->$function($request, $response, $args));
});
 
// POST - Авторизоваться
$app->map(['GET', 'POST'], $post_id.$router['login']['route'], function ($request, $response, $args) {
    $controller = $this->get('config')['vendor']['controllers']['controller'];
	$function = strtolower($request->getMethod());
    $class = new $controller($request->getMethod(), 'login', $this->get('config'), $this->get('package'), $this->get('view'), $this->get('logger'));
    return $response->write($class->$function($request, $response, $args));
});
 
// POST - Зарегистрироваться
$app->post($post_id.$router['check_in']['route'], function ($request, $response, $args) {
    $controller = $this->get('config')['vendor']['controllers']['controller'];
	$function = strtolower($request->getMethod());
    $class = new $controller($request->getMethod(), 'check_in', $this->get('config'), $this->get('package'), $this->get('view'), $this->get('logger'));
    return $response->write($class->$function($request, $response, $args));
});
 
// POST - Выйти
$app->post($post_id.$router['logout']['route'], function ($request, $response, $args) {
    $controller = $this->get('config')['vendor']['controllers']['controller'];
	$function = strtolower($request->getMethod());
    $class = new $controller($request->getMethod(), 'logout', $this->get('config'), $this->get('package'), $this->get('view'), $this->get('logger'));
    return $response->write($class->$function($request, $response, $args));
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
 
        $app->get($val['route'], function ($request, $response, $args) {
 
            print("<br>{$this->settings['keys']}<br>");
 
            $router = $this->config['routers']['site'][$this->settings['keys']];
            
           $controller = $router['controller'];
 
            print("<br>{$router['controller']}<br>");
 
            $function = strtolower($request->getMethod());
            $class = new $controller($request->getMethod(), $key, $this->get('config'), $this->get('package'), $this->get('view'), $this->get('logger'));
            
            $return = $class->$function($request, $response, $args);
 
            return $return;
 
        });
    }
}
*/
 