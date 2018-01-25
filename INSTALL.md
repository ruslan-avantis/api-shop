# Установка API Shop
## Установка с помощью `public_key`
- Скачайте [`/install.php`](https://raw.githubusercontent.com/pllano/api-shop/master/install.php)
- Скопируйте `install.php` в корень вашего сайта `http://example.com/`
- Перейдите по ссылке `http://example.com/install.php`
- Введите public_key
- Выберите дизайн (шаблон) сайта
- API Shop уже работает !

При активации с помощью `public_key` система автоматически создаст пользователя, для того чтобы вы могли попасть в админ панель.

Пользователь по умолчанию:
- телефон: 0670101010
- email: admin@example.com
- пароль: admin12345

## Установка с созданием учетной записи
- Скачайте [`/install.php`](https://raw.githubusercontent.com/pllano/api-shop/master/install.php)
- Скопируйте `install.php` в корень вашего сайта `http://example.com/`
- Перейдите по ссылке `http://example.com/install.php`
- Создайте учетную запись
- Выбирите тип магазина (набор товаров)
- Выберите дизайн (шаблон) сайта
- API Shop уже работает !

`P.S.` Во время первого запуска, загрузка страницы может длится до 60 секунд, в связи с тем что [AutoRequire](https://github.com/pllano/auto-require) скачивает необходимые компоненты (зависимости).

## Требования к хостингу
### Для работы API Shop рекомендуется хостинг, который поддерживает:
- PHP версии 7 или выше
- Протокол HTTPS
- расширение PHP для CGI `zip`

## Пошаговая инструкция по установке API Shop
### 1. Скачать дистрибутив
Рекомендуем скачивать последнюю версию дистрибутива по ссылке: [api-shop-master.zip](https://github.com/pllano/api-shop/archive/master.zip)
### 2. Переместить файлы в корень сайта
Переместите необходимы следующие директории и файлы в корень вашего сайта:
- `/api/`
- `/app/`
- `/images/`
- `/themes/`
- `/vendor/`
- `/.htaccess`
- `/index.php`
### 3. Установка зависимостей
#### С помощью [Composer](https://getcomposer.org/)
```php
require __DIR__ . '/../vendor/autoload.php';
```
```json
{
  "require": {
    "php": "^7.0",
    "pllano/router-db": "^1.0.4",
    "pllano/json-db": "^1.0.7",
    "psr/http-message": "^1.0",
    "slim/slim": "^3.0",
    "twig/twig": "~2.0",
    "phpunit/phpunit": "^6.4",
    "monolog/monolog": "^1.23",
    "guzzlehttp/guzzle": "^6.3",
    "defuse/php-encryption": "^v2.1",
    "adbario/slim-secure-session-middleware": "^1.3.4",
    "sinergi/browser-detector": "^6.1.2",
    "imagine/imagine": "~0.5.0"
    
  }
}
```
#### С помощью [AutoRequire](https://github.com/pllano/auto-require)
`AutoRequire` идет в комплекте с API Shop и является его компонентом. Он полностью настроен и вы можете ничего не менять. `AutoRequire` - проверит подключен ли Composer, если нет, загрузит все необходимые пакеты самостоятельно в папку `/vendor`
``` php
// Connect \AutoRequire\Autoloader
require __DIR__ . '/vendor/AutoRequire.php';
 
// instantiate the loader
$require = new \AutoRequire\Autoloader;
 
// Указываем путь к папке vendor для AutoRequire
$vendor_dir = __DIR__ . '/vendor';
 
// Указываем путь к auto_require.json
// Использовать стабильные версии пакетов
$auto_require = __DIR__ . '/vendor/auto_require.json';
// Использовать master версии пакетов
$auto_require_master = __DIR__ . '/vendor/auto_require_master.json';
// Для подключения ядра API Shop
$auto_require_min = __DIR__ . '/vendor/auto_require_min.json';
 
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
 
    // Запускаем Автозагрузку
    $require->run($vendor_dir, $auto_require_min);
 
    // Подключаем Composer
    require __DIR__ . '/../vendor/autoload.php';
 
} else {
 
    // Запускаем Автозагрузку без Composer
    $require->run($vendor_dir, $auto_require);
 
}
 
```
Если вы хотите сконфигурировать под себя необходимо подключить или отредактировать один из файлов [auto_require.json](https://github.com/pllano/auto-require/blob/master/auto_require.json) или [auto_require_master.json](https://github.com/pllano/auto-require/blob/master/auto_require_master.json).

В файле [`/index.php`](https://github.com/pllano/api-shop/blob/master/index.php) необходимо прописать пути ко всем необходимым файлам.

### 4. Конфигурация
За конфигурацию API Shop отвечает файл [`/app/config/settings.php`](https://github.com/pllano/api-shop/blob/master/app/config/settings.php)
#### Конфигурация `jsonDB` и `routerDb`
Большую часть в файле [`/app/config/settings.php`](https://github.com/pllano/api-shop/blob/master/app/config/settings.php) занимают настройки для:
- [jsonDB](https://github.com/pllano/router-db)
- [routerDb](https://github.com/pllano/json-db)
 
