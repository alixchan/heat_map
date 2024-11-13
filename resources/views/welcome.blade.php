<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <title>@yield('title')</title>
</head>
<body class="vsc-initialized">

    @include('components/header')

    <div class="container-fluid">
        <main class="col-md-9 ml-sm-auto col-lg-10 px-4">
          <h1 class="pt-5 m-5 display-5">Инструмент диагностики IT-архитектора</h1>
        </main>
    </div>

</body>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</html>
