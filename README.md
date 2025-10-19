# Benotes NEXT
Изменённая версия Benotes.

## Изменения:
- Обновлены все пакеты;
- Доработана типизация (PHP 8.3);
- Доработаны карточки постов;
- Добавлена регистрация по Email и коду;
- Удалён вход по паролю;
- Доработана система общего доступа к коллекциям;
- Добавлен Redis для очередей и кэширования;
- Добавлен Horizon для мониторинга очередей;
- Множество мелких изменений и фиксов.

## Основное
Для использования лучше всего использовать [Benotes Docker](https://github.com/coolycow/benotes-docker)

### Что используется
- NGINX Unit;
- MariaDB;
- Redis;
- Horizon.

### Установка
Скачиваем последнюю версию приложения:
```shell
git clone https://github.com/coolycow/benotes
```

Создаём .`env` из шаблона:
```shell
cp .env.example .env
```

Теперь необходимо доработать файл `.env`:
- Заполнить настройки базы данных;
- Заполнить настройки почты;
- Заполнить логин и пароль для Horizon.

Далее устанавливаем зависимости:
```shell
composer install
```

### Финал
После того как были сделаны все настройки достаточно запустить команду:
```shell
php artisan install
```
Данная команда установит все недостающие данные в `.env`, сгенерирует ссылку на хранилище и создаст нужных для начала работы пользователей.

**ВНИМАНИЕ**: при выполнении данной команды база проекта всегда очищается от всех данных!

### Обновление
Для обновления приложения требуется запустить несколько команд.

* Подтягиваем изменения из репозитория:
```shell
git pull
```

* Устанавливаем зависимости:
```shell
composer install
```

* Вносим нужные изменения в БД: 
```shell
php artisan migrate
```

* Очищаем кэш/настройки и далее проводим оптимизацию:
```shell
php artisan optimize:clear
php artisan optimize
```

## Изображения
### Автоматическая генерация
По умолчанию отсутствующие миниатюры генерируются каждые 2 часа.
Если вы хотите изменить это (например, сделать генерацию раз в неделю), добавьте следующее в свой .env:
```dotenv
THUMBNAIL_FILLER_INTERVAL="0 0 */7 * *"
```

### Создание миниатюр вручную
Миниатюры можно создать вручную, выполнив следующую команду:
```shell
php artisan thumbnail:generate
```

## Bookmarklet
Пакет позволяет использовать своеобразное расширение браузера и может быть использован в этом случае как ярлык для создания новых постов.

![Bookmarklet](https://raw.githubusercontent.com/coolycow/benotes/master/public/bookmarklet.gif)

### Как его установить
* Создать новую закладку в вашем браузере (любое название, например, Benotes);
* Добавить в поле URL код, который показан ниже. В этом коде необходимо заменить на второй строчке значение `https://YouNeedToChangeThat.com` на ваш ip-адрес или домен.

```javascript
javascript: (function() {
	var server = 'https://YouNeedToChangeThat.com';
	var applicationUrl = server + '/c/0/p/create';
	applicationUrl += '?url=' + encodeURIComponent(window.location);
	applicationUrl += '&auto_close';
	window.open(applicationUrl);
})();
```
Вот и все. Теперь вы можете посетить любой сайт и добавить его в закладки, нажав на недавно созданную закладку.

![Benotes Logo](https://raw.githubusercontent.com/coolycow/benotes/master/public/apple-touch-icon.png)

<h1 align="center">Benotes</h1>

![Benotes Thumbnail](https://user-images.githubusercontent.com/33751346/177018302-61f0e613-c7ff-40ff-b260-771f78489233.jpg)

An open source self hosted web app for your notes and bookmarks side by side.

This project is currently in **Beta**. You may encounter bugs or errors.

### Features

-   URLs are automatically saved with an image, title and description
-   supports both markdown and a rich text editor
-   can be installed as a PWA on your mobile devices (and desktop)
-   share content via this app (if installed as an PWA and supported by your browser)
-   collections can be shared via a public available URL
-   links can be instantly pasted as new posts
-   can be hosted almost anywhere thanks to its use of the lightweight Lumen framework and well supported PHP language
-   works with and without a persistent storage layer (both filesystem and S3 are supported)
-   can also be hosted via Docker or on Heroku
-   protect your data with daily backups

## Installation & Upgrade

Currently their are three options for you to choose from:

-   [Normal classical way](https://benotes.org/docs/installation/classic)
-   [Docker](https://benotes.org/docs/installation/docker)
-   [Docker Compose](https://benotes.org/docs/installation/docker-compose)
-   [Heroku](https://benotes.org/docs/installation/heroku) ([not free anymore](https://blog.heroku.com/next-chapter))

## Additional Features

-   [Backups](https://benotes.org/docs/extras/backup)
-   [Bookmarklet](https://benotes.org/docs/extras/bookmarklet)

## Issues

Feel free to [contact me](https://twitter.com/_fr0tt) if you need any help or open an [issue](https://github.com/fr0tt/benotes/issues) or a [discussion](https://github.com/fr0tt/benotes/discussions) or join the [subreddit](https://reddit.com/r/benotes).

Q: Having trouble with **reordering** posts ?

Use this command in order to fix it.

```
php artisan fix-position
```

or if you have already installed newer php versions on your system:

```
/usr/bin/php7.4 artisan fix-position
```

## Rest API

Further information can be found here: [Rest API Documentation](api.md)
