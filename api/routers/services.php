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
 
$app->get("/v1/json/{service:[\w]+}[/{resource:[\w]+}[/{id:[\w]+}]]", function (Request $request, Response $response, array $args) {
    $service = $request->getAttribute('service');
    $param = $request->getQueryParams();
    
    if ($request->getAttribute('resource') !== null) {
        $resource = $request->getAttribute('resource');
    } else {
        $resource = null;
    }
    if ($request->getAttribute('id') !== null) {
        $id = $request->getAttribute('id');
    } else {
        $id = null;
    }
 
    if (isset($service)) {
            $service_name = "\\ApiShop\\Api\\Services\\".ucfirst($service);
            $services = new $service_name();
            $resp = $services->get($resource, $param, $id);
    } else {
        // Сервис не определен. Возвращаем ошибку 404
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

$app->post("/v1/json/{service:[\w]+}[/{resource:[\w]+}]", function (Request $request, Response $response, array $args) {
    $service = $request->getAttribute('service');
 
    if ($request->getAttribute('resource') !== null) {
        $resource = $request->getAttribute('resource');
    } else {
        $resource = null;
    }
 
    $param = $request->getParsedBody();
    if (isset($service)) {
            $service_name = "\\ApiShop\\Api\\Services\\".ucfirst($service);
            $services = new $service_name();
            $resp = $services->post($resource, $param);
    } else {
        // Сервис не определен. Возвращаем ошибку 404
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

$app->put("/v1/json/{service:[\w]+}[/{resource:[\w]+}[/{id:[\w]+}]]", function (Request $request, Response $response, array $args) {
    $service = $request->getAttribute('service');
    $param = $request->getParsedBody();
 
    if ($request->getAttribute('resource') !== null) {
        $resource = $request->getAttribute('resource');
    } else {
        $resource = null;
    }
    if ($request->getAttribute('id') !== null) {
        $id = $request->getAttribute('id');
    } else {
        $id = null;
    }
 
    if (isset($service)) {
            $service_name = "\\ApiShop\\Api\\Services\\".ucfirst($service);
            $services = new $service_name();
            $resp = $services->put($resource, $param, $id);
    } else {
        // Сервис не определен. Возвращаем ошибку 404
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

$app->patch("/v1/json/{service:[\w]+}[/{resource:[\w]+}[/{id:[\w]+}]]", function (Request $request, Response $response, array $args) {
    $service = $request->getAttribute('service');
    $param = $request->getParsedBody();
 
    if ($request->getAttribute('resource') !== null) {
        $resource = $request->getAttribute('resource');
    } else {
        $resource = null;
    }
    if ($request->getAttribute('id') !== null) {
        $id = $request->getAttribute('id');
    } else {
        $id = null;
    }
	
    if (isset($service)) {
            $service_name = "\\ApiShop\\Api\\Services\\".ucfirst($service);
            $services = new $service_name();
            $resp = $services->patch($resource, $param, $id);
    } else {
        // Сервис не определен. Возвращаем ошибку 404
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

$app->delete("/v1/json/{service:[\w]+}[/{resource:[\w]+}[/{id:[\w]+}]]", function (Request $request, Response $response, array $args) {
    $service = $request->getAttribute('service');
    $param = $request->getParsedBody();
 
    if ($request->getAttribute('resource') !== null) {
        $resource = $request->getAttribute('resource');
    } else {
        $resource = null;
    }
    if ($request->getAttribute('id') !== null) {
        $id = $request->getAttribute('id');
    } else {
        $id = null;
    }
 
    if (isset($service)) {
            $service_name = "\\ApiShop\\Api\\Services\\".ucfirst($service);
            $services = new $service_name();
            $resp = $services->delete($resource, $param, $id);
    } else {
        // Сервис не определен. Возвращаем ошибку 404
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
