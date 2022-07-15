<?php

class ModelListing extends Model {

    public function getNewListings($from, $to) {

        try {

            $query = $this->db->prepare('SELECT COUNT(*) AS `total` FROM `listing`
                                                                   WHERE `added` >= ?
                                                                     AND `added` < ?');

            $query->execute([$from, $to]);

            return $query->rowCount() ? $query->fetch()['total'] : 0;

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getListingIdByHash($hash) {

        try {

            $query = $this->db->prepare('SELECT `listingId` FROM `listing` WHERE `hash` = ? LIMIT 1');
            $query->execute([$hash]);

            if ($query->rowCount()) {
                return $query->fetch()['listingId'];
            } else {
                return false;
            }

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getTotalListingShippings($listingId) {

        try {

            $query = $this->db->prepare('SELECT COUNT(*) AS `total` FROM `listingShipping` WHERE `listingId` = ?');
            $query->execute([$listingId]);

            if ($query->rowCount()) {
                return $query->fetch()['total'];
            } else {
                return false;
            }

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getListingShippings($listingId) {

        try {

            $query = $this->db->prepare('SELECT `c`.`code`,
                                                `ls`.`name`,
                                                `ls`.`type`,
                                                `ls`.`services`,
                                                `ls`.`countries` FROM `listingShipping` AS `ls`
                                                                 LEFT JOIN `listing` AS `l` ON (`l`.`listingId` = `ls`.`listingId`)
                                                                 LEFT JOIN `currency` AS `c` ON (`c`.`currencyId` = `l`.`currencyId`)

                                                                WHERE `ls`.`listingId` = ?');
            $query->execute([$listingId]);

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

    public function getListingShippingCountries($listingId) {

        try {

            $query = $this->db->prepare('SELECT `c`.`name`,
                                                `c`.`code`,
                                                `c`.`codeIso2`

                                                FROM `listingShippingCountry` AS `lsc`
                                                JOIN `country` AS `c` ON (`c`.`countryId` = `lsc`.`countryId`)

                                               WHERE `lsc`.`listingId` = ?');
            $query->execute([$listingId]);

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

    public function getListingOptions($listingId) {

        try {

            $query = $this->db->prepare('SELECT * FROM `listingOption` WHERE `listingId` = ?');
            $query->execute([$listingId]);

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

    public function getListingImages($listingId) {

        try {

            $query = $this->db->prepare('SELECT * FROM `listingImage` WHERE `listingId` = ?');
            $query->execute([$listingId]);

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

    public function getListingCurrencies($listingId) {

        try {

            $query = $this->db->prepare('SELECT * FROM `listingCurrency` AS `lc`
                                                  JOIN `currency` AS `c` ON (`c`.`currencyId` = `lc`.`currencyId`)

                                                 WHERE `lc`.`listingId` = ?');
            $query->execute([$listingId]);

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

    public function getListingModerators($listingId, $start, $limit) {

        try {

            $query = $this->db->prepare('SELECT `p`.`peerId`,
                                                `p`.`profileId`,
                                                `p`.`updated`,
                                                `p`.`name`,
                                                `p`.`shortDescription`,
                                                `p`.`moderatorFeeType`,
                                                `p`.`moderatorPercentage`,
                                                `p`.`moderatorAmount`,
                                                `p`.`avatarHashMedium`,
                                                `p`.`nsfw`,
                                                `p`.`online`,
                                                `c`.`code` AS `moderatorCurrencyCode`

                                                FROM `listingModerator` AS `lm`
                                                LEFT JOIN `profile` AS `p` ON (`p`.`profileId` = `lm`.`profileId`)
                                                LEFT JOIN `currency` AS `c` ON (`c`.`currencyId` = `p`.`moderatorCurrencyId`)

                                                WHERE `lm`.`listingId` = :listingId

                                                GROUP BY `lm`.`profileId`
                                                ORDER BY `online` DESC

                                                LIMIT :start, :limit');

            $query->bindValue(':listingId', $listingId, PDO::PARAM_INT);
            $query->bindValue(':start', $start, PDO::PARAM_INT);
            $query->bindValue(':limit', $limit, PDO::PARAM_INT);

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

    public function getListingByHash($hash) {

        try {

            $query = $this->db->prepare('SELECT `l`.*,
                                                `c`.`code`,
                                                `p`.`peerId`,
                                                `p`.`name`,
                                                `p`.`ratingAverage`,
                                                `p`.`ratingCount`,
                                                `p`.`online`,
                                                `p`.`blocked`,
                                                 COUNT(DISTINCT `lm`.`profileId`) AS `moderators`

                                              FROM `listing` AS `l`
                                              LEFT JOIN `listingModerator` AS `lm` ON (`lm`.`listingId` = `l`.`listingId`)
                                              LEFT JOIN `currency` AS `c` ON (`c`.`currencyId` = `l`.`currencyId`)
                                              LEFT JOIN `profile` AS `p` ON (`p`.`profileId` = `l`.`profileId`)

                                            WHERE `l`.`hash` = ?

                                            GROUP BY `l`.`listingId`

                                            LIMIT 1');
            $query->execute([$hash]);

            if ($query->rowCount()) {
                return $query->fetch();
            } else {
                return false;
            }

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getTotalListings() {

        try {

            $query = $this->db->prepare('SELECT COUNT(*) AS `total` FROM `listing`');
            $query->execute();

            if ($query->rowCount()) {
                return $query->fetch()['total'];
            } else {
                return false;
            }

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getTotalListingModerators($listingId) {

        try {

            $query = $this->db->prepare('SELECT COUNT(*) AS `total` FROM `listingModerator` WHERE `listingId` = ?');
            $query->execute([$listingId]);

            if ($query->rowCount()) {
                return $query->fetch()['total'];
            } else {
                return false;
            }

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getTotalProfileListings($profileId) {

        try {

            $query = $this->db->prepare('SELECT COUNT(*) AS `total` FROM `listing` WHERE `profileId` = ?');
            $query->execute([$profileId]);

            if ($query->rowCount()) {
                return $query->fetch()['total'];
            } else {
                return false;
            }

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getProfileListings($profileId, $start, $limit) {

        try {

            $query = $this->db->prepare('SELECT `l`.*,
                                                `c`.*,
                                                `p`.`peerId`,
                                                `p`.`ratingAverage`,
                                                `p`.`ratingCount`,
                                                `p`.`updated`,
                                                `p`.`name`,
                                                `p`.`shortDescription`,
                                                 COUNT(DISTINCT `lm`.`profileId`) AS `moderators`,
                                                (SELECT `li`.`small` FROM `listingImage` AS `li` WHERE `li`.`listingId` = `l`.`listingId` LIMIT 1) AS `image`

                                                FROM `listing` AS `l`
                                                LEFT JOIN `listingModerator` AS `lm` ON (`lm`.`listingId` = `l`.`listingId`)
                                                LEFT JOIN `profile` AS `p` ON (`p`.`profileId` = `l`.`profileId`)
                                                LEFT JOIN `currency` AS `c` ON (`c`.`currencyId` = `l`.`currencyId`)

                                                WHERE `l`.`profileId` = :profileId

                                                GROUP BY `l`.`listingId`
                                                ORDER BY `l`.`updatedIpns` DESC, `l`.`updatedIpfs` DESC

                                                LIMIT :start, :limit');

            $query->bindValue(':profileId', $profileId, PDO::PARAM_INT);
            $query->bindValue(':start', $start, PDO::PARAM_INT);
            $query->bindValue(':limit', $limit, PDO::PARAM_INT);

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
