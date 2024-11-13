@extends('layout')

@section('title') анализа БC @endsection

@section('function') "{{ route('upload.bs.file') }}" @endsection

@section('type') "inputFile" @endsection


@section('content')
    @if(isset($results) && count($results) > 0)

        @include('components/information')

    <div class="row justify-content-center">
        <h2 class="h2">Результаты анализа </h2>

        <form action="{{ route('download.bs', ['uid' => $uid]) }}" method="GET" class="no-spinner">
        <button type="submit" class="btn btn-light mt-3 mb-2" id="download">Скачать в Excel</button>
        </form>
        
        <h4 class="h4">Непрерывность ИТ-систем</h4>

        <table class="table table-bordered table-hover table-responsive">
            <thead class="text-center align-middle" style="text-transform: uppercase;">
                <tr class="table-success">  
                    <th>Код (вход)</th>
                    <th>Имя (вход)</th>
                    <th>Имя (ЕСИС)</th>
                    <th>RTO (вход)</th>
                    <th>RTO (ЕСИС)</th>
                    <th>RPO (вход)</th>
                    <th>RPO (ЕСИС)</th>
                    <th>HA (ЕСИС)</th>
                    <th>DR (ЕСИС)</th>
                    <th>Имя</th>
                    <th>RTO</th>
                    <th>RPO</th>
                    <th>Статус</th>
                    <th>Сервера</th>
                    <th>Отказоустойчивость</th>
                </tr>
            </thead>
            <tbody class="text-center align-middle">
                @php
                    $counts = [];
                    foreach ($results as $result) {
                        $counts[$result->i_code] = ($counts[$result->i_code] ?? 0) + 1;
                    }
                    $current = null;
                @endphp
                @foreach($results as $result)
                    <tr>
                        @if($current !== $result->i_code)
                            @php $current = $result->i_code; @endphp
                            <td rowspan="{{ $counts[$result->i_code] }}">{{ $result->i_code }}</td>
                        @endif
                        <td style="background-color: {{ empty($result->i_name) ? 'lightyellow' : '' }}">{{ $result->i_name ?? '' }}</td>
                        <td style="background-color: {{ empty($result->e_name) ? 'lightyellow' : '' }}">{{ $result->e_name ?? '' }}</td>
                        <td style="background-color: {{ empty($result->i_rto) ? 'lightyellow' : '' }}">{{ $result->i_rto }}</td>
                        <td style="background-color: {{ empty($result->e_rto) ? 'lightyellow' : '' }}">{{ $result->e_rto }}</td>
                        <td style="background-color: {{ is_null($result->i_rpo) ? 'lightyellow' : '' }}">{{ $result->i_rpo }}</td>
                        <td style="background-color: {{ empty($result->e_rpo) ? 'lightyellow' : '' }}">{{ $result->e_rpo ?? '' }}</td>
                        <td style="background-color: {{ empty($result->e_ha) ? 'lightyellow' : '' }}">{{ $result->e_ha ?? '' }}</td>
                        <td style="background-color: {{ empty($result->e_dr) ? 'lightyellow' : '' }}">{{ $result->e_dr ?? '' }}</td>

                        <td style="background-color: {{ $result->name == 'Ok' ? 'palegreen' : 'mistyrose'}}">{{ $result->name }}</td>
                        <td style="background-color: {{ $result->rto == 'Ok' ? 'palegreen' : 'mistyrose'}}">{{ $result->rto }}</td>
                        <td style="background-color: {{ $result->rpo == 'Ok' ? 'palegreen' : 'mistyrose'}}">{{ $result->rpo }}</td>
                        <td style="background-color: {{ $result->ha_dr_status == 'Ok' ? 'palegreen' : 'mistyrose'}}">{{ $result->ha_dr_status }}</td>
                    
                        <td style="background-color: {{ empty($result->server) ? 'lightyellow' : '' }}">{{ $result->server ?? '' }}</td>
                        <td style="background-color: {{ empty($result->fault_tolerance) ? 'lightyellow' : '' }}">{{ $result->fault_tolerance ?? '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    @endif
@endsection