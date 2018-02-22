# Конфигурация API Shop
За конфигурацию API Shop отвечают два файла:
- [`/app/settings.php`](https://github.com/pllano/api-shop/blob/master/app/settings.php) - класс отдающий конфигурацию скриптам
- [`/app/settings.json`](https://github.com/pllano/api-shop/blob/master/app/settings.json) - файл в котором хранится конфигурация

## Колоссальные возможности конфигурации 
{API}$hop — это конструктор с огромной гибкостью
К примеру вы можете менять:
- Глобальную логику url - `[routers]`
- URL Админ панели - `[routers][admin]`
- Настройки админ панели - `[admin]`
- Визуальный редактор -`[admin][editor]`
- Директории хранения файлов системы - `[dir]`
- Подключение к базам данных - `[db]`
- и много другого
## Разделы конфигурации
- `[cache]` - Кэширование
- `[seller]` - Настройки продавца
- `[payments]` - Платежи
- `[dir]` - Глобальные директории
- `[vendor]` - Пакеты
- `[routers]` - Роутинг
- `[modules]` - Модули
- `[hooks]` - Хуки
- `[admin]` - Настройки админ панели
- `[template]` - Управление шаблонами
- `[settings]` - Настройки
- `[db]` - Базы данных
### `[seller]` - Настройки продавца
- `[seller][name]` - название вкладки - по умолчанию: seller
- `[seller][public_key]` - публичный ключ продавца
- `[seller][private_key]` - приватный ключ продавца
- `[seller][alias]` - alias продавца на платформе (не менять!)
- `[seller][download_dir]` - папка файлов продавца на платформе (не менять!)
- `[seller][download_alias]` - папка файлов продавца на платформе (не менять!)
- `[seller][terms_of_delivery]` - условия доставки
- `[seller][currency_code]` - код валюты - по умолчанию:UAH
- `[seller][currency_id]` - id валюты - по умолчанию: `1` (UAH)
- `[seller][store]` - Тип магазина или набор товаров - по умолчанию: `1`
### settings - Конфигурация сайта
- `language` - по умолчанию: `ru`
- `http-codes` - по умолчанию: `https://github.com/pllano/APIS-2018/tree/master/http-codes/`
- `debug` - по умолчанию: `1`
- `displayErrorDetails` - по умолчанию: `1`
- `addContentLengthHeader` - по умолчанию: `0`
- `determineRouteBeforeAppMiddleware` - по умолчанию: `1`
- `cookies.httponly` - по умолчанию: `1`
- `phpSettings.session.cookie_httponly` - по умолчанию: `1`
- `rebodys.session.cookie_httponly` - по умолчанию: `1`
#### settings: `site` - настройка сайта
- `demo_panel` - активация демо панели шаблона - по умолчанию: `1` активна
- `title` - по умолчанию: `API Shop`
- `description` - по умолчанию: `API Shop`
- `keywords` - по умолчанию: `API Shop`
- `robots` - по умолчанию: `index, follow`
- `og_title` - по умолчанию: `API Shop`
- `og_description` - по умолчанию: `API Shop`
- `og_image` - по умолчанию: `null`
- `og_locale` - по умолчанию: `ru_RU`
- `og_type` - по умолчанию: `site`
- `og_url` - по умолчанию: `null`
- `cookie_httponly` - по умолчанию: `0`
#### settings: `session` - настройка session
- `name` - по умолчанию: `_session`
- `lifetime` - по умолчанию: `48`
- `path` - по умолчанию: `/`
- `domain` - по умолчанию: `null`
- `secure` - по умолчанию: `0`
- `httponly` - по умолчанию: `0`
- `cookie_autoset` - по умолчанию: `1`
- `save_path` - по умолчанию: `0`
- `cache_limiter` - по умолчанию: `nocache`
- `autorefresh` - по умолчанию: `1`
- `encryption_key` - по умолчанию: `0`
- `namespace` - по умолчанию: `_session`

#### settings: `themes` - настройка шаблонов
- `template` - название шаблона - по умолчанию: `mini-mo`
- `dir_name` - глобальная директория - по умолчанию: `/../../themes`
- `templates` - директория самих шаблонов - по умолчанию: `templates`

### admin - Настройки админ-панели

### dir - Глобальные папки

### vendor - Управление классами обработки
API Shop жестко не привязана к сторонним классам обработки. Вы можете подключать свои классы.

### classes - Управление дополнительными классами

### routers - Настройка роутинга
API Shop дает возможность настраивать роутинг (маршрутизацию) URL как вам угодно.

### db - Управление базами данных
По умолчанию API Shop использует два источника данных PLLANO API и JsonDB и не требует подключения к базе данных MySQL. При желании вы можете для каждого ресурса (таблицы) подключать свою базу данных. API Shop может одновременно работать с любым количеством баз данных.
