<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Reviews Comments App</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="{{ secure_asset('css/app.css') }}">

</head>
<body>
    <div id="app">
        <div class="container">
            <example-component></example-component>
        </div>
    </div>

    <script type="text/javascript" src="{{ secure_asset('js/app.js') }}"></script>
</body>
</html>
