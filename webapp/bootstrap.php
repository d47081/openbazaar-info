<?php

function sanitizeRequest($string) {

    // Encode URL
    $string = urldecode($string);

    // Remove all tags
    $string = strip_tags($string);

    // Remove all symbols except required
    $string = preg_replace("/[^-\d\w\s\|\/]/ui", " ", $string);

    // Remove double spaces
    $string = preg_replace('/\s+/', ' ',$string);

    // Trim spaces
    $string = trim($string);

    return $string;
}

function sanitizeSearchQuery($string) {

    // Encode URL
    $string = urldecode($string);

    // Remove all tags
    $string = strip_tags($string);

    // Remove all symbols except required
    $string = preg_replace("/[^-\d\w\s\|]/ui", " ", $string);

    // Remove double spaces
    $string = preg_replace('/\s+/', ' ',$string);

    // Trim spaces
    $string = trim($string);

    return $string;
}

function plural($number, array $texts) {
    $cases = [2, 0, 1, 1, 1, 2];
    return $texts[(($number % 100) > 4 && ($number % 100) < 20) ? 2 : $cases[min($number % 10, 5)]];
}

function timeLeft($ptime) {

    $etime = time() - $ptime;

    if ($etime < 1) {
        return _('0 seconds');
    }

    $a = [365 * 24 * 60 * 60  => [_('year'), _('years'), _('years')],
                30 * 24 * 60 * 60  => [_('month'), _('months'), _('months')],
                     24 * 60 * 60  => [_('day'), _('days'), _('days')],
                          60 * 60  => [_('hour'), _('hours'), _('hours')],
                               60  => [_('minute'), _('minutes'), _('minutes')],
                                1  => [_('second'), _('seconds'), _('seconds')]];

    foreach ($a as $secs => $v) {
        $d = $etime / $secs;
        if ($d >= 1) {
            $r = round($d);
            return sprintf('%s %s ago', $r, plural($r, $v));
        }
    }
}

function formatText($string, $nl2br = false, $trim = false, $lenght = 160) {

    if ($trim && !empty($string) && strlen($string) > $lenght) {

        // Soft trim at first separator
        $string = substr($string, 0, strpos($string, ' ', $lenght)) . '...';

        // Hard trim separator not found
        if (strlen($string) > $lenght * 1.2) {
            $string = substr($string, 0, $lenght) . '...';
        }
    }

    // Decode htmlentities
    $string = html_entity_decode($string, ENT_QUOTES, 'UTF-8');

    // Remove all tags
    $string = strip_tags($string);

    // Encode htmlentities
    $string = htmlentities($string, ENT_QUOTES, 'UTF-8');

    if ($nl2br) {
        $string = nl2br($string);
    }

    return $string;
}

function formatURL($string) {
    return urlencode(html_entity_decode($string, ENT_COMPAT, 'UTF-8'));
}

function convertPrice($rates, $amount, $from, $to) {

    if ($from == '') {
        return false;
    }

    switch (false) {
        case isset($rates[$from]['type']):
        case isset($rates[$from]['rate']):
        //case isset($rates[$from]['symbol']):
        case isset($rates[$to]['type']):
        case isset($rates[$to]['rate']):
        //case isset($rates[$to]['symbol']):
            return false;
    }

    if($rates[$from]['type'] == 'crypto') {
        return $rates[$to]['rate'] * ((1 / $rates[$from]['rate']) * $amount);
    } else {
        return (($amount / $rates[$from]['rate']) / 100) * $rates[$to]['rate'];
    }
}

function formatPrice($rates, $price, $code, $digits = 2) {

    switch (false) {
        case isset($rates[$code]['type']):
        case isset($rates[$code]['rate']):
        //case isset($rates[$code]['symbol']):
            return false;
    }

    if (in_array($code, ['BTC', 'BCH', 'ZEC', 'LTC'])) {
        //$digits = 8;
        $amount = convertPrice($rates, $price / 100000000, $code, $code);
    } else {
        $amount = convertPrice($rates, $price, $code, $code);
    }

    //$formattedAmount = money_format("%." . $digits . "n", $amount);
    //$formattedAmount = number_format($formattedAmount, $digits);

    return $amount . ' ' . $rates[$code]['symbol'];
}

function formatModeratorPrice($rates, $price, $code, $percentage, $feeType, $digits = 2) {

    if ($price === 0 && $percentage === 0) {
        return _('FREE');
    }

    switch ($feeType) {
        case 'FIXED':
            return formatPrice($rates, $price, $code, $digits);
        break;
        case 'PERCENTAGE':
            return $percentage . '%';
        break;
        case 'FIXED_PLUS_PERCENTAGE':
            return formatPrice($rates, $price, $code, $digits) . ' + ' . $percentage . '%';
        break;
        default:
            return false;
    }
}

function formatModeratorFeeType($feeType) {

    switch ($feeType) {
        case 'FIXED':
            return _('Fixed');
        break;
        case 'PERCENTAGE':
            return _('Percent');
        break;
        case 'FIXED_PLUS_PERCENTAGE':
            return _('Fixed + Percent');
        break;
        default:
            return false;
    }
}

function formatShippingType($feeType) {

    switch ($feeType) {
        case 'LOCAL_PICKUP':
            return _('Local Pickup');
        break;
        case 'FIXED_PRICE':
            return _('Fixed Price');
        break;
        default:
            return false;
    }
}

function formatListingCondition($type) {

    switch ($type) {
        case 'NEW':
        case 'new':
            return _('New');
        break;
        case 'used':
            return _('Used');
        break;
        case 'USED_GOOD':
            return _('Used (Good)');
        break;
        break;
        case 'USED_EXCELLENT':
            return _('Used (Excelent)');
        break;
        case 'USED_POOR':
            return _('Used (Poor)');
        break;
        case 'REFURBISHED':
            return _('Refurbished');
        break;
        default:
            return false;
    }
}

function formatListingContractType($type) {

    switch ($type) {
        case 'SERVICE':
            return _('Service');
        break;
        case 'PHYSICAL_GOOD':
            return _('Physical Good');
        break;
        case 'DIGITAL_GOOD':
            return _('Digital Good');
        break;
        case 'CRYPTOCURRENCY':
            return _('Cryptocurrency');
        break;
        default:
            return false;
    }
}

function getRemoteIP() {

    $address = isset($_SERVER['HTTP_X_REAL_IP']) && $_SERVER['HTTP_X_REAL_IP'] ? $_SERVER['HTTP_X_REAL_IP'] : (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0');
    $varsion = false !== strpos($address, ':') ? 6 : 4;

    return [
        'address' => $address,
        'version' => $varsion,
    ];
}

require(__DIR__ . '/config.php');

require(PROJECT_DIR . '/model/model.php');
require(PROJECT_DIR . '/model/sphinx.php');
require(PROJECT_DIR . '/model/health.php');
require(PROJECT_DIR . '/model/currency.php');
require(PROJECT_DIR . '/model/location.php');
require(PROJECT_DIR . '/model/extension.php');
require(PROJECT_DIR . '/model/profile.php');
require(PROJECT_DIR . '/model/listing.php');
require(PROJECT_DIR . '/model/ip.php');
require(PROJECT_DIR . '/curl/curl.php');
require(PROJECT_DIR . '/curl/image.php');
require(PROJECT_DIR . '/curl/chat.php');

$curlImage      = new CurlImage(OB_PROTOCOL, OB_HOST, OB_PORT, OB_USERNAME, OB_PASSWORD);
$curlChat       = new CurlChat(OB_PROTOCOL, OB_HOST, OB_PORT, OB_USERNAME, OB_PASSWORD);

$modelHealth    = new ModelHealth(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$modelProfile   = new ModelProfile(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$modelListing   = new ModelListing(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$modelCurrency  = new ModelCurrency(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$modelLocation  = new ModelLocation(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$modelExtension = new ModelExtension(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$modelIp        = new ModelIp(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$modelSphinx    = new ModelSphinx(SPHINX_HOST, SPHINX_PORT);
