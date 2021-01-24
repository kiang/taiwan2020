<?php
$rootPath = dirname(dirname(__DIR__));

$population = [];
for($year = 2014; $year < 2021; $year++) {
    foreach(glob($rootPath . '/tw_population/村里戶數人口數單一年齡人口數/' . $year . '/12/*.csv') AS $csvFile) {
        $fh = fopen($csvFile, 'r');
        $lineCount = 0;
        $head = fgetcsv($fh, 4000);
        $check = array_combine($head, $head);
        if(!isset($check['區域別'])) {
            $head = fgetcsv($fh, 4000);
        }
        while($line = fgetcsv($fh, 4000)) {
            $data = array_combine($head, $line);
            $data['區域別'] = str_replace(array('　', ' '), '', $data['區域別']);
            switch($data['區域別']) {
                case '苗栗縣頭份鎮':
                    $data['區域別'] = '苗栗縣頭份市';
                    break;
                case '彰化縣員林鎮':
                    $data['區域別'] = '彰化縣員林市';
                    break;
            }
            if(!isset($population[$data['區域別']])) {
                $population[$data['區域別']] = [
                    'name' => $data['區域別'],
                ];
            }
            $yKey = $year . '12';
            if(!isset($population[$data['區域別']][$yKey])) {
                $population[$data['區域別']][$yKey] = 0;
            }
            for($i = 0; $i < 15; $i++) {
                $population[$data['區域別']][$yKey] += $data[$i . '歲-男'];
                $population[$data['區域別']][$yKey] += $data[$i . '歲-女'];
            }
        }    
    }
}

$toMerge = [
    '高雄市三民一' => '高雄市三民區',
    '高雄市三民二' => '高雄市三民區',
    '高雄市鳳山一' => '高雄市鳳山區',
    '高雄市鳳山二' => '高雄市鳳山區',
];

foreach($toMerge AS $from => $to) {
    foreach($population[$from] AS $k => $v) {
        if($k === 'name') {
            continue;
        }
        if(!isset($population[$to][$k])) {
            $population[$to][$k] = 0;
        }
        $population[$to][$k] += intval($v);
    }
    unset($population[$from]);
}

foreach($population AS $code => $data) {
    $population[$code]['change'] = $population[$code][202012] - $population[$code][201812];
    $population[$code]['rate'] = round($population[$code]['change'] / $population[$code][201812], 4) * 100;
}
usort($population, "cmp");

$oFh = fopen(dirname(__DIR__) . '/taiwan_aged_area.csv', 'w');
fputcsv($oFh, array_keys($population[0]));
foreach($population AS $data) {
    fputcsv($oFh, $data);
}

function cmp($a, $b)
{
    return $a['rate'] < $b['rate'];
}