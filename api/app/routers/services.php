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

$app->get("/v1/json/services/{service:[\w]+}[/{id:[\w]+}]", function (Request $request, Response $response, array $args) {
    $service = $request->getAttribute('service');
    $param = $request->getQueryParams();
	$id = (isset($request->getAttribute('id'))) ? $request->getAttribute('id') : null;
    if (isset($service)) {
            $service = ucfirst($service);
            $services = new $service();
	        $resp = $services->get($id, $param);
    } else {
        // Сервис не определен. Возвращаем ошибку 400
        $resp["headers"]["status"] = "404 Not Found";
        $resp["headers"]["code"] = 404;
        $resp["headers"]["message"] = "Bad Request";
        $resp["headers"]["message_id"] = $this->get("settings")["http-codes"]."".$resp["headers"]["code"].".md";
    }
	if ($resp != null) {
        echo json_encode($resp, JSON_PRETTY_PRINT);
        return $response->withStatus(200)->withHeader("Content-Type","application/json");
	}
});

$app->post("/v1/json/services/{service:[\w]+}", function (Request $request, Response $response, array $args) {
    $service = $request->getAttribute('service');
    $param = $request->getParsedBody();
	if (isset($service)) {
            $service = ucfirst($service);
            $services = new $service();
	        $resp = $services->post($param);
    } else {
        // Сервис не определен. Возвращаем ошибку 400
        $resp["headers"]["status"] = "404 Not Found";
        $resp["headers"]["code"] = 404;
        $resp["headers"]["message"] = "Bad Request";
        $resp["headers"]["message_id"] = $this->get("settings")["http-codes"]."".$resp["headers"]["code"].".md";
    }
	if ($resp != null) {
        echo json_encode($resp, JSON_PRETTY_PRINT);
        return $response->withStatus(200)->withHeader("Content-Type","application/json");
	}
});

$app->put("/v1/json/services/{service:[\w]+}[/{id:[\w]+}]", function (Request $request, Response $response, array $args) {
    $service = $request->getAttribute('service');
    $param = $request->getParsedBody();
	$id = (isset($request->getAttribute('id'))) ? $request->getAttribute('id') : null;
	if (isset($service)) {
            $service = ucfirst($service);
            $services = new $service();
	        $resp = $services->put($id, $param);
    } else {
        // Сервис не определен. Возвращаем ошибку 400
        $resp["headers"]["status"] = "404 Not Found";
        $resp["headers"]["code"] = 404;
        $resp["headers"]["message"] = "Bad Request";
        $resp["headers"]["message_id"] = $this->get("settings")["http-codes"]."".$resp["headers"]["code"].".md";
    }
	if ($resp != null) {
        echo json_encode($resp, JSON_PRETTY_PRINT);
        return $response->withStatus(200)->withHeader("Content-Type","application/json");
	}
});

$app->patch("/v1/json/services/{service:[\w]+}[/{id:[\w]+}]", function (Request $request, Response $response, array $args) {
    $service = $request->getAttribute('service');
    $param = $request->getParsedBody();
	$id = (isset($request->getAttribute('id'))) ? $request->getAttribute('id') : null;
	if (isset($service)) {
            $service = ucfirst($service);
            $services = new $service();
	        $resp = $services->patch($id, $param);
    } else {
        // Сервис не определен. Возвращаем ошибку 400
        $resp["headers"]["status"] = "404 Not Found";
        $resp["headers"]["code"] = 404;
        $resp["headers"]["message"] = "Bad Request";
        $resp["headers"]["message_id"] = $this->get("settings")["http-codes"]."".$resp["headers"]["code"].".md";
    }
	if ($resp != null) {
        echo json_encode($resp, JSON_PRETTY_PRINT);
        return $response->withStatus(200)->withHeader("Content-Type","application/json");
	}
});

$app->delete("/v1/json/services/{service:[\w]+}[/{id:[\w]+}]", function (Request $request, Response $response, array $args) {
    $service = $request->getAttribute('service');
    $param = $request->getParsedBody();
	$id = (isset($request->getAttribute('id'))) ? $request->getAttribute('id') : null;
	if (isset($service)) {
            $service = ucfirst($service);
            $services = new $service();
	        $resp = $services->delete($id, $param);
    } else {
        // Сервис не определен. Возвращаем ошибку 400
        $resp["headers"]["status"] = "404 Not Found";
        $resp["headers"]["code"] = 404;
        $resp["headers"]["message"] = "Bad Request";
        $resp["headers"]["message_id"] = $this->get("settings")["http-codes"]."".$resp["headers"]["code"].".md";
    }
	if ($resp != null) {
        echo json_encode($resp, JSON_PRETTY_PRINT);
        return $response->withStatus(200)->withHeader("Content-Type","application/json");
	}
});
