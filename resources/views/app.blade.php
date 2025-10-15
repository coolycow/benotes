<!doctype html>
<html lang="en" class="min-h-full">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Основные SEO теги -->
    <title>Benotes NEXT - Открытая платформа для заметок и закладок</title>
    <meta name="description" content="Benotes NEXT - это бесплатное приложение с открытым исходным кодом для сохранения заметок и закладок в одном месте. Поддерживает Markdown, PWA, самостоятельный хостинг и автоматическое сохранение ссылок с изображениями.">
    <meta name="keywords" content="заметки, закладки, приложение, PWA, markdown, самостоятельный хостинг, открытый код, Laravel, органайзер информации">
    <meta name="author" content="Benotes NEXT">
    <meta name="robots" content="index, follow">
    <meta name="language" content="ru">

    <!-- Open Graph теги -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Benotes NEXT">
    <meta property="og:title" content="Benotes NEXT - Открытая платформа для заметок и закладок">
    <meta property="og:description" content="Сохраняйте заметки и закладки рядом друг с другом. Поддержка Markdown, PWA установки, самостоятельный хостинг. Автоматическое сохранение ссылок с изображениями и описаниями.">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ url('/logo_144x144.png') }}">
    <meta property="og:image:alt" content="Логотип Benotes NEXT - приложение для заметок и закладок">
    <meta property="og:image:width" content="144">
    <meta property="og:image:height" content="144">
    <meta property="og:locale" content="ru_RU">

    <!-- Twitter Card теги -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="Benotes NEXT - Открытая платформа для заметок и закладок">
    <meta name="twitter:description" content="Сохраняйте заметки и закладки рядом друг с другом. Поддержка Markdown, PWA установки, самостоятельный хостинг.">
    <meta name="twitter:image" content="{{ url('/apple-touch-icon.png') }}">
    <meta name="twitter:image:alt" content="Логотип Benotes NEXT">

    <!-- Дополнительные мета теги -->
    <meta name="application-name" content="Benotes NEXT">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ url('/apple-touch-icon.png') }}">
    <meta name="theme-color" content="#ffffff">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Favicons и иконки -->
    <link rel="shortcut icon" type="image/png" href="/apple-touch-icon.png">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="144x144" href="/apple-touch-icon.png">

    <!-- PWA манифест -->
    <link rel="manifest" href="/manifest.json">

    <!-- Стили -->
    <link href="/css/inter.css" type="text/css" rel="stylesheet">
    <link href="{{ mix('css/app.css') }}" type="text/css" rel="stylesheet">

    <!-- Структурированные данные (JSON-LD) -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "WebApplication",
            "name": "Benotes NEXT",
            "description": "Открытое приложение для сохранения заметок и закладок с поддержкой Markdown и самостоятельного хостинга",
            "url": "{{ url()->current() }}",
        "applicationCategory": "ProductivityApplication",
        "operatingSystem": "Web Browser",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        },
        "author": {
            "@type": "Organization",
            "name": "Benotes NEXT"
        },
        "softwareVersion": "Beta",
        "releaseNotes": "Приложение с открытым исходным кодом для заметок и закладок",
        "featureList": [
            "Автоматическое сохранение ссылок с изображениями",
            "Поддержка Markdown и Rich Text редактора",
            "Установка как PWA приложение",
            "Возможность самостоятельного хостинга",
            "Создание коллекций и тегов",
            "Публичное совместное использование коллекций",
            "Ежедневные резервные копии"
        ]
    }
    </script>
</head>

<body class="h-full">
    <div id="app" class="h-full">
        <div class="w-full mx-auto h-full">
            <router-view></router-view>
        </div>
    </div>
    <script src="{{ mix('js/app.js') }}"></script>
</body>

</html>
