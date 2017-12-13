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

$app->get("/v1/json/services/{service:[\w]+}", function (Request $request, Response $response, array $args) {
    $service = $request->getAttribute('service');
	$param = $request->getQueryParams();	
	$key = (isset($param['key'])) ? $param['key'] : null;
    
	if (isset($service) && isset($key)) {
        $key = filter_var($key, FILTER_SANITIZE_STRING);
        if ($this->get("settings")["services"][$service] == $key) {
            $service = ucfirst($service);
            $services = new $service();
	        $resp = $services->get($key, $param);
        } else {
            // Доступ запрещен. Ключ доступа не совпадает.
            $resp["headers"]["status"] = "401 Unauthorized";
            $resp["headers"]["code"] = 401;
            $resp["headers"]["message"] = "Access is denied";
            $resp["headers"]["message_id"] = $this->get("settings")["http-codes"]."".$resp["headers"]["code"].".md";
        }
    } else {
        // Сервис не определен. Возвращаем ошибку 400
        $resp["headers"]["status"] = "404 Not Found";
        $resp["headers"]["code"] = 404;
        $resp["headers"]["message"] = "Bad Request";
        $resp["headers"]["message_id"] = $this->get("settings")["http-codes"]."".$resp["headers"]["code"].".md";
    }

    echo json_encode($resp, JSON_PRETTY_PRINT);
    return $response->withStatus(200)->withHeader("Content-Type","application/json");

});

$app->post("/v1/json/services/{service:[\w]+}", function (Request $request, Response $response, array $args) {
    $service = $request->getAttribute('service');
    $param = $request->getParsedBody();
    
	if (isset($service)) {
        $key = filter_var($param["key"], FILTER_SANITIZE_STRING);
        if ($this->get("settings")["services"][$service] == $key) {
            $service = ucfirst($service);
            $services = new $service();
	        $resp = $services->post($key, $param);
        } else {
            // Доступ запрещен. Ключ доступа не совпадает.
            $resp["headers"]["status"] = "401 Unauthorized";
            $resp["headers"]["code"] = 401;
            $resp["headers"]["message"] = "Access is denied";
            $resp["headers"]["message_id"] = $this->get("settings")["http-codes"]."".$resp["headers"]["code"].".md";
        }
    } else {
        // Сервис не определен. Возвращаем ошибку 400
        $resp["headers"]["status"] = "404 Not Found";
        $resp["headers"]["code"] = 404;
        $resp["headers"]["message"] = "Bad Request";
        $resp["headers"]["message_id"] = $this->get("settings")["http-codes"]."".$resp["headers"]["code"].".md";
    }

    echo json_encode($resp, JSON_PRETTY_PRINT);
    return $response->withStatus(200)->withHeader("Content-Type","application/json");

});