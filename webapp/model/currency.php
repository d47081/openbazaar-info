<?php

class ModelCurrency extends Model {

    public function getLastRates() {

        try {

            $query = $this->db->prepare('SELECT `c`.`type`,
                                                `c`.`code`,
                                                `c`.`symbol`,
                                                (SELECT `cr`.`last` FROM `currencyRate` AS `cr`
                                                                   WHERE `cr`.`currencyId` = `c`.`currencyId`
                                                                   ORDER BY `cr`.`updated` DESC
                                                                   LIMIT 1) AS `rate`

                                                 FROM `currency` AS `c`');

            $query->execute();

            return $query->rowCount() ? $query->fetchAll() : [];

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    public function getMainRates() {

        try {

            $query = $this->db->prepare('SELECT `c`.`type`,
                                                `c`.`code`,
                                                `c`.`symbol`,
                                                (SELECT `cr`.`last` FROM `currencyRate` AS `cr`
                                                                   WHERE `cr`.`currencyId` = `c`.`currencyId`
                                                                   ORDER BY `cr`.`updated` DESC
                                                                   LIMIT 1) AS `rate`

                                                 FROM `currency` AS `c`

                                                 WHERE `c`.`main` = "1"');

            $query->execute();

            return $query->rowCount() ? $query->fetchAll() : [];

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }
}
