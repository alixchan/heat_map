<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

use App\Models\Input;


class AnalysisController
{
    
    private const table1 = "SELECT DISTINCT
                i.bs_code AS i_code,
                i.bs_name AS i_name,

                bs.name AS e_name,
                i.bs_rto AS i_rto,
                bs.rto AS e_rto,
                i.bs_rpo AS i_rpo,
                bs.rpo AS e_rpo,
                bs.ha AS e_ha,
                bs.dr AS e_dr,
                CASE
                    WHEN i.bs_name COLLATE Latin1_General_CI_AS = bs.name COLLATE Latin1_General_CI_AS 
                    THEN 'Ok'
                    ELSE 'Некорректно'
                END AS name,
                CASE 
                    WHEN i.bs_rto = bs.rto 
                    THEN 'Ok'
                    ELSE 'Некорректно'
                END AS rto,
                CASE 
                    WHEN i.bs_rpo = bs.rpo 
                    THEN 'Ok'
                    ELSE 'Некорректно'
                END AS rpo,
                CASE 
                    WHEN i.bs_rto > 0 AND i.bs_rto <= 4 AND i.bs_rpo > 0 AND i.bs_rpo <= 2 THEN --критичный
                    CASE 
                        WHEN bs.ha = 'Да' AND bs.dr = 'Да' 
                        THEN 'Ok'
                        ELSE 'Некорректно'
                    END
                    WHEN i.bs_rto > 4 AND i.bs_rto <= 48 AND i.bs_rpo > 2 AND i.bs_rpo <= 24 THEN --важный
                    CASE 
                        WHEN bs.ha = 'Да' 
                        THEN 'Ok'
                        ELSE 'Некорректно'
                    END
                    ELSE -- поддерживающий
                    CASE
                        WHEN bs.dr = 'Нет' 
                        THEN 'Ok' 
                        ELSE 'Некорректно' 
                    END
                END AS ha_dr_status,
                i.rsp_server AS server,
                r.fault_tolerance AS fault_tolerance
			FROM inputs i
			LEFT JOIN r_s_p_s r ON i.rsp_server COLLATE Latin1_General_CI_AS = r.host COLLATE Latin1_General_CI_AS
			LEFT JOIN b_s bs ON bs.id = r.b_s_id";

    private const table2 = "SELECT DISTINCT
                i.br_code AS i_code,
                i.br_name AS i_name,
                i.br_rto AS i_rto,
                i.br_rpo AS i_rpo,
                i.br_criticaty AS i_crit,

                k.br_name AS k_name,
                k.br_rto AS k_rto,
                k.br_rpo AS k_rpo,
                k.br_criticaty AS k_crit,

                br.name AS br_name,
                br.rto AS br_rto,
                br.rpo AS br_rpo,
                CASE 
                    WHEN i.br_name COLLATE Latin1_General_CI_AS = k.br_name COLLATE Latin1_General_CI_AS
                    AND i.br_name COLLATE Latin1_General_CI_AS = br.name COLLATE Latin1_General_CI_AS
                    THEN 'Ok'
                    ELSE 'Некорректно'
                END AS name,
                CASE 
                    WHEN i.br_rto = k.br_rto 
                    AND i.br_rto = br.rto
                    THEN 'Ok'
                    ELSE 'Некорректно'
                END AS rto,
                CASE 
                    WHEN i.br_rpo = k.br_rpo 
                    AND i.br_rpo = br.rpo
                    THEN 'Ok'
                    ELSE 'Некорректно'
                END AS rpo,
                CASE 
                    WHEN i.br_criticaty COLLATE Latin1_General_CI_AS = k.br_criticaty COLLATE Latin1_General_CI_AS
                    THEN 'Ok'
                    ELSE 'Некорректно'
                END AS crit,

                CASE 
                    WHEN i.br_rto > 0 AND i.br_rto <= 4 AND i.br_rpo > 0 AND i.br_rpo <= 2 THEN --критичный
                    CASE 
                        WHEN i.br_criticaty COLLATE Latin1_General_CI_AS = 'Критичная' COLLATE Latin1_General_CI_AS 
                        THEN 'Ok'
                        ELSE 'Некорректно'
                    END
                    WHEN i.br_rto > 4 AND i.br_rto <= 48 AND i.br_rpo > 2 AND i.br_rpo <= 24 THEN --важный
                    CASE 
                        WHEN i.br_criticaty COLLATE Latin1_General_CI_AS = 'Важная' COLLATE Latin1_General_CI_AS
                        THEN 'Ok'
                        ELSE 'Некорректно'
                    END
                    ELSE -- поддерживающий
                    CASE
                        WHEN i.br_criticaty COLLATE Latin1_General_CI_AS = 'Поддерживающая' COLLATE Latin1_General_CI_AS
                        THEN 'Ok' 
                        ELSE 'Некорректно' 
                    END
                END AS ha_dr_status
            FROM inputs i
            LEFT JOIN k_t670_s k ON i.br_code = k.br_code
            LEFT JOIN b_r_s br ON br.code = i.br_code";
    
    private const table3 = "SELECT DISTINCT
            i.rsp_server AS i_server,
            e.system_platform AS platform,
            e.os AS esis_os,
            COALESCE(v.vm_os,  ph.ci_os, NULL) AS cmdb_os,
            CASE
                WHEN e.os IS NULL AND COALESCE(v.vm_os, ph.ci_os) IS NULL THEN 'ОС отсутствует'
                WHEN e.os IS NOT NULL AND COALESCE(v.vm_os, ph.ci_os) IS NULL THEN 'не подтверждена ОС'
                WHEN e.os IS NOT NULL AND COALESCE(v.vm_os, ph.ci_os) IS NOT NULL THEN
                    CASE
                        WHEN e.os LIKE 'MS Windows Server%' 
                            AND COALESCE(v.vm_os, ph.ci_os) LIKE '%Windows Server%' 
                            AND ISNULL(PATINDEX('%[0-9][0-9][0-9][0-9]%', e.os), 0) > 0
                            AND ISNULL(PATINDEX('%[0-9][0-9][0-9][0-9]%', COALESCE(v.vm_os, ph.ci_os)), 0) > 0
                            AND SUBSTRING(e.os, PATINDEX('%[0-9][0-9][0-9][0-9]%', e.os), 4) = 
                                SUBSTRING(COALESCE(v.vm_os, ph.ci_os), PATINDEX('%[0-9][0-9][0-9][0-9]%', COALESCE(v.vm_os, ph.ci_os)), 4)
                        THEN 'актуально'
                        WHEN e.os LIKE '%Linux%' 
                            AND COALESCE(v.vm_os, ph.ci_os) LIKE '%Other%Linux%' 
                        THEN 'совпадение семейств'

                        WHEN e.os LIKE '%SLES%' 
                            AND COALESCE(v.vm_os, ph.ci_os) LIKE '%SUSE Linux Enterprise%' THEN 'актуально'
                        WHEN e.os LIKE '%Astra Linux%' 
                            AND COALESCE(v.vm_os, ph.ci_os) LIKE '%Astra Linux%' THEN 'актуально'
                        WHEN e.os LIKE '%Debian%' 
                            AND COALESCE(v.vm_os, ph.ci_os) LIKE '%Debian%' THEN 'актуально'
                        WHEN e.os LIKE '%Ubuntu%' 
                            AND COALESCE(v.vm_os, ph.ci_os) LIKE '%Ubuntu%' THEN 'актуально'
                        WHEN (e.os LIKE '%Cent OS%' OR e.os LIKE '%CentOS%') 
                            AND COALESCE(v.vm_os, ph.ci_os) LIKE '%Cent OS%' THEN 'актуально'
                        WHEN e.os LIKE '%Oracle Linux%' 
                            AND COALESCE(v.vm_os, ph.ci_os) LIKE '%Oracle Linux%' THEN 'актуально'
                        WHEN e.os LIKE '%RHEL%' 
                            AND COALESCE(v.vm_os, ph.ci_os) LIKE '%Red Hat%' THEN 'актуально'
                        WHEN e.os LIKE '%РЕД ОС%' 
                            AND COALESCE(v.vm_os, ph.ci_os) LIKE '%Red%' THEN 'актуально' 
                        WHEN e.os LIKE '%Linux%' 
                            AND COALESCE(v.vm_os, ph.ci_os) LIKE '%Other%Linux%' THEN 'актуально'
                        WHEN 
                            LOWER(e.os) LIKE '%' + LOWER(COALESCE(v.vm_os, ph.ci_os)) + '%' OR
                            LOWER(COALESCE(v.vm_os, ph.ci_os)) LIKE '%' + LOWER(e.os) + '%' 
                        THEN 'требуется уточнение в CMDB'
                        
                        ELSE 'требует актуализации'
                    END
                ELSE 'ОС отсутствует'
            END AS actuality,
            CASE
                WHEN e.host IS NOT NULL 
                THEN 'Да' 
                ELSE 'Нет'
            END AS esis,
            CASE
                WHEN d.dns_name IS NOT NULL 
                THEN 'Да' 
                ELSE 'Нет'
            END AS dns,
            CASE
                WHEN s.sw_name IS NOT NULL
                OR s.sw_domain IS NOT NULL
                OR s.sw_server IS NOT NULL
                THEN 'Да' 
                ELSE 'Нет'
            END AS sw
        FROM inputs i
        LEFT JOIN r_s_p_s e ON i.rsp_server COLLATE Latin1_General_CI_AS = e.host COLLATE Latin1_General_CI_AS
        LEFT JOIN dns d ON d.dns_name COLLATE Latin1_General_CI_AS = i.rsp_server COLLATE Latin1_General_CI_AS
        LEFT JOIN solar_winds s ON s.sw_domain COLLATE Latin1_General_CI_AS = i.rsp_server COLLATE Latin1_General_CI_AS
            OR s.sw_name COLLATE Latin1_General_CI_AS = i.rsp_server COLLATE Latin1_General_CI_AS
            OR s.sw_server COLLATE Latin1_General_CI_AS = i.rsp_server COLLATE Latin1_General_CI_AS
        LEFT JOIN c_m_d_b_vs v ON i.rsp_server COLLATE Latin1_General_CI_AS = v.ci_name COLLATE Latin1_General_CI_AS
        LEFT JOIN c_m_d_b_phs ph ON i.rsp_server COLLATE Latin1_General_CI_AS = ph.ci_name COLLATE Latin1_General_CI_AS
        WHERE i.rsp_server IS NOT NULL AND i.rsp_server NOT LIKE ''";

    private const table4 = "SELECT DISTINCT
                i.rsp_server AS i_server,
                CASE
                    WHEN v.ci_name IS NOT NULL THEN 'виртуальный'
                    WHEN ph2.ci_name IS NOT NULL THEN 'физический'
                    ELSE 'не найден в CMDB'
                END AS t_type,
                v.vm_os AS v_os,
                v.vm_platform AS platform_virt,
                v.vm_host AS v_host,

                v.vm_vcenter AS v_vcenter,
                CASE 
                    WHEN v.ci_name IS NOT NULL THEN ph.vcenter_name
                    WHEN ph2.ci_name IS NOT NULL THEN ph2.vcenter_name
                END AS p_vcenter,

                CASE 
                    WHEN v.vm_vcenter IS NULL AND ph.vcenter_name IS NULL THEN NULL
                    WHEN v.vm_vcenter COLLATE Latin1_General_CI_AS = ph.vcenter_name COLLATE Latin1_General_CI_AS THEN 'Ok' 
                    ELSE 'Некорректно' 
                END AS vcenter,
                
                v.vm_cluster AS v_cluster,
                CASE 
                    WHEN v.ci_name IS NOT NULL THEN ph.cluster
                    WHEN ph2.ci_name IS NOT NULL THEN ph2.cluster
                END AS p_cluster,

                CASE 
                    WHEN v.vm_cluster IS NULL AND ph.cluster IS NULL THEN NULL
                    WHEN v.vm_cluster COLLATE Latin1_General_CI_AS = ph.cluster COLLATE Latin1_General_CI_AS THEN 'Ok' 
                    ELSE 'Некорректно' 
                END AS cluster, 
                CASE 
                    WHEN v.ci_name IS NOT NULL THEN ph.ci_name
                    WHEN ph2.ci_name IS NOT NULL THEN ph2.ci_name
                END AS p_name,
                CASE 
                    WHEN v.ci_name IS NOT NULL THEN ph.ci_os
                    WHEN ph2.ci_name IS NOT NULL THEN ph2.ci_os
                END AS p_os,
                        CASE 
                    WHEN v.ci_name IS NOT NULL THEN ph.ci_vendor
                    WHEN ph2.ci_name IS NOT NULL THEN ph2.ci_vendor
                END AS p_vendor
                FROM inputs i
                LEFT JOIN c_m_d_b_vs v ON i.rsp_server COLLATE Latin1_General_CI_AS = v.ci_name COLLATE Latin1_General_CI_AS
                LEFT JOIN c_m_d_b_phs ph2 ON i.rsp_server COLLATE Latin1_General_CI_AS = ph2.ci_name COLLATE Latin1_General_CI_AS
                LEFT JOIN c_m_d_b_phs ph ON ph.cluster COLLATE Latin1_General_CI_AS = v.vm_cluster COLLATE Latin1_General_CI_AS
                    AND ph.vcenter_name COLLATE Latin1_General_CI_AS = v.vm_vcenter COLLATE Latin1_General_CI_AS
                WHERE i.rsp_server IS NOT NULL AND i.rsp_server NOT LIKE ''"; 

    private function table3_ke($keItemsString) {
        return "SELECT DISTINCT
            i.rsp_server AS i_server,
            e.system_platform AS platform,
            e.os AS esis_os,
            COALESCE(v.vm_os, ph.ci_os, NULL) AS cmdb_os,
            CASE
                WHEN e.os IS NULL AND COALESCE(v.vm_os, ph.ci_os) IS NULL THEN 'ОС отсутствует'
                WHEN e.os IS NOT NULL AND COALESCE(v.vm_os, ph.ci_os) IS NULL THEN 'не подтверждена ОС'
                WHEN e.os IS NOT NULL AND COALESCE(v.vm_os, ph.ci_os) IS NOT NULL THEN
                    CASE
                        WHEN e.os LIKE 'MS Windows Server%' 
                            AND COALESCE(v.vm_os, ph.ci_os) LIKE '%Windows Server%' 
                            AND ISNULL(PATINDEX('%[0-9][0-9][0-9][0-9]%', e.os), 0) > 0
                            AND ISNULL(PATINDEX('%[0-9][0-9][0-9][0-9]%', COALESCE(v.vm_os, ph.ci_os)), 0) > 0
                            AND SUBSTRING(e.os, PATINDEX('%[0-9][0-9][0-9][0-9]%', e.os), 4) = 
                                SUBSTRING(COALESCE(v.vm_os, ph.ci_os), PATINDEX('%[0-9][0-9][0-9][0-9]%', COALESCE(v.vm_os, ph.ci_os)), 4)
                        THEN 'актуально'
                        WHEN e.os LIKE '%Linux%' 
                            AND COALESCE(v.vm_os, ph.ci_os) LIKE '%Other%Linux%' 
                        THEN 'совпадение семейств'

                        WHEN e.os LIKE '%SLES%' 
                            AND COALESCE(v.vm_os, ph.ci_os) LIKE '%SUSE Linux Enterprise%' THEN 'актуально'
                        WHEN e.os LIKE '%Astra Linux%' 
                            AND COALESCE(v.vm_os, ph.ci_os) LIKE '%Astra Linux%' THEN 'актуально'
                        WHEN e.os LIKE '%Debian%' 
                            AND COALESCE(v.vm_os, ph.ci_os) LIKE '%Debian%' THEN 'актуально'
                        WHEN e.os LIKE '%Ubuntu%' 
                            AND COALESCE(v.vm_os, ph.ci_os) LIKE '%Ubuntu%' THEN 'актуально'
                        WHEN (e.os LIKE '%Cent OS%' OR e.os LIKE '%CentOS%') 
                            AND COALESCE(v.vm_os, ph.ci_os) LIKE '%Cent OS%' THEN 'актуально'
                        WHEN e.os LIKE '%Oracle Linux%' 
                            AND COALESCE(v.vm_os, ph.ci_os) LIKE '%Oracle Linux%' THEN 'актуально'
                        WHEN e.os LIKE '%RHEL%' 
                            AND COALESCE(v.vm_os, ph.ci_os) LIKE '%Red Hat%' THEN 'актуально'
                        WHEN e.os LIKE '%РЕД ОС%' 
                            AND COALESCE(v.vm_os, ph.ci_os) LIKE '%Red%' THEN 'актуально' 
                        WHEN e.os LIKE '%Linux%' 
                            AND COALESCE(v.vm_os, ph.ci_os) LIKE '%Other%Linux%' THEN 'актуально'
                        WHEN 
                            LOWER(e.os) LIKE '%' + LOWER(COALESCE(v.vm_os, ph.ci_os)) + '%' OR
                            LOWER(COALESCE(v.vm_os, ph.ci_os)) LIKE '%' + LOWER(e.os) + '%' 
                        THEN 'требуется уточнение в CMDB'
                        
                        ELSE 'требует актуализации'
                    END
                ELSE 'ОС отсутствует'
            END AS actuality,
            CASE
                WHEN e.host IS NOT NULL 
                THEN 'Да' 
                ELSE 'Нет'
            END AS esis,
            CASE
                WHEN d.dns_name IS NOT NULL 
                THEN 'Да' 
                ELSE 'Нет'
            END AS dns,
            CASE
                WHEN s.sw_name IS NOT NULL
                OR s.sw_domain IS NOT NULL
                OR s.sw_server IS NOT NULL
                THEN 'Да' 
                ELSE 'Нет'
            END AS sw
        FROM (VALUES {$keItemsString}) AS i(rsp_server)
        LEFT JOIN r_s_p_s e ON i.rsp_server COLLATE Latin1_General_CI_AS = e.host COLLATE Latin1_General_CI_AS
        LEFT JOIN dns d ON d.dns_name COLLATE Latin1_General_CI_AS = i.rsp_server COLLATE Latin1_General_CI_AS
        LEFT JOIN solar_winds s ON s.sw_domain COLLATE Latin1_General_CI_AS = i.rsp_server COLLATE Latin1_General_CI_AS
            OR s.sw_name COLLATE Latin1_General_CI_AS = i.rsp_server COLLATE Latin1_General_CI_AS
            OR s.sw_server COLLATE Latin1_General_CI_AS = i.rsp_server COLLATE Latin1_General_CI_AS
        LEFT JOIN c_m_d_b_vs v ON i.rsp_server COLLATE Latin1_General_CI_AS = v.ci_name COLLATE Latin1_General_CI_AS
        LEFT JOIN c_m_d_b_phs ph ON i.rsp_server COLLATE Latin1_General_CI_AS = ph.ci_name COLLATE Latin1_General_CI_AS
        WHERE i.rsp_server IS NOT NULL AND i.rsp_server NOT LIKE ''
		ORDER BY i.rsp_server;";
    }   

    private function table4_ke($keItemsString) {
        return "SELECT DISTINCT
                i.rsp_server AS i_server,
                CASE
                    WHEN v.ci_name IS NOT NULL THEN 'виртуальный'
                    WHEN ph2.ci_name IS NOT NULL THEN 'физический'
                    ELSE 'не найден в CMDB'
                END AS t_type,
                v.vm_os AS v_os,
                v.vm_platform AS platform_virt,
                v.vm_host AS v_host,

                v.vm_vcenter AS v_vcenter,
                CASE 
                    WHEN v.ci_name IS NOT NULL THEN ph.vcenter_name
                    WHEN ph2.ci_name IS NOT NULL THEN ph2.vcenter_name
                END AS p_vcenter,

                CASE 
                    WHEN v.vm_vcenter IS NULL AND ph.vcenter_name IS NULL THEN NULL
                    WHEN v.vm_vcenter COLLATE Latin1_General_CI_AS = ph.vcenter_name COLLATE Latin1_General_CI_AS THEN 'Ok' 
                    ELSE 'Некорректно' 
                END AS vcenter,
                
                v.vm_cluster AS v_cluster,
                CASE 
                    WHEN v.ci_name IS NOT NULL THEN ph.cluster
                    WHEN ph2.ci_name IS NOT NULL THEN ph2.cluster
                END AS p_cluster,

                CASE 
                    WHEN v.vm_cluster IS NULL AND ph.cluster IS NULL THEN NULL
                    WHEN v.vm_cluster COLLATE Latin1_General_CI_AS = ph.cluster COLLATE Latin1_General_CI_AS THEN 'Ok' 
                    ELSE 'Некорректно' 
                END AS cluster, 
                CASE 
                    WHEN v.ci_name IS NOT NULL THEN ph.ci_name
                    WHEN ph2.ci_name IS NOT NULL THEN ph2.ci_name
                END AS p_name,
                CASE 
                    WHEN v.ci_name IS NOT NULL THEN ph.ci_os
                    WHEN ph2.ci_name IS NOT NULL THEN ph2.ci_os
                END AS p_os,
                        CASE 
                    WHEN v.ci_name IS NOT NULL THEN ph.ci_vendor
                    WHEN ph2.ci_name IS NOT NULL THEN ph2.ci_vendor
                END AS p_vendor
                FROM (VALUES {$keItemsString}) AS i(rsp_server)
                LEFT JOIN c_m_d_b_vs v ON i.rsp_server COLLATE Latin1_General_CI_AS = v.ci_name COLLATE Latin1_General_CI_AS
                LEFT JOIN c_m_d_b_phs ph2 ON i.rsp_server COLLATE Latin1_General_CI_AS = ph2.ci_name COLLATE Latin1_General_CI_AS
                LEFT JOIN c_m_d_b_phs ph ON ph.cluster COLLATE Latin1_General_CI_AS = v.vm_cluster COLLATE Latin1_General_CI_AS
                    AND ph.vcenter_name COLLATE Latin1_General_CI_AS = v.vm_vcenter COLLATE Latin1_General_CI_AS
                WHERE i.rsp_server IS NOT NULL AND i.rsp_server NOT LIKE ''
                ORDER BY t_type, i.rsp_server;";
    }

    private function isServerReachable($hostname) {
        $output = shell_exec("nslookup $hostname");
        if (strpos($output, "Server:") !== false && strpos($output, $hostname) !== false) {
            return true;
        } else {
            return false;
        }
    }

    private function isServerReachableIp($hostname) {
        $output = shell_exec("dig +short $hostname");
        return !empty($output);
    }

    private function getItColor($item) {
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
            'veeam', 'red hat', 'project okd', 'fedora project', 
            'infinidat', 'centos', 'cloudian', 'windows server', 'esxi',
            'cisco'
        ];

        foreach ($green_list as $keyword) {
            if (strpos($item_lower, $keyword) !== false) {
                return '98FB98';
            }
        }

        foreach ($yellow_list as $keyword) {
            if (strpos($item_lower, $keyword) !== false) {
                return 'FFAD66';
            }
        }

        foreach ($red_list as $keyword) {
            if (strpos($item_lower, $keyword) !== false) {
                return 'FE8173';
            }
        }
        return 'FFFFFF';
    }
    
    private function cleanData($value) {
        return trim($value);
    }

    private function nullData($value) {
        return isset($value) && !empty($value->getValue()) ? $this->cleanData($value->getValue()) : null;
    }

    private function uploadFile(Request $request) {
        // $request->validate([
        //     'inputFile' => 'required',
        // ]);
  
        $filePath = $request->file('inputFile')->store('input_files');
        $fullFilePath = str_replace('\\', '/', storage_path('app/private/' . $filePath));

        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($fullFilePath);
        
        // Input::truncate();

        $rowIndex = 0;
        $batchData = []; 
        $batchSize = 100; 
        $validationIssue = [];

        DB::beginTransaction();
        try {
        
        $uid = (string) Str::uuid();

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {

                $rowIndex++;
                if ($rowIndex < 3) {
                    continue;
                }

                $cells = $row->getCells();

                $brCode = $this->nullData($cells[0]); 
                $brName = $this->nullData($cells[1]); 
                $brCrit = $this->nullData($cells[2]);
                $brRto = $this->nullData($cells[3]);
                $brRpo = $this->nullData($cells[4]);

                $bsCode = $this->cleanData($cells[5]->getValue());
                $bsName = $this->cleanData($cells[6]->getValue());
                $bsRto = $this->cleanData($cells[7]->getValue());
                $bsRpo = $this->cleanData($cells[8]->getValue());

                $rsp = $this->nullData($cells[9]);
                $rspParts = explode('@', $rsp, 2);
                $rspPlatform = isset($rspParts[0]) ? trim($rspParts[0]) : null;
                $rspServer = isset($rspParts[1]) ? trim($rspParts[1]) : null;
    
                if (!preg_match('/^22\d{4}$/', $bsCode)) {
                    $validationIssue[] = ['row' => $rowIndex, 'issue' => 'Формат кода БС отличается.'];
                }
                if (!preg_match('/[a-zA-Zа-яА-Я]/', $bsName)) {
                    $validationIssue[] = ['row' => $rowIndex, 'issue' => 'Формат имени БС отличается.'];
                }
                if (!is_numeric($bsRto) || $bsRto < 0 || $bsRto > 300) {
                    $validationIssue[] = ['row' => $rowIndex, 'issue' => 'RTO должен быть представлен числом от 0 до 300'];
                }
                if (!is_numeric($bsRpo) || $bsRpo < 0 || $bsRpo > 200) {
                    $validationIssue[] = ['row' => $rowIndex, 'issue' => 'RPO должен быть представлен числом от 0 до 200'];
                }
                // if (!preg_match('/[a-zA-Zа-яА-Я]+\s*@\s*[\w.-]+/', $rsp)) {
                //     if (filter_var($rsp, FILTER_VALIDATE_IP)) {
                //         $validationIssue[] = [
                //             'row' => $rowIndex, 
                //             'issue' => 'В РСП использован IP'];
                //     }
                // }

                $batchData[] = [
                    'uid' => $uid,
                    'br_code' => $brCode,
                    'br_name' => $brName,
                    'br_criticaty' => $brCrit,
                    'br_rto' => $brRto,
                    'br_rpo' => $brRpo,

                    'bs_code' => $bsCode,
                    'bs_name' => $bsName,
                    'bs_rto' => $bsRto,
                    'bs_rpo' => $bsRpo,

                    'rsp' => $rsp,
                    'rsp_system_platform' => $rspPlatform,
                    'rsp_server' => $rspServer,
                ];
                
                if (count($batchData) >= $batchSize) {
                    Input::insert($batchData);
                    $batchData = [];
                }
            }
            break;
        }

        if (!empty($batchData)) {
            Input::insert($batchData);
        }

        DB::commit();
        $reader->close();

        if (file_exists($fullFilePath)) {
            unlink($fullFilePath);
        }

        return [$validationIssue, $uid];
    } catch (\Exception $e) {

            DB::rollback();
            error_log('Error during data insertion: ' . $e->getMessage());
            return back()->withErrors('Ошибка при загрузке файла: ' . $e->getMessage());
        }
    }

    public function uploadBsFile(Request $request)
    {
        [$validation, $uid] = $this->uploadFile($request);

        $results = DB::select(self::table1 . ' WHERE i.uid = ?', [$uid]);

        session(['analysis_bs_' . $uid => $results]);
        
        Input::where('uid', $uid)->delete();

        return view('analysis_bs', ['uid' => $uid, 'results' => $results, 'validation' => $validation])
            ->with('success', 'Входной файл загружен. Данные успешно сохранены.');
    }
    
    public function uploadBrFile(Request $request)
    {
        [$validation, $uid] = $this->uploadFile($request);

        $results = DB::select(self::table2 . ' WHERE i.uid = ?', [$uid]);
        
        session(['analysis_br_' . $uid => $results]);

        return view('analysis_br', ['uid' => $uid, 'results' => $results, 'validation' => $validation])
            ->with('success', 'Входной файл загружен. Данные успешно сохранены.');
    }

    public function uploadActualFile(Request $request)
    {
        [$validation, $uid] = $this->uploadFile($request);

        $results = DB::select(self::table3 . ' AND i.uid = ? ORDER BY i.rsp_server', [$uid]);
        
        session(['analysis_esis_' . $uid => $results]);

        return view('analysis_esis', ['uid' => $uid, 'results' => $results, 'validation' => $validation])
            ->with('success', 'Входной файл загружен. Данные успешно сохранены.');
    }

    public function uploadActualKe(Request $request)
    {
        $keList = $request->input('ke_list');
        $keItems = array_filter(array_map('trim', explode("\n", $keList)));
    
        if (empty($keItems)) {
            return redirect()->route('esis.page')
                ->with('error', 'Список КЕ пуст. Пожалуйста, введите хотя бы один элемент.');
        }

        $keItemsString = "('" . implode("'), ('", $keItems) . "')";
        $query = $this->table3_ke($keItemsString);

        $results = DB::select($query);

        $uid = (string) Str::uuid();

        session(['analysis_esis_' . $uid => $results]);

        return view('analysis_esis', ['uid' => $uid, 'results' => $results])
            ->with('success', 'Данные успешно обработаны.');
    }

    public function uploadImportFile(Request $request)
    {
        [$validation, $uid] = $this->uploadFile($request);

        $results = DB::select(self::table4 . ' AND i.uid = ? ORDER BY t_type, i.rsp_server', [$uid]);
        
        session(['analysis_import_' . $uid => $results]);

        return view('analysis_import', ['uid' => $uid, 'results' => $results, 'validation' => $validation])
            ->with('success', 'Входной файл загружен. Данные успешно обработаны.');   
    }

    public function uploadImportKe(Request $request)
    {
        $keList = $request->input('ke_list');
        $keItems = array_filter(array_map('trim', explode("\n", $keList)));
    
        if (empty($keItems)) {
            return redirect()->route('import.page')
                ->with('error', 'Список КЕ пуст. Пожалуйста, введите хотя бы один элемент.');
        }

        $keItemsString = "('" . implode("'), ('", $keItems) . "')";
        $query = $this->table4_ke($keItemsString);

        $results = DB::select($query); 
        
        $uid = (string) Str::uuid();

        session(['analysis_import_' . $uid => $results]);

        return view('analysis_import', ['uid' => $uid, 'results' => $results])
            ->with('success', 'Данные успешно обработаны.');
    }

    public function downloadAnalysisEsis($uid)
    {
        $results = session('analysis_esis_' . $uid, []);

        if (empty($results)) {
            return redirect()->back()->with('error', 'Нет данных для экспорта.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = [
            'Сервер', 'Платформа', 'ОС (ЕСИС)', 'ОС (CMDB)', 'Актуальность ОС', 'ЕСИС', 'DNS', 'SW'
        ];
        $sheet->fromArray($headers, null, 'A1');

        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('C3E6CB');
        
        $rowIndex = 2;

        foreach ($results as $result) {
            $serverColor = ($result->esis == 'Нет' && $result->dns == 'Нет' && $result->sw == 'Нет') ? 'FFE4E1' : 'FFFFFF';
            $platformColor = empty($result->platform) ? 'FFFFE0' : 'FFFFFF';
            $esisOsColor = empty($result->esis_os) ? 'FFFFE0' : 'FFFFFF';
            $cmdbOsColor = empty($result->cmdb_os) ? 'FFFFE0' : 'FFFFFF';
            $esisColor = $result->esis == 'Да' ? '98FB98' : 'FFE4E1';
            $dnsColor = $result->dns == 'Да' ? '98FB98' : 'FFE4E1';
            $swColor = $result->sw == 'Да' ? '98FB98' : 'FFE4E1';

            $sheet->setCellValue("A{$rowIndex}", $result->i_server);
            $sheet->setCellValue("B{$rowIndex}", $result->platform);
            $sheet->setCellValue("C{$rowIndex}", $result->esis_os);
            $sheet->setCellValue("D{$rowIndex}", $result->cmdb_os);
            $sheet->setCellValue("E{$rowIndex}", $result->actuality);
            $sheet->setCellValue("F{$rowIndex}", $result->esis);
            $sheet->setCellValue("G{$rowIndex}", $result->dns);
            $sheet->setCellValue("H{$rowIndex}", $result->sw);

            $sheet->getStyle("A{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($serverColor);
            $sheet->getStyle("B{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($platformColor);
            $sheet->getStyle("C{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($esisOsColor);
            $sheet->getStyle("D{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($cmdbOsColor);
            $sheet->getStyle("F{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($esisColor);
            $sheet->getStyle("G{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($dnsColor);
            $sheet->getStyle("H{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($swColor);

            $rowIndex++;
        }

        $fileName = 'esis_' . $uid . '.xlsx';
        $tempFile = storage_path('app/' . $fileName);
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);
        session()->forget('analysis_esis_' . $uid);
        return response()->download($tempFile)->deleteFileAfterSend(true);
    }

    public function downloadAnalysisBs($uid)
    {
        $results = session('analysis_bs_' . $uid, []);

        if (empty($results)) {
            return redirect()->back()->with('error', 'Нет данных для экспорта.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = [
            'Код (вход)', 'Имя (вход)', 'Имя (ЕСИС)', 'RTO (вход)', 'RTO (ЕСИС)',
            'RPO (вход)', 'RPO (ЕСИС)', 'HA (ЕСИС)', 'DR (ЕСИС)', 'Имя', 'RTO',
            'RPO', 'Статус', 'Сервера', 'Отказоустойчивость'
        ];
        $sheet->fromArray($headers, null, 'A1');

        $sheet->getStyle('A1:O1')->getFont()->setBold(true);
        $sheet->getStyle('A1:O1')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('C3E6CB');
        
        $rowIndex = 2;

        foreach ($results as $result) {
            $sheet->setCellValue("A{$rowIndex}", $result->i_code);
            $sheet->setCellValue("B{$rowIndex}", $result->i_name);
            $sheet->setCellValue("C{$rowIndex}", $result->e_name);
            $sheet->setCellValue("D{$rowIndex}", $result->i_rto);
            $sheet->setCellValue("E{$rowIndex}", $result->e_rto);
            $sheet->setCellValue("F{$rowIndex}", $result->i_rpo);
            $sheet->setCellValue("G{$rowIndex}", $result->e_rpo);
            $sheet->setCellValue("H{$rowIndex}", $result->e_ha);
            $sheet->setCellValue("I{$rowIndex}", $result->e_dr);

            $sheet->setCellValue("J{$rowIndex}", $result->name);
            $sheet->setCellValue("K{$rowIndex}", $result->rto);
            $sheet->setCellValue("L{$rowIndex}", $result->rpo);
            $sheet->setCellValue("M{$rowIndex}", $result->ha_dr_status);
            $sheet->setCellValue("N{$rowIndex}", $result->server);
            $sheet->setCellValue("O{$rowIndex}", $result->fault_tolerance);

            $nameI = empty($result->i_name) ? 'FFFFE0' : 'FFFFFF';
            $nameE = empty($result->e_name) ? 'FFFFE0' : 'FFFFFF';
            $rtoI = empty($result->i_rto) ? 'FFFFE0' : 'FFFFFF';
            $rtoE = empty($result->e_rto) ? 'FFFFE0' : 'FFFFFF';
            $rpoI = empty($result->i_rpo) ? 'FFFFE0' : 'FFFFFF';
            $rpoE = empty($result->e_rpo) ? 'FFFFE0' : 'FFFFFF';
            $ha = empty($result->e_ha) ? 'FFFFE0' : 'FFFFFF';
            $dr = empty($result->e_dr) ? 'FFFFE0' : 'FFFFFF';

            $name = $result->name == 'Ok' ? '98FB98' : 'FFE4E1';
            $rto = $result->rto == 'Ok' ? '98FB98' : 'FFE4E1';
            $rpo = $result->rpo == 'Ok' ? '98FB98' : 'FFE4E1';
            $ha_dr = $result->ha_dr_status == 'Ok' ? '98FB98' : 'FFE4E1';
            $srvr = empty($result->server) ? 'FFFFE0' : 'FFFFFF';
            $fault = empty($result->fault_tolerance) ? 'FFFFE0' : 'FFFFFF';

            $sheet->getStyle("B{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($nameI);
            $sheet->getStyle("C{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($nameE);
            $sheet->getStyle("D{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rtoI);
            $sheet->getStyle("E{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rtoE);
            $sheet->getStyle("F{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rpoI);
            $sheet->getStyle("G{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rpoE);
            $sheet->getStyle("H{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($ha);
            $sheet->getStyle("I{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($dr);

            $sheet->getStyle("J{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($name);
            $sheet->getStyle("K{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rto);
            $sheet->getStyle("L{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rpo);
            $sheet->getStyle("M{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($ha_dr);
            $sheet->getStyle("N{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($srvr);
            $sheet->getStyle("O{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($fault);

            $rowIndex++;
        }

        $fileName = 'bs_' . $uid . '.xlsx';
        $tempFile = storage_path('app/' . $fileName);
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);
        session()->forget('analysis_bs_' . $uid);
        return response()->download($tempFile)->deleteFileAfterSend(true);
    }

    public function downloadAnalysisBr($uid)
    {
        $results = session('analysis_br_' . $uid, []);

        if (empty($results)) {
            return redirect()->back()->with('error', 'Нет данных для экспорта.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = [
            'Код (вход)', 'Имя (вход)', 'RTO (вход)', 'RPO (вход)', 'Критичность (вход)',
            'Имя (КТ-670)', 'RTO (КТ-670)', 'RPO (КТ-670)', 'Критичность (КТ-670)',
            'Имя', 'RTO', 'RPO', 'Критичность', 'Статус'
        ];
        $sheet->fromArray($headers, null, 'A1');

        $sheet->getStyle('A1:N1')->getFont()->setBold(true);
        $sheet->getStyle('A1:N1')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('C3E6CB');
        
        $rowIndex = 2;

        foreach ($results as $result) {
            $sheet->setCellValue("A{$rowIndex}", $result->i_code);
            $sheet->setCellValue("B{$rowIndex}", $result->i_name);
            $sheet->setCellValue("C{$rowIndex}", $result->i_rto);
            $sheet->setCellValue("D{$rowIndex}", $result->i_rpo);
            $sheet->setCellValue("E{$rowIndex}", $result->i_crit);
            $sheet->setCellValue("F{$rowIndex}", $result->k_name);
            $sheet->setCellValue("G{$rowIndex}", $result->k_rto);
            $sheet->setCellValue("H{$rowIndex}", $result->k_rpo);
            $sheet->setCellValue("I{$rowIndex}", $result->k_crit);

            $sheet->setCellValue("J{$rowIndex}", $result->name);
            $sheet->setCellValue("K{$rowIndex}", $result->rto);
            $sheet->setCellValue("L{$rowIndex}", $result->rpo);
            $sheet->setCellValue("M{$rowIndex}", $result->crit);
            $sheet->setCellValue("N{$rowIndex}", $result->ha_dr_status);

            $nameI = empty($result->i_name) ? 'FFFFE0' : 'FFFFFF';
            $rtoI = empty($result->i_rto) ? 'FFFFE0' : 'FFFFFF';
            $rpoI = empty($result->i_rpo) ? 'FFFFE0' : 'FFFFFF';
            $critI = empty($result->i_crit) ? 'FFFFE0' : 'FFFFFF';
            $nameK = empty($result->k_name) ? 'FFFFE0' : 'FFFFFF';
            $rtoK = empty($result->k_rto) ? 'FFFFE0' : 'FFFFFF';
            $rpoK = empty($result->k_rpo) ? 'FFFFE0' : 'FFFFFF';
            $critK = empty($result->k_crit) ? 'FFFFE0' : 'FFFFFF';

            $name = $result->name == 'Ok' ? '98FB98' : 'FFE4E1';
            $rto = $result->rto == 'Ok' ? '98FB98' : 'FFE4E1';
            $rpo = $result->rpo == 'Ok' ? '98FB98' : 'FFE4E1';
            $crit = $result->crit == 'Ok' ? '98FB98' : 'FFE4E1';
            $ha_dr = $result->ha_dr_status == 'Ok' ? '98FB98' : 'FFE4E1';

            $sheet->getStyle("B{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($nameI);
            $sheet->getStyle("C{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rtoI);
            $sheet->getStyle("D{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rpoI);
            $sheet->getStyle("E{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($critI);
            $sheet->getStyle("F{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($nameK);
            $sheet->getStyle("G{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rtoK);
            $sheet->getStyle("H{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rpoK);
            $sheet->getStyle("I{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($critK);
            $sheet->getStyle("J{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($name);
            $sheet->getStyle("K{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rto);
            $sheet->getStyle("L{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($rpo);
            $sheet->getStyle("M{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($crit);
            $sheet->getStyle("N{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($ha_dr);

            $rowIndex++;
        }

        $fileName = 'br_' . $uid . '.xlsx';
        $tempFile = storage_path('app/' . $fileName);
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);
        session()->forget('analysis_br_' . $uid);
        return response()->download($tempFile)->deleteFileAfterSend(true);
    }

    public function downloadAnalysisImport($uid)
    {
        $results = session('analysis_import_' . $uid, []);

        if (empty($results)) {
            return redirect()->back()->with('error', 'Нет данных для экспорта.');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $headers = [
            'Сервер', 'Тип', 'ОС (вирт.)', 'Платформа виртуализации', 'Хост', 'vCenter (вирт.)', 'vCenter (физ.)', 'Корректность vCenter',
            'Кластер (вирт.)', 'Кластер (физ.)', 'Корректность кластера', 'Имя физ. сервера', 'ОС', 'Вендор'
        ];
        $sheet->fromArray($headers, null, 'A1');

        $sheet->getStyle('A1:N1')->getFont()->setBold(true);
        $sheet->getStyle('A1:N1')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('C3E6CB');
        
        $rowIndex = 2;

        foreach ($results as $result) {
            $sheet->setCellValue("A{$rowIndex}", $result->i_server);
            $sheet->setCellValue("B{$rowIndex}", $result->t_type);
            $sheet->setCellValue("C{$rowIndex}", $result->v_os);
            $sheet->setCellValue("D{$rowIndex}", $result->platform_virt);
            $sheet->setCellValue("E{$rowIndex}", $result->v_host);
            $sheet->setCellValue("F{$rowIndex}", $result->v_vcenter);
            $sheet->setCellValue("G{$rowIndex}", $result->p_vcenter);
            $sheet->setCellValue("H{$rowIndex}", $result->vcenter);
            $sheet->setCellValue("I{$rowIndex}", $result->v_cluster);
            $sheet->setCellValue("J{$rowIndex}", $result->p_cluster);
            $sheet->setCellValue("K{$rowIndex}", $result->cluster);
            $sheet->setCellValue("L{$rowIndex}", $result->p_name);
            $sheet->setCellValue("M{$rowIndex}", $result->p_os);
            $sheet->setCellValue("N{$rowIndex}", $result->p_vendor);

            $type = $result->t_type == 'не найден в CMDB' ? 'FFFFE0' : 'FFFFFF';
            $vOs = empty($result->v_os) ? 'FFFFE0' : 'FFFFFF';
            $platform = empty($result->platform_virt) ? 'FFFFE0' : 'FFFFFF';
            $host = empty($result->v_host) ? 'FFFFE0' : 'FFFFFF';
            $vC = empty($result->v_vcenter) ? 'FFFFE0' : 'FFFFFF';
            $pC = empty($result->p_vcenter) ? 'FFFFE0' : 'FFFFFF';
            $v = $result->vcenter == 'Ok' ? '98FB98' : 'FFE4E1';
            $vCl = empty($result->v_cluster) ? 'FFFFE0' : 'FFFFFF';
            $pCl = empty($result->p_cluster) ? 'FFFFE0' : 'FFFFFF';
            $cl = $result->cluster == 'Ok' ? '98FB98' : 'FFE4E1';

            $sheet->getStyle("B{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($type);
            $sheet->getStyle("C{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($vOs);
            $sheet->getStyle("D{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($platform);
            $sheet->getStyle("F{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($host);
            $sheet->getStyle("G{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($vC);
            $sheet->getStyle("H{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($pC);
            $sheet->getStyle("I{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($v);
            $sheet->getStyle("J{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($vCl);
            $sheet->getStyle("K{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($pCl);
            $sheet->getStyle("L{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($cl);
            $sheet->getStyle("M{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($this->getItColor($result->p_os));
            $sheet->getStyle("N{$rowIndex}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($this->getItColor($result->p_vendor));

            $rowIndex++;
        }

        $fileName = 'import_' . $uid . '.xlsx';
        $tempFile = storage_path('app/' . $fileName);
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);
        session()->forget('analysis_import_' . $uid);
        return response()->download($tempFile)->deleteFileAfterSend(true);
    }
}
