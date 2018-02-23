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

use Pimple\Container;
use Pimple\Psr11\Container as PsrContainer;
use ApiShop\Model\{SessionUser, Language, Site, Template, Security};

$container = new Container();

// Создаем контейнер с глобальной конфигурацией
$container['config'] = $config;

// Создаем контейнер с конфигурацией пакетов
$container['package'] = $package;

// monolog
$container['logger'] = function ($c)
{
    $settings = $c['config']['settings']['logger'];
    $logger = new \Monolog\Logger($settings['name']);
    $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};

// session
$container['session'] = function ($c)
{
    return new $c['config']['vendor']['session']['session']($c['config']['settings']['session']['name']);
};
$session = $container['session'];

// Конфигурация шаблона
$container['template'] = function ($c)
{
	return (new Template($c['config'], $c['config']['template']['front_end']['themes']['template']))->get();
};
$template = $container['template'];

// Конфигурация шаблона
$container['admin_template'] = function ($c)
{
	return (new Template($c['config'], $c['config']['template']['back_end']['themes']['template']))->get();
};
$admin_template = $container['admin_template'];

// session
$container['languages'] = function ($c)
{
	return new Language($c['config'], $c['session']);
};
$languages = $container['languages'];

// Register \Pllano\Adapter\TemplateEngine
$container['view'] = function ($c)
{
	$view = null;
	if ($c['config']['settings']['install']['status'] != null) {
        // Получаем название шаблона из конфигурации
        $template = $c['config']['template']['front_end']['themes']['template']; // По умолчанию mini-mo-twig
        $site = new \ApiShop\Model\Site($c['config']);
        $site->get();
        // Получаем название шаблона из конфигурации сайта
        if ($site->template()) {$template = $site->template();}
        $view = new $c['config']['vendor']['templates']['template_engine']($c['config'], $c['package']['require'], $template);
	} else {
        $loader = new \Twig_Loader_Filesystem($c['config']['template']['front_end']['themes']['dir']."/".$c['config']['template']['front_end']['themes']['templates']."/install");
        $view = new \Twig_Environment($loader, ['cache' => false, 'strict_variables' => false]);
    }
	return $view;
};
// Register Original Twig View Admin Panel
$container['admin'] = function ($c)
{
    // Получаем название шаблона
    $template = $c['config']['template']['back_end']['themes']['template'];
    $loader = new \Twig_Loader_Filesystem($c['config']['template']['back_end']['themes']['dir']."/".$c['config']['template']['back_end']['themes']['templates']."/".$template."/layouts");
	$twig_config = [];
	$twig_config['cache'] = false;
	$twig_config['strict_variables'] = false;
	if($c['config']['template']['back_end']['cache'] == 1){
	    $twig_config['cache'] = $c['config']['template']['back_end']['cache'];
	}
	if($c['package']['require']['twig.twig']['settings']['strict_variables'] == 1) {
	    $twig_config['strict_variables'] = true;
	}
    return new \Twig_Environment($loader, $twig_config);
};
// Регистрируем контейнеры
$routing->setContainer(new PsrContainer($container));

// Для POST запросов вначале url генерируем post_id
// Если у пользователя нет сессии он не сможет отправлять POST запросы
$post_id = '/_'; if(isset($session->post_id)){$post_id = '/'.$session->post_id;}

// GET - Главная
$routing->get($router['index']['route'], function ($request, $response, $args) {
    $router = 'index';
	$controller = $this->get('config')['vendor']['controllers']['controller'];
    $function = strtolower($request->getMethod());
    $class = new $controller(
	    $request->getMethod(),
		$router,
		$this->get('config'),
		$this->get('package'),
		$this->get('session'),
		$this->get('languages'),
		$this->get('template'),
		$this->get('view'), 
		$this->get('logger')
	);
	return $response->write($class->$function($request, $response, $args));
});
 
// GET - Статьи
$routing->get($router['article']['route'], function ($request, $response, $args) {
	$router = 'article';
    $controller = $this->get('config')['vendor']['controllers']['controller'];
    $function = strtolower($request->getMethod());
    $class = new $controller(
	    $request->getMethod(),
		$router,
		$this->get('config'),
		$this->get('package'),
		$this->get('session'),
		$this->get('languages'),
		$this->get('template'),
		$this->get('view'), 
		$this->get('logger')
	);
    return $response->write($class->$function($request, $response, $args));
});
 
// GET - Категории статей
$routing->get($router['article_category']['route'], function ($request, $response, $args) {
	$router = 'article_category';
    $controller = $this->get('config')['vendor']['controllers']['controller'];
    $function = strtolower($request->getMethod());
    $class = new $controller(
	    $request->getMethod(),
		$router,
		$this->get('config'),
		$this->get('package'),
		$this->get('session'),
		$this->get('languages'),
		$this->get('template'),
		$this->get('view'), 
		$this->get('logger')
	);
    return $response->write($class->$function($request, $response, $args));
}); 
 
// GET - Категории товаров
$routing->get($router['category']['route'], function ($request, $response, $args) {
	$router = 'category';
    $controller = $this->get('config')['vendor']['controllers']['controller'];
    $function = strtolower($request->getMethod());
    $class = new $controller(
	    $request->getMethod(),
		$router,
		$this->get('config'),
		$this->get('package'),
		$this->get('session'),
		$this->get('languages'),
		$this->get('template'),
		$this->get('view'), 
		$this->get('logger')
	);
    return $response->write($class->$function($request, $response, $args));
});
 
// GET - Страница товара
$routing->get($router['product']['route'], function ($request, $response, $args) {
	$router = 'product';
    $controller = $this->get('config')['vendor']['controllers']['controller'];
    $function = strtolower($request->getMethod());
    $class = new $controller(
	    $request->getMethod(),
		$router,
		$this->get('config'),
		$this->get('package'),
		$this->get('session'),
		$this->get('languages'),
		$this->get('template'),
		$this->get('view'), 
		$this->get('logger')
	);
    return $response->write($class->$function($request, $response, $args));
});
 
// GET - Страница товара quick_view
$routing->get($router['quick_view']['route'], function ($request, $response, $args) {
	$router = 'quick_view';
    $controller = $this->get('config')['vendor']['controllers']['controller'];
    $function = strtolower($request->getMethod());
    $class = new $controller(
	    $request->getMethod(),
		$router,
		$this->get('config'),
		$this->get('package'),
		$this->get('session'),
		$this->get('languages'),
		$this->get('template'),
		$this->get('view'), 
		$this->get('logger')
	);
    return $response->write($class->$function($request, $response, $args));
});
 
// GET - Получить локализацию
$routing->map(['GET', 'POST'], $post_id.$router['language']['route'], function ($request, $response, $args) {
	$router = 'language';
    $controller = $this->get('config')['routers']['site']['language']['controller'];
    $function = strtolower($request->getMethod());
    $class = new $controller(
	    $request->getMethod(),
		$router,
		$this->get('config'),
		$this->get('package'),
		$this->get('session'),
		$this->get('languages'),
		$this->get('template'),
		$this->get('view'), 
		$this->get('logger')
	);
    return $response->write($class->$function($request, $response, $args));
});
 
// GET - запросы к корзине
$routing->map(['GET', 'POST'], $router['cart']['route'].'{function:[a-z0-9_-]+}', function ($request, $response, $args) {
    $router = 'cart';
    $controller = $this->get('config')['routers']['site']['cart']['controller'];
    if ($request->getAttribute('function') && method_exists($controller,'get_'.str_replace("-", "_", $request->getAttribute('function')))) {
        $function = 'get_'.str_replace("-", "_", $request->getAttribute('function'));
    } else {
       $controller = $this->get('config')['routers']['site']['error']['controller'];
        $function = strtolower($request->getMethod());
    }
    $class = new $controller(
	    $request->getMethod(),
		$router,
		$this->get('config'),
		$this->get('package'),
		$this->get('session'),
		$this->get('languages'),
		$this->get('template'),
		$this->get('view'), 
		$this->get('logger')
	);
    return $response->write($class->$function($request, $response, $args));
});
 
// GET - Страница авторизации. Войти в систему
$routing->get($router['sign_in']['route'], function ($request, $response, $args) {
    $router = 'sign_in';
	$controller = $this->get('config')['vendor']['controllers']['controller'];
    $function = strtolower($request->getMethod());
    $class = new $controller(
	    $request->getMethod(),
		$router,
		$this->get('config'),
		$this->get('package'),
		$this->get('session'),
		$this->get('languages'),
		$this->get('template'),
		$this->get('view'), 
		$this->get('logger')
	);
    return $response->write($class->$function($request, $response, $args));
});

// GET - Страница регистрации. Зарегистрироваться
$routing->get($router['sign_up']['route'], function ($request, $response, $args) {
    $router = 'sign_up';
	$controller = $this->get('config')['vendor']['controllers']['controller'];
    $function = strtolower($request->getMethod());
    $class = new $controller(
	    $request->getMethod(),
		$router,
		$this->get('config'),
		$this->get('package'),
		$this->get('session'),
		$this->get('languages'),
		$this->get('template'),
		$this->get('view'), 
		$this->get('logger')
	);
    return $response->write($class->$function($request, $response, $args));
});
 
// POST - Авторизоваться
$routing->map(['GET', 'POST'], $post_id.$router['login']['route'], function ($request, $response, $args) {
    $router = 'login';
	$controller = $this->get('config')['vendor']['controllers']['controller'];
	$function = strtolower($request->getMethod());
    $class = new $controller(
	    $request->getMethod(),
		$router,
		$this->get('config'),
		$this->get('package'),
		$this->get('session'),
		$this->get('languages'),
		$this->get('template'),
		$this->get('view'), 
		$this->get('logger')
	);
    return $response->write($class->$function($request, $response, $args));
});
 
// POST - Зарегистрироваться
$routing->post($post_id.$router['check_in']['route'], function ($request, $response, $args) {
    $router = 'check_in';
	$controller = $this->get('config')['vendor']['controllers']['controller'];
	$function = strtolower($request->getMethod());
    $class = new $controller(
	    $request->getMethod(),
		$router,
		$this->get('config'),
		$this->get('package'),
		$this->get('session'),
		$this->get('languages'),
		$this->get('template'),
		$this->get('view'), 
		$this->get('logger')
	);
    return $response->write($class->$function($request, $response, $args));
});
 
// POST - Выйти
$routing->post($post_id.$router['logout']['route'], function ($request, $response, $args) {
    $router = 'logout';
	$controller = $this->get('config')['vendor']['controllers']['controller'];
	$function = strtolower($request->getMethod());
    $class = new $controller(
	    $request->getMethod(),
		$router,
		$this->get('config'),
		$this->get('package'),
		$this->get('session'),
		$this->get('languages'),
		$this->get('template'),
		$this->get('view'), 
		$this->get('logger')
	);
    return $response->write($class->$function($request, $response, $args));
});

// Automatically register routers
// Автоматическое подключение роутеров
$routers = glob(__DIR__ . '/routers/*.php');
foreach ($routers as $router) {
    require $router;
}

// Если одина из баз json запускаем jsonDB
if ($config['db']['master'] == "json" || $config['db']['slave'] == "json") {
    // Запускаем jsonDB\Db
    $jsonDb = new \jsonDB\Db($config['db']['json']['dir']);
    $jsonDb->run();
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
 
        $routing->get($val['route'], function ($request, $response, $args) {
 
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
 