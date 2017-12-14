# API Shop — имеет свою мини-API

«API Shop» — имеет своюмини RESTful API 

Формат обмена данными сервер-сервер и клиент-сервер по стандарту [APIS-2018](https://github.com/pllano/APIS-2018/)

### RESTful API потдерживает `POST` `GET` `PUT` `PATCH` `DELETE` запросы:
- `POST /api/v1/json/services/{service_name}` Создание записи с параметрами в теле запроса
- `POST /api/v1/json/services/{service_name}/{id}` Ошибка
- `GET /api/v1/json/services/{service_name}` Список всех записей
- `GET /api/v1/json/services/{service_name}?{param}` Список всех записей с фильтром по параметрам
- `GET /api/v1/json/services/{service_name}/{id}` Данные конкретной записи
- `PUT /api/v1/json/services/{service_name}` Обновить данные записей с параметрами в теле запроса
- `PUT /api/v1/json/services/{service_name}/{id}` Обновить данные конкретной записи с параметрами в теле запроса
- `PATCH /api/v1/json/services/{service_name}` Обновить данные записей с параметрами в теле запроса
- `PATCH /api/v1/json/services/{service_name}/{id}` Обновить данные конкретной записи с параметрами в теле запроса
- `DELETE /api/v1/json/services/{service_name}` Удалить все записи с параметрами в теле запроса
- `DELETE /api/v1/json/services/{service_name}/{id}` Удалить конкретную запись

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

