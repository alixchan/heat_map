<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\SolarWinds;
use App\Models\KT670;
use App\Models\ESIS;
use App\Models\DNS;
use App\Models\CMDB_ph;
use App\Models\CMDB_v;

use App\Models\BR;
use App\Models\BS;
use App\Models\RSP;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class UpdateController
{
    private function cleanData($value) {
        return trim($value);
    }

    private function nullData($value) {
        return $value !== null && trim($value->getValue()) !== '' ? trim($value->getValue()) : null;
    }

    public function uploadSwFile(Request $request)
    {
        
        $request->validate([
            'swFile' => 'required',
        ]);
        
        $filePath = $request->file('swFile')->store('sw_files');
        $fullFilePath = str_replace('\\', '/', storage_path('app/private/' . $filePath));

        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($fullFilePath);
        
        SolarWinds::truncate();

        $batchData = []; 
        $batchSize = 100; 

        DB::beginTransaction();
        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {

                    $cells = $row->getCells();

                    $batchData[] = [
                        'sw_name' => trim($cells[0]->getValue()),
                        'sw_server' => $this->nullData(value: $cells[1]),
                        'sw_domain' => $this->nullData($cells[2]),
                    ];

                    if (count($batchData) >= $batchSize) {
                        SolarWinds::insert($batchData);
                        $batchData = [];
                    }
                }
                break;
            }

            if (!empty($batchData)) {
                SolarWinds::insert($batchData);
            }

        DB::commit();
        $reader->close();

        if (file_exists($fullFilePath)) {
            unlink($fullFilePath);
        }

        return back()->with('success', 'SolarWinds загружен. Данные успешно сохранены.');
    } catch (\Exception $e) {
            DB::rollback();
            error_log('Error during data insertion: ' . $e->getMessage());
            return back()->withErrors('Ошибка при загрузке файла: ' . $e->getMessage());
        }
    }

    public function uploadKtFile(Request $request)
    {
        
        $request->validate([
            'ktFile' => 'required',
        ]);
        
        $filePath = $request->file('ktFile')->store('kt_files');
        $fullFilePath = str_replace('\\', '/', storage_path('app/private/' . $filePath));

        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($fullFilePath);
        
        KT670::truncate();

        $rowIndex = 0;
        $batchData = []; 
        $batchSize = 100; 

        DB::beginTransaction();
        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {

                    $rowIndex++;
                    if ($rowIndex < 18) {
                        continue;
                    }

                    $cells = $row->getCells();

                    if (isset($cells[0]) && !empty($cells[0]->getValue()) && !in_array($cells[0]->getValue(), ['*', '**', '***'])) {
                        $brCode = trim($cells[0]->getValue()); 
                    } else {
                        continue;
                    }

                    $batchData[] = [
                        'br_code' => $brCode,
                        'br_name' => trim($cells[1]->getValue()),
                        'br_rto' => $this->nullData($cells[2]),
                        'br_rpo' => $this->nullData($cells[3]),
                        'br_criticaty' => $this->nullData($cells[4]),
                        'service' => $this->nullData($cells[5]),
                        'service_name' => $this->nullData($cells[6]),
                        'service_owner' => $this->nullData($cells[7]),
                        'service_rto' => $this->nullData($cells[8]),
                    ];

                    if (count($batchData) >= $batchSize) {
                        KT670::insert($batchData);
                        $batchData = [];
                    }
                }
                break;
            }

        if (!empty($batchData)) {
            KT670::insert($batchData);
        }

        DB::commit();
        $reader->close();

        if (file_exists($fullFilePath)) {
            unlink($fullFilePath);
        }

        return back()->with('success', 'KT670 загружен. Данные успешно сохранены.');
    } catch (\Exception $e) {
            DB::rollback();
            error_log('Error during data insertion: ' . $e->getMessage());
            return back()->withErrors('Ошибка при загрузке файла: ' . $e->getMessage());
        }
    }

    public function uploadEsisFile(Request $request)
    {
        $request->validate([
            'esisFile' => 'required',
        ]);
        
        $filePath = $request->file('esisFile')->store('esis_files');
        $fullFilePath = str_replace('\\', '/', storage_path('app/private/' . $filePath));

        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($fullFilePath);
        
        ESIS::truncate();

        $rowIndex = 0;
        $batchData = []; 
        $batchSize = 100; 

        DB::beginTransaction();
        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {

                    $rowIndex++;
                    if ($rowIndex < 4) {
                        continue;
                    }

                    $cells = $row->getCells();

                    $batchData[] = [
                        'deployed_platform' => trim($cells[0]->getValue()),
                        'bs_name' => trim($cells[1]->getValue()), 
                        'bs_code' => trim($cells[2]->getValue()), 
                        'ha' => $this->nullData($cells[3]), 
                        'dr' => $this->nullData($cells[4]), 
                        'rpo' => $this->nullData($cells[5]), 
                        'rto' => $this->nullData($cells[6]),
                        'rsp_name' => $this->nullData($cells[7]), 
                        'rsp_system_platform' => $this->nullData($cells[8]), 
                        
                        'rsp_platform_version' => $this->nullData($cells[9]), 
                        'rsp_hostname' => $this->nullData($cells[10]), 
                        'rsp_os' => $this->nullData($cells[11]), 
                        'rsp_os_version' => $this->nullData($cells[12]), 
                        'rsp_fault_tolerance' => $this->nullData($cells[13]), 
                        'rsp_fault_tolerance_role' => $this->nullData($cells[14]), 
                        'rsp_host_data' => $this->nullData($cells[15])
                    ];

                    if (count($batchData) >= $batchSize) {
                        ESIS::insert($batchData);
                        $batchData = [];
                    }
                }
                break;
            }

        if (!empty($batchData)) {
            ESIS::insert($batchData);
        }

        DB::commit();
        $reader->close();

        if (file_exists($fullFilePath)) {
            unlink($fullFilePath);
        }

        return back()->with('success', 'ЕСИС загружен. Данные успешно сохранены.');
    } catch (\Exception $e) {
            DB::rollback();
            error_log('Error during data insertion: ' . $e->getMessage());
            return back()->withErrors('Ошибка при загрузке файла: ' . $e->getMessage());
        }
    }

    public function uploadDnsFile(Request $request)
    {
        $request->validate([
            'dnsFile' => 'required',
        ]);

        $filePath = $request->file('dnsFile')->store('dns_files');
        $fullFilePath = str_replace('\\', '/', storage_path('app/private/' . $filePath));

        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($fullFilePath);

        Dns::truncate();

        $rowIndex = 0;

        $batchData = []; 
        $batchSize = 100; 

        DB::beginTransaction();
        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $rowIndex++;
                    if ($rowIndex == 1) {
                        continue;
                    }

                    $cells = $row->getCells();

                    $batchData[] = [
                        'dns_name' => trim($cells[0]->getValue()),
                        'dns_ip' => isset($cells[1]) && !empty($cells[1]->getValue()) ? trim($cells[1]->getValue()) : null,
                    ];

                    if (count($batchData) >= $batchSize) {
                        Dns::insert($batchData);
                        $batchData = [];
                    }
                }
                break;
            }

        if (!empty($batchData)) {
            Dns::insert($batchData);
        }

        DB::commit();
        $reader->close();

        if (file_exists($fullFilePath)) {
            unlink($fullFilePath);
        }
        
        return back()->with('success', 'DNS файл загружен. Данные успешно сохранены.');
    } catch (\Exception $e) {
        DB::rollback();
        error_log('Error during data insertion: ' . $e->getMessage());
        return back()->withErrors('Ошибка при загрузке файла: ' . $e->getMessage());
        }
    }

    public function uploadCmdbFile(Request $request)
    {
        $request->validate([
            'cmdbFile' => 'required',
        ]);
    
        $filePath = $request->file('cmdbFile')->store('cmdb_files');
        $fullFilePath = str_replace('\\', '/', storage_path('app/private/' . $filePath));
    
        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($fullFilePath);
    
        CMDB_v::truncate();
        CMDB_ph::truncate();

        $batchSize = 100; 

        DB::beginTransaction();
        try {
        foreach ($reader->getSheetIterator() as $sheet) {
            $sheetName = $sheet->getName();
            $rowIndex = 0;
            $batchInsertData = [];
        
            foreach ($sheet->getRowIterator() as $row) {
                $rowIndex++;
                if ($rowIndex == 1) {
                    continue;
                }
        
                $cells = $row->getCells();
        
                if ($sheetName === 'Virtual') {        
                    $batchInsertData[] = [
                        'ci_name' => trim($cells[0]->getValue()), 
                        'ci_development' => trim($cells[1]->getValue()), 
                        'vm_os' => isset($cells[2]) ? $this->nullData($cells[2]) : null, 
                        'vm_dns_name' => isset($cells[3]) ? $this->nullData($cells[3]) : null, 
                        'vm_name' => trim($cells[4]->getValue()), 
                        'vm_platform' => trim($cells[5]->getValue()), 
                        'vm_vcenter' => isset($cells[6]) ? $this->nullData($cells[6]) : null,
                        'vm_cluster' => isset($cells[7]) ? $this->nullData($cells[7]) : null,
                        'vm_id' => isset($cells[8]) ? $this->nullData($cells[8]) : null,
                        'sw_node_id' => isset($cells[9]) ? $this->nullData($cells[9]) : null,
                        'sw_node_name' => isset($cells[10]) ? $this->nullData($cells[10]) : null,
                        'vm_host' => isset($cells[11]) ? $this->nullData($cells[11]) : null,
                    ];
                } elseif ($sheetName === 'Physical') {       
                    $batchInsertData[] = [
                        'ci_name' => isset($cells[0]) ? $this->nullData($cells[0]) : null,
                        'ci_vendor' => isset($cells[1]) ? $this->nullData($cells[1]) : null,
                        'ci_os' => isset($cells[2]) ? $this->nullData($cells[2]) : null,
                        'ci_dns' => isset($cells[3]) ? $this->nullData($cells[3]) : null,
                        'hpsm_id' => isset($cells[4]) ? $this->nullData($cells[4]) : null,
                        'cluster' => isset($cells[5]) ? $this->nullData($cells[5]) : null,
                        'host_id' => isset($cells[6]) ? $this->nullData($cells[6]) : null,
                        'sw_node_id' => isset($cells[7]) ? $this->nullData($cells[7]) : null,
                        'sw_node_name' => isset($cells[8]) ? $this->nullData($cells[8]) : null,
                        'ilo_host_name' => isset($cells[9]) ? $this->nullData($cells[9]) : null,
                        'vcenter_name' => isset($cells[10]) ? $this->nullData($cells[10]) : null,
                    ];
                }
        
                if (count($batchInsertData) >= $batchSize) {
                    if ($sheetName === 'Virtual') {
                        CMDB_v::insert($batchInsertData);
                    } elseif ($sheetName === 'Physical') {
                        CMDB_ph::insert($batchInsertData);
                    }
                    $batchInsertData = [];
                }
            }
        
            if (!empty($batchInsertData)) {
                if ($sheetName === 'Virtual') {
                    CMDB_v::insert($batchInsertData);
                } elseif ($sheetName === 'Physical') {
                    CMDB_ph::insert($batchInsertData);
                }
            }
        }

        DB::commit();
        $reader->close();
    
        if (file_exists($fullFilePath)) {
            unlink($fullFilePath);
        }
    
        return back()->with('success', 'Файл загружен. Данные успешно сохранены.');
    } catch (\Exception $e) {
        DB::rollback();
        error_log('Error during data insertion: ' . $e->getMessage());
        return back()->withErrors('Ошибка при загрузке файла: ' . $e->getMessage());
        }
    }

    public function uploadEsisFullFile(Request $request)
    {
        $request->validate(['esisFullFile' => 'required']);

        $filePath = $request->file('esisFullFile')->store('esis_files');
        $fullFilePath = str_replace('\\', '/', storage_path('app/private/' . $filePath));

        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($fullFilePath);
        
        DB::statement('ALTER TABLE r_s_p_s NOCHECK CONSTRAINT ALL');
        DB::statement('ALTER TABLE b_s NOCHECK CONSTRAINT ALL');
        DB::statement('ALTER TABLE b_r_s NOCHECK CONSTRAINT ALL');

        DB::table('r_s_p_s')->delete();
        DB::table('b_s')->delete();
        DB::table('b_r_s')->delete();

        DB::statement('DBCC CHECKIDENT (\'r_s_p_s\', RESEED, 0)');
        DB::statement('DBCC CHECKIDENT (\'b_s\', RESEED, 0)');
        DB::statement('DBCC CHECKIDENT (\'b_r_s\', RESEED, 0)');

        DB::statement('ALTER TABLE r_s_p_s CHECK CONSTRAINT ALL');
        DB::statement('ALTER TABLE b_s CHECK CONSTRAINT ALL');
        DB::statement('ALTER TABLE b_r_s CHECK CONSTRAINT ALL');
            
        $rowIndex = 0;
        $rspBuffer = [];
        $lastBRId = null;
        $lastBSId = null;
        $batchSize = 100;

        DB::beginTransaction();
        try {
                foreach ($reader->getSheetIterator() as $sheet) {
                    foreach ($sheet->getRowIterator() as $row) {
                        $rowIndex++;
                        if ($rowIndex <= 4) {
                            continue;
                        }
        
                        $cells = $row->getCells();
        
                        $isBr = !empty($cells[0]->getValue()) && !empty($cells[1]->getValue()) && 
                                !empty($cells[2]->getValue()) && !empty($cells[3]->getValue()) && 
                                !empty($cells[4]->getValue()) && !empty($cells[5]->getValue()) && 
                                empty($cells[9]->getValue());
        
                        $isBusinessSystem = empty($cells[0]->getValue()) && !empty($cells[18]->getValue()) &&
                                            !empty($cells[19]->getValue()) && !empty($cells[20]->getValue());
        
                        $isRSP = empty($cells[7]->getValue()) && !empty($cells[8]->getValue()) && 
                                 !empty($cells[10]->getValue()) && !empty($cells[11]->getValue()) &&
                                 !empty($cells[12]->getValue()) && !empty($cells[13]->getValue());
        
                        if ($isBr) {
                            $brRecord = [
                                'code' => trim($cells[1]->getValue()),
                                'name' => trim($cells[2]->getValue()),
                                'status' => trim($cells[3]->getValue()),
                                'ha' => trim($cells[4]->getValue()),
                                'dr' => trim($cells[5]->getValue()),
                                'rpo' => trim($cells[6]->getValue()),
                                'rto' => trim($cells[7]->getValue()),
                            ];
                            $lastBRId = DB::table('b_r_s')->insertGetId($brRecord);
                        } elseif ($isBusinessSystem && $lastBRId) {
                            $bsRecord = [
                                'code' => trim($cells[18]->getValue()),
                                'name' => trim($cells[19]->getValue()),
                                'full_name' => trim($cells[20]->getValue()),
                                'ha' => trim($cells[21]->getValue()),
                                'dr' => trim($cells[22]->getValue()),
                                'rpo' => trim($cells[23]->getValue()),
                                'rto' => trim($cells[24]->getValue()),
                                'b_r_s_id' => $lastBRId,
                            ];
                            $lastBSId = DB::table('b_s')->insertGetId($bsRecord);
                        } elseif ($isRSP && $lastBSId) {
                            $rspBuffer[] = [
                                'code' => trim($cells[11]->getValue()),
                                'name' => trim($cells[12]->getValue()),
                                'system_platform' => trim($cells[13]->getValue()),
                                'host' => isset($cells[14]) ? $this->nullData($cells[14]) : null,
                                'os' => isset($cells[15]) ? $this->nullData($cells[15]) : null,
                                'fault_tolerance' => isset($cells[16]) ? $this->nullData($cells[16]) : null,
                                'role' => isset($cells[17]) ? $this->nullData($cells[17]) : null,
                                'b_r_s_id' => $lastBRId,
                                'b_s_id' => $lastBSId,
                            ];                                    
                        }
        
                        if (count($rspBuffer) >= $batchSize) {
                            DB::table('r_s_p_s')->insert($rspBuffer);
                            $rspBuffer = [];
                        }
                    }
                    break;
                }
        
                if (!empty($rspBuffer)) {
                    DB::table('r_s_p_s')->insert($rspBuffer);
                }
        
                DB::commit();
                $reader->close();
        
                if (file_exists($fullFilePath)) {
                    unlink($fullFilePath);
                }

                return back()->with('success', 'Выборка ЕСИС загружен. Данные успешно сохранены.');
            
        } catch (\Exception $e) {
            DB::rollback();
            error_log('Error during data insertion: ' . $e->getMessage());
            return back()->withErrors('Ошибка при загрузке файла: ' . $e->getMessage());
        }
    }   
}
