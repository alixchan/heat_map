@extends('layout')

@section('title') анализа БР @endsection

@section('function') "{{ route('upload.br.file') }}" @endsection

@section('type') "inputFile" @endsection

@section('content')
    @if(isset($results) && count($results) > 0)
    
    @include('components/information')

        @if(count($results) === 1)
            @foreach($results as $result)
                @if (is_null($result->i_code))
                    <div class="alert alert-light mt-3" role="alert">
                        <b>Предупреждение</b>: в результате не получена информация. Заполнены ли значения во входном файле?
                    </div>          
                @endif
            @endforeach
        @endif

    <div class="row justify-content-center">
        <h2 class="h2">Результаты анализа</h2>

        <form action="{{ route('download.br', ['uid' => $uid]) }}" method="GET" class="no-spinner">
            <button type="submit" class="btn btn-light mt-3 mb-2" id="download">Скачать в Excel</button>
        </form>
        
        <h4 class="h4">Непрерывность ИТ-решения</h4>
            <table class="table table-bordered table-hover table-responsive">
            <thead class="text-center align-middle" style="text-transform: uppercase;">
                <tr class="table-success">
                    <th scope="col">Код (вход)</th>
                    <th scope="col">Имя (вход)</th>
                    <th scope="col">RTO (вход)</th>
                    <th scope="col">RPO (вход)</th>
                    <th scope="col">Критичность (вход)</th>
                    <th scope="col">Имя (КТ-670)</th>
                    <th scope="col">RTO (КТ-670)</th>
                    <th scope="col">RPO (КТ-670)</th>
                    <th scope="col">Критичность (КТ-670)</th>
                    <th scope="col">Имя (ЕСИС)</th>
                    <th scope="col">RTO (ЕСИС)</th>
                    <th scope="col">RPO (ЕСИС)</th>                  
                    <th scope="col">Имя</th>
                    <th scope="col">RTO</th>
                    <th scope="col">RPO</th>
                    <th scope="col">Критичность</th>
                    <th scope="col">Статус</th>
                </tr>
            </thead>
            <tbody class="text-center align-middle">
                @php
                    $codeCounts = []; 
                    foreach ($results as $result) {
                        $codeCounts[$result->i_code] = ($codeCounts[$result->i_code] ?? 0) + 1;
                    }
                    $currentCode = null;
                @endphp

                @foreach($results as $result)
                    <tr>
                        @if($currentCode !== $result->i_code)
                            @php $currentCode = $result->i_code; @endphp
                            <td rowspan="{{ $codeCounts[$result->i_code] }}">{{ $result->i_code }}</td>
                            
                        @endif

                        <td style="background-color: {{ empty($result->i_name) ? 'lightyellow' : '' }}">{{ $result->i_name ?? '' }}</td>
                        <td style="background-color: {{ empty($result->i_rto) ? 'lightyellow' : '' }}">{{ $result->i_rto }}</td>
                        <td style="background-color: {{ empty($result->i_rpo) ? 'lightyellow' : '' }}">{{ $result->i_rpo }}</td>
                        <td style="background-color: {{ empty($result->i_crit) ? 'lightyellow' : '' }}">{{ $result->i_crit }}</td>

                        <td style="background-color: {{ empty($result->k_name) ? 'lightyellow' : '' }}">{{ $result->k_name }}</td>
                        <td style="background-color: {{ empty($result->k_rto) ? 'lightyellow' : '' }}">{{ $result->k_rto }}</td>
                        <td style="background-color: {{ empty($result->k_rpo) ? 'lightyellow' : '' }}">{{ $result->k_rpo }}</td>
                        <td style="background-color: {{ empty($result->k_crit) ? 'lightyellow' : '' }}">{{ $result->k_crit }}</td> 

                        <td style="background-color: {{ empty($result->br_name) ? 'lightyellow' : '' }}">{{ $result->br_name }}</td>
                        <td style="background-color: {{ empty($result->br_rto) ? 'lightyellow' : '' }}">{{ $result->br_rto }}</td>
                        <td style="background-color: {{ empty($result->br_rpo) ? 'lightyellow' : '' }}">{{ $result->br_rpo }}</td>

                        <td style="background-color: {{ $result->name == 'Ok' ? 'palegreen' : 'mistyrose'}}">{{ $result->name }}</td>
                        <td style="background-color: {{ $result->rto == 'Ok' ? 'palegreen' : 'mistyrose'}}">{{ $result->rto }}</td>
                        <td style="background-color: {{ $result->rpo == 'Ok' ? 'palegreen' : 'mistyrose'}}">{{ $result->rpo }}</td>
                        <td style="background-color: {{ $result->crit == 'Ok' ? 'palegreen' : 'mistyrose'}}">{{ $result->crit }}</td>

                        <td style="background-color: {{ (($result->ha_dr_status == 'Некорректно') or is_null($result->ha_dr_status)) ? 'mistyrose' : 'palegreen'}}">{{ $result->ha_dr_status ?? ''}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @endif
@endsection

