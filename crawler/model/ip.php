<?php

class ModelIp extends Model {

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

    public function getTorTime($ipId) {

        try {

            $query = $this->db->prepare('SELECT `tor` FROM `ip` WHERE `ipId` = ? AND `tor` > 0 LIMIT 1');

            $query->execute([$ipId]);

            return $query->rowCount() ? $query->fetch()['tor'] : 0;

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateIpIndexed($ipId, $indexed) {

        try {

            $query = $this->db->prepare('UPDATE `ip` SET `indexed` = ? WHERE `ipId` = ? LIMIT 1');

            $query->execute([$indexed, $ipId]);

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateCountryId($ipId, $countryId) {

        try {

            $query = $this->db->prepare('UPDATE `ip` SET `countryId` = ? WHERE `ipId` = ? LIMIT 1');

            $query->execute([$countryId, $ipId]);

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateRegionId($ipId, $regionId) {

        try {

            $query = $this->db->prepare('UPDATE `ip` SET `regionId` = ? WHERE `ipId` = ? LIMIT 1');

            $query->execute([$regionId, $ipId]);

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateCityId($ipId, $cityId) {

        try {

            $query = $this->db->prepare('UPDATE `ip` SET `cityId` = ? WHERE `ipId` = ? LIMIT 1');

            $query->execute([$cityId, $ipId]);

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function expireTor($time) {

        try {

            $query = $this->db->prepare("UPDATE `ip` SET `tor` = '0' WHERE (`tor` + :time) < UNIX_TIMESTAMP()");

            $query->bindValue(':time', $time, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateTor($ipId, $tor) {

        try {

            $query = $this->db->prepare('UPDATE `ip` SET `tor` = :tor WHERE `ipId` = :ipId LIMIT 1');

            $query->bindValue(':ipId', $ipId, PDO::PARAM_INT);
            $query->bindValue(':tor', $tor, PDO::PARAM_STR);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getIndexQueue($limit = 1) {

        try {

            $query = $this->db->prepare('SELECT `ipId`,
                                                `version`,
                                                 INET_NTOA(`ip`) AS `ipv4`,
                                                 INET6_NTOA(`ip`) AS `ipv6`

                                                FROM `ip`
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
}
