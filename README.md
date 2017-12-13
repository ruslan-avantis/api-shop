# API Shop — e-commerce platform с открытым кодом

«API Shop» — Использует REST API торговых платформ для хранения и обработки информации. Написан на PHP с использованием [Micro Framework Slim](https://github.com/slimphp) который использует [PSR-7](http://www.php-fig.org/psr/psr-7/) интерфейс HTTP-сообщений. Использует шаблонизатор [Twig](https://github.com/twigphp/Twig/) и менеджер зависимостей для PHP — [Composer](https://github.com/composer). Распространяется по лицензии [MIT](https://opensource.org/licenses/MIT).

«API Shop» — Использует собственный стандарт [APIS-2018](https://github.com/pllano/APIS-2018/) — это формат обмена данными сервер-сервер и клиент-сервер. `Стандарт APIS-2018 - не является общепринятым !` Стандарт является взглядом в будущее и рекомендацией для унификации построения легких движков интернет-магазинов нового поколения.

«API Shop» — Может работать без базы данных, но при этом имеет свою собственную базу данных jsonDB главная задача которой обеспечить быструю отдачу контента (кэширование) и бесперебойную работу сайта при сбоях или недоступности API от которой она получает данные.

«jsonDB» — система управления базами данных с открытым исходным кодом которая использует JSON документы и схему базы данных. Написана на PHP. Подключается через Composer как обычный пакет PHP, после подключения сама настраивается за несколько секунд. Имеет свой RESTful API интерфейс, что позволяет использовать ее с любым другим языком программирования. Умеет ставить в очередь на запись при блокировке файлов другими процессами и кэшировать любые запросы.

## Demo
[life24.com.ua](https://life24.com.ua/)

## Зависимости

- Менеджер зависимостей: [Composer](https://getcomposer.org/) - [github](https://github.com/composer)
- База данных: [API json DB](https://github.com/pllano/api-json-db) - [packagist.org](https://packagist.org/packages/pllano/api-json-db)
- Интерфейс для HTTP-сообщений: [PSR-7](http://www.php-fig.org/psr/psr-7/) - [github](https://github.com/php-fig/http-message) - [packagist](https://packagist.org/packages/psr/http-message)
- Framework: [Slim](https://www.slimframework.com/) - [github](https://github.com/slimphp) - [packagist](https://packagist.org/packages/slim/slim)
- Шаблонизатор: [Twig](https://twig.symfony.com/) - [github](https://github.com/twigphp/Twig/)
- Управление Cookies: [FIG Cookies](https://github.com/dflydev/dflydev-fig-cookies)
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
    "dflydev/fig-cookies": "v1.0.*",
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

The MIT License (MIT). Please see [LICENSE](LICENSE.md) for more information.

