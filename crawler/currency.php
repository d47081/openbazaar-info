<?php

require(__DIR__ . '/config.php');

require(__DIR__ . '/model/model.php');
require(__DIR__ . '/model/currency.php');

require(__DIR__ . '/curl/curl.php');
require(__DIR__ . '/curl/currency.php');

$currenciesAdded = 0;
$ratesUpdated    = 0;

$timeStart = microtime(true);

$modelCurrency = new ModelCurrency(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$curlCurrency  = new CurlCurrency(CURRENCY_RATE_PROTOCOL, CURRENCY_RATE_HOST, CURRENCY_RATE_PORT);

$updated = time();

foreach ($curlCurrency->getRates(CURRENCY_RATE_TIMEOUT) as $code => $value) {

    $code = $curlCurrency->sanitize($code);

    if (!$currencyId = $modelCurrency->currencyExists($code)) {
         $currencyId = $modelCurrency->addCurrency($code, $value['type']);
         $currenciesAdded++;
    }

    $modelCurrency->addCurrencyRate($currencyId,
                                    $value['ask'],
                                    $value['bid'],
                                    $value['last'],
                                    $updated);

    $ratesUpdated++;
}

$timeEnd = microtime(true);

// Debug output
echo sprintf("\nExecution time: %s\n\n", $timeEnd - $timeStart);
echo sprintf("Currencies added: %s\n", $currenciesAdded);
echo sprintf("Rates updated: %s\n\n", $ratesUpdated);
