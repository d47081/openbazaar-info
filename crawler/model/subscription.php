<?php

class ModelSubscription extends Model {

    public function flushSubscriptions($profileId) {

        try {

            $query = $this->db->prepare('DELETE FROM `subscription` WHERE `profileId` = ?');

            $query->execute([$profileId]);

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function deleteExpiredSubscriptions() {

        try {

            $query = $this->db->prepare('DELETE FROM `subscription` WHERE UNIX_TIMESTAMP() > `expired`');

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function deleteSubscription($subscriptionId) {

        try {

            $query = $this->db->prepare('DELETE FROM `subscription` WHERE `subscriptionId` = ? LIMIT 1');

            $query->execute([$subscriptionId]);

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addSubscription( $profileId,
                                     $added,
                                     $expired,
                                     $hash,
                                     $protocol,
                                     $t,
                                     $q,
                                     $s,
                                     $o,
                                     $m,
                                     $lf,
                                     $lc,
                                     $lt,
                                     $pr,
                                     $ps,
                                     $id) {

        try {

            $query = $this->db->prepare('INSERT INTO `subscription` SET  `profileId` = :profileId,
                                                                         `added`     = :added,
                                                                         `expired`   = :expired,
                                                                         `hash`      = :hash,
                                                                         `protocol`  = :protocol,
                                                                         `t`         = :t,
                                                                         `q`         = :q,
                                                                         `s`         = :s,
                                                                         `o`         = :o,
                                                                         `m`         = :m,
                                                                         `lf`        = :lf,
                                                                         `lc`        = :lc,
                                                                         `lt`        = :lt,
                                                                         `pr`        = :pr,
                                                                         `ps`        = :ps,
                                                                         `id`        = :id,
                                                                         `sent`      = 0');

            $query->bindValue(':profileId', $profileId, PDO::PARAM_INT);
            $query->bindValue(':added', $added, PDO::PARAM_INT);
            $query->bindValue(':expired', $expired, PDO::PARAM_INT);
            $query->bindValue(':hash', $hash, PDO::PARAM_STR);
            $query->bindValue(':protocol', $protocol, PDO::PARAM_STR);
            $query->bindValue(':t', $t, PDO::PARAM_STR);
            $query->bindValue(':q', $q, PDO::PARAM_STR);
            $query->bindValue(':s', $s, PDO::PARAM_STR);
            $query->bindValue(':o', $o, PDO::PARAM_STR);
            $query->bindValue(':m', $m, PDO::PARAM_STR);
            $query->bindValue(':lf', $lf, PDO::PARAM_STR);
            $query->bindValue(':lc', $lc, PDO::PARAM_STR);
            $query->bindValue(':lt', $lt, PDO::PARAM_STR);
            $query->bindValue(':pr', $pr, PDO::PARAM_STR);
            $query->bindValue(':ps', $ps, PDO::PARAM_STR);
            $query->bindValue(':id', $id, PDO::PARAM_STR);

            $query->execute();

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateSubscription($subscriptionId, $updated, $expired) {

        try {

            $query = $this->db->prepare('UPDATE `subscription` SET `updated`   = :updated,
                                                                   `expired`   = :expired

                                                               WHERE `subscriptionId` = :subscriptionId
                                                               LIMIT 1');

            $query->bindValue(':subscriptionId', $subscriptionId, PDO::PARAM_INT);
            $query->bindValue(':updated', $updated, PDO::PARAM_INT);
            $query->bindValue(':expired', $expired, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateSubscriptionHash($subscriptionId, $hash, $updated) {

        try {

            $query = $this->db->prepare('UPDATE `subscription` SET `hash`    = :hash,
                                                                   `updated` = :updated

                                                             WHERE `subscriptionId` = :subscriptionId
                                                             LIMIT 1');

            $query->bindValue(':subscriptionId', $subscriptionId, PDO::PARAM_INT);
            $query->bindValue(':updated', $updated, PDO::PARAM_INT);
            $query->bindValue(':hash', $hash, PDO::PARAM_STR);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateSubscriptionIndexed($subscriptionId, $indexed) {

        try {

            $query = $this->db->prepare('UPDATE `subscription` SET `indexed` = :indexed

                                                             WHERE `subscriptionId` = :subscriptionId
                                                             LIMIT 1');

            $query->bindValue(':subscriptionId', $subscriptionId, PDO::PARAM_INT);
            $query->bindValue(':indexed', $indexed, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function subscriptionHashExists($subscriptionId, $hash) {

        try {

            $query = $this->db->prepare('SELECT NULL FROM `subscription` WHERE `subscriptionId` = ? AND `hash` = ? LIMIT 1');

            $query->execute([$subscriptionId, $hash]);

            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function subscriptionExists($profileId,
                                       $protocol,
                                       $t,
                                       $q,
                                       $s,
                                       $o,
                                       $m,
                                       $lf,
                                       $lc,
                                       $lt,
                                       $pr,
                                       $ps,
                                       $id) {

        try {

            $query = $this->db->prepare('SELECT `subscriptionId` FROM `subscription` WHERE `profileId` = :profileId
                                                                                       AND `protocol`  = :protocol
                                                                                       AND `t`         = :t
                                                                                       AND `q`         = :q
                                                                                       AND `s`         = :s
                                                                                       AND `o`         = :o
                                                                                       AND `m`         = :m
                                                                                       AND `lf`        = :lf
                                                                                       AND `lc`        = :lc
                                                                                       AND `lt`        = :lt
                                                                                       AND `pr`        = :pr
                                                                                       AND `ps`        = :ps
                                                                                       AND `id`        = :id

                                                                                     LIMIT 1');

            $query->bindValue(':profileId', $profileId, PDO::PARAM_INT);
            $query->bindValue(':protocol', $protocol, PDO::PARAM_STR);
            $query->bindValue(':t', $t, PDO::PARAM_STR);
            $query->bindValue(':q', $q, PDO::PARAM_STR);
            $query->bindValue(':s', $s, PDO::PARAM_STR);
            $query->bindValue(':o', $o, PDO::PARAM_STR);
            $query->bindValue(':m', $m, PDO::PARAM_STR);
            $query->bindValue(':lf', $lf, PDO::PARAM_STR);
            $query->bindValue(':lc', $lc, PDO::PARAM_STR);
            $query->bindValue(':lt', $lt, PDO::PARAM_STR);
            $query->bindValue(':pr', $pr, PDO::PARAM_STR);
            $query->bindValue(':ps', $ps, PDO::PARAM_STR);
            $query->bindValue(':id', $id, PDO::PARAM_STR);

            $query->execute();

            return $query->rowCount() ? $query->fetch()['subscriptionId'] : false;

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getSubscriptions($profileId) {

        try {

            $query = $this->db->prepare('SELECT * FROM `subscription` WHERE `profileId` = :profileId');

            $query->bindValue(':profileId', $profileId, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount() ? $query->fetchAll() : [];

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getTotalSubscriptions($profileId) {

        try {

            $query = $this->db->prepare('SELECT COUNT(*) AS `total` FROM `subscription` WHERE `profileId` = :profileId');

            $query->bindValue(':profileId', $profileId, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount() ? $query->fetch()['total'] : 0;

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getSubscriptionsQueue($limit = 100) {

        try {

            $query = $this->db->prepare('SELECT `s`.*, `p`.`peerId` FROM `subscription` AS `s`
                                                                    JOIN `profile` AS `p` ON (`p`.`profileId` = `s`.`profileId`)

                                                                ORDER BY `s`.`indexed` ASC

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
