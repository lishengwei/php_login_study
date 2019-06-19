<?php
global $APP_CONFIGS, $exist;
$APP_CONFIGS = [
    'dir'       => './files',
    'product_configs'    => [
        'BOL3M_DRV_V11.S19' => [
            'device_type' => 'BOL3M_DRV_V11.S19', 'row_key' => 'B20000A2', 'qty' => 40,
        ],
        'DR_BOL256_VROFF_V04.S19' => [
            'device_type' => 'DR_BOL256_VROFF_V04.S19', 'row_key' => 'B20000A2', 'qty' => 55,
        ],
        'DR_BOL256_VROFF_64LD_V04.S19' => [
            'device_type' => 'DR_BOL256_VROFF_64LD_V04.S19', 'row_key' => 'B20000A2', 'qty' => 72,
        ],
        'BOL2_DRV_64_V38B.S19' => [
            'device_type' => 'BOL2_DRV_64_V38B.S19', 'row_key' => 'B20000A2', 'qty' => 72,
        ],
        'M5675_DRV_V32.S19/M5675_DRV_V36.S19' => [
            'device_type' => 'M5675_DRV_V32.S19/M5675_DRV_V36.S19', 'row_key' => 'B20000A2',
            'qty' => ['010257' => 24, '010357' => 36]
        ],
        'DR_PICTUS256_021.S19' => [
            'device_type' => 'DR_PICTUS256_021.S19', 'row_key' => 'B20000A2', 'qty' => 55,
        ],
        'DR_PICTUS256_64l.S19' => [
            'device_type' => 'DR_PICTUS256_64l.S19', 'row_key' => 'B20000A2', 'qty' => 72,
        ],
        'SALSA_DRV_V10.S19' => [
            'device_type' => 'SALSA_DRV_V10.S19', 'row_key' => 'B20000A2', 'qty' => 72,
        ],
        'FS8500_HTOL_R5P1.S19' => [
            'device_type' => 'FS8500_HTOL_R5P1.S19', 'row_key' => '39800000', 'qty' => 40,
        ]
    ],
];

if (!is_dir($APP_CONFIGS['dir'])) {
    exit("不存在文件目录，程序退出");
}

$dirHandle  = dir($APP_CONFIGS['dir']);
$handle     = fopen($APP_CONFIGS['dir'] . '/result.csv', 'w+');
$count      = $count1 = 0;
$header     = [
    'Lot Number', 'BIB Number', 'Pass Qty1', 'Pass Qty2'
];
for ($si = 1;$si < 73; $si ++) {
    $header[] = "Socket". str_pad($si, 2, '0',STR_PAD_LEFT);
}
fputcsv($handle, $header);
while ($file = $dirHandle->read()) {
    if ($file == '.' || $file == '..') {
        continue;
    }
    $fileNameInfo = explode('_', $file);
    if ($fileNameInfo[0] != "B2") {
        continue;
    }
    $bibInfos   = getNumberInfos($file);
    $result     = getResult($bibInfos);
    if (!isset($result['number_infos']) || empty($result['number_infos'])) {
        continue;
    }

    foreach ($result['number_infos'] as $numberInfo) {
        if (!isset($numberInfo['result']) || empty($numberInfo['result'])) {
            continue;
        }
        $repeatNum = 0;
        $numbers = [];
        //去重复
        foreach ($numberInfo['result'] as $v) {
            if ($v != "F" && isset($exist[$v])) {
                $numbers[] = "C";
                $repeatNum++;
            } else {
                $numbers[] = "{$v}";
                $exist[$v] = $v;
            }
        }
echo $numberInfo['all_num'] . " " . $numberInfo['error_num'] . " " . $repeatNum . "\n";
        $lineArray = [
            $result['file_name_key'],
            "\"{$numberInfo['number']}\"",
            $numberInfo['all_num'] - $numberInfo['error_num'],
            $numberInfo['all_num'] - $numberInfo['error_num'] - $repeatNum,
        ];

        foreach ($numbers as $number) {
            $lineArray[] = "\"{$number}\"";
        }
        fputcsv($handle, $lineArray);
        $count = $count + $lineArray[3];
        $count1 = $count1 + $lineArray[2];
    }
}
fputcsv($handle, ['', '', $count1, $count]);
fclose($handle);
echo "执行成功";



/**
 * 读取B2文件，获取对应的编号和错误位置
 * @param $fileName
 * @return array
 */
function getNumberInfos($fileName)
{
    global $APP_CONFIGS;
    $fileNameInfo = explode('_', $fileName);
    list($secondFileName, $extend) = explode('.', $fileNameInfo[2]);
    $return = [];
    $firstFileHandler = fopen($APP_CONFIGS['dir'] . '/' . $fileName, 'r');
    while (($firstFileLineContent = fgets($firstFileHandler)) !== false) {
        $firstFileLineContent = trim($firstFileLineContent);
        if (empty($firstFileLineContent)) {
            continue;
        }
        $lineContentArray = explode('###', $firstFileLineContent);
        if (isset($lineContentArray[1]) && isset($lineContentArray[2])) {
            $item = [
                'number'        => $lineContentArray[1],
                'keys'          => array_unique(array_filter(array_map('intval', explode(',', $lineContentArray[2])))),
            ];
            $return['number_infos'][$lineContentArray[1]] = $item;
        }
    }
    fclose($firstFileHandler);
    if (!empty($return['number_infos'])) {
        $return['file_name_key'] = $secondFileName;
    }
    return $return;
}

function getResult($params)
{
    global $APP_CONFIGS;
    $fileName   = $APP_CONFIGS['dir'] . '/' . $params['file_name_key'] . '_DriverMonitor.log';

    if (!file_exists($fileName)) {
        return [];
    }
    $deviceType = '';
    $secondFileHandler = fopen($fileName, 'r');
    while (($secondFileLineContent = fgets($secondFileHandler)) !== false) {
        $secondFileLineContent = trim($secondFileLineContent);
        if (empty($secondFileLineContent)) {
            continue;
        }
        $deviceTypePos = strpos($secondFileLineContent, 'Primary Diag:');
        if ($deviceTypePos !== false) {
            $deviceTypeStr      = trim(substr($secondFileLineContent, $deviceTypePos + 13));
            $deviceTypeInfos    = explode(',', $deviceTypeStr);
            $deviceType         = strtoupper(trim($deviceTypeInfos[0]));
            break;
        }
    }

    if (empty($deviceType) || empty($APP_CONFIGS['product_configs'][$deviceType])) {
        return [];
    }
    $config = $APP_CONFIGS['product_configs'][$deviceType];
    while (($secondFileLineContent = fgets($secondFileHandler)) !== false) {
        $secondFileLineContent = trim($secondFileLineContent);
        $errorNum = $effectNum = 0;
        if (empty($secondFileLineContent)) {
            continue;
        }
        $rowArray   = explode(',', $secondFileLineContent);
        $keyPos     = false;
        $rowArrayCount = 0;
        is_array($rowArray) && $rowArrayCount = count($rowArray);
        $rowArrayKeyNum = $rowArrayCount - 2;
        if ($rowArrayKeyNum > 0 && $rowArray[$rowArrayKeyNum] == $config['row_key']) {
            $keyPos = true;
        }
        if ($keyPos === false) {
            continue;
        }

        $didNum     = $rowArray[3];
        if (!isset($params['number_infos'][$didNum])) {
            continue;
        }
        $qty = $config['qty'];
        if (is_array($qty)) {
            foreach ($qty as $didKey => $qtyNum) {
                if (strpos($didNum, $didKey) !== false) {
                    $qty = $qtyNum;
                    break;
                }
            }
        }

        for ($i = 1; $i <= $qty; $i++) {
            if (in_array($i, $params['number_infos'][$didNum]['keys'])) {
                $id = "F";
                $errorNum ++;
            } else {
                $k          = $i + 5;
                $id         = (string)$rowArray[$k];
            }
            $params['number_infos'][$didNum]['result'][$i] = $id;
        }

        $params['number_infos'][$didNum]['error_num']   = $errorNum;
        $params['number_infos'][$didNum]['all_num']     = count($params['number_infos'][$didNum]['result']);
    }

    $params['device_type'] = $deviceType;
    return $params;
}


