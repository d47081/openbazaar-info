<?php

/*
 * Sitemap generator
 * Priority: medium
 */

require(__DIR__ . '/config.php');

require(__DIR__ . '/model/model.php');
require(__DIR__ . '/model/profile.php');
require(__DIR__ . '/model/listing.php');

$modelProfile  = new ModelProfile(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$modelListing  = new modelListing(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);

$url       = [];
$lastmod   = date('Y-m-d');
$timeStart = microtime(true);

// Collect profiles
$profilesTotal = 0;
$profilesIndex = 1;
foreach ($modelProfile->getSitemapProfiles() as $profile) {

    $profilesTotal++;

    if ($profilesTotal > ($profilesIndex * SITEMAP_ITEMS_PER_SITEMAP)) {
        $profilesIndex++;
    }

    $url['profile'][$profilesIndex][] = [
        'loc'     => SITEMAP_DOMAIN . '/profile/' . $profile['peerId'],
        'lastmod' => $lastmod,
    ];
}

// Collect listings
$listingsTotal = 0;
$listingsIndex = 1;
foreach ($modelListing->getSitemapListings() as $listing) {

    $listingsTotal++;

    if ($listingsTotal > ($listingsIndex * SITEMAP_ITEMS_PER_SITEMAP)) {
        $listingsIndex++;
    }

    $url['listing'][$listingsIndex][] = [
        'loc'     => SITEMAP_DOMAIN . '/listing/' . $listing['hash'],
        'lastmod' => $lastmod,
    ];
}

// Delete previous sitemap
foreach (scandir(SITEMAP_BASE) as $file) {
    if (false !== strpos($file, 'sitemap')) {
        if (file_exists(SITEMAP_BASE . '/' . $file)) {
            unlink(SITEMAP_BASE . '/' . $file);
        }
    }
}

// Generate new sitemap urlsets
$sitemaps = [];
foreach ($url as $type => $indexes) {
    foreach ($indexes as $index => $datas) {

        $handle     = fopen(sprintf("%s/sitemap.%s.%s.xml", SITEMAP_BASE, $type, $index), 'a');
        $sitemaps[] = sprintf("%s/sitemap.%s.%s.xml", SITEMAP_DOMAIN, $type, $index);

        fwrite($handle, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n");
        foreach ($datas as $data) {
            fwrite($handle, "\t<url>\n\t\t<loc>" . $data['loc'] . "</loc>\n\t\t<lastmod>" . $data['lastmod'] . "</lastmod>\n\t</url>\n");
        }
        fwrite($handle, "</urlset>");
        fclose($handle);
    }
}

// Generate new sitemap
$handle = fopen(sprintf("%s/sitemap.xml", SITEMAP_BASE), 'a');
fwrite($handle, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n");
foreach ($sitemaps as $sitemap) {
    fwrite($handle, "\t<sitemap>\n\t\t<loc>" . $sitemap . "</loc>\n\t\t<lastmod>" . $lastmod . "</lastmod>\n\t</sitemap>\n");
}
fwrite($handle, "</sitemapindex>");
fclose($handle);

$timeEnd = microtime(true);

// Debug output
echo sprintf("\nExecution time: %s\n\n", $timeEnd - $timeStart);
echo sprintf("Profiles: %s\n", $profilesTotal);
echo sprintf("Listings: %s\n\n", $listingsTotal);
