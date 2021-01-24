<?php
$rootPath = dirname(dirname(__DIR__));

$population = [];
for($year = 2017; $year < 2021; $year++) {
    $m = 12;
    $csvFile = $rootPath . '/tw_population/村里戶數人口數單一年齡人口數/' . $year . '/' . $m . '/data.csv';
    $fh = fopen($csvFile, 'r');
    $head = fgetcsv($fh, 4000);
    fgetcsv($fh, 4000);
    while($line = fgetcsv($fh, 4000)) {
        $line[1] = trim($line[1]);
        if(substr($line[1], 0, 2) !== '67') {
            continue;
        }
        
        if(!isset($population[$line[1]])) {
            $population[$line[1]] = [
                $line[2] . $line[3]
            ];
        }
        $population[$line[1]][$year . $m] = 0;
        for($i = 8; $i <= 37; $i++) {
            $population[$line[1]][$year . $m] += $line[$i];
        }
    }
}
$changeSum = 0;
$cunliCount = 0;
$oFh = fopen(dirname(__DIR__) . '/tainan_aged_cunli.csv', 'w');
$keys = ['name', '201712', '201812', '201912', '202012', 'change'];
fputcsv($oFh, $keys);
foreach($population AS $code => $data) {
    if(!isset($population[$code][201812]) || !isset($population[$code][202012])) {
        $data['change'] = $population[$code]['change'] = 0;
    } else {
        ++$cunliCount;
        $data['change'] = $population[$code]['change'] = $population[$code][202012] - $population[$code][201812];
        $changeSum += $population[$code]['change'];
        
    }
    
}

usort($population, "cmp");

foreach($population AS $code => $data) {
    if(!isset($population[$code][201812]) || !isset($population[$code][202012])) {
        continue;
    }
    foreach($keys AS $key) {
        if(!isset($data[$key])) {
            $data[$key] = '';
        }
    }
    fputcsv($oFh, [$data[0], $data[201712], $data[201812], $data[201912], $data[202012], $data['change']]);
}

function cmp($a, $b)
{
    return $a['change'] < $b['change'];
}