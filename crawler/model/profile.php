<?php

class ModelProfile extends Model {

    public function getSitemapProfiles() {

        try {

            $query = $this->db->prepare('SELECT `peerId` FROM `profile` WHERE `updated` IS NOT NULL');

            $query->execute();

            return $query->rowCount() ? $query->fetchAll() : [];

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getProfilesOnlineExpired($time) {

        try {

            $query = $this->db->prepare('SELECT `profileId` FROM `profile` WHERE `online` IS NOT NULL
                                                                             AND (UNIX_TIMESTAMP() - `online`) > :time');

            $query->bindValue(':time', $time, PDO::PARAM_INT);
            $query->execute();

            return $query->rowCount() ? $query->fetchAll() : [];

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addProfile($peerId, $added) {

        try {

            $query = $this->db->prepare('INSERT INTO `profile` SET `peerId` = :peerId, `added` = :added');

            $query->bindValue(':peerId', $peerId, PDO::PARAM_STR);
            $query->bindValue(':added', $added, PDO::PARAM_INT);

            $query->execute();

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function profileMessageExists($profileId, $messageId) {

        try {

            $query = $this->db->prepare('SELECT NULL FROM `profileMessage` WHERE `profileId` = ? AND `messageId` = ? LIMIT 1');

            $query->execute([$profileId, $messageId]);

            return $query->rowCount() ? true : false;

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addProfileMessage($profileId, $messageId, $subject, $message, $outgoing, $added, $created) {

        try {
            $query = $this->db->prepare('INSERT INTO `profileMessage` SET `profileId` = :profileId,
                                                                          `messageId` = :messageId,
                                                                          `subject`   = :subject,
                                                                          `message`   = :message,
                                                                          `outgoing`  = :outgoing,
                                                                          `added`     = :added,
                                                                          `created`   = :created');

            $query->bindValue(':profileId', $profileId, PDO::PARAM_INT);
            $query->bindValue(':messageId', $messageId, PDO::PARAM_STR);
            $query->bindValue(':subject', $subject, PDO::PARAM_STR);
            $query->bindValue(':message', $message, PDO::PARAM_STR);
            $query->bindValue(':outgoing', (int) $outgoing, PDO::PARAM_STR);
            $query->bindValue(':added', $added, PDO::PARAM_INT);
            $query->bindValue(':created', $created, PDO::PARAM_INT);

            $query->execute();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateProfileMessageProcessed($profileId, $messageId, $processed) {

        try {

            $query = $this->db->prepare('UPDATE `profileMessage`  SET `processed` = :processed

                                                                WHERE `profileId` = :profileId
                                                                AND   `messageId` = :messageId

                                                                LIMIT 1');

            $query->bindParam(':profileId', $profileId, PDO::PARAM_INT);
            $query->bindParam(':messageId', $messageId, PDO::PARAM_STR);
            $query->bindParam(':processed', $processed, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addProfileConnection($profileId, $ipId, $protocol, $time) {

        try {
            $query = $this->db->prepare('INSERT INTO `profileConnection` SET `profileId` = :profileId,
                                                                             `protocol`  = :protocol,
                                                                             `ipId`      = :ipId,
                                                                             `time`      = :time');

            $query->bindValue(':profileId', $profileId, PDO::PARAM_INT);
            $query->bindValue(':ipId', $ipId, PDO::PARAM_INT);
            $query->bindValue(':protocol', $protocol, PDO::PARAM_STR);
            $query->bindValue(':time', $time, PDO::PARAM_INT);

            $query->execute();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addProfileSocial($profileId, $type, $username, $proof) {

        try {

            $query = $this->db->prepare('INSERT INTO `profileSocial` SET `profileId` = ?, `type` = ?, `username` = ?, `proof` = ?');

            $query->execute([$profileId, $type, $username, $proof]);

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function flushProfileRatings($profileId) {

        try {

            $query = $this->db->prepare('DELETE FROM `profileRating` WHERE `profileId` = ?');

            $query->execute([$profileId]);

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addProfileRating($profileId,
                                     $buyerProfileId,
                                     $ratingHash,
                                     $time,
                                     $customerService,
                                     $deliverySpeed,
                                     $description,
                                     $overall,
                                     $quality,
                                     $review) {

        try {

            $query = $this->db->prepare('INSERT INTO `profileRating` SET `profileId`       = :profileId,
                                                                         `buyerProfileId`  = :buyerProfileId,
                                                                         `ratingHash`      = :ratingHash,
                                                                         `time`            = :time,
                                                                         `customerService` = :customerService,
                                                                         `deliverySpeed`   = :deliverySpeed,
                                                                         `description`     = :description,
                                                                         `overall`         = :overall,
                                                                         `quality`         = :quality,
                                                                         `review`          = :review');

            $query->bindValue(':profileId', $profileId, PDO::PARAM_INT);
            $query->bindValue(':buyerProfileId', $buyerProfileId, PDO::PARAM_INT);
            $query->bindValue(':ratingHash', $ratingHash, PDO::PARAM_STR);
            $query->bindValue(':time', $time, PDO::PARAM_INT);
            $query->bindValue(':customerService', $customerService, PDO::PARAM_INT);
            $query->bindValue(':deliverySpeed', $deliverySpeed, PDO::PARAM_INT);
            $query->bindValue(':description', $description, PDO::PARAM_INT);
            $query->bindValue(':overall', $overall, PDO::PARAM_INT);
            $query->bindValue(':quality', $quality, PDO::PARAM_INT);
            $query->bindValue(':review', $review, PDO::PARAM_STR);

            $query->execute();

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function flushProfileSocial($profileId) {

        try {

            $query = $this->db->prepare('DELETE FROM `profileSocial` WHERE `profileId` = ?');

            $query->execute([$profileId]);

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addProfileModeratorLanguage($profileId, $languageId) {

        try {

            $query = $this->db->prepare('INSERT INTO `profileModeratorLanguage` SET `profileId` = ?, `languageId` = ?');

            $query->execute([$profileId, $languageId]);

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function flushProfileModeratorLanguage($profileId) {

        try {

            $query = $this->db->prepare('DELETE FROM `profileModeratorLanguage` WHERE `profileId` = ?');

            $query->execute([$profileId]);

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addProfileModeratorCurrency($profileId, $currencyId) {

        try {

            $query = $this->db->prepare('INSERT INTO `profileModeratorCurrency` SET `profileId` = ?, `currencyId` = ?');

            $query->execute([$profileId, $currencyId]);

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function flushProfileModeratorCurrency($profileId) {

        try {

            $query = $this->db->prepare('DELETE FROM `profileModeratorCurrency` WHERE `profileId` = ?');

            $query->execute([$profileId]);

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function profileCurrencyExists($profileId, $currencyId) {

        try {

            $query = $this->db->prepare('SELECT NULL FROM `profileCurrency` WHERE `profileId` = ? AND `currencyId` = ? LIMIT 1');

            $query->execute([$profileId, $currencyId]);

            return $query->rowCount() ? true : false;

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addProfileCurrency($profileId, $currencyId) {

        try {

            $query = $this->db->prepare('INSERT INTO `profileCurrency` SET `profileId` = ?, `currencyId` = ?');

            $query->execute([$profileId, $currencyId]);

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function flushProfileCurrency($profileId) {

        try {

            $query = $this->db->prepare('DELETE FROM `profileCurrency` WHERE `profileId` = ?');

            $query->execute([$profileId]);

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function flushProfileFollowing($profileId) {

        try {

            $query = $this->db->prepare('DELETE FROM `profileFollowing` WHERE `profileId` = ?');

            $query->execute([$profileId]);

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addProfileFollowing($profileId, $followingProfileId) {

        try {

            $query = $this->db->prepare('INSERT INTO `profileFollowing` SET `profileId` = ?, `followingProfileId` = ?');

            $query->execute([$profileId, $followingProfileId]);

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function flushProfileFollowers($profileId) {

        try {

            $query = $this->db->prepare('DELETE FROM `profileFollower` WHERE `profileId` = ?');

            $query->execute([$profileId]);

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addProfileFollower($profileId, $followerProfileId) {

        try {

            $query = $this->db->prepare('INSERT INTO `profileFollower` SET `profileId` = ?, `followerProfileId` = ?');

            $query->execute([$profileId, $followerProfileId]);

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateProfile($profileId,
                                  $version,
                                  $handle,
                                  $bitcoinPubkey,
                                  $lastModified,
                                  $updated,
                                  $vendor,
                                  $website,
                                  $email,
                                  $phoneNumber,
                                  $colorPrimary,
                                  $colorSecondary,
                                  $colorText,
                                  $colorHighlight,
                                  $colorHighlightText,
                                  $avatarHashTiny,
                                  $avatarHashSmall,
                                  $avatarHashMedium,
                                  $avatarHashLarge,
                                  $avatarHashOriginal,
                                  $headerHashTiny,
                                  $headerHashSmall,
                                  $headerHashMedium,
                                  $headerHashLarge,
                                  $headerHashOriginal,
                                  $name,
                                  $location,
                                  $shortDescription,
                                  $about,
                                  $moderator) {

        try {

            $query = $this->db->prepare('UPDATE `profile` SET `version`             = :version,
                                                              `handle`              = :handle,
                                                              `bitcoinPubkey`       = :bitcoinPubkey,
                                                              `lastModified`        = :lastModified,
                                                              `updated`             = :updated,
                                                              `vendor`              = :vendor,
                                                              `website`             = :website,
                                                              `email`               = :email,
                                                              `phoneNumber`         = :phoneNumber,
                                                              `colorPrimary`        = :colorPrimary,
                                                              `colorSecondary`      = :colorSecondary,
                                                              `colorText`           = :colorText,
                                                              `colorHighlight`      = :colorHighlight,
                                                              `colorHighlightText`  = :colorHighlightText,
                                                              `avatarHashTiny`      = :avatarHashTiny,
                                                              `avatarHashSmall`     = :avatarHashSmall,
                                                              `avatarHashMedium`    = :avatarHashMedium,
                                                              `avatarHashLarge`     = :avatarHashLarge,
                                                              `avatarHashOriginal`  = :avatarHashOriginal,
                                                              `headerHashTiny`      = :headerHashTiny,
                                                              `headerHashSmall`     = :headerHashSmall,
                                                              `headerHashMedium`    = :headerHashMedium,
                                                              `headerHashLarge`     = :headerHashLarge,
                                                              `headerHashOriginal`  = :headerHashOriginal,
                                                              `name`                = :name,
                                                              `location`            = :location,
                                                              `shortDescription`    = :shortDescription,
                                                              `about`               = :about,
                                                              `moderator`           = :moderator

                                                          WHERE `profileId` = :profileId
                                                          LIMIT 1');

            $query->bindValue(':profileId', $profileId, PDO::PARAM_INT);
            $query->bindValue(':version', $version, PDO::PARAM_STR);
            $query->bindValue(':handle', $handle, PDO::PARAM_STR);
            $query->bindValue(':bitcoinPubkey', $bitcoinPubkey, PDO::PARAM_STR);
            $query->bindValue(':lastModified', $lastModified, PDO::PARAM_STR);
            $query->bindValue(':updated', $updated, PDO::PARAM_INT);
            $query->bindValue(':vendor', (int) $vendor, PDO::PARAM_STR);
            $query->bindValue(':website', $website, PDO::PARAM_STR);
            $query->bindValue(':email', $email, PDO::PARAM_STR);
            $query->bindValue(':phoneNumber', $phoneNumber, PDO::PARAM_STR);
            $query->bindValue(':colorPrimary', $colorPrimary, PDO::PARAM_STR);
            $query->bindValue(':colorSecondary', $colorSecondary, PDO::PARAM_STR);
            $query->bindValue(':colorText', $colorText, PDO::PARAM_STR);
            $query->bindValue(':colorHighlight', $colorHighlight, PDO::PARAM_STR);
            $query->bindValue(':colorHighlightText', $colorHighlightText, PDO::PARAM_STR);
            $query->bindValue(':avatarHashTiny', $avatarHashTiny, PDO::PARAM_STR);
            $query->bindValue(':avatarHashSmall', $avatarHashSmall, PDO::PARAM_STR);
            $query->bindValue(':avatarHashMedium', $avatarHashMedium, PDO::PARAM_STR);
            $query->bindValue(':avatarHashLarge', $avatarHashLarge, PDO::PARAM_STR);
            $query->bindValue(':avatarHashOriginal', $avatarHashOriginal, PDO::PARAM_STR);
            $query->bindValue(':headerHashTiny', $headerHashTiny, PDO::PARAM_STR);
            $query->bindValue(':headerHashSmall', $headerHashSmall, PDO::PARAM_STR);
            $query->bindValue(':headerHashMedium', $headerHashMedium, PDO::PARAM_STR);
            $query->bindValue(':headerHashLarge', $headerHashLarge, PDO::PARAM_STR);
            $query->bindValue(':headerHashOriginal', $headerHashOriginal, PDO::PARAM_STR);
            $query->bindValue(':name', $name, PDO::PARAM_STR);
            $query->bindValue(':location', $location, PDO::PARAM_STR);
            $query->bindValue(':shortDescription', $shortDescription, PDO::PARAM_STR);
            $query->bindValue(':about', $about, PDO::PARAM_STR);
            $query->bindValue(':moderator', (int) $moderator, PDO::PARAM_STR);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateProfileModerator( $profileId,
                                            $moderatorFeeType,
                                            $moderatorAmount,
                                            $moderatorPercentage,
                                            $moderatorCurrencyId,
                                            $moderatorDescription,
                                            $moderatorTerms) {

        try {

          $query = $this->db->prepare('UPDATE `profile` SET  `moderatorFeeType`     = :moderatorFeeType,
                                                             `moderatorAmount`      = :moderatorAmount,
                                                             `moderatorPercentage`  = :moderatorPercentage,
                                                             `moderatorCurrencyId`  = :moderatorCurrencyId,
                                                             `moderatorDescription` = :moderatorDescription,
                                                             `moderatorTerms`       = :moderatorTerms

                                                        WHERE `profileId` = :profileId
                                                        LIMIT 1');


            $query->bindValue(':profileId', $profileId, PDO::PARAM_INT);
            $query->bindValue(':moderatorCurrencyId', $moderatorCurrencyId, PDO::PARAM_INT);
            $query->bindValue(':moderatorFeeType', $moderatorFeeType, PDO::PARAM_STR);
            $query->bindValue(':moderatorAmount', $moderatorAmount, PDO::PARAM_STR);
            $query->bindValue(':moderatorPercentage', $moderatorPercentage, PDO::PARAM_STR);
            $query->bindValue(':moderatorDescription', $moderatorDescription, PDO::PARAM_STR);
            $query->bindValue(':moderatorTerms', $moderatorTerms, PDO::PARAM_STR);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateProfileIndexed($profileId, $indexed) {

        try {

            $query = $this->db->prepare('UPDATE `profile` SET `indexed` = :indexed

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

    public function updateProfileNsfw($profileId, $nsfw) {

        try {

            $query = $this->db->prepare('UPDATE `profile` SET `nsfw` = :nsfw

                                                        WHERE `profileId` = :profileId
                                                        LIMIT 1
                                                ');

            $query->bindParam(':profileId', $profileId, PDO::PARAM_INT);
            $query->bindParam(':nsfw', $nsfw, PDO::PARAM_STR);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateProfileOnline($profileId, $online) {

        try {

            $query = $this->db->prepare('UPDATE `profile` SET `online` = :online WHERE `profileId` = :profileId LIMIT 1');

            $query->bindParam(':profileId', $profileId, PDO::PARAM_INT);
            $query->bindParam(':online', $online, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateProfileTor($profileId, $tor) {

        try {

            $query = $this->db->prepare('UPDATE `profile` SET `tor` = :tor WHERE `profileId` = :profileId LIMIT 1');

            $query->bindParam(':profileId', $profileId, PDO::PARAM_INT);
            $query->bindParam(':tor', $tor, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateProfileCountryId($profileId, $countryId) {

        try {

            $query = $this->db->prepare('UPDATE `profile` SET `countryId` = :countryId

                                                        WHERE `profileId` = :profileId
                                                        LIMIT 1
                                                ');

            $query->bindParam(':profileId', $profileId, PDO::PARAM_INT);
            $query->bindParam(':countryId', $countryId, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateProfileRating($profileId, $ratingAverage, $ratingCount, $businessLevel) {

        try {

            $query = $this->db->prepare('UPDATE `profile` SET `ratingAverage` = :ratingAverage,
                                                              `ratingCount`   = :ratingCount,
                                                              `businessLevel` = :businessLevel

                                                        WHERE `profileId`     = :profileId
                                                        LIMIT 1
                                                ');

            $query->bindParam(':profileId', $profileId, PDO::PARAM_INT);
            $query->bindParam(':ratingAverage', $ratingAverage, PDO::PARAM_STR);
            $query->bindParam(':ratingCount', $ratingCount, PDO::PARAM_INT);
            $query->bindParam(':businessLevel', $businessLevel, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function profileExists($peerId) {

        try {

            $query = $this->db->prepare('SELECT `profileId` FROM `profile` WHERE `peerId` = ? LIMIT 1');
            $query->execute([$peerId]);

            return $query->rowCount() ? $query->fetch()['profileId'] : false;

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function isWaiting($profileId) {

        try {

            $query = $this->db->prepare('SELECT NULL FROM `profile` WHERE `profileId` = ?
                                                                      AND `updated` IS NULL
                                                                      AND `error` IS NULL
                                                                      AND `indexed` IS NOT NULL LIMIT 1');
            $query->execute([$profileId]);

            return $query->rowCount() ? true : false;

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateProfileError($profileId, $time) {

        try {

            $query = $this->db->prepare('UPDATE `profile` SET `error`     = :error
                                                        WHERE `profileId` = :profileId
                                                        LIMIT 1');

            $query->bindParam(':profileId', $profileId, PDO::PARAM_INT);
            $query->bindParam(':error', $time, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getLastProfileConnection($profileId) {

        try {

            $query = $this->db->prepare('SELECT `pc`.`time`,
                                                `i`.`tor`,
                                                `i`.`countryId`,
                                                `c`.`codeIso2` FROM `profileConnection` AS `pc`
                                                               JOIN `ip` AS `i` ON (`i`.`ipId` = `pc`.`ipId`)
                                                               LEFT JOIN `country` AS `c` ON (`c`.`countryId` = `i`.`countryId`)

                                                               WHERE `pc`.`profileId` = ?

                                                               ORDER BY `pc`.`time` DESC
                                                               LIMIT 1');
            $query->execute([$profileId]);

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

    public function getIndexedProfiles() {

        try {

            $query = $this->db->prepare('SELECT `profileId`

                                                FROM `profile`

                                                WHERE `indexed` IS NOT NULL');
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

    public function getIndexQueue($limit = 1) {

        try {

            $query = $this->db->prepare('SELECT `profileId`, `peerId`, `lastModified`, `indexed`

                                                FROM `profile`

                                                ORDER BY `indexed` ASC

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

    public function profileMessagesQueue($limit = 100) {

        try {

            $query = $this->db->prepare('SELECT `pm`.*, `p`.`peerId` FROM `profileMessage` AS `pm`
                                                                     JOIN `profile` AS `p` ON (`p`.`profileId` = `pm`.`profileId`)

                                                                     WHERE (`pm`.`processed` IS NULL OR `pm`.`processed` = 0)
                                                                       AND `pm`.`outgoing` = "0"
                                                                     ORDER BY `pm`.`created` ASC

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
}
