<?php

class ModelProfile extends Model {

    public function getTotalProfiles() {

        try {

            $query = $this->db->prepare('SELECT COUNT(*) AS `total` FROM `profile`');
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

    public function getNewProfiles($from, $to) {

        try {
            $query = $this->db->prepare('SELECT COUNT(*) AS `total` FROM `profile`
                                                                   WHERE `added` >= ?
                                                                     AND `added` < ?');

            $query->execute([$from, $to]);

            return $query->rowCount() ? $query->fetch()['total'] : 0;

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getPeersOnline($from, $to) {

        try {

            $query = $this->db->prepare('SELECT COUNT(*) AS `total` FROM `profileConnection`
                                                                   WHERE `time` >= ?
                                                                     AND `time` < ?
                                                                GROUP BY `profileId`');

            $query->execute([$from, $to]);

            return $query->rowCount() ? $query->fetch()['total'] : 0;

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getProfileUptime($profileId, $division, $from, $to) {

        try {

            $query = $this->db->prepare('SELECT NULL FROM `profileConnection`
                                                    WHERE `profileId` = ?
                                                      AND `time` >= ?
                                                      AND `time` < ?
                                                 GROUP BY FLOOR(`time` / ?)');

            $query->execute([$profileId, $from, $to, $division]);
            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getTotalProfileRatings($profileId) {

        try {

            $query = $this->db->prepare('SELECT COUNT(*) AS `total` FROM `profileRating` WHERE `profileId` = ?');
            $query->execute([$profileId]);

            if ($query->rowCount()) {
                return $query->fetch()['total'];
            } else {
                return [];
            }

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getProfileRatings($profileId, $start, $limit) {

        try {

            $query = $this->db->prepare('SELECT `p`.`peerId`,
                                                `p`.`profileId`,
                                                `p`.`updated`,
                                                `p`.`name`,
                                                `p`.`online`,
                                                `pr`.`time`,
                                                `pr`.`customerService`,
                                                `pr`.`deliverySpeed`,
                                                `pr`.`description`,
                                                `pr`.`overall`,
                                                `pr`.`quality`,
                                                `pr`.`review`

                                                FROM `profileRating` AS `pr`
                                                LEFT JOIN `profile` AS `p` ON (`p`.`profileId` = `pr`.`buyerProfileId`)

                                                WHERE `pr`.`profileId` = :profileId

                                                GROUP BY `pr`.`time`
                                                ORDER BY `pr`.`time` DESC

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

    public function getTotalProfileConnections($profileId) {

        try {

            $query = $this->db->prepare('SELECT COUNT(*) AS `total` FROM `profileConnection` WHERE `profileId` = ?');
            $query->execute([$profileId]);

            if ($query->rowCount()) {
                return $query->fetch()['total'];
            } else {
                return [];
            }

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getDistinctTotalProfileConnections($profileId) {

        try {

            $query = $this->db->prepare('SELECT DISTINCT `ipId` FROM `profileConnection` WHERE `profileId` = ?');
            $query->execute([$profileId]);

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getProfileConnections($profileId, $start, $limit) {

        try {

            $query = $this->db->prepare('SELECT DISTINCT `pc`.`ipId`,
                                                         `pc`.`protocol`,
                                                          MAX(`pc`.`time`) AS `time`,
                                                          COUNT(`pc`.`ipId`) AS `frequency`,
                                                          `i`.`tor`,
                                                          `c`.`codeIso2` AS `countryCode`,
                                                          `c`.`name` AS `country`,
                                                          `r`.`name` AS `region`,
                                                          `s`.`name` AS `city`  FROM `profileConnection` AS `pc`
                                                                                JOIN `ip` AS `i` ON (`i`.`ipId` = `pc`.`ipId`)
                                                                           LEFT JOIN `country` AS `c` ON (`c`.`countryId` = `i`.`countryId`)
                                                                           LEFT JOIN `region` AS `r` ON (`r`.`regionId` = `i`.`regionId`)
                                                                           LEFT JOIN `city` AS `s` ON (`s`.`cityId` = `i`.`cityId`)

                                                                               WHERE `pc`.`profileId` = :profileId

                                                                            GROUP BY `i`.`ipId`
                                                                            ORDER BY `frequency` DESC

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

    public function getProfileFollowing($profileId, $start, $limit) {

        try {

            $query = $this->db->prepare('SELECT `p`.`peerId`,
                                                `p`.`profileId`,
                                                `p`.`updated`,
                                                `p`.`name`,
                                                `p`.`shortDescription`,
                                                `p`.`avatarHashMedium`,
                                                `p`.`nsfw`,
                                                `p`.`online`

                                                FROM `profileFollowing` AS `pf`
                                                LEFT JOIN `profile` AS `p` ON (`p`.`profileId` = `pf`.`followingProfileId`)

                                                WHERE `pf`.`profileId` = :profileId
                                                GROUP BY `pf`.`followingProfileId`
                                                ORDER BY `online` DESC

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

    public function getTotalProfileFollowing($profileId) {

        try {

            $query = $this->db->prepare('SELECT COUNT(*) AS `total` FROM `profileFollowing` WHERE `profileId` = ?');

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

    public function getProfileFollowers($profileId, $start, $limit) {

        try {

            $query = $this->db->prepare('SELECT `p`.`peerId`,
                                                `p`.`profileId`,
                                                `p`.`updated`,
                                                `p`.`name`,
                                                `p`.`shortDescription`,
                                                `p`.`avatarHashMedium`,
                                                `p`.`nsfw`,
                                                `p`.`online`

                                                FROM `profileFollower` AS `pf`
                                                LEFT JOIN `profile` AS `p` ON (`p`.`profileId` = `pf`.`followerProfileId`)

                                                WHERE `pf`.`profileId` = :profileId
                                                GROUP BY `pf`.`followerProfileId`
                                                ORDER BY `online` DESC

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

    public function getProfileSocials($profileId) {

        try {

            $query = $this->db->prepare('SELECT * FROM `profileSocial` WHERE `profileId` = ?');

            $query->execute([$profileId]);

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

    public function getTotalProfileSocials($profileId) {

        try {

            $query = $this->db->prepare('SELECT COUNT(*) AS `total` FROM `profileSocial` WHERE `profileId` = ?');

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

    public function getTotalProfileFollowers($profileId) {

        try {

            $query = $this->db->prepare('SELECT COUNT(*) AS `total` FROM `profileFollower` WHERE `profileId` = ?');

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

    public function getProfileIdByPeerId($peerId) {

        try {

            $query = $this->db->prepare('SELECT `profileId` FROM `profile` WHERE `peerId` = ? LIMIT 1');
            $query->execute([$peerId]);

            if ($query->rowCount()) {
                return $query->fetch()['profileId'];
            } else {
                return false;
            }

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getModeratorCurrencies($profileId) {

        try {

            $query = $this->db->prepare('SELECT * FROM `profileModeratorCurrency` AS `pmc`
                                                  JOIN `currency` AS `c` ON (`c`.`currencyId` = `pmc`.`currencyId`)

                                                 WHERE `pmc`.`profileId` = ?');
            $query->execute([$profileId]);

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

    public function getModeratorLanguages($profileId) {

        try {

            $query = $this->db->prepare('SELECT * FROM `profileModeratorLanguage` AS `pml`
                                                  JOIN `language` AS `l` ON (`l`.`languageId` = `pml`.`languageId`)

                                                 WHERE `pml`.`profileId` = ?');
            $query->execute([$profileId]);

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

    public function getProfileByPeer($peerId) {

        try {

          $query = $this->db->prepare('SELECT `p`.`peerId`,
                                              `p`.`profileId`,
                                              `p`.`handle`,
                                              `p`.`updated`,
                                              `p`.`added`,
                                              `p`.`indexed`,
                                              `p`.`location`,
                                              `p`.`shortDescription`,
                                              `p`.`about`,
                                              `p`.`email`,
                                              `p`.`phoneNumber`,
                                              `p`.`website`,
                                              `p`.`moderator`,
                                              `p`.`moderatorDescription`,
                                              `p`.`moderatorTerms`,
                                              `p`.`moderatorFeeType`,
                                              `p`.`moderatorPercentage`,
                                              `p`.`moderatorAmount`,
                                              `c`.`code` AS `moderatorCurrencyCode`,
                                              `p`.`vendor`,
                                              `p`.`nsfw`,
                                              `p`.`blocked`,
                                              `p`.`name`,
                                              `p`.`shortDescription`,
                                              `p`.`bitcoinPubkey`,
                                              `p`.`avatarHashTiny`,
                                              `p`.`avatarHashSmall`,
                                              `p`.`avatarHashMedium`,
                                              `p`.`avatarHashLarge`,
                                              `p`.`avatarHashOriginal`,
                                              `p`.`ratingAverage`,
                                              `p`.`ratingCount`,
                                               MAX(`pc`.`time`) AS `online` FROM `profile` AS `p`
                                                                            LEFT JOIN `profileConnection` AS `pc` ON (`pc`.`profileId` = `p`.`profileId`)
                                                                            LEFT JOIN `currency` AS `c` ON (`c`.`currencyId` = `p`.`moderatorCurrencyId`)

                                                                            WHERE `p`.`peerId` = ?
                                                                            GROUP BY `p`.`profileId`
                                                                            LIMIT 1');

            $query->execute([$peerId]);

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

    public function addIp($ip, $version) {

        try {

          $query   = $this->db->prepare('INSERT INTO `ip`
                                                SET  `ip`      = ' . ($version == 6 ? 'INET6_ATON' : 'INET_ATON') . '(:ip),
                                                     `hash`    = CRC32(:ip),
                                                     `version` = :version');

          $query->bindValue(':ip', $ip, PDO::PARAM_STR);
          $query->bindValue(':version', $version, PDO::PARAM_STR);

          $query->execute();

          return $this->db->lastInsertId();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function ipExists($ip, $version) {

        try {

            $query = $this->db->prepare('SELECT `ipId` FROM  `ip`
                                                       WHERE `hash`    = CRC32(:ip)
                                                       AND   `version` = :version

                                                       LIMIT 1');

            $query->bindValue(':ip', $ip, PDO::PARAM_STR);
            $query->bindValue(':version', $version, PDO::PARAM_STR);

            $query->execute();

            return $query->rowCount() ? $query->fetch()['ipId'] : false;

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }
}
