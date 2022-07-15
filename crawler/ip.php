<?php

/*
 * Update ip info
 * Priority: low
 */

require(__DIR__ . '/config.php');

require(__DIR__ . '/model/model.php');
require(__DIR__ . '/model/location.php');
require(__DIR__ . '/model/ip.php');

require(__DIR__ . '/curl/curl.php');
require(__DIR__ . '/curl/ip.php');

$totalIpIndexed        = 0;
$totalIpUpdated        = 0;

$totalCountriesAdded   = 0;
$totalCountriesUpdated = 0;

$totalRegionsAdded     = 0;
$totalRegionsUpdated   = 0;

$totalCitiesAdded      = 0;
$totalCitiesUpdated    = 0;

$time      = time();
$timeStart = microtime(true);

$modelIp       = new ModelIp(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$modelLocation = new ModelLocation(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$curlIp        = new CurlIp(IP_PROTOCOL, IP_HOST, IP_PORT);

// Update index
if ($modelIndexQueue = $modelIp->getIndexQueue(IP__MODEL_IP_INDEX_QUEUE)) {
    foreach ($modelIndexQueue as $ip) {

        if ($location = $curlIp->getLocation(($ip['version'] == 6 ? $ip['ipv6'] : $ip['ipv4']), IP__CURL_TIMEOUT_GET_LOCATION)) {

            $latitude        = $curlIp->sanitize($location['geoplugin_latitude']);
            $longitude       = $curlIp->sanitize($location['geoplugin_longitude']);
            $countryName     = $curlIp->sanitize($location['geoplugin_countryName']);
            $countryCodeIso2 = $curlIp->sanitize($location['geoplugin_countryCode']);
            $regionName      = $location['geoplugin_regionName'] ? $curlIp->sanitize($location['geoplugin_regionName']) : $curlIp->sanitize($location['geoplugin_region']);
            $cityName        = $curlIp->sanitize($location['geoplugin_city']);

            // If country name & code is not empty add it
            if ($countryName && $countryCodeIso2) {

                if (!$countryId = $modelLocation->countryCodeIso2Exists($countryCodeIso2)) {
                     $countryId = $modelLocation->addCountry($countryName, $countryCodeIso2, $countryCodeIso2);
                     $totalCountriesAdded++;
                }

                $modelIp->updateCountryId($ip['ipId'], $countryId);

                if (!$regionName && !$cityName && $latitude && $longitude) {
                   $modelLocation->updateCountryCoordinates($countryId, $latitude, $longitude);
                   $totalCountriesUpdated++;
                }

                // If region name is not empty add it
                if ($regionName) {

                    if (!$regionId = $modelLocation->regionExists($countryId, $regionName)) {
                         $regionId = $modelLocation->addRegion($countryId, $regionName);
                         $totalRegionsAdded++;
                    }

                    $modelIp->updateRegionId($ip['ipId'], $regionId);

                    if (!$cityName && $latitude && $longitude) {
                       $modelLocation->updateRegionCoordinates($regionId, $latitude, $longitude);
                       $totalRegionsUpdated++;
                    }

                    // If city name is not empty add it
                    if ($cityName) {
                        if (!$cityId = $modelLocation->cityExists($regionId, $cityName)) {
                             $cityId = $modelLocation->addCity($regionId, $cityName);
                             $totalCitiesAdded++;
                        }

                        $modelIp->updateCityId($ip['ipId'], $cityId);

                        if ($latitude && $longitude) {
                           $modelLocation->updateCityCoordinates($cityId, $latitude, $longitude);
                           $totalCitiesUpdated++;
                        }
                    }
                }
            }

            $totalIpUpdated++;
        }

        $modelIp->updateIpIndexed($ip['ipId'], time());
        $totalIpIndexed++;
    }
}

$timeEnd = microtime(true);

// Debug output
echo sprintf("\nExecution time: %s\n\n", $timeEnd - $timeStart);
echo sprintf("IP total indexed: %s\n", $totalIpIndexed);
echo sprintf("IP total updated: %s\n\n", $totalIpUpdated);
echo sprintf("Countries added: %s\n", $totalCountriesAdded);
echo sprintf("Countries updated: %s\n\n", $totalCountriesUpdated);
echo sprintf("Regions added: %s\n", $totalRegionsAdded);
echo sprintf("Regions updated: %s\n\n", $totalRegionsUpdated);
echo sprintf("Cities added: %s\n", $totalCitiesAdded);
echo sprintf("Cities updated: %s\n\n", $totalCitiesUpdated);
