<?php
$rootPath = dirname(dirname(__DIR__));

$geo = json_decode(file_get_contents($rootPath . '/taiwan_basecode/cunli/geo/20201016.json'), true);
$count = [];
foreach($geo['features'] AS $f) {
    if(!isset($count[$f['properties']['COUNTYNAME']])) {
        $count[$f['properties']['COUNTYNAME']] = 0;
    }
    ++$count[$f['properties']['COUNTYNAME']];
}
print_r($count);