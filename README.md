
# E-Commerce Platform API Shop

![](https://github.com/pllano/api-shop/blob/master/themes/templates/mini-mo/img/logo.png)

API Shop — E-Commerce Platform (движок интернет-магазина), использует RESTful API сторонних платформ для хранения и обработки информации. Написан на PHP. Распространяется по лицензии [MIT](https://opensource.org/licenses/MIT).

### Использует
- [Composer](https://github.com/composer) - Менеджер зависимостей
- [PSR-2](http://www.php-fig.org/psr/psr-2/) – Рекомендации по оформлению кода
- [PSR-4](http://www.php-fig.org/psr/psr-4/) – Улучшенная автозагрузка
- [PSR-7](http://www.php-fig.org/psr/psr-7/) – Интерфейс HTTP-сообщений
- [Slim](https://github.com/slimphp) – [Slim Micro Framework](https://www.slimframework.com/)
- [Twig](https://github.com/twigphp/Twig/) – Шаблонизатор
- [Guzzle](https://github.com/guzzle/guzzle) – HTTP client
- [Monolog](https://github.com/Seldaek/monolog) – Логирование
- [PHPUnit](https://github.com/sebastianbergmann/phpunit) – Тесты
- [jsonDB](https://github.com/pllano/json-db) – База данных на json файлах

### Внимание ! API Shop в активной разработке.
- Выпуск `1.0.1-BETA-1` планируется в январе 2018 г.
- Первая стабильная версия `1.0.1` планируется в феврале-марте 2018 г.

### Демо сайт [pllano.eu](https://pllano.eu/) 
Работает на текущих на файлах API Shop. Здесь вы можете видеть реальный статус разработки.
### Пример внедрения API Shop
Интернет-магазин [life24.com.ua](https://life24.com.ua/) работает на API Shop `1.0.1-ALFA-1` через API платформы [PLLANO Marketplace](https://pllano.com/) документация [PLLANO API](https://github.com/pllano/pllano-api)

## Интернет-магазин как один из ваших сервисов
Предоставьте своим пользователям возможность создавать интернет-магазины на своей платформе (сайте) с максимальной интеграцией своих сервисов в эти интернет-магазины.
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

## «API Shop» — не имеет своей админ панели
«API Shop» рассчитан на то, что данные будет получать через API платформы на которой он создан, админ панель должна быть встроена в интерфейс этой платформы.

## «API Shop» — может работать без базы данных
«API Shop» может работать без базы данных и все данные получать и отдавать через API платформы на которой он создан. Также может иметь свое собственное хранилище данных, главная задача которого обеспечить быструю отдачу контента (кэширование) и бесперебойную работу сайта при сбоях или недоступности API от которой она получает данные.

Поддерживаются следующие системы хранения и управления данными через роутер [Database\Router](https://github.com/pllano/api-shop/blob/master/app/classes/Database/Router.php):
- работа через API (без своей базы данных) транзитом через клас [ApiDb](https://github.com/pllano/api-shop/blob/master/app/classes/Database/ApiDb.php)
- [jsonDB](https://github.com/pllano/json-db) позволяет работать напрямую, транзитом через клас [JsonDb](https://github.com/pllano/api-shop/blob/master/app/classes/Database/JsonDb.php)
- jsonapiDb - Вы можете хранить данные в [jsonDB](https://github.com/pllano/json-db) в любом месте (даже на удаленном сервере) и работать с ней через API интерфейс, транзитом через клас [jsonapiDb](https://github.com/pllano/api-shop/blob/master/app/classes/Database/jsonapiDb.php)
- MySQL с использованием [slim/pdo](https://packagist.org/packages/slim/pdo) транзитом через клас [MysqlDb](https://github.com/pllano/api-shop/blob/master/app/classes/Database/MysqlDb.php)
- Elasticsearch с использованием [Elasticsearch-PHP](https://github.com/elastic/elasticsearch-php) транзитом через клас [ElasticsearchDb](https://github.com/pllano/api-shop/blob/master/app/classes/Database/ElasticsearchDb.php)
- Без особых сложностей возможно написать клас для работы с любой другой базой данных.

### Резервная база данных
API Shop может переключатся между базами данных на лету, если основная база данных недоступна. Для этого необходимо в конфигурации указать названия обоих баз.
```php
// Название основной базы данных. По умолчанию api
$config["db"]["master"] = "api";
// Название резервной базы данных. По умолчанию json
$config["db"]["slave"] = "json"; // Рекомендуется оставить json
```
### Использовать несколько баз данных
API Shop позволяет одновременно работать с любым количеством баз данных. Название базы данных можно задать для каждого ресурса индивидуально. По умолчанию api.

`Database\Ping` контролирует состояние баз данных `master` и `slave`. Если база указанная в конфигурации `$resource` недоступна, подключит `master` или `slave` базу.
```php
// Цены получать через API
$config["resource"]["price"]["db"] = "api";
// Данные пользователей хранить в MySQL
$config["resource"]["user"]["db"] = "mysql";
// Свойтва товара хранить в Elasticsearch
$config["resource"]["params"]["db"] = "elasticsearch";
// Локализацию хранить в jsonDB
$config["resource"]["language"]["db"] = "json";
// Платежи хранить в Oracle
$config["resource"]["pay"]["db"] = "oracle";
```
### Встроенный роутер переключения между базами
`Database\Router` — роутер подключения к базам данных, дает возможность писать один код для всех баз данных а интеграцию вывести в отдельный класс для каждой базы данных.
```php
use ApiShop\Database\Router as Database;
use ApiShop\Database\Ping;

// Ресурс к которому обращаемся
$resource = "price";
// id записи. (Не обязательное поле) По умолчанию = null
$id = null;
// Массив с данными
$arr = [
    "limit" => 10,
    "offset" => 0,
    "order" => "DESC",
    "sort" => "created",
    "state" => 1,
    "relations" => base64_encode('{
        "product": ["type_id","brand_id","serie_id","articul"],
        "user": "all",
        "address": "all"
    }')
];
 
// Получаем название базы данных
$db_name = new Ping($resource);
// Подключаемся к базе
$db = new Database($db_name);
// Отправляем запрос
$db->get($resource, $arr, $id);
```
Обратите внимание на очень важный параметр запроса [`relations`](https://github.com/pllano/APIS-2018/blob/master/structure/relations.md) позволяющий получать в ответе необходимые данные из других связанных ресурсов.

## Конфигурация API Shop
Файлы и документация по конфигурации API Shop — [config](https://github.com/pllano/api-shop/tree/master/app/config)

## Собственный стандарт обмена данными
API Shop — Использует собственный стандарт обмена данными сервер-сервер [APIS-2018](https://github.com/pllano/APIS-2018/) дающий возможность не писать своей документации по работе с вашим API. Вы можете писать свою API зная что другим API использующим стандарт APIS-2018 не придется тратиться на дополнительную доработку и интеграцию с вашим API. Для подключения к вашему API будет необходимо только получить данные аутентификации для доступа к учетной записи.

## Зависимости
```json
{
  "require": {
    "php": "^7.0",
    "pllano/json-db": "^1.0.5",
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

