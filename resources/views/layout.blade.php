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
      <main role="main" class="col-md-12 pt-3 px-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
            <h1 class="h2">Загрузка файла @yield('title')</h1>
        </div>

        @include('components/requirements')

        <div class="row">
            <div class="col-md-6">
                <form action=@yield('function') method="POST" enctype="multipart/form-data">
                    @csrf

                    @include('components/upload')

                    <div class="d-flex">
                      <button type="submit" class="btn btn-success me-md-5" id="submit-btn">Загрузить</button>
                      @yield('button')
                    </div>

                    @yield('text_area')

                    @include('components/spinner')
                </form>
            </div>
        </div>

        @yield('content')

      </main>
    </div>

</body>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

<script>

document.addEventListener('DOMContentLoaded', function () {
    // Ищем все формы на странице
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function (event) {
            // Проверяем, если форма не для загрузки файла, показываем спиннер
            if (!form.classList.contains('no-spinner')) {
                document.getElementById('spinner').style.display = 'block';
            }
        });
    });
});

</script>


@yield('script')
    

</html>
