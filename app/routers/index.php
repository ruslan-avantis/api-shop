<?php
/**
 * This file is part of the API SHOP
 *
 * @license http://opensource.org/licenses/MIT
 * @link https://github.com/pllano/api-shop
 * @version 1.0.1
 * @package pllano.api-shop
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/', function (Request $request, Response $response, array $args) {
    
        $resp["headers"]["status"] = "200 OK";
        $resp["headers"]["code"] = 200;
        $resp["headers"]["message"] = "API Shop works!";
        $resp["headers"]["message_id"] = $this->get('settings')['http-codes']."".$resp["headers"]["code"].".md";
        echo json_encode($resp, JSON_PRETTY_PRINT);
        
    return $response->withStatus(200)->withHeader('Content-Type','application/json');

});
 