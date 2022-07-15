<?php

class ModelHealth extends Model {

    public function add($time, $openbazaard, $online) {

        try {

            $query = $this->db->prepare('INSERT INTO `health` SET `time`        = :time,
                                                                  `openbazaard` = :openbazaard,
                                                                  `online`      = :online');

            $query->bindValue(':time', $time, PDO::PARAM_INT);
            $query->bindValue(':openbazaard', (int) $openbazaard, PDO::PARAM_STR);
            $query->bindValue(':online', (int) $online, PDO::PARAM_STR);

            $query->execute();

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }
}
