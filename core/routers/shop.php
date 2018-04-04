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

// GET - Категории товаров
$routing->get($routes['category']['route'], function (Request $request, Response $response, array $args = []) use ($core, $app) {
    return (new $app($core, 'category'))->get($request, $response, $args);
});

// GET - Страница товара
$routing->get($routes['product']['route'], function (Request $request, Response $response, array $args = []) use ($core, $app) {
    return (new $app($core, 'product'))->get($request, $response, $args);
});

// GET - Страница товара quick_view
$routing->get($routes['quick_view']['route'], function (Request $request, Response $response, array $args = []) use ($core, $app) {
    return (new $app($core, 'quick_view'))->get($request, $response, $args);
});
 