@extends('layout')

@section('title') анализа импортонезависимости @endsection

@section('function') "{{ route('upload.import.file') }}" @endsection

@section('type') "inputFile" @endsection

@section('button')
    <button type="button" class="btn btn-dark me-md-5" id="use-ke-btn">Использовать список КЕ</button><br>
@endsection

@section('text_area')
    <div id="ke-input-block" style="display:none;">
        <div class="form-group mt-4">
            <label for="ke-list">Введите список КЕ:</label>
            <textarea id="ke-list" name="ke_list" class="form-control" rows="4" placeholder="Введите текст"></textarea>
        </div>
        <br>
        <button type="submit" class="btn btn-success" id="upload-ke-btn">Загрузить КЕ</button>
    </div>  
@endsection

@section('content')
    @if(isset($results) && count($results) > 0)

    @include('components/information')

    <div class="row justify-content-center">
        <h2 class="h2">Результаты анализа</h2>

        <form action="{{ route('download.import', ['uid' => $uid]) }}" method="GET" class="no-spinner">
            <button type="submit" class="btn btn-light mt-3 mb-2" id="download">Скачать Excel-отчет</button>
        </form>

        <table class="table table-bordered table-hover table-responsive">
            <thead class="text-center align-middle" style="text-transform: uppercase;">
                <tr class="table-success">
                    <th scope="col">Сервер</th>
                    <th scope="col">Тип</th>
                    <th scope="col">ОС (вирт.)</th>
                    <th scope="col">Платформа виртуализации</th>
                    <th scope="col">Хост</th>
                    <th scope="col">vCenter (вирт.)</th>
                    <th scope="col">vCenter (физ.)</th>
                    <th scope="col">Корректность vCenter</th>
                    <th scope="col">Кластер (вирт.)</th>
                    <th scope="col">Кластер (физ.)</th>
                    <th scope="col">Корректность кластера</th>
                    <th scope="col">Имя физ. сервера </th>
                    <th scope="col">ОС</th>
                    <th scope="col">Вендор</th>
                </tr>
            </thead>
            @php
                function getItColor($item) {
                    $item_lower = strtolower($item);
                    
                    $green_list = [
                        'depo', 'raidix', 'ооо "гагар.ин"', 'yadro', 'aquarius',
                        'ооо "даком м"', 'mail.ru', 'ао «флант»', 'нии "масштаб"', 
                        'orion soft (орион)', 'редос', 'zvirt', 'astra'
                    ];

                    $yellow_list = [
                        'lenovo', 'huawei', 'xfusion'
                    ];

                    $red_list = [
                        'hpe', 'ibm', 'nvidia', 'hitachi data system', 'dell emc', 
                        'netapp', 'radware', 'citrix', 'hp', 'vmware', 'oracle', 
                        'veeam', 'red hat', 'project okd', 'fedora', 
                        'infinidat', 'centos', 'cloudian', 'windows server', 'esxi',
                        'cisco'
                    ];

                    foreach ($green_list as $keyword) {
                        if (strpos($item_lower, $keyword) !== false) {
                            return 'lightgreen';
                        }
                    }

                    foreach ($yellow_list as $keyword) {
                        if (strpos($item_lower, $keyword) !== false) {
                            return 'lightsalmon';
                        }
                    }

                    foreach ($red_list as $keyword) {
                        if (strpos($item_lower, $keyword) !== false) {
                            return 'lightcoral';
                        }
                    }
                    return '';
                }
            @endphp
            <tbody class="text-center align-middle">
                @php
                    $codeCounts = []; 
                    foreach ($results as $result) {
                        $key = $result->i_server . '-' . $result->t_type . '-' . $result->v_os . '-' . $result->platform_virt . '-' . $result->v_host .
                            '-' . $result->v_vcenter . '-' . $result->p_vcenter . '-' . $result->vcenter . '-' . $result->v_cluster . '-' .
                            $result->p_cluster . '-' . $result->cluster;
                        $codeCounts[$key] = ($codeCounts[$key] ?? 0) + 1;
                    }
                    $processedKeys = [];
                @endphp

                @foreach($results as $result)
                    @php 
                        $key = $result->i_server . '-' . $result->t_type . '-' . $result->v_os . '-' . $result->platform_virt . '-' . $result->v_host .
                                '-' . $result->v_vcenter . '-' . $result->p_vcenter . '-' . $result->vcenter . '-' . $result->v_cluster . '-' .
                                $result->p_cluster . '-' . $result->cluster;
                        
                        $isFirstOccurrence = !in_array($key, $processedKeys);
                        if($isFirstOccurrence) {
                            $rowspan = $codeCounts[$key];
                            $processedKeys[] = $key;
                        }
                    @endphp

                    <tr>
                        @if($isFirstOccurrence)
                            <td rowspan="{{ $rowspan }}" style="background-color: {{ empty($result->i_server) ? 'lightyellow' : '' }}">{{ $result->i_server }}</td>
                            <td rowspan="{{ $rowspan }}" style="background-color: {{ $result->t_type == 'не найден в CMDB' ? 'lightyellow' : '' }}">{{ $result->t_type }}</td>
                            <td rowspan="{{ $rowspan }}" style="background-color: {{ empty($result->v_os) ? 'lightyellow' : '' }}">{{ $result->v_os }}</td>
                            <td rowspan="{{ $rowspan }}" style="background-color: {{ empty($result->platform_virt) ? 'lightyellow' : '' }}">{{ $result->platform_virt }}</td>
                            <td rowspan="{{ $rowspan }}" style="background-color: {{ empty($result->v_host) ? 'lightyellow' : '' }}">{{ $result->v_host }}</td>
                            <td rowspan="{{ $rowspan }}" style="background-color: {{ empty($result->v_vcenter) ? 'lightyellow' : '' }}">{{ $result->v_vcenter }}</td>
                            <td rowspan="{{ $rowspan }}" style="background-color: {{ empty($result->p_vcenter) ? 'lightyellow' : '' }}">{{ $result->p_vcenter }}</td>
                            <td rowspan="{{ $rowspan }}" style="background-color: {{ ($result->vcenter == 'Ok') ? 'palegreen' : 'mistyrose' }}">{{ $result->vcenter }}</td>
                            <td rowspan="{{ $rowspan }}" style="background-color: {{ empty($result->v_cluster) ? 'lightyellow' : '' }}">{{ $result->v_cluster }}</td>
                            <td rowspan="{{ $rowspan }}" style="background-color: {{ empty($result->p_cluster) ? 'lightyellow' : '' }}">{{ $result->p_cluster }}</td>
                            <td rowspan="{{ $rowspan }}" style="background-color: {{ ($result->cluster == 'Ok') ? 'palegreen' : 'mistyrose' }}">{{ $result->cluster }}</td>
                        @endif

                        <td style="background-color: {{ empty($result->p_name) ? 'lightyellow' : '' }}">{{ $result->p_name }}</td>
                        <td style="background-color: {{ getItColor($result->p_os ?? '') }}">{{ $result->p_os }}</td>
                        <td style="background-color: {{ getItColor($result->p_vendor ?? '') }}">{{ $result->p_vendor }}</td>
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
    document.querySelector('form').setAttribute('action', "{{ route('upload.import_ke.file') }}");
});

document.getElementById('submit-btn').addEventListener('click', function() {
    document.querySelector('form').setAttribute('action', "{{ route('upload.import.file') }}");
});
</script>
@endsection