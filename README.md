### Внимание ! Файлы в процессе добавления. Не пробуйте устанавливать API Shop !
# E-Commerce Platform API Shop
API Shop — E-Commerce Platform (движок интернет-магазина) с открытым исходным кодом, использует RESTful API сторонних платформ для хранения и обработки информации. Написан на PHP с использованием [Micro Framework Slim](https://github.com/slimphp) который использует [PSR-7](http://www.php-fig.org/psr/psr-7/) интерфейс HTTP-сообщений. Использует шаблонизатор [Twig](https://github.com/twigphp/Twig/) и менеджер зависимостей для PHP — [Composer](https://github.com/composer). Распространяется по лицензии [MIT](https://opensource.org/licenses/MIT).
## Создавать интернет-магазины на вашей платформе
Предоставьте своим пользователям возможность создавать интернет-магазины на своей платформе с максимальной интеграцией своих сервисов в эти интернет-магазины.
### Банки
Банки — могут дать возможность своим клиентам создавать интернет-магазины с максимальной интеграцией своих сервисов (платежи, мгновенные кредиты итд.).
### Службы доставки
Службы доставки —  могут дать возможность своим клиентам создавать интернет-магазины в которых будет реализована возможность отгрузки заказа покупателю только через ихнюю службу.
### Маркетплейсы
Маркетплейсы —  могут дать возможность продавцам создавать интернет-магазины с оформлением заказов через свою платформу, тем самым расширить количество источников привлечения покупателей.
### Интернет-магазины
Интернет-магазины —  Лидеры рынка могут использовать API Shop как составляющую в реализации функционала маркетплейса на своих сайтах.

## «API Shop» — Работает без своей базы данных
— Может работать без базы данных и все данные получать через API платформы на которой он создан.

— Может иметь свое собственное хранилище данных главная задача которой обеспечить быструю отдачу контента (кэширование) и бесперебойную работу сайта при сбоях или недоступности API от которой она получает данные.

Поддерживаются следующие системы управления базами данных:
- [API json DB](https://github.com/pllano/api-json-db)
- MySQL
- Elasticsearch

## Собственный стандарт обмена данными
— Использует собственный стандарт обмена данными сервер-сервер [APIS-2018](https://github.com/pllano/APIS-2018/).

## Demo
[life24.com.ua](https://life24.com.ua/)

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

