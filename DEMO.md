# Демонстрация работы API Shop

## Демо сайт
[xti.com.ua](https://xti.com.ua/) — Работает на текущих на файлах API Shop. Здесь вы можете видеть реальный статус разработки.

### Конфигурация демо сайта
- [Конфигурация](https://github.com/pllano/api-shop/blob/master/app/config/settings.php)
```php
// Название основной базы данных. По умолчанию api
$config["db"]["master"] = "api";
// Название резервной базы данных. По умолчанию jsonapi
$config["db"]["slave"] = "jsonapi";
```
#### По какому признаку мы храним данные в той или иной базе данных
- API - данные которые часто меняются
- Elasticsearch - данные которые записываются по накопительной и редко изменяются.
- json - данные которые имеют небольшой размер и не меняются
- MySQL - оставили статьи только для демонстрации работы

## Пример внедрения API Shop
Интернет-магазин [life24.com.ua](https://life24.com.ua/) работает на API Shop `1.0.1-ALFA-1` через API платформы [PLLANO Marketplace](https://pllano.com/) документация [PLLANO API](https://github.com/pllano/pllano-api)
