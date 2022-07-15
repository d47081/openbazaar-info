<?php

class ModelCurrency extends Model {

    public function currencyExists($code) {

        try {

            $query = $this->db->prepare('SELECT `currencyId` FROM  `currency` WHERE `code` = ? LIMIT 1');

            $query->execute([$code]);

            return $query->rowCount() ? $query->fetch()['currencyId'] : false;

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addCurrency($code, $type = 'crypto') {

        try {

            $query = $this->db->prepare('INSERT INTO `currency` SET `code` = ?, `type` = ?');

            $query->execute([$code, $type]);

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addCurrencyRate($currencyId, $ask, $bid, $last, $updated) {

        try {

            $query = $this->db->prepare('INSERT INTO `currencyRate` SET `currencyId` = ?,
                                                                        `ask`        = ?,
                                                                        `bid`        = ?,
                                                                        `last`       = ?,
                                                                        `updated`    = ?');

            $query->execute([$currencyId, $ask, $bid, $last, $updated]);

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateCurrencySymbol($code, $symbol) {

        try {

            $query = $this->db->prepare('UPDATE `currency` SET `symbol` = ? WHERE `code` = ? LIMIT 1');
            $query->execute([$symbol, $code]);

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }
}
