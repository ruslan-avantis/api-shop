# Установка API Shop
## Установка с помощью `public_key`
- Скачайте [`/install.php`](https://raw.githubusercontent.com/pllano/api-shop/master/install.php)
- Скопируйте `install.php` в корень вашего сайта `http://example.com/`
- Перейдите по ссылке `http://example.com/install.php`
- Введите public_key
- Выберите дизайн (шаблон) сайта
- API Shop уже работает !

При активации с помощью `public_key` система автоматически создаст пользователя, для того чтобы вы могли попасть в админ панель. Для перехода в админ панель в правом верхнем меню нажмите на имя и фамилию - Admin Admin.

Для установки Демо версии `public_key` - test

### Пользователи по умолчанию:
#### 1
- телефон: 380670101010
- email: admin@example.com
- пароль: admin12345
#### 2
- телефон: 380670010011
- email: admin@pllano.com
- пароль: admin@pllano.com
### Обязательно после установки поменяйте данные и пароли пользователей по умолчанию !!!

## Установка с созданием учетной записи
- Скачайте [`/install.php`](https://raw.githubusercontent.com/pllano/api-shop/master/install.php)
- Скопируйте `install.php` в корень вашего сайта `http://example.com/`
- Перейдите по ссылке `http://example.com/install.php`
- Создайте учетную запись
- Выбирите тип магазина (набор товаров)
- Выберите дизайн (шаблон) сайта
- API Shop уже работает !

После установки вы автоматически будете авторизованны как администратор под своей учетной записью. Для перехода в админ панель в правом верхнем меню нажмите на свое имя и фамилию.

`P.S.` Во время первого запуска, загрузка страницы может длится до 60 секунд, в связи с тем что [AutoRequire](https://github.com/pllano/auto-require) скачивает необходимые компоненты (зависимости).

## Требования к хостингу
### Для работы API Shop необходим хостинг, который поддерживает:
- PHP версии от 7.0.25 до 7.2.2
- Протокол HTTPS
- Расширение PHP: `openssl` `zip`
- Для кеширования необходимо установить требуемое хранилище кеша (по умолчанию используется файловое кеширование) и расширение PHP: `memcache` `memcached` или другое.
### Настройки `php.ini`
- `max_execution_time` = 120 или 240 (по умолчанию 30)
- `memory_limit` = 512 или 1024 (по умолчанию 128)

Эти параметры нужны для установки API Shop и загрузки пакетов с помощью 
[install.php](https://github.com/pllano/api-shop/blob/master/install.php). 
При работе он менее требователен.

## Пошаговая инструкция по ручной установке API Shop
### 1. Скачать дистрибутив
Рекомендуем скачивать последнюю версию дистрибутива по ссылке: [api-shop-master.zip](https://github.com/pllano/api-shop/archive/master.zip)
### 2. Переместить файлы в корень сайта
Переместите необходимы следующие директории и файлы в корень вашего сайта:
- `/api/` - API
- `/app/` - Ядро
- `/cache/` - папка хранения кеша
- `/images/` - изображения
- `/themes/` - шаблоны
- `/vendor/` - пакеты
- `/.htaccess`
- `/index.php`
### 3. Установка зависимостей
#### С помощью [AutoRequire](https://github.com/pllano/auto-require)
`AutoRequire` идет в комплекте с API Shop и является его компонентом. Он полностью настроен и вы можете ничего не менять. `AutoRequire` - проверит и загрузит все необходимые пакеты самостоятельно в папку `/vendor`
``` php
$vendor_dir = '';
// Ищем путь к папке vendor
if (file_exists(BASE_PATH . '/vendor')) {
    $vendor_dir = BASE_PATH . '/vendor';
} elseif (BASE_PATH . '/../vendor') {
    $vendor_dir = BASE_PATH . '/../vendor';
}

// Указываем путь к AutoRequire
$autoRequire = $vendor_dir.'/AutoRequire.php';
// Указываем путь к auto_require.json
$auto_require = $vendor_dir.'/auto_require.json';
 
if (file_exists($autoRequire) && file_exists($auto_require)) {
 
    // Connect \Pllano\AutoRequire\Autoloader
    require $autoRequire;
    // instantiate the loader
    $require = new \Pllano\AutoRequire\Autoloader();
    // Запускаем Автозагрузку
    $require->run($vendor_dir, $auto_require);
    
}
```
Если вы хотите сконфигурировать под себя необходимо подключить или отредактировать [auto_require.json](https://github.com/pllano/auto-require/blob/master/auto_require.json)

Если у вас специфическая конфигурация структуры файлов, в файле [`/index.php`](https://github.com/pllano/api-shop/blob/master/index.php) необходимо прописать пути ко всем необходимым файлам.

## Конструктор - Настраивай так как привык
### 4. Конфигурация
За конфигурацию API Shop отвечает файл [`/app/settings.php`](https://github.com/pllano/api-shop/blob/master/app/settings.php), сама конфигурация хранится в файле [`/app/settings.json`](https://github.com/pllano/api-shop/blob/master/app/settings.json)
