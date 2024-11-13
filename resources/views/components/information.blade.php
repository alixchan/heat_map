@if(session('success'))
    <div class="alert alert-success mt-3" role="alert">
        {{ session('success') }}
    </div>
@elseif(session('error'))
    <div class="alert alert-warning mt-3" role="alert">
        {{ session('error') }}
    </div>
@endif

@if(isset($validation) && count($validation) > 0)
    <div class="alert alert-light mt-3" role="alert">
        <h5 class="alert-heading">Замечания при валидации</h5>
        <small class="form-text text-muted">приведены номера строк исходного документа</small>
        <ul class="mb-0">
            @foreach($validation as $issue)
                <li><strong>Строка {{ $issue['row'] }}</strong> - {{ $issue['issue'] }}</li>
            @endforeach
        </ul>
    </div>
@endif