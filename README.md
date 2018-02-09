
# «API Shop» — E-Commerce Platform
Написан на PHP. Распространяется по лицензии [MIT](https://opensource.org/licenses/MIT).

API Shop — это конструктор который находится между фреймворками и готовыми платформами имеющий админ панель, систему управления пакетами, шаблонами и плагинами и все остальное чтобы быстро создать интернет-магазин или сайт, использует Micro Framework Slim который отвечает за роутинг (то что у него получается лучше всего), при этом есть возможность использовать компоненты Symfony, Laravel и любого другого фреймворка. Имеет отличную скорость работы без дополнительных оптимизаций. 

Не важно какой шаблонизатор, систему кеширования или базу данных хотите использовать.
## API Shop — из коробки будет поддерживать:
Автозагрузчики и управление пакетами: `Composer` и `AutoRequire` + `PackageManager` + `PackageRouter`
Шаблонизаторы: `Twig`, `Smarty`, `Blade`, `Mustache`, `PhpRenderer`
Кеширование: `Memcached`, `Memcache`, `Redis`, `Predis`, `Filesystem`, `JsonCache`, `MongoDB`, `Elasticsearch`, `Array`, `Apcu`, `Apc` + `illuminate`, `Doctrine`
Хранение данных: `RESTful API`, `JsonDB`, `MySQL`, `PostgreSQL`, `MongoDB`, `SQLite`, `MariaDB`, `Redis`, `Elasticsearch` (каждая таблица может работать с любой из поддерживаемых баз данных благодаря routerDb который дает один интерфейс для работы со всеми базами данных).
HTTP клиенты: `Guzzle`, `Buzz`, `Httplug`, `Httpful`, `Requests`, `Yii2 Httpclient`, `Unirest PHP`
Обработчики изображений: `Imagine`, `Intervention`, `Spatie`

## Требования к хостингу
### Для работы API Shop необходим хостинг, который поддерживает:
- PHP версии 7 или выше
- Протокол HTTPS
- Расширение PHP: `openssl` `zip`

![](https://github.com/pllano/api-shop/blob/master/themes/templates/mini-mo/img/logo.png)
## Демо версия API Shop — [DEMO](https://github.com/pllano/api-shop/blob/master/DEMO.md)

### [Документация по установке](https://github.com/pllano/api-shop/blob/master/INSTALL.md)
### [Документация по конфигурации](https://github.com/pllano/api-shop/blob/master/app/config/README.md)

## Собственный стандарт обмена данными
API Shop — Использует собственный стандарт обмена данными сервер-сервер [APIS-2018](https://github.com/pllano/APIS-2018/) дающий возможность не писать своей документации по работе с вашим API. Вы можете писать свою API зная что другим API использующим стандарт APIS-2018 не придется тратиться на дополнительную доработку и интеграцию с вашим API. Для подключения к вашему API будет необходимо только получить данные аутентификации для доступа к учетной записи.

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

