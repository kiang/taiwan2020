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
            if(false === strpos($data['區域別'], '南市')) {
                continue;
            }
            $data['區域別'] = str_replace(array('　', ' '), '', $data['區域別']);
            if(!isset($population[$data['區域別']])) {
                $population[$data['區域別']] = [
                    'name' => $data['區域別'],
                ];
            }
            $yKey = $year . '12';
            if(!isset($population[$data['區域別']][$yKey])) {
                $population[$data['區域別']][$yKey] = 0;
            }
            $population[$data['區域別']][$yKey] += $data['人口數'];
        }    
    }
}
foreach($population AS $code => $data) {
    $population[$code]['change'] = $population[$code][202012] - $population[$code][201812];
    $population[$code]['rate'] = round($population[$code]['change'] / $population[$code][201812], 4) * 100;
}
usort($population, "cmp");

$oFh = fopen(dirname(__DIR__) . '/tainan_area.csv', 'w');
fputcsv($oFh, array_keys($population[0]));
foreach($population AS $data) {
    fputcsv($oFh, $data);
}

function cmp($a, $b)
{
    return $a['change'] < $b['change'];
}