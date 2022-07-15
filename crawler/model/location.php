<?php

class ModelLocation extends Model {

    public function getCountries() {

        try {

            $query = $this->db->prepare('SELECT * FROM  `country`');

            $query->execute();

            return $query->rowCount() ? $query->fetchAll() : [];

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function countryCodeExists($code) {

        try {

            $query = $this->db->prepare('SELECT `countryId` FROM  `country` WHERE `code` = ? LIMIT 1');

            $query->execute([$code]);

            return $query->rowCount() ? $query->fetch()['countryId'] : false;

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function countryCodeIso2Exists($codeIso2) {

        try {

            $query = $this->db->prepare('SELECT `countryId` FROM  `country` WHERE `codeIso2` = ? LIMIT 1');

            $query->execute([$codeIso2]);

            return $query->rowCount() ? $query->fetch()['countryId'] : false;

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addCountry($name, $code, $codeIso2) {

        try {

            $query = $this->db->prepare('INSERT INTO `country` SET `name` = ?, `code` = ?, `codeIso2` = ?');

            $query->execute([$name, $code, $codeIso2]);

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function regionExists($countryId, $name) {

        try {

            $query = $this->db->prepare('SELECT `regionId` FROM  `region` WHERE `countryId` = ? AND `name` = ? LIMIT 1');

            $query->execute([$countryId, $name]);

            return $query->rowCount() ? $query->fetch()['regionId'] : false;

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addRegion($countryId, $name) {

        try {

            $query = $this->db->prepare('INSERT INTO `region` SET `countryId` = ?, `name` = ?');

            $query->execute([$countryId, $name]);

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function cityExists($regionId, $name) {

        try {

            $query = $this->db->prepare('SELECT `cityId` FROM  `city` WHERE `regionId` = ? AND `name` = ? LIMIT 1');

            $query->execute([$regionId, $name]);

            return $query->rowCount() ? $query->fetch()['cityId'] : false;

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function addCity($regionId, $name) {

        try {

            $query = $this->db->prepare('INSERT INTO `city` SET `regionId` = ?, `name` = ?');

            $query->execute([$regionId, $name]);

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error("SELECT `cityId` FROM  `city` WHERE `regionId` = $regionId AND `name` = $name LIMIT 1" . $e->getMessage());
            return false;
        }
    }

    public function updateCountryCoordinates($countryId, $latitude, $longitude) {

        try {

            $query = $this->db->prepare('UPDATE `country` SET `latitude` = ?, `longitude` = ? WHERE `countryId` = ? LIMIT 1');

            $query->execute([$latitude, $longitude, $countryId]);

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateRegionCoordinates($regionId, $latitude, $longitude) {

        try {

            $query = $this->db->prepare('UPDATE `region` SET `latitude` = ?, `longitude` = ? WHERE `regionId` = ? LIMIT 1');

            $query->execute([$latitude, $longitude, $regionId]);

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    public function updateCityCoordinates($cityId, $latitude, $longitude) {

        try {

            $query = $this->db->prepare('UPDATE `city` SET `latitude` = ?, `longitude` = ? WHERE `cityId` = ? LIMIT 1');

            $query->execute([$latitude, $longitude, $cityId]);

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }
}
