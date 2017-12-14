# Шаблоны в API Shop
«API Shop» — поддерживает индивидуальные шаблоны
## Структура директории `themes`
- /`app`/`js` - скрипты API Shop
- /`fonts` - шрифты
- /`lib` - подключаемые библиотеки
- /`templates` - шаблоны
### Бесплатные шаблоны
- /`templates`/`default` - базовый каркас для создания индивидуальных шаблонов
- /`templates`/`mini-mo` - шаблон по умолчанию. Создан на базе бесплатного шаблона [Mini-Mo](https://github.com/pllano/mini-mo)
## Структура директории `lib`
- /`lib`/`lib_name`/`version`/`js`/`lib_name.js`
- /`lib`/`lib_name`/`version`/`css`/`lib_name.css`

Такой подход позволяет избежать дублирования библиотек в различных шаблонах а также избежать конфликтов версий.
### Примеры
- /`lib`/`jquery`/`3.0.0`/`js`/`jquery-3.0.0.js`
- /`lib`/`animate`/`3.5.1`/`css`/`animate.css`
- /`lib`/`animate`/`3.5.1`/`css`/`animate.min.css`
- /`lib`/`bootstrap`/`4.0.0-beta.2`/`css`/`bootstrap.css`
- /`lib`/`bootstrap`/`4.0.0-beta.2`/`js`/`bootstrap.js`

 
