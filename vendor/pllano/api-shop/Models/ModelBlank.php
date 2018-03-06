<?php 
/**
 * Pllano Api Shop (https://pllano.com)
 *
 * @link https://github.com/pllano/api-shop
 * @version 1.2.1
 * @copyright Copyright (c) 2017-2018 PLLANO
 * @license http://opensource.org/licenses/MIT (MIT License)
 */
namespace Pllano\ApiShop\Models;

use Psr\Container\ContainerInterface as Container;
use Psr\Http\Message\{ServerRequestInterface as Request, ResponseInterface as Response};
use Pllano\Core\Interfaces\ModelInterface;
use Pllano\Core\Model;

class ModelBlank extends Model implements ModelInterface
{

    public function __construct(Container $app)
    {
        parent::__construct($app);
		$this->connectContainer();
		$this->connectDatabases();
    }

    public function get(Request $request, Response $response, array $args)
    {
        return null;
    }

    public function post(Request $request, Response $response, array $args)
    {
        return null;
    }

}
 