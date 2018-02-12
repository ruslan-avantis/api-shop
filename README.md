
# «API Shop» — E-Commerce Platform
Написан на PHP. Распространяется по лицензии [MIT](https://opensource.org/licenses/MIT).
## `{API}$hop` — это конструктор
`{API}$hop` — находится между фреймворками и готовыми платформами. Имеет свою админ панель, систему управления пакетами, шаблонами и плагинами и все остальное чтобы быстро создать интернет-магазин или сайт. Использует `Micro Framework Slim` который отвечает за роутинг (то что у него получается лучше всего), при этом есть возможность использовать компоненты `Symfony`, `Laravel` и любого другого фреймворка. Вы можете менять классы обработки на свои или на любые другие.
## Отличная скорость работы без дополнительных оптимизаций
`{API}$hop` — способен выдерживать огромную посещаемость и подойдет для высоконагруженных проектов. Если вы запустите обычный интернет-магазин или Landing Page - вы получите сайт который открывается со скоростью мысли. 
- Демо - https://xti.com.ua/
- Тест скорости - https://developers.google.com/speed/pagespeed/insights/?url=https://xti.com.ua
## Низкий порог входа для программистов
Самым главным преимуществом API Shop является низкий порог входа для разработчиков. Мы стараемся писать максимально просто ! За несколько дней с платформой разберется даже начинающий программист.
### [Документация по установке](https://github.com/pllano/api-shop/blob/master/INSTALL.md)
### [Документация по конфигурации](https://github.com/pllano/api-shop/blob/master/app/README.md)
### Мы ищем единомышленников ! Присоединяйтесь.

Не важно какой шаблонизатор, кеширование или базу данных хотите использовать.
## API Shop — из коробки будет поддерживать:
- Автозагрузчики и управление пакетами: `Composer` и [`AutoRequire`](https://github.com/pllano/auto-require) + `PackageManager` + `PackageRouter`
- Поддерживает подключение сторонних классов через [Hooks](https://github.com/pllano/hooks) без внесения изменений в код API Shop
- Шаблонизаторы: - [`Twig`](https://github.com/twigphp/Twig) [`PhpRenderer`](https://github.com/slimphp/PHP-View) [`Smarty`](https://github.com/smarty-php/smarty) [`Dwoo`](https://github.com/dwoo-project/dwoo) [`Fenom`](https://github.com/fenom-template/fenom)  [`Mustache`](https://github.com/bobthecow/mustache.php) [`Blade`](https://github.com/PhiloNL/Laravel-Blade) `Volt`
- [Кеширование](https://github.com/pllano/cache): `Memcached`, `Memcache`, `Redis`, `Predis`, `Filesystem`, `JsonCache`, `MongoDB`, `Elasticsearch`, `Array`, `Apcu`, `Apc` + `illuminate`, `Doctrine`
- Управление данными [routerDb](https://github.com/pllano/router-db)
- Хранение данных: `RESTful API`, [`JsonDB`](https://github.com/pllano/json-db), `MySQL`, `PostgreSQL`, `MongoDB`, `SQLite`, `MariaDB`, `Redis`, `Elasticsearch` (каждая таблица может работать с любой из поддерживаемых баз данных благодаря routerDb который дает один интерфейс для работы со всеми базами данных).
- HTTP клиенты: `Guzzle`, `Buzz`, `Httplug`, `Httpful`, `Requests`, `Yii2 Httpclient`, `Unirest PHP`
- [Обработчики изображений](https://github.com/pllano/router-image): `Imagine`, `Intervention`, `Spatie`
## Требования к хостингу
### Для работы API Shop необходим хостинг, который поддерживает:
- PHP версии 7 или выше
- Протокол HTTPS
- Расширение PHP: `openssl` `zip`

Для кеширования необходимо установить
- Расширение PHP: `memcache` `memcached`
- Требуемое хранилище (По умолчанию используется файловое кеширование)

![](https://github.com/pllano/api-shop/blob/master/themes/templates/mini-mo/img/logo.png)

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

