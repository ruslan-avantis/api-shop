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

## Пример внедрения API Shop
Интернет-магазин [life24.com.ua](https://life24.com.ua/) работает на API Shop `1.0.1-ALFA-1` через API платформы [PLLANO Marketplace](https://pllano.com/)
