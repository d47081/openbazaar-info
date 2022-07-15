<?php

class ModelListing extends Model {

    public function getSitemapListings() {

        try {

            $query = $this->db->prepare('SELECT `hash` FROM `listing` WHERE `updatedIpns` IS NOT NULL OR `updatedIpfs` IS NOT NULL');

            $query->execute();

            return $query->rowCount() ? $query->fetchAll() : [];

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addListing($profileId, $slug, $hash, $added) {

        try {

            $query = $this->db->prepare('INSERT INTO `listing` SET `profileId` = :profileId,
                                                                   `slug`      = :slug,
                                                                   `hash`      = :hash,
                                                                   `added`     = :added');

            $query->bindValue(':profileId', $profileId, PDO::PARAM_INT);
            $query->bindValue(':slug', $slug, PDO::PARAM_STR);
            $query->bindValue(':hash', $hash, PDO::PARAM_STR);
            $query->bindValue(':added', $added, PDO::PARAM_INT);

            $query->execute();

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function deleteListing($listingId) {
        try {

            $this->flushListingModerators($listingId);
            $this->flushListingImages($listingId);
            $this->flushListingCurrency($listingId);
            $this->flushListingShippings($listingId);
            $this->flushListingShippingCountries($listingId);
            $this->flushListingOptions($listingId);

            $query = $this->db->prepare('DELETE FROM `listing` WHERE `listingId` = ?');

            $query->execute([$listingId]);

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function flushListingShippings($listingId) {

        try {
            $query = $this->db->prepare('DELETE FROM `listingShipping` WHERE `listingId` = ?');

            $query->execute([$listingId]);

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function flushListingOptions($listingId) {

        try {
            $query = $this->db->prepare('DELETE FROM `listingOption` WHERE `listingId` = ?');

            $query->execute([$listingId]);

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function flushListingModerators($listingId) {

        try {
            $query = $this->db->prepare('DELETE FROM `listingModerator` WHERE `listingId` = ?');

            $query->execute([$listingId]);

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addModerator($listingId, $profileId) {

        try {
            $query = $this->db->prepare('INSERT INTO `listingModerator` SET `listingId` = ?, `profileId` = ?');

            $query->execute([$listingId, $profileId]);

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function listingShippingCountryExists($listingId, $countryId) {

        try {

            $query = $this->db->prepare('SELECT NULL FROM  `listingShippingCountry` WHERE `listingId` = ? AND `countryId` = ? LIMIT 1');

            $query->execute([$listingId, $countryId]);

            return $query->rowCount() ? true : false;

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addlistingShippingCountry($listingId, $countryId) {

        try {

            $query = $this->db->prepare('INSERT INTO `listingShippingCountry` SET `listingId` = ?, `countryId` = ?');

            $query->execute([$listingId, $countryId]);

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error("SELECT NULL FROM  `listingShippingCountry` WHERE `listingId` = $listingId AND `countryId` = $countryId LIMIT 1" . $e->getMessage());
            return false;
        }
    }

    public function flushListingShippingCountries($listingId) {

        try {
            $query = $this->db->prepare('DELETE FROM `listingShippingCountry` WHERE `listingId` = ?');

            $query->execute([$listingId]);

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function flushListingImages($listingId) {

        try {
            $query = $this->db->prepare('DELETE FROM `listingImage` WHERE `listingId` = ?');

            $query->execute([$listingId]);

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addListingImage($listingId,
                                    $filename,
                                    $original,
                                    $large,
                                    $medium,
                                    $small,
                                    $tiny) {

        try {
            $query = $this->db->prepare('INSERT INTO `listingImage` SET `listingId` = :listingId,
                                                                        `filename`  = :filename,
                                                                        `original`  = :original,
                                                                        `large`     = :large,
                                                                        `medium`    = :medium,
                                                                        `small`     = :small,
                                                                        `tiny`      = :tiny');

            $query->bindParam(':listingId', $listingId, PDO::PARAM_INT);
            $query->bindParam(':filename', $filename, PDO::PARAM_STR);
            $query->bindParam(':original', $original, PDO::PARAM_STR);
            $query->bindParam(':large', $large, PDO::PARAM_STR);
            $query->bindParam(':medium', $medium, PDO::PARAM_STR);
            $query->bindParam(':small', $small, PDO::PARAM_STR);
            $query->bindParam(':tiny', $tiny, PDO::PARAM_STR);

            $query->execute();

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addListingShipping($listingId,
                                       $name,
                                       $type,
                                       $countries,
                                       $services) {

        try {
            $query = $this->db->prepare('INSERT INTO `listingShipping` SET `listingId` = :listingId,
                                                                           `name`      = :name,
                                                                           `type`      = :type,
                                                                           `countries` = :countries,
                                                                           `services`  = :services');

            $query->bindParam(':listingId', $listingId, PDO::PARAM_INT);
            $query->bindParam(':name', $name, PDO::PARAM_STR);
            $query->bindParam(':type', $type, PDO::PARAM_STR);
            $query->bindParam(':countries', $countries, PDO::PARAM_STR);
            $query->bindParam(':services', $services, PDO::PARAM_STR);

            $query->execute();

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addListingOption($listingId,
                                     $name,
                                     $description,
                                     $variants) {

        try {
            $query = $this->db->prepare('INSERT INTO `listingOption` SET `listingId`   = :listingId,
                                                                         `name`        = :name,
                                                                         `description` = :description,
                                                                         `variants`    = :variants');

            $query->bindParam(':listingId', $listingId, PDO::PARAM_INT);
            $query->bindParam(':name', $name, PDO::PARAM_STR);
            $query->bindParam(':description', $description, PDO::PARAM_STR);
            $query->bindParam(':variants', $variants, PDO::PARAM_STR);

            $query->execute();

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function flushListingCurrency($listingId) {

        try {
            $query = $this->db->prepare('DELETE FROM `listingCurrency` WHERE `listingId` = ?');

            $query->execute([$listingId]);

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function listingCurrencyExists($listingId, $currencyId) {

        try {
            $query = $this->db->prepare('SELECT NULL FROM `listingCurrency` WHERE `listingId` = ? AND `currencyId` = ? LIMIT 1');

            $query->execute([$listingId, $currencyId]);

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addListingCurrency($listingId, $currencyId) {

        try {
            $query = $this->db->prepare('INSERT INTO `listingCurrency` SET `listingId` = ?, `currencyId` = ?');

            $query->execute([$listingId, $currencyId]);

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateListingsIndexed($profileId, $indexed) {

        try {

            $query = $this->db->prepare('UPDATE `listing` SET `indexed`   = :indexed
                                                        WHERE `profileId` = :profileId
                                                        LIMIT 1
                                                ');

            $query->bindParam(':profileId', $profileId, PDO::PARAM_INT);
            $query->bindParam(':indexed', $indexed, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateListingIndexed($listingId, $indexed) {

        try {

            $query = $this->db->prepare('UPDATE `listing` SET `indexed`   = :indexed
                                                        WHERE `listingId` = :listingId
                                                        LIMIT 1
                                                ');

            $query->bindParam(':listingId', $listingId, PDO::PARAM_INT);
            $query->bindParam(':indexed', $indexed, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateListingNsfw($listingId, $nsfw) {

        try {

            $query = $this->db->prepare('UPDATE `listing` SET `nsfw` = :nsfw

                                                        WHERE `listingId` = :listingId
                                                        LIMIT 1
                                                ');

            $query->bindParam(':listingId', $listingId, PDO::PARAM_INT);
            $query->bindParam(':nsfw', $nsfw, PDO::PARAM_STR);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateListingsRemoved($profileId, $removed) {

        try {

            $query = $this->db->prepare('UPDATE `listing` SET `removed`   = :removed
                                                        WHERE `profileId` = :profileId');

            $query->bindParam(':profileId', $profileId, PDO::PARAM_INT);
            $query->bindParam(':removed', $removed, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateListingRemoved($listingId, $removed) {

        try {

            $query = $this->db->prepare('UPDATE `listing` SET `removed`   = :removed
                                                        WHERE `listingId` = :listingId
                                                        LIMIT 1
                                                ');

            $query->bindParam(':listingId', $listingId, PDO::PARAM_INT);
            $query->bindParam(':removed', $removed, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getListingsRemoved($timeout, $time) {

        try {

            $query = $this->db->prepare('SELECT `listingId` FROM `listing` WHERE `removed` > 0 AND (`removed` + :timeout) <= :time');

            $query->bindParam(':timeout', $timeout, PDO::PARAM_INT);
            $query->bindParam(':time', $time, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount() ? $query->fetchAll() : [];

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getListingBtcPrice($listingId) {

        try {

            $query = $this->db->prepare('SELECT (CASE WHEN `c`.`code` <> "BTC"
                                                      THEN (((1 / (SELECT `cr`.`last` FROM `currencyRate` AS `cr` WHERE `cr`.`currencyid` = `l`.`currencyId` ORDER BY `cr`.`updated` DESC LIMIT 1)) * `l`.`price`) / 100)
                                                      ELSE (`l`.`price` / 100000000)
                                                      END) AS `priceBTC`

                                                      FROM `listing` AS `l`
                                                      JOIN `currency` AS `c` ON (`c`.`currencyId` = `l`.`currencyId`)
                                                      WHERE `l`.`listingId` = :listingId
                                                      LIMIT 1');

            $query->bindParam(':listingId', $listingId, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount() ? $query->fetch()['priceBTC'] : 0;

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateListing($listingId,
                                  $currencyId,
                                  $divisibility,
                                  $hash,
                                  $signature,
                                  $version,
                                  $contractType,
                                  $format,
                                  $expiry,
                                  $price,
                                  $condition,
                                  $grams,
                                  $escrowTimeoutHours,
                                  $coinType,
                                  $coinDivisibility,
                                  $priceModifier,
                                  $updateType,
                                  $updated,
                                  $title,
                                  $description,
                                  $tags,
                                  $categories,
                                  $processingTime,
                                  $termsAndConditions,
                                  $refundPolicy,
                                  $identityPublicKey,
                                  $bitcoinPublicKey,
                                  $bitcoinSig) {

        try {

            $query = $this->db->prepare('UPDATE `listing` SET `hash`               = :hash,
                                                              `signature`          = :signature,
                                                              `currencyId`         = :currencyId,
                                                              `divisibility`       = :divisibility,
                                                              `version`            = :version,
                                                              `contractType`       = :contractType,
                                                              `format`             = :format,
                                                              `expiry`             = :expiry,
                                                              `price`              = :price,
                                                              `condition`          = :condition,
                                                              `grams`              = :grams,
                                                              `escrowTimeoutHours` = :escrowTimeoutHours,
                                                              `coinType`           = :coinType,
                                                              `coinDivisibility`   = :coinDivisibility,
                                                              `priceModifier`      = :priceModifier,
                                                              `title`              = :title,
                                                              `description`        = :description,
                                                              `tags`               = :tags,
                                                              `categories`         = :categories,
                                                              `processingTime`     = :processingTime,
                                                              `termsAndConditions` = :termsAndConditions,
                                                              `refundPolicy`       = :refundPolicy,
                                                              `identityPublicKey`  = :identityPublicKey,
                                                              `bitcoinPublicKey`   = :bitcoinPublicKey,
                                                              `bitcoinSig`         = :bitcoinSig,

                                                              ' . ($updateType == 'IPNS' ? '`updatedIpns` = :updated' : '`updatedIpfs` = :updated') . '


                                                        WHERE `listingId`       = :listingId

                                                        LIMIT 1');

            $query->bindValue(':listingId', $listingId, PDO::PARAM_INT);
            $query->bindValue(':currencyId', $currencyId, PDO::PARAM_INT);
            $query->bindValue(':divisibility', $divisibility, PDO::PARAM_INT);
            $query->bindValue(':hash', $hash, PDO::PARAM_STR);
            $query->bindValue(':signature', $signature, PDO::PARAM_STR);
            $query->bindValue(':version', $version, PDO::PARAM_STR);
            $query->bindValue(':contractType', $contractType, PDO::PARAM_STR);
            $query->bindValue(':format', $format, PDO::PARAM_STR);
            $query->bindValue(':expiry', $expiry, PDO::PARAM_STR);
            $query->bindValue(':price', $price, PDO::PARAM_STR);
            $query->bindValue(':condition', $condition, PDO::PARAM_STR);
            $query->bindValue(':updated', $updated, PDO::PARAM_INT);
            $query->bindValue(':grams', $grams, PDO::PARAM_STR);
            $query->bindValue(':escrowTimeoutHours', $escrowTimeoutHours, PDO::PARAM_INT);
            $query->bindValue(':coinType', $coinType, PDO::PARAM_STR);
            $query->bindValue(':coinDivisibility', $coinDivisibility, PDO::PARAM_STR);
            $query->bindValue(':priceModifier', $priceModifier, PDO::PARAM_STR);
            $query->bindValue(':listingId', $listingId, PDO::PARAM_INT);
            $query->bindValue(':title', $title, PDO::PARAM_STR);
            $query->bindValue(':description', $description, PDO::PARAM_STR);
            $query->bindValue(':tags', $tags, PDO::PARAM_STR);
            $query->bindValue(':categories', $categories, PDO::PARAM_STR);
            $query->bindValue(':processingTime', $processingTime, PDO::PARAM_STR);
            $query->bindValue(':termsAndConditions', $termsAndConditions, PDO::PARAM_STR);
            $query->bindValue(':refundPolicy', $refundPolicy, PDO::PARAM_STR);
            $query->bindValue(':identityPublicKey', $identityPublicKey, PDO::PARAM_STR);
            $query->bindValue(':bitcoinPublicKey', $bitcoinPublicKey, PDO::PARAM_STR);
            $query->bindValue(':bitcoinSig', $bitcoinSig, PDO::PARAM_STR);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function listingExists($hash) {

        try {

            $query = $this->db->prepare('SELECT `listingId` FROM `listing` WHERE `hash` = ? LIMIT 1');
            $query->execute([$hash]);

            return $query->rowCount() ? $query->fetch()['listingId'] : false;

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getIndexQueue($limit = 1) {

        try {

            $query = $this->db->prepare('SELECT `l`.`listingId`, `l`.`profileId`, `l`.`slug`, `l`.`hash`, `l`.`indexed`, `p`.`peerId`

                                                FROM `listing` AS `l`
                                                JOIN `profile` AS `p` ON (`p`.`profileId` = `l`.`profileId`)

                                                ORDER BY `l`.`indexed` ASC

                                                LIMIT ' . (int) $limit);
            $query->execute();

            if ($query->rowCount()) {
                return $query->fetchAll();
            } else {
                return [];
            }

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getProfileListings($profileId) {

        try {

            $query = $this->db->prepare('SELECT `listingId` FROM `listing` WHERE `profileId` = :profileId');

            $query->bindValue(':profileId', $profileId, PDO::PARAM_INT);

            $query->execute();

            if ($query->rowCount()) {
                return $query->fetchAll();
            } else {
                return [];
            }

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }
}
