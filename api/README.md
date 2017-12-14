# API Shop — имеет свою мини-API

RESTful API — Предназначен для обмена данными с платежными сервисами и транспортными компаниями

Формат обмена данными сервер-сервер и клиент-сервер по стандарту [APIS-2018](https://github.com/pllano/APIS-2018/)

Для подключения сервисов необходимо промежуточное ПО которое размещается в директории [services](https://github.com/pllano/api-shop/tree/master/api/services)

### RESTful API потдерживает `POST` `GET` `PUT` `PATCH` `DELETE` запросы:
- `POST /api/v1/json/{service_name}/{resource}` Создание записи
- `POST /api/v1/json/{service_name}/{resource}/{id}` Ошибка
- `GET /api/v1/json/{service_name}/{resource}` Список всех записей
- `GET /api/v1/json/{service_name}/{resource}?{param}` Список всех записей
- `GET /api/v1/json/{service_name}/{resource}/{id}` Данные конкретной записи
- `PUT /api/v1/json/{service_name}/{resource}` Обновить данные записей
- `PUT /api/v1/json/{service_name}/{resource}/{id}` Обновить данные конкретной записи
- `PATCH /api/v1/json/{service_name}/{resource}` Обновить данные записей
- `PATCH /api/v1/json/{service_name}/{resource}/{id}` Обновить данные конкретной записи
- `DELETE /api/v1/json/{service_name}/{resource}` Удалить все записи
- `DELETE /api/v1/json/{service_name}/{resource}/{id}` Удалить конкретную запись

### Пример ответа от LiqPay в виде GET запроса
- `https://example.com/api/v1/json/liqpay?action=pay&payment_id=568015786&status=success&order_id=1234`
- или тоже самое но гораздо проще
- `https://example.com/api/v1/json/liqpay/pay/1234?status=success`

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

