### Внимание ! Файлы в процессе добавления. Не пробуйте устанавливать API Shop !
# E-Commerce Platform API Shop
API Shop — E-Commerce Platform (движок интернет-магазина), использует RESTful API сторонних платформ для хранения и обработки информации. Написан на PHP с использованием [Micro Framework Slim](https://github.com/slimphp) который использует [PSR-7](http://www.php-fig.org/psr/psr-7/) интерфейс HTTP-сообщений. Использует шаблонизатор [Twig](https://github.com/twigphp/Twig/) и менеджер зависимостей для PHP — [Composer](https://github.com/composer). Распространяется по лицензии [MIT](https://opensource.org/licenses/MIT).
## Создавать интернет-магазины на вашей платформе
Предоставьте своим пользователям возможность создавать интернет-магазины на своей платформе с максимальной интеграцией своих сервисов в эти интернет-магазины.
### Банки
Банки могут дать возможность своим клиентам создавать интернет-магазины с максимальной интеграцией своих сервисов (платежи, мгновенные кредиты итд.).
### Службы доставки
Службы доставки могут дать возможность своим клиентам создавать интернет-магазины в которых будет реализована возможность отгрузки заказа покупателю только через ихнюю службу.
### Страховые компании
Страховые компании могут дать возможность своим потенциальным клиентам создавать интернет-магазины с обязательной страховкой всех продаж.
### Маркетплейсы
Маркетплейсы могут дать возможность продавцам создавать интернет-магазины с оформлением заказов через свою платформу, тем самым расширить количество источников привлечения покупателей.
### Интернет-магазины
Интернет-магазины которые хотят реализовать функционал маркетплейса на своих сайтах, могут использовать API Shop как дополнительный инструмент продаж.
### Франшиза PLLANO
Если вы не хотите разрабатывать платформу своими силами, а хотите получить готовый продукт, вы можете подать заявку на получение франшизы [Franchise PLLANO](https://github.com/pllano/Franchise-PLLANO). 

## «API Shop» — Работает без своей базы данных
«API Shop» может работать без базы данных и все данные получать через API платформы на которой он создан. Также может иметь свое собственное хранилище данных, главная задача которого обеспечить быструю отдачу контента (кэширование) и бесперебойную работу сайта при сбоях или недоступности API от которой она получает данные.

Поддерживаются следующие системы управления базами данных:
- [API json DB](https://github.com/pllano/api-json-db) транзитом через классы [Bridge](https://github.com/pllano/api-shop/blob/master/app/classes/Db/Bridge.php) и [JsonDb](https://github.com/pllano/api-shop/blob/master/app/classes/Db/JsonDb.php)
- MySQL с использованием [slim/pdo](https://packagist.org/packages/slim/pdo) транзитом через классы [Bridge](https://github.com/pllano/api-shop/blob/master/app/classes/Db/Bridge.php) и [MysqlDb](https://github.com/pllano/api-shop/blob/master/app/classes/Db/MysqlDb.php)
- Elasticsearch с использованием [Elasticsearch-PHP](https://github.com/elastic/elasticsearch-php) транзитом через классы [Bridge](https://github.com/pllano/api-shop/blob/master/app/classes/Db/Bridge.php) и [ElasticsearchDb](https://github.com/pllano/api-shop/blob/master/app/classes/Db/ElasticsearchDb.php)

`Bridge` работает роутером подключения к классам баз данных и дает возможность писать один код для всех баз данных.
```php
// Используем Bridge
use Pllano\ApiShop\Db\Bridge;
$db_name = $this->get('settings')['db']['name']; // name = elasticsearch
$db = new Bridge($db_name);
$db->get($resource, $arr, $id);

// Аналогично коду
use Pllano\ApiShop\Db\ElasticsearchDb as Elasticsearch;
$db = new Elasticsearch();
$db->get($resource, $arr, $id);
```

## Собственный стандарт обмена данными
API Shop — Использует собственный стандарт обмена данными сервер-сервер [APIS-2018](https://github.com/pllano/APIS-2018/) дающий возможность не писать своей документации по работе с вашим API. Вы можете писать свою API зная что другим API использующим стандарт APIS-2018 не придется тратиться на дополнительную доработку и интеграцию с вашим API. Для подключения к вашему API будет необходимо только получить данные аутентификации для доступа к учетной записи.

## Demo
Интернет-магазин [life24.com.ua](https://life24.com.ua/) работает через API платформы [PLLANO Marketplace](https://pllano.com/) документация [PLLANO API](https://github.com/pllano/pllano-api)

## Зависимости

- Менеджер зависимостей: [Composer](https://getcomposer.org/) - [github](https://github.com/composer)
- База данных: [API json DB](https://github.com/pllano/api-json-db) - [packagist.org](https://packagist.org/packages/pllano/api-json-db)
- Интерфейс для HTTP-сообщений: [PSR-7](http://www.php-fig.org/psr/psr-7/) - [github](https://github.com/php-fig/http-message) - [packagist](https://packagist.org/packages/psr/http-message)
- Framework: [Slim](https://www.slimframework.com/) - [github](https://github.com/slimphp) - [packagist](https://packagist.org/packages/slim/slim)
- Шаблонизатор: [Twig](https://twig.symfony.com/) - [github](https://github.com/twigphp/Twig/)
- Управление Session & Cookies: [Slim Secure Session Middleware](https://github.com/adbario/slim-secure-session-middleware)
- HTTP client: [Guzzle](http://docs.guzzlephp.org/en/stable/) - [Guzzle](https://github.com/guzzle/guzzle) - [packagist](https://packagist.org/packages/guzzlehttp/guzzle)
- Логирование: [Monolog](https://github.com/Seldaek/monolog) - [packagist](https://packagist.org/packages/monolog/monolog)
- Тесты: [PHPUnit](https://phpunit.de/) - [github](https://github.com/sebastianbergmann/phpunit) - [packagist](https://packagist.org/packages/phpunit/phpunit)

```json
{
  "require": {
    "php": "^7.0",
    "pllano/api-json-db": "^1.0",
    "psr/http-message": "^1.0",
    "slim/slim": "^3.0",
    "slim/csrf": "^0.8.",
    "twig/twig": "~2.0",
    "phpunit/phpunit": "^6.4",
    "monolog/monolog": "^1.23",
    "guzzlehttp/guzzle": "^6.3",
    "defuse/php-encryption": "^v2.1",
    "adbario/slim-secure-session-middleware": "^1.3.4",
    "spatie/image-optimizer": "^1.0.9",
    "imagine/imagine": "~0.5.0"
  }
}
```

## Мы ищем единомышленников ! Присоединяйтесь.

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

