@extends('layout')

@section('title') анализа ЕСИС @endsection

@section('function') "{{ route('upload.act_esis.file') }}" @endsection

@section('type') "inputFile" @endsection

@section('button')
    <button type="button" class="btn btn-dark" id="use-ke-btn">Использовать список КЕ</button>
    <br>

@endsection

@section('text_area')
    <div id="ke-input-block" style="display:none;">
        <div class="form-group mt-4">
            <label for="ke-list">Введите список КЕ:</label>
            <textarea id="ke-list" name="ke_list" class="form-control" rows="4" placeholder="Введите текст"></textarea>
        </div><br>
        
        <button type="submit" class="btn btn-success" id="upload-ke-btn">Загрузить КЕ</button>
    </div>  

@endsection

@section('content')
    
  @if(isset($results) && count($results) > 0)

  @include('components/information')

    <div class="row justify-content-center">
        <h2 class="h2">Результаты анализа</h2>

        <form action="{{ route('download.esis', ['uid' => $uid]) }}" method="GET" class="no-spinner">
            <button type="submit" class="btn btn-light mt-3 mb-2" id="download">Скачать в Excel</button>
        </form>

        <table class="table table-bordered table-hover table-responsive">
            <thead class="text-center align-middle" style="text-transform: uppercase;">
                <tr class="table-success">
                    <th scope="col">Сервер</th>
                    <th scope="col">Платформа</th>
                    <th scope="col">ОС (ЕСИС)</th>
                    <th scope="col">ОС (CMDB)</th>
                    <th scope="col">Актуальность ОС</th>
                    <th scope="col">ЕСИС</th>
                    <th scope="col">DNS</th>
                    <th scope="col">SW</th>
                </tr>
            </thead>
            <tbody class="text-center align-middle">
                @php
                    $serverCounts = [];
                    foreach ($results as $result) {
                        $serverCounts[$result->i_server] = ($serverCounts[$result->i_server] ?? 0) + 1;
                    }
                    $currentServer = null;
                @endphp
                @foreach($results as $result)
                    <tr>
                        @if($currentServer !== $result->i_server)
                            @php $currentServer = $result->i_server; @endphp
                            <td rowspan="{{ $serverCounts[$result->i_server] }}" style="background-color: {{ ($result->esis == 'Нет' && $result->dns == 'Нет' && $result->sw == 'Нет') ? 'mistyrose' : '' }}">{{ $result->i_server }}</td>
                        @endif

                        <td style="background-color: {{ empty($result->platform) ? 'lightyellow' : '' }}">{{ $result->platform ?? '' }}</td>
                        <td style="background-color: {{ empty($result->esis_os) ? 'lightyellow' : '' }}">{{ $result->esis_os }}</td>
                        <td style="background-color: {{ empty($result->cmdb_os) ? 'lightyellow' : '' }}">{{ $result->cmdb_os }}</td>

                        <td>{{ $result->actuality }}</td>

                        <td style="background-color: {{ $result->esis == 'Да' ? 'palegreen' : 'mistyrose'}}">{{ $result->esis }}</td>
                        <td style="background-color: {{ $result->dns == 'Да' ? 'palegreen' : 'mistyrose'}}">{{ $result->dns }}</td>
                        <td style="background-color: {{ $result->sw == 'Да' ? 'palegreen' : 'mistyrose'}}">{{ $result->sw }}</td>
                    </tr>
                    @endforeach
            </tbody>
        </table>
        </div>
  @endif
@endsection

@section('script')
<script>

document.getElementById('use-ke-btn').addEventListener('click', function() {
    document.getElementById('ke-input-block').style.display = 'block';
    document.querySelector('form').setAttribute('action', "{{ route('upload.act_ke.file') }}"); 
});

document.getElementById('submit-btn').addEventListener('click', function() {
    document.querySelector('form').setAttribute('action', "{{ route('upload.act_esis.file') }}"); 
});
</script>
@endsection