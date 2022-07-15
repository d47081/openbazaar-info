<?php

// Map
$mainLocations = [];
foreach($modelLocation->getLocationTotals() as $location) {
    $mainLocations[] = mb_strtolower($location['codeIso2'], 'UTF-8') . ':' . $location['profiles'] . ':' . $location['listings'];
}
$mainLocations = implode('|', $mainLocations);

require(PROJECT_DIR . '/view/index.phtml');
