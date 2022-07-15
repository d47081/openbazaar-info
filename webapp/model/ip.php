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
}
