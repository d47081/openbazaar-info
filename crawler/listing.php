<?php

/*
 * Update accessible listings
 * Priority: 3
 */

require(__DIR__ . '/config.php');

require(__DIR__ . '/model/model.php');
require(__DIR__ . '/model/currency.php');
require(__DIR__ . '/model/location.php');
require(__DIR__ . '/model/listing.php');
require(__DIR__ . '/model/profile.php');
require(__DIR__ . '/model/word.php');
require(__DIR__ . '/model/sphinx.php');

require(__DIR__ . '/curl/curl.php');
require(__DIR__ . '/curl/listing.php');

$totalProfilesAdded = 0;
$totalListingsIndexed = 0;
$totalListingsUpdatedIpns = 0;
$totalListingsUpdatedIpfs = 0;
$totalListingsNotFound = 0;
$totalListingsRemoved = 0;
$nsfwListingsAdded = 0;
$nsfwListingsRemoved = 0;

$listingsProcessed = [];

$timeStart = microtime(true);

$modelSphinx   = new ModelSphinx(SPHINX_HOST, SPHINX_PORT);
$modelCurrency = new ModelCurrency(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$modelListing  = new ModelListing(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$modelLocation = new ModelLocation(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$modelProfile  = new ModelProfile(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$modelWord     = new ModelWord(DB_DATABASE, DB_HOSTNAME, DB_PORT, DB_USERNAME, DB_PASSWORD);
$curlListing   = new CurlListing(OB_PROTOCOL, OB_HOST, OB_PORT, OB_USERNAME, OB_PASSWORD);

// Get nsfw words
$nsfwWords = [];
foreach ($modelWord->getNsfwFords(1) as $word) {
    $nsfwWords[] = mb_strtolower($word['name'], 'UTF-8');
}

// Delete search index for removed listings
foreach ($modelListing->getListingsRemoved(LISTING__REMOVED_DELETE_TIMEOUT, time()) as $removed) {
    $totalListingsRemoved = $totalListingsRemoved + $modelListing->deleteListing($removed['listingId']);
    $modelSphinx->updateListingRemoved($removed['listingId'], time());
}

// Update index
if ($modelIndexQueue = $modelListing->getIndexQueue(LISTING__MODEL_LISTING_INDEX_QUEUE)) {

    foreach ($modelIndexQueue as $listing) {

        // Define variables
        $listingIndexContent = [];
        $time = time();

        // Update processed listings
        $listingsProcessed[] = $listing['slug'] . '/' . $listing['hash'] ;

        // Listing is available via IPNS (actual)
        if ($curlIpnsListingInfo = $curlListing->getIpnsListing($listing['peerId'], $listing['slug'], LISTING__CURL_TIMEOUT_GET_IPNS_LISTING)) {

            $updateType = 'IPNS';
            $curlListingInfo = $curlIpnsListingInfo;
            $modelSphinx->updateListingUpdatedIpns($listing['listingId'], $time);
            $totalListingsUpdatedIpns++;

        // Listing is available via IPFS (may be older version from other peers)
        } else if ($curlIpfsListingInfo = $curlListing->getIpfsListing($listing['hash'], LISTING__CURL_TIMEOUT_GET_IPFS_LISTING)) {

            $updateType = 'IPFS';
            $curlListingInfo = $curlIpfsListingInfo;
            $modelSphinx->updateListingUpdatedIpfs($listing['listingId'], $time);
            $totalListingsUpdatedIpfs++;

        } else {
            $updateType = false;
            $curlListingInfo = false;
            $totalListingsNotFound++;
        }

        // Update listing data
        if ($curlListingInfo) {

            // Add shipping options
            $modelListing->flushListingShippingCountries($listing['listingId']);
            $modelListing->flushListingShippings($listing['listingId']);
            if (isset($curlListingInfo['listing']['shippingOptions']) && is_array($curlListingInfo['listing']['shippingOptions'])) {
                foreach ($curlListingInfo['listing']['shippingOptions'] as $shippingOption) {
                  if (isset($shippingOption['name']) &&
                      isset($shippingOption['type']) &&
                      isset($shippingOption['regions']) && is_array($shippingOption['regions']) &&
                      isset($shippingOption['services']) && is_array($shippingOption['services'])) {

                      $countries = [];
                      foreach ($shippingOption['regions'] as $key => $value) {

                          $value = $curlListing->sanitize($value);

                          // If listing shipping has all countries
                          if ($value == 'ALL') {

                              // Extract all of them
                              foreach ($modelLocation->getCountries() as $country) {

                                  // Add shipping country relation
                                  if (!$modelListing->listingShippingCountryExists($listing['listingId'], $country['countryId'])) {
                                       $modelListing->addlistingShippingCountry($listing['listingId'], $country['countryId']);
                                  }

                                  // Add country
                                  $countries[] = $country['countryId'];
                              }

                          // If shipping has specific country
                          } else {

                              // Add country if not exists relation
                              if (!$countryId = $modelLocation->countryCodeExists($value)) {
                                   $countryId = $modelLocation->addCountry($value, $value, $value);
                              }

                              // Add shipping country
                              if (!$modelListing->listingShippingCountryExists($listing['listingId'], $countryId)) {
                                   $modelListing->addlistingShippingCountry($listing['listingId'], $countryId);
                              }

                              // Add country
                              $countries[] = $countryId;
                          }
                      }

                      // Save unique countries only
                      $countries = array_unique($countries);

                      $services = [];
                      foreach ($shippingOption['services'] as $service) {
                          if (isset($service['name']) &&
                              isset($service['price']) &&
                              isset($service['estimatedDelivery']) &&
                              isset($service['additionalItemPrice'])) {

                              $services[] = [
                                  'name'                => $curlListing->sanitize($service['name']),
                                  'price'               => $curlListing->sanitize($service['price']),
                                  'estimatedDelivery'   => $curlListing->sanitize($service['estimatedDelivery']),
                                  'additionalItemPrice' => $curlListing->sanitize($service['additionalItemPrice']),
                              ];
                          }

                      }

                      $modelListing->addListingShipping($listing['listingId'],
                                                        $curlListing->sanitize($shippingOption['name']),
                                                        $curlListing->sanitize($shippingOption['type']),
                                                        json_encode($countries),
                                                        json_encode($services));
                  }
                }
            }

            // Add options
            $modelListing->flushListingOptions($listing['listingId']);
            if (isset($curlListingInfo['listing']['item']['options']) && is_array($curlListingInfo['listing']['item']['options'])) {
                foreach ($curlListingInfo['listing']['item']['options'] as $option) {
                  if (isset($option['name']) &&
                      isset($option['description']) &&
                      isset($option['variants']) && is_array($option['variants'])) {

                      $variants = [];
                      foreach ($option['variants'] as $variant) {
                          if (isset($variant['name'])) {
                              $variants[] = [
                                  'name' => $curlListing->sanitize($variant['name']),
                              ];
                          }
                      }

                      $modelListing->addListingOption($listing['listingId'],
                                                      $curlListing->sanitize($option['name']),
                                                      $curlListing->sanitize($option['description']),
                                                      json_encode($variants));
                  }
                }
            }

            // Add images
            $modelListing->flushListingImages($listing['listingId']);
            if ($curlListingInfo['listing']['item']['images']) {
                foreach ($curlListingInfo['listing']['item']['images'] as $image) {
                    if (isset($image['filename']) &&
                        isset($image['original']) &&
                        isset($image['large']) &&
                        isset($image['medium']) &&
                        isset($image['small']) &&
                        isset($image['tiny'])) {

                        $modelListing->addListingImage($listing['listingId'],
                                                       $curlListing->sanitize($image['filename']),
                                                       $curlListing->sanitize($image['original']),
                                                       $curlListing->sanitize($image['large']),
                                                       $curlListing->sanitize($image['medium']),
                                                       $curlListing->sanitize($image['small']),
                                                       $curlListing->sanitize($image['tiny']));
                    }
                }
            }

            // Add moderators
            $modelListing->flushListingModerators($listing['listingId']);

            $listingModerators = 0;
            if ($curlListingInfo['listing']['moderators']) {
                foreach (array_unique($curlListingInfo['listing']['moderators']) as $peerId) {

                    // Sanitize hash
                    $peerId = $curlListing->sanitize($peerId);

                    // Add profile if not exists
                    if (!$profileId = $modelProfile->profileExists($peerId)) {
                         $profileId = $modelProfile->addProfile($peerId, time());
                         $totalProfilesAdded++;
                    }

                    // Add listing moderator
                    $modelListing->addModerator($listing['listingId'], $profileId);

                    $listingModerators++;
                }
            }
            $modelSphinx->updateListingModerators($listing['listingId'], $listingModerators);

            // Add acceptedCurrencies
            $modelListing->flushListingCurrency($listing['listingId']);
            if ($curlListingInfo['listing']['metadata']['acceptedCurrencies']) {
                foreach (array_unique($curlListingInfo['listing']['metadata']['acceptedCurrencies']) as $currency) {

                    // Sanitize hash
                    $currency = $curlListing->sanitize($currency);

                    // Add currency if not exists
                    if (!$acceptedCurrencyId = $modelCurrency->currencyExists($currency)) {
                         $acceptedCurrencyId = $modelCurrency->addCurrency($currency);
                    }

                    // Add listing currency
                    if (!$modelListing->listingCurrencyExists($listing['listingId'], $acceptedCurrencyId)) {
                        $modelListing->addListingCurrency($listing['listingId'], $acceptedCurrencyId);
                    }
                }
            }

            // Add currency if not exists @TODO fill values
            if (isset($curlListingInfo['listing']['item']['priceCurrency']['code'])) {
                $pricingCurrency = $curlListing->sanitize($curlListingInfo['listing']['item']['priceCurrency']['code']);
            } else {
                $pricingCurrency = '';
            }

            if (isset($curlListingInfo['listing']['item']['priceCurrency']['divisibility'])) {
                $pricingDivisibility = $curlListing->sanitize($curlListingInfo['listing']['item']['priceCurrency']['divisibility']);
            } else {
                $pricingDivisibility = '';
            }

            if (!$currencyId = $modelCurrency->currencyExists($pricingCurrency)) {
                 $currencyId = $modelCurrency->addCurrency($pricingCurrency);
            }

            $tags = [];
            foreach ($curlListingInfo['listing']['item']['tags'] as $tag) {
                $tags[] = $curlListing->sanitize($tag, true);
            }

            $categories = [];
            foreach ($curlListingInfo['listing']['item']['categories'] as $category) {
                $categories[] = $curlListing->sanitize($category);
            }

            if (isset($curlListingInfo['listing']['metadata']['contractType'])) {
                $contractType = $curlListing->sanitize($curlListingInfo['listing']['metadata']['contractType']);
            } else {
                $contractType = '';
            }

            if (isset($curlListingInfo['listing']['item']['condition'])) {
                $condition = $curlListing->sanitize($curlListingInfo['listing']['item']['condition']);
            } else {
                $condition = '';
            }

            if (isset($curlListingInfo['listing']['item']['bigPrice'])) {
                $price = $curlListing->sanitize($curlListingInfo['listing']['item']['bigPrice']);
            } else {
                $price = 0;
            }

            if (isset($curlListingInfo['listing']['metadata']['priceModifier'])) {
                $priceModifier = $curlListing->sanitize($curlListingInfo['listing']['metadata']['priceModifier']);
            } else {
                $priceModifier = 0;
            }

            $sanitizedListingTitle = $curlListing->sanitize($curlListingInfo['listing']['item']['title']);
            $sanitizedListingDescription = $curlListing->sanitize($curlListingInfo['listing']['item']['description']);
            $sanitizedListingTermsAndConditions = $curlListing->sanitize($curlListingInfo['listing']['termsAndConditions']);
            $sanitizedListingRefundPolicy = $curlListing->sanitize($curlListingInfo['listing']['refundPolicy']);

            // If not indexed create temporary content registry
            if (NULL == $listing['indexed']) {
                 $listingIndexContent = explode(' ', mb_strtolower($sanitizedListingTitle, 'UTF-8') . ' ' .
                                                     mb_strtolower($sanitizedListingDescription, 'UTF-8') . ' ' .
                                                     mb_strtolower($sanitizedListingTermsAndConditions, 'UTF-8') . ' ' .
                                                     mb_strtolower($sanitizedListingRefundPolicy, 'UTF-8') . ' ' .
                                                     mb_strtolower(implode(' ', $tags), 'UTF-8') . ' ' .
                                                     mb_strtolower(implode(' ', $categories), 'UTF-8'));
            }

            $modelListing->updateListing( $listing['listingId'],
                                          $currencyId,
                                          $pricingDivisibility,
                                          $curlListing->sanitize($curlListingInfo['hash']),
                                          $curlListing->sanitize($curlListingInfo['signature']),
                                          $curlListing->sanitize($curlListingInfo['listing']['metadata']['version']),
                                          $contractType,
                                          $curlListing->sanitize($curlListingInfo['listing']['metadata']['format']),
                                          $curlListing->sanitize($curlListingInfo['listing']['metadata']['expiry']),
                                          $price,
                                          $condition,
                                          $curlListing->sanitize($curlListingInfo['listing']['item']['grams']),
                                          $curlListing->sanitize($curlListingInfo['listing']['metadata']['escrowTimeoutHours']),
                                          $curlListing->sanitize($curlListingInfo['listing']['metadata']['coinType']),
                                          $curlListing->sanitize($curlListingInfo['listing']['metadata']['coinDivisibility']),
                                          $priceModifier,
                                          $updateType,
                                          $time,
                                          $sanitizedListingTitle,
                                          $sanitizedListingDescription,
                                          implode(',', $tags),
                                          implode(',', $categories),
                                          $curlListing->sanitize($curlListingInfo['listing']['item']['processingTime']),
                                          $sanitizedListingTermsAndConditions,
                                          $sanitizedListingRefundPolicy,
                                          $curlListing->sanitize($curlListingInfo['listing']['vendorID']['pubkeys']['identity']),
                                          $curlListing->sanitize($curlListingInfo['listing']['vendorID']['pubkeys']['bitcoin']),
                                          $curlListing->sanitize($curlListingInfo['listing']['vendorID']['bitcoinSig']));



            $modelSphinx->updateListingContractType($listing['listingId'], $contractType);
            $modelSphinx->updateListingCondition($listing['listingId'], $condition);
            $modelSphinx->updateListingPrice($listing['listingId'], $price);
            $modelSphinx->updateListingPriceModifier($listing['listingId'], $priceModifier);
            $modelSphinx->updateListingCode($listing['listingId'], $pricingCurrency);

            // Convert listing price to BTC by sphinx indexer algorithm
            $modelSphinx->updateListingPriceBtc($listing['listingId'], $modelListing->getListingBtcPrice($listing['listingId']));
        }

        // Update nsfw
        if ($nsfwWords) {
            $nsfwWordsTotal = 0;
            foreach ($nsfwWords as $nsfwWord) {

                // If listing was not indexed
                if ($listingIndexContent) {

                    if (in_array($nsfwWord, $listingIndexContent)) {
                        $nsfwWordsTotal++;
                    }

                // Profile indexed
                } else {
                    if ($modelSphinx->wordExists('listing', $nsfwWord, $listing['listingId'])) {
                        $nsfwWordsTotal++;
                    }
                }
            }
            if($nsfwWordsTotal) {
                $nsfwListingsAdded = $nsfwListingsAdded + $modelListing->updateListingNsfw($listing['listingId'], 1);
                $modelSphinx->updateListingNsfw($listing['listingId'], 1);
            } else {
                $nsfwListingsRemoved = $nsfwListingsRemoved + $modelListing->updateListingNsfw($listing['listingId'], 0);
                $modelSphinx->updateListingNsfw($listing['listingId'], 0);
            }
        }

        // Update index date
        $modelListing->updateListingIndexed($listing['listingId'], $time);
        $modelSphinx->updateListingIndexed($listing['listingId'], $time);
        $totalListingsIndexed++;
    }
}

$timeEnd = microtime(true);

// Debug output
echo sprintf("\nExecution time: %s\n\n", $timeEnd - $timeStart);
echo sprintf("Listings removed: %s\n", $totalListingsRemoved);
echo sprintf("Listings indexed: %s\n", $totalListingsIndexed);
echo sprintf("Listings updated from IPNS: %s\n", $totalListingsUpdatedIpns);
echo sprintf("Listings updated from IPFS: %s\n\n", $totalListingsUpdatedIpfs);
echo sprintf("Listings not found: %s\n", $totalListingsNotFound);
echo sprintf("NSFW Listings added: %s\n", $nsfwListingsAdded);
echo sprintf("NSFW Listings removed: %s\n", $nsfwListingsRemoved);
echo sprintf("Profiles added: %s\n\n", $totalProfilesAdded);

if ($listingsProcessed) {
    print("Slug/Hash processed:\n\n");
    foreach ($listingsProcessed as $value) {
        print($value) . "\n";
    }
    print("\n");
}
