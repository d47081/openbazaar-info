<?php

class ModelHealth extends Model {

    public function getServerUptime($division, $from, $to) {

        try {

            $query = $this->db->prepare('SELECT NULL FROM `health`
                                                    WHERE `openbazaard` = "1"
                                                      AND `online` = "1"
                                                      AND `time` >= ?
                                                      AND `time` < ?
                                                 GROUP BY FLOOR(`time` / ?)');

            $query->execute([$from, $to, $division]);
            return $query->rowCount();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }
}
