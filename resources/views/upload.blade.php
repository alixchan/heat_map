<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <title>Обновление {{$name}} </title>
</head>
<body class="vsc-initialized">

      @include('components/header')

      <div class="container-fluid">
        <main role="main" class="col-md-12 ml-sm-auto col-lg-12 pt-3 px-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
          <h1 class="h2">Загрузка файла {{$name}} </h1>
        </div>

        <div class="row">
            <div class="col-md-6">
            <form action='{{$function}}' method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                <label for={{$type}}>Выберите входной файл:</label><br><br>
                <input type="file" name={{$type}} accept=".xls,.xlsx" class="form-control-file" required>
                </div><br>
                <button type="submit" class="btn btn-success">Загрузить</button>
                <br><br>
                <div id="spinner" class="spinner-border text-success" role="status" style="display:none; width: 3rem; height: 3rem;">
                  <span class="sr-only"></span>
                </div>
            </form>
            </div>
        </div>

        @if(session('success'))
          <div class="alert alert-success mt-3" role="alert">
            {{ session('success') }}
          </div>
        @endif

        </main>
      </div>

</body>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

<script>
  document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('spinner').style.display = 'inline-block';
    document.getElementById('submit-btn').disabled = true;
  });
</script>
</html>
