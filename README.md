
# «API Shop» — E-Commerce Platform
Написан на PHP. Распространяется по лицензии [MIT](https://opensource.org/licenses/MIT).

`API Shop` — это конструктор. Он находится между фреймворками и готовыми платформами. Имеет свою админ панель, систему управления пакетами, шаблонами и плагинами и все остальное чтобы быстро создать интернет-магазин или сайт. Использует `Micro Framework Slim` который отвечает за роутинг (то что у него получается лучше всего), при этом есть возможность использовать компоненты `Symfony`, `Laravel` и любого другого фреймворка. Вы можете менять классы обработки на свои или на любые другие. 

### Отличная скорость работы без дополнительных оптимизаций
- Демо - https://xti.com.ua/
- Тест скорости - https://developers.google.com/speed/pagespeed/insights/?url=https://xti.com.ua

### Низкий порог входа для программистов
Самым главным преимуществом API Shop является низкий порог входа для разработчиков. Мы стараемся писать максимально просто ! За несколько дней с платформой разберется даже начинающий программист.

Не важно какой шаблонизатор, кеширование или базу данных хотите использовать.
## API Shop — из коробки будет поддерживать:
- Автозагрузчики и управление пакетами: `Composer` и [`AutoRequire`](https://github.com/pllano/auto-require) + `PackageManager` + `PackageRouter`
- Поддерживает подключение сторонних классов через [Hooks](https://github.com/pllano/hooks) без внесения изменений в код API Shop
- Шаблонизаторы: `Twig`, `Smarty`, `Blade`, `Mustache`, `PhpRenderer`
- [Кеширование](https://github.com/pllano/cache): `Memcached`, `Memcache`, `Redis`, `Predis`, `Filesystem`, `JsonCache`, `MongoDB`, `Elasticsearch`, `Array`, `Apcu`, `Apc` + `illuminate`, `Doctrine`
- [Хранение данных](https://github.com/pllano/router-db): `RESTful API`, [`JsonDB`](https://github.com/pllano/json-db), `MySQL`, `PostgreSQL`, `MongoDB`, `SQLite`, `MariaDB`, `Redis`, `Elasticsearch` (каждая таблица может работать с любой из поддерживаемых баз данных благодаря routerDb который дает один интерфейс для работы со всеми базами данных).
- HTTP клиенты: `Guzzle`, `Buzz`, `Httplug`, `Httpful`, `Requests`, `Yii2 Httpclient`, `Unirest PHP`
- [Обработчики изображений](https://github.com/pllano/router-image): `Imagine`, `Intervention`, `Spatie`
## Конструктор - Настраивай так как привык
### Конфигурация
```php
namespace ApiShop\Config;
 
class Settings {
 
    public static function get() {
    
        return [
            "settings" => [
                "debug" => 0
                "displayErrorDetails" => 0,
            ],
            "vendor" => [
                "template_engine" => "\\Pllano\\Adapter\\TemplateEngine"
            ],
            "template" => [
                "front_end" => [
                    "template_engine" => "twig",
                    "themes" => [
                       "template" => "mini-mo",
                        "templates" => "templates",
                        "dir_name" => "\/..\/themes"
                    ]
                ],
                "twig" => [
                    "cache_state" => 0,
                    "strict_variables" => 0,
                    "cache_dir" => "\/..\/cache\/_twig_cache"
                ]
            ],
            "routers" => [
                "site" => [
                    "index" => [
                        "route" => "\/",
                        "controller" => "\\ApiShop\\Controller\\Index",
                        "function" => "get",
                    ],
                    "article" => [
                        "route" => "\/{alias:[a-z0-9_-]+}.html",
                        "controller" => "\\ApiShop\\Controller\\Article",
                        "function" => "get",
                    ]
                ]
            ]
        ];
    }
}
```
### Вы можете заменить контроллер, шаблонизатор или базу данных
```php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use ApiShop\Config\Settings;
use Monolog\Processor\UidProcessor;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Slim\App;
 
$config = Settings::get();
 
$app = new App($config);
 
$container = $app->getContainer();
 
// Конфигурация
$container['config'] = function () {
    return Settings::get();
};
 
// Monolog
$container['logger'] = function ($logger) {
    $config = Settings::get();
    $settings = $config['settings']['logger'];
    $logger = new Logger($settings['name']);
    $logger->pushProcessor(new UidProcessor());
    $logger->pushHandler(new StreamHandler($settings['path'], $settings['level']));
    return $logger;
};
 
// Register \Pllano\Adapter\TemplateEngine
$container['view'] = function ($view) {
    $config = Settings::get();
    // Получаем название шаблона
    $template = $config['template']['front_end']['themes']["template"]; // По умолчанию mini-mo
    return new $config['vendor']['template_engine']($config, $template);
};
 
$app->get($config['routers']['site']['index']['route'], function (Request $req, Response $res, $args = []) {
    // Получаем настройки из конфигурации
    $router = $this->config['routers']['site']['index'];
    // Назначает контроллер
    $controller = $router['controller'];
    // Назначает функцию вызова
    $function = $router['function'];
    // Отдаем контроллеру конфигурацию, шаблонизатор и класс обработки логов
    $class = new $controller($this->config, $this->view, $this->logger);
    // Получаем ответ и выводим на страницу
    return $class->$function($req, $res, $args);
});
 
$app->run();
```
## Требования к хостингу
### Для работы API Shop необходим хостинг, который поддерживает:
- PHP версии 7 или выше
- Протокол HTTPS
- Расширение PHP: `openssl` `zip`

Для кеширования необходимо установить
- Расширение PHP: `memcache` `memcached`
- Требуемое хранилище (По умолчанию используется файловое кеширование)

![](https://github.com/pllano/api-shop/blob/master/themes/templates/mini-mo/img/logo.png)

### [Документация по установке](https://github.com/pllano/api-shop/blob/master/INSTALL.md)
### [Документация по конфигурации](https://github.com/pllano/api-shop/blob/master/app/config/README.md)
### Мы ищем единомышленников ! Присоединяйтесь.

<a name="feedback"></a>
## Поддержка, обратная связь, новости

Общайтесь с нами через почту open.source@pllano.com

Если вы нашли баг в работе API Shop загляните в
[issues](https://github.com/pllano/api-shop/issues), возможно, про него мы уже знаем и
чиним. Если нет, лучше всего сообщить о нём там. Там же вы можете оставлять свои
пожелания и предложения.

За новостями вы можете следить по
[коммитам](https://github.com/pllano/api-shop/commits/master) в этом репозитории.
[RSS](https://github.com/pllano/api-shop/commits/master.atom).

Лицензия API Shop
-------

The MIT License (MIT). Please see [LICENSE](https://github.com/pllano/api-shop/blob/master/LICENSE) for more information.

