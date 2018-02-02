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
 
namespace ApiShop\Adapter;
 
use Slim\Http\Request;
use Slim\Http\Response;
 
use ApiShop\Utilities\Server;
 
class Cache {
 
    private $args;
    private $request;
    private $response;
    private $config;
    private $content = '';
    private $state = '0';
    private $dynamic = '0';
    private $vendor;
    private $adapter;
    private $driver;
    private $pool;
    private $client;
    private $www;
    private $cpu = '80';
    private $memory = '80';
    private $print = 0;
    private $cache_lifetime = 60;
    private $item;
    private $meminfo;
    private $nproc;
    private $url = null;
 
    public function __construct(Request $request, Response $response, array $args, $config)
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        $this->state = $config['cache']['state'];
        $this->dynamic = $config['cache']['dynamic'];
        $this->vendor = $config['cache']['vendor'];
        $this->adapter = $config['cache']['adapter'];
        $this->driver = $config['cache']['driver'];
        $this->cache_lifetime = $config['cache']['cache_lifetime'];
        $this->www = $config['dir']['www'];
        $this->cpu = $config['cache']['cpu'];
        $this->print = $config['cache']['print'];
        $this->memory = $config['cache']['memory'];
 
        $server = new Server();
        $this->meminfo = $server->meminfo();
        $this->nproc = $server->nproc();
 
        $memory = $this->meminfo['MemFree'] / ($this->meminfo['MemTotal'] / 100);
        $cpu = $this->nproc/100*$this->cpu;
        $cpu_r = $this->nproc/100*80;
 
        if ($this->dynamic == '1') {
            $sys_get = sys_getloadavg();
            if ((int)$this->print == 1) {
                print('<br>Занято оперативной памяти: '.round($memory,2).' %');
                print('<br>Допустимый максимум оперативной памяти: '.$this->memory.' %');
                print('<br>Допустимый максимум CPU: '.$cpu.' ядер из '.$this->nproc);
                print('<br>Занято ядрер: '.$sys_get['1'].' из '.$this->nproc);
            }
            if ($sys_get['1'] >= $cpu || $sys_get['0'] >= $cpu_r || $memory >= $this->memory) {
                $this->state = '1';
            } else {
                $this->state = '0';
            }
            if ((int)$memory >= 90) {
                $this->state = '1';
                $this->driver = 'filesystem';
            }
        }
 
        if ((int)$this->print == 1) {
            print('<br>driver: '.$this->driver);
            print('<br>state: '.$this->state);
            print('<br>Время жизни кеша, сек.: '.$this->cache_lifetime);
        }
 
        $this->config = $config['cache'][$this->driver];
        $this->driver();
    }
 
    public function run($url = null, $cache_lifetime = null)
    {
        if(isset($url)) {
            $this->url = $url;
        }
        if(isset($cache_lifetime)) {
            $this->cache_lifetime = $cache_lifetime;
        }
        if ($this->state == '1') {
            $this->item = $this->pool->getItem($this->cacheKey());
            $this->content = $this->item->get();
            if(isset($this->content)) {
                if ((int)$this->print == 1) {
                    print('<br>content из кеша<br>');
                }
                return true;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }
 
    public function cacheKey()
    {
        if(isset($this->url)) {
            $url = $this->url;
        } else {
            $request = $this->request;
            $host = $request->getUri()->getHost();
            $path = $request->getUri()->getPath();
            $query = isset($_SERVER['QUERY_STRING']) ? "?" . $_SERVER['QUERY_STRING'] : "";
            //$scheme = $request->getUri()->getScheme();
            //$params = $request->getQueryParams();
            $url = '//'.$host.$path.$query;
        }
        $key = hash('md5', $url);
        //$key = sha1($_SERVER['REQUEST_URI']);
        if ((int)$this->print == 1) {
            print('<br>url: '.$url);
            print('<br>key: '.$key);
        }
        return $key;
    }
 
    public function content()
    {
         $content = json_decode($this->content, true);
         return $content;
    }
 
    public function set($content)
    {
        $this->content = json_encode($content);
        //$this->item = $this->pool->getItem($this->cacheKey());
        $this->item->set($this->content);
        $this->item->expiresAfter((int)$this->cache_lifetime);
        $this->pool->save($this->item);
    }
 
    public function vendor()
    {
        return $this->vendor;
    }
 
    public function state()
    {
        return $this->state;
    }
 
    public function driver()
    {
        $driver = strtolower($this->driver);
 
        if ($driver == 'memcached') {
            $client = new \Memcached();
            $client->addServer($this->config['host'], $this->config['port']);
            $this->pool = new $this->config['pool']($client);
        } elseif ($driver == 'memcache') {
            $client = new \Memcache();
            $client->addServer($this->config['host'], $this->config['port']);
            $this->pool = new $this->config['pool']($client);
        } elseif ($driver == 'filesystem') {
            $filesystemAdapter = new $this->config['filesystem_adapter']($this->www.'/');
            $filesystem = new $this->config['filesystem']($filesystemAdapter);
            $this->pool = new $this->config['pool']($filesystem);
            $this->pool->setFolder($this->config['path']);
        } elseif ($driver == 'elasticsearch' || $driver == 'jsondb' || $driver == 'apcu' || $driver == 'apc' || $driver == 'array' || $driver == 'void') {
            $this->pool = new $this->config['pool']();
        } elseif ($driver == 'predis' || $driver == 'redis') {
            $client = new \Predis\Client('tcp:/'.$this->config['host'].':'.$this->config['port']);
            $this->pool = new $this->config['pool']($client);
        } elseif ($driver == 'mongodb') {
            $manager = new $this->config['manager']('mongodb://'.getenv('MONGODB_HOST'));
            $collection = $this->config['pool']::createCollection($manager, $this->config['host'].':'.$this->config['port'], $this->config['name']);
            $this->pool = new $this->config['pool']($collection);
        } elseif ($driver == 'illuminate') {
            // Create an instance of an Illuminate's Store
            $store = new $this->config['store']();
            // Wrap the Illuminate's store with the PSR-6 adapter
            $this->pool = new $this->config['pool']($store);
        } elseif ($driver == 'doctrine') {
            $memcached = new \Memcached();
            $memcached->addServer($this->config['host'], $this->config['port']);
            // Create a instance of Doctrine's MemcachedCache
            $doctrineCache = new $this->config['memcached']();
            $doctrineCache->setMemcached($memcached);
            // Wrap Doctrine's cache with the PSR-6 adapter
            $this->pool = new $this->config['pool']($doctrineCache);
        }
 
        return $driver;
    }
 
}
 