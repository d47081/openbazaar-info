<?php

class ModelExtension extends Model {

    public function getExtensions($clientVersion, $serverVersion) {

        try {

            $query = $this->db->prepare('SELECT `e`.*, `p`.`peerId` FROM `extension` AS `e`
                                                                    JOIN `profile` AS `p` ON (`p`.`profileId` = `e`.`profileId`)

                                                                   WHERE `e`.`clientVersion` = :clientVersion
                                                                     AND `e`.`serverVersion` = :serverVersion');

            $query->bindValue(':clientVersion', $clientVersion, PDO::PARAM_STR);
            $query->bindValue(':serverVersion', $serverVersion, PDO::PARAM_STR);

            $query->execute();

            return $query->rowCount() ? $query->fetchAll() : [];

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getExtensionMirrors($extensionId) {

        try {

            $query = $this->db->prepare('SELECT * FROM `extensionMirrors` WHERE `extensionId` = :extensionId');

            $query->bindValue(':extensionId', $extensionId, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount() ? $query->fetchAll() : [];

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getTotalExtensionDownloads($extensionId) {

        try {

            $query = $this->db->prepare('SELECT COUNT(DISTINCT `ipId`) AS `total` FROM `extensionDownload` WHERE `extensionId` = :extensionId');

            $query->bindValue(':extensionId', $extensionId, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount() ? $query->fetch()['total'] : 0;

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addExtensionDownload($extensionId, $ipId, $time) {

        try {

            $query = $this->db->prepare('INSERT INTO `extensionDownload` SET `extensionId` = :extensionId, `ipId` = :ipId, `time` = :time');

            $query->bindValue(':extensionId', $extensionId, PDO::PARAM_INT);
            $query->bindValue(':ipId', $ipId, PDO::PARAM_INT);
            $query->bindValue(':time', $time, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount() ? $query->fetch()['total'] : 0;

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getExtensionIdByKeys($profileId, $name, $clientVersion, $serverVersion) {

        try {

            $query = $this->db->prepare('SELECT `extensionId` FROM `extension` WHERE `profileId`     = :profileId
                                                                                 AND `clientVersion` = :clientVersion
                                                                                 AND `serverVersion` = :serverVersion
                                                                                 AND `name`          = :name

                                                                               LIMIT 1');

            $query->bindValue(':profileId', $profileId, PDO::PARAM_INT);
            $query->bindValue(':name', $name, PDO::PARAM_STR);
            $query->bindValue(':clientVersion', $clientVersion, PDO::PARAM_STR);
            $query->bindValue(':serverVersion', $serverVersion, PDO::PARAM_STR);

            $query->execute();

            return $query->rowCount() ? $query->fetch()['extensionId'] : false;

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }
}
