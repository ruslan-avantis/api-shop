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
use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Register App
$app = $core->get('config')['vendor']['controllers']['controller'];
// For POST requests, we first generate the url post_id
// If the user does not have a session, he can not send POST requests
$post_id = '/_';
if(isset($session->post_id)){
    $post_id = '/'.$session->post_id;
}

// GET - Главная
$routing->get($routes['index']['route'], function (Request $request, Response $response, array $args = []) use ($core, $app) {
    return (new $app($core, 'index'))->get($request, $response, $args);
});

// GET - Статьи
$routing->get($routes['article']['route'], function (Request $request, Response $response, array $args = []) use ($core, $app) {
    return (new $app($core, 'article'))->get($request, $response, $args);
});

// GET - Категории статей
$routing->get($routes['article_category']['route'], function (Request $request, Response $response, array $args = []) use ($core, $app) {
    return (new $app($core, 'article_category'))->get($request, $response, $args);
});

// GET - Получить локализацию 
$routing->map(['GET', 'POST'], $post_id.$routes['language']['route'], function (Request $request, Response $response, array $args = []) use ($core, $app) {
    $app = $this->get('config')['routers']['site']['language']['controller'];
    $function = strtolower($request->getMethod());
    return (new $app($core, 'language'))->$function($request, $response, $args);
});

// POST - Авторизоваться
$routing->post($post_id.$routes['login']['route'], function (Request $request, Response $response, array $args = []) use ($core, $app) {
    return (new $app($core, 'login'))->post($request, $response, $args);
});

// GET - Страница авторизации. Войти в систему
$routing->get($routes['sign_in']['route'], function (Request $request, Response $response, array $args = []) use ($core, $app) {
    $function = strtolower($request->getMethod());
    return (new $app($core, 'sign_in'))->get($request, $response, $args);
});

// GET - Страница авторизации. Войти в систему
$routing->get($routes['sign_up']['route'], function (Request $request, Response $response, array $args = []) use ($core, $app) {
    $function = strtolower($request->getMethod());
    return (new $app($core, 'sign_up'))->get($request, $response, $args);
});

// GET - запросы к корзине
$routing->post($post_id.$routes['cart']['route'].'{function:[a-z0-9_-]+}', function (Request $request, Response $response, array $args = []) use ($core, $app) {
    $cart = $this->get('config')['routers']['site']['cart']['controller'];
	$error = $this->get('config')['routers']['site']['error']['controller'];
	$function = strtolower($request->getMethod());
    if ($request->getAttribute('function')) {
        $method = 'post_'.str_replace("-", "_", $request->getAttribute('function'));
        if (method_exists($cart, $method)) {
            $function = $method;
			$app = $cart;
        } else {
		    $app = $error;
        }
    } else {
        $app = $error;
    }
    return (new $app($core, 'cart'))->$function($request, $response, $args);
});

// GET - запросы к корзине
$routing->get($routes['cart']['route'].'{function:[a-z0-9_-]+}', function (Request $request, Response $response, array $args = []) use ($core, $app) {
    $cart = $this->get('config')['routers']['site']['cart']['controller'];
	$error = $this->get('config')['routers']['site']['error']['controller'];
	$function = strtolower($request->getMethod());
    if ($request->getAttribute('function')) {
        $method = 'get_'.str_replace("-", "_", $request->getAttribute('function'));
        if (method_exists($cart, $method)) {
            $function = $method;
			$app = $cart;
        } else {
		    $app = $error;
        }
    } else {
        $app = $error;
    }
    return (new $app($core, 'cart'))->$function($request, $response, $args);
});

// POST - Зарегистрироваться
$routing->post($post_id.$routes['check_in']['route'], function (Request $request, Response $response, array $args = []) use ($core, $app) {
    //$function = strtolower($request->getMethod());
    return (new $app($core, 'check_in'))->post($request, $response, $args);
});

// POST - Выйти
$routing->post($post_id.$routes['logout']['route'], function (Request $request, Response $response, array $args = []) use ($core, $app) {
    return (new $app($core, 'logout'))->post($request, $response, $args);
});
 