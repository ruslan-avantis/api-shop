<?php 
/**
    * This file is part of the API SHOP
    *
    * @license http://opensource.org/licenses/MIT
    * @link https://github.com/pllano/api-shop
    * @version 1.1.0
    * @package pllano.api-shop
    *
    * For the full copyright and license information, please view the LICENSE
    * file that was distributed with this source code.
*/
 
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
 
// Для POST запросов вначале url генерируем post_id
// Если у пользователя нет сессии он не сможет отправлять POST запросы
$session = new $config['vendor']['session']($config['settings']['session']['name']);
$post_id = '/_'; if(isset($session->post_id)){$post_id = '/'.$session->post_id;}
 
/**
    * API Shop дает полную свободу с выбора классов обработки страниц
    * При установке пекетов или шаблонов вы можете перезаписать в конфиге класс и функцию обработки
    * Вы можете использовать контроллеры по умолчанию и вносить изменения с помощью \Pllano\Hooks\Hook
	* Вы можете использовать ApiShop\Adapter\ и менять vendor в конфигурации
*/
 
// Получаем конфигурацию роутеров
$router = $config['routers']['site'];
 
// GET - Главная
$app->get($router['index']['route'], function (Request $req, Response $res, $args = []) {
    $router = $this->config['routers']['site']['index'];
    $controller = $router['controller'];
    $function = $router['function'];
    $class = new $controller($this->config, $this->view, $this->logger);
    return $class->$function($req, $res, $args);
}); 
 
// GET - Статьи
$app->get($router['article']['route'], function (Request $req, Response $res, $args = []) {
    $router = $this->config['routers']['site']['article'];
    $controller = $router['controller'];
    $function = $router['function'];
    $class = new $controller($this->config, $this->view, $this->logger);
    return $class->$function($req, $res, $args);
});
 
// GET - Категории статей
$app->get($router['article_category']['route'], function (Request $req, Response $res, $args = []) {
    $router = $this->config['routers']['site']['article_category'];
    $controller = $router['controller'];
    $function = $router['function'];
    $class = new $controller($this->config, $this->view, $this->logger);
    return $class->$function($req, $res, $args);
}); 
 
// GET - Категории товаров
$app->get($router['category']['route'], function (Request $req, Response $res, $args = []) {
    $router = $this->config['routers']['site']['category'];
    $controller = $router['controller'];
    $function = $router['function'];
    $class = new $controller($this->config, $this->view, $this->logger);
    return $class->$function($req, $res, $args);
});
 
// GET - Страница товара
$app->get($router['product']['route'], function (Request $req, Response $res, $args = []) {
    $router = $this->config['routers']['site']['product'];
    $controller = $router['controller'];
    $function = $router['function'];
    $class = new $controller($this->config, $this->view, $this->logger);
    return $class->$function($req, $res, $args);
});
 
// GET - Страница товара quick_view
$app->get($router['product_quick_view']['route'], function (Request $req, Response $res, $args = []) {
    $router = $this->config['routers']['site']['product_quick_view'];
    $controller = $router['controller'];
    $function = $router['function'];
    $class = new $controller($this->config, $this->view, $this->logger);
    return $class->$function($req, $res, $args);
});
 
// GET - Получить локализацию
$app->get($post_id.$router['language']['route'], function (Request $req, Response $res, $args = []) {
    $router = $this->config['routers']['site']['language'];
    $controller = $router['controller'];
    $class = new $controller($this->config, $this->view, $this->logger);
    return $class->get($req, $res, $args);
});
 
// POST - Поменять язык
$app->post($post_id.$router['language']['route'], function (Request $req, Response $res, $args = []) {
    $router = $this->config['routers']['site']['language'];
    $controller = $router['controller'];
    $class = new $controller($this->config, $this->view, $this->logger);
    return $class->post($req, $res, $args);
});
 
// GET - запросы к корзине
$app->get($router['cart']['route'].'{function:[a-z0-9_-]+}', function (Request $req, Response $res, $args = []) {
    $router = $this->config['routers']['site']['cart'];
    $controller = $router['controller'];
    if ($req->getAttribute('function') && method_exists($controller,'get_'.str_replace("-", "_", $req->getAttribute('function')))) {
        $function = 'get_'.str_replace("-", "_", $req->getAttribute('function'));
    } else {
	    $controller = $this->config['routers']['site']['error']['controller'];
        $function = 'get';
    }
    $class = new $controller($this->config, $this->view, $this->logger);
    return $class->$function($req, $res, $args);
});
 
// POST - запросы к корзине
$app->post($post_id.$router['cart']['route'].'{function:[a-z0-9_-]+}', function (Request $req, Response $res, $args = []) {
    $router = $this->config['routers']['site']['cart'];
    $controller = $router['controller'];
    if ($req->getAttribute('function') && method_exists($controller,'post_'.str_replace("-", "_", $req->getAttribute('function')))) {
        $function = 'post_'.str_replace("-", "_", $req->getAttribute('function'));
    } else {
	    $controller = $this->config['routers']['site']['error']['controller'];
        $function = 'post';
    }
    $class = new $controller($this->config, $this->view, $this->logger);
    return $class->$function($req, $res, $args);
});
 
// GET - Страница авторизации. Войти в систему
$app->get($router['sign_in']['route'], function (Request $req, Response $res, $args = []) {
    $router = $this->config['routers']['site']['sign_in'];
    $controller = $router['controller'];
    $class = new $controller($this->config, $this->view, $this->logger);
    return $class->sign_in($req, $res, $args);
});
 
// POST - Авторизоваться
$app->post($post_id.$router['login']['route'], function (Request $req, Response $res, $args = []) {
    $router = $this->config['routers']['site']['login'];
    $controller = $router['controller'];
    $class = new $controller($this->config, $this->view, $this->logger);
    return $class->login($req, $res, $args);
});
 
// GET - Страница регистрации. Зарегистрироваться
$app->get($router['sign_up']['route'], function (Request $req, Response $res, $args = []) {
    $router = $this->config['routers']['site']['sign_up'];
    $controller = $router['controller'];
    $class = new $controller($this->config, $this->view, $this->logger);
    return $class->sign_up($req, $res, $args);
});
 
// POST - Зарегистрироваться
$app->post($post_id.$router['check_in']['route'], function (Request $req, Response $res, $args = []) {
    $router = $this->config['routers']['site']['check_in'];
    $controller = $router['controller'];
    $class = new $controller($this->config, $this->view, $this->logger);
    return $class->check_in($req, $res, $args);
});
 
// POST - Выйти
$app->post($post_id.$router['logout']['route'], function (Request $req, Response $res, $args = []) {
    $router = $this->config['routers']['site']['logout'];
    $controller = $router['controller'];
    $class = new $controller($this->config, $this->view, $this->logger);
    return $class->logout($req, $res, $args);
});
 
/*
// Если решить эту задачку получим крутое подключение роутринга
// Сейчас подключает но только один роутер
// Думаю стоит взять юрл и по регулярке сравнить с конфигом если совпало выполнить
 
$router = $config['routers']['site'];
 
foreach ($router as $key => $val)
{
    if($key == 'index' || $key == 'article_category') {
 
        $rep = $container->get('settings');
        $rep->replace(['keys' => $key]);
 
        $app->get($val['route'], function (Request $req, Response $res, $args = []) {
 
            // print("<br>{$this->settings['keys']}<br>");
 
            $router = $this->config['routers']['site'][$this->settings['keys']];
            $controller = $router['controller'];
 
            // print("<br>{$router['controller']}<br>");
 
            $function = $router['function'];
            $class = new $controller($this->config, $this->view, $this->logger);
 
            return $class->$function($req, $res, $args);
 
        });
    }
}
*/

 