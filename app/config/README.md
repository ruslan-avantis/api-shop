# Конфигурация API Shop
За конфигурацию API Shop отвечают два файла:
- `settings.php` - класс отдающий конфигурацию скриптам
- `settings.json` - файл в котором хранится конфигурация
## Колоссальные возможности конфигурации
По умолчанию API Shop использует два источника данных PLLANO API и JsonDB и не требует подключения к базе данных MySQL. При желании вы можете для каждого ресурса (таблицы) подключать свою базу данных. API Shop может одновременно работать с любым количеством баз данных.

К примеру вы можете менять:
- Глобальную логику url - `routers`
- URL Админ панели - `routers` `admin`
- Настройки админ панели - `admin`
- Визуальный редактор - `admin` `editor`
- Класс формирующий меню сайта - `vendor` `menu`
- Директории хранения файлов системы - `dir`
- Подключение к базам данных - `db`

## Разделы конфигурации
### seller - Настройки продавца
- `name` - название вкладки - по умолчанию: seller
- `public_key` - публичный ключ продавца
- `private_key` - приватный ключ продавца
- `alias` - alias продавца на платформе (не менять!)
- `download_dir` - папка файлов продавца на платформе (не менять!)
- `download_alias` - папка файлов продавца на платформе (не менять!)
- `terms_of_delivery` - условия доставки
- `currency_code` - код валюты - по умолчанию:UAH
- `currency_id` - id валюты - по умолчанию: `1` (UAH)
- `store` - Тип магазина или набор товаров - по умолчанию: `1`
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

### classes - Управление дополнительными классами

### routers - Настройка роутинга

### db - Управление базами данных
