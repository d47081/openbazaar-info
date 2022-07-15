<?php

/**
 * The MIT License (MIT)
 *
 * Relevant-oriented MySQL & PHP Search
 * Copyright 2020 (c) D4708/1 (https://github.com/d47081/reSearch)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

class ReSearch {

    const MATCH_ALL = 'AND';
    const MATCH_ANY = 'OR';
    const NSFW_ON   = 1;
    const NSFW_OFF  = 0;
    const NSFW_ALL  = false;

    private $_db;
    private $_index = [];

    private $_searchFields = [];
    private $_searchWords  = [];
    private $_searchIds    = [];
    private $_nsfw         = false;

    private $_searchFieldsMode;
    private $_searchWordsMode;

    private $_config = [
        'min_str_len'         => 2,
        'search_fields_mode'  => self::MATCH_ALL,
        'search_words_mode'   => self::MATCH_ANY,
        'search_nsfw'         => self::NSFW_ALL,
    ];

    public function __construct($database, $hostname, $port, $user, $password) {
        try {
            $this->_db = new PDO('mysql:dbname=' . $database . ';host=' . $hostname . ';port=' . $port . ';charset=utf8', $user, $password, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8']);
            $this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->_db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            trigger_error($e->getMessage());
        }

        $this->setFieldsMode($this->_config['search_fields_mode']);
        $this->setWordsMode($this->_config['search_words_mode']);
        $this->setNsfw($this->_config['search_nsfw']);
    }

    public function index($index, $field, $id, $string) {

        foreach (explode(' ', $this->_prepare($string)) as $word) {
            if (mb_strlen($word, 'UTF-8') > $this->_config['min_str_len']) {
                if (isset($this->_index[$index][$field][$id][$word])) {
                    $this->_index[$index][$field][$id][$word]++;
                } else {
                    $this->_index[$index][$field][$id][$word]=1;
                }
            }
        }
    }

    public function flush($index, $id) {

        $total = 0;

        if ($indexId = $this->_getIndex($index)) {
            foreach ($this->_getFields($indexId) as $field) {
                $total = $total + $this->_flush($field['fieldId'], $id);
            }
        }

        return $total;
    }

    public function save() {

        foreach($this->_index as $index => $fields) {

            if (!$indexId = $this->_getIndex($index)) {
                 $indexId = $this->_addIndex($index);
            }

            foreach($fields as $field => $words) {

                if (!$fieldId = $this->_getField($indexId, $field)) {
                     $fieldId = $this->_addField($indexId, $field);
                }

                foreach($words as $id => $words) {

                    foreach($words as $word => $total) {

                        if (!$wordId = $this->_getWord($word)) {
                             $wordId = $this->_addWord($word);
                        }

                        $this->_deleteValue($fieldId, $wordId, $id);
                        $this->_addValue($fieldId, $wordId, $total, $id);
                    }
                }
            }
        }

        $this->_index = [];
    }

    public function addField($value) {
        $this->_searchFields[] = $this->_prepare($value);
    }

    public function addWord($value) {
        $this->_searchWords[] = $this->_prepare($value);
    }

    public function addId($value) {
        $this->_searchIds[] = (int) $value;
    }

    public function setFieldsMode($value) {
        if ($value == self::MATCH_ALL) {
            $this->_searchFieldsMode = ' AND ';
        } else {
            $this->_searchFieldsMode = ' OR ';
        }
    }

    public function setWordsMode($value) {
        if ($value == self::MATCH_ALL) {
            $this->_searchWordsMode = ' AND ';
        } else {
            $this->_searchWordsMode = ' OR ';
        }
    }

    public function setNsfw($value) {
        switch ($value) {
            case self::NSFW_ON:
            case self::NSFW_OFF:
            case self::NSFW_ALL:
                $this->_nsfw = $value;
            break;
            default:
                $this->_nsfw = false;
        }
    }

    public function get($index, $start = 0, $limit = 1000) {

        $result = $this->_get($index, $start, $limit);

        $this->setFieldsMode($this->_config['search_fields_mode']);
        $this->setWordsMode($this->_config['search_words_mode']);
        $this->setNsfw($this->_config['search_nsfw']);

        $this->_searchFields = [];
        $this->_searchWords  = [];
        $this->_searchIds    = [];

        return $result;
    }

    public function total($index) {
        return $this->_total($index);
    }

    private function _prepare($string) {

        $string = preg_replace("/&[#\w\d]+;/ui", "", $string);
        $string = preg_replace("/[^-\d\w\s]/ui", " ", $string);
        $string = preg_replace("/\s+/ui", " ",$string);
        $string = mb_strtolower($string, 'UTF-8');
        $string = trim($string);

        return $string;
    }

    private function _deleteValue($fieldId, $wordId, $id) {

        try {
            $query = $this->_db->prepare('DELETE FROM `value` WHERE `fieldId` = :fieldId AND `wordId` = :wordId AND `id` = :id');

            $query->bindValue(':fieldId', $fieldId, PDO::PARAM_INT);
            $query->bindValue(':wordId', $wordId, PDO::PARAM_INT);
            $query->bindValue(':id', $id, PDO::PARAM_INT);

            $query->execute();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    private function _addValue($fieldId, $wordId, $words, $id) {

        try {
            $query = $this->_db->prepare('INSERT INTO `value` SET `fieldId` = :fieldId,
                                                                  `wordId`  = :wordId,
                                                                  `words`   = :words,
                                                                  `id`      = :id ON DUPLICATE KEY UPDATE `words` = :words');

            $query->bindValue(':fieldId', $fieldId, PDO::PARAM_INT);
            $query->bindValue(':wordId', $wordId, PDO::PARAM_INT);
            $query->bindValue(':words', $words, PDO::PARAM_INT);
            $query->bindValue(':id', $id, PDO::PARAM_INT);

            $query->execute();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    private function _addIndex($name) {

        try {
            $query = $this->_db->prepare('INSERT INTO `index` SET `name` = :name, `hash` = CRC32(:name)');

            $query->bindValue(':name', $name, PDO::PARAM_STR);
            $query->execute();

            return $this->_db->lastInsertId();

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return false;
        }
    }

    private function _addField($indexId, $name, $weight = 1) {

        try {
            $query = $this->_db->prepare('INSERT INTO `field` SET `indexId` = :indexId,
                                                                  `name`    = :name,
                                                                  `hash`    = CRC32(:name),
                                                                  `weight`  = :weight');

            $query->bindValue(':indexId', $indexId, PDO::PARAM_INT);
            $query->bindValue(':weight', $weight, PDO::PARAM_INT);
            $query->bindValue(':name', $name, PDO::PARAM_STR);
            $query->execute();

            return $this->_db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    private function _addWord($name) {

        try {
            $query = $this->_db->prepare('INSERT INTO `word` SET `name` = :name, `hash` = CRC32(:name)');

            $query->bindValue(':name', $name, PDO::PARAM_STR);
            $query->execute();

            return $this->_db->lastInsertId();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    private function _getIndex($name) {

        try {

            $query = $this->_db->prepare('SELECT `indexId` FROM `index` WHERE `hash` = CRC32(?)');
            $query->execute([$name]);

            return $query->rowCount() ? $query->fetch()['indexId'] : false;

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    private function _getField($indexId, $name) {

        try {

            $query = $this->_db->prepare('SELECT `fieldId` FROM `field` WHERE `indexId` = ? AND `hash` = CRC32(?)');
            $query->execute([$indexId, $name]);

            return $query->rowCount() ? $query->fetch()['fieldId'] : false;

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    private function _getFields($indexId) {

        try {

            $query = $this->_db->prepare('SELECT `fieldId` FROM `field` WHERE `indexId` = ?');
            $query->execute([$indexId]);

            return $query->rowCount() ? $query->fetchAll() : [];

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    private function _getWord($name) {

        try {

            $query = $this->_db->prepare('SELECT `wordId` FROM `word` WHERE `hash` = CRC32(?)');
            $query->execute([$name]);

            return $query->rowCount() ? $query->fetch()['wordId'] : false;

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    private function _get($index, $start, $limit) {

        try {

            $where  = [];
            $having = [];

            $fields = [];
            foreach ($this->_searchFields as $field) {
                $fields[] = "`f`.`hash` = CRC32('$field')";
            }

            $words = [];
            foreach ($this->_searchWords as $word) {
                $words[] = "`w`.`hash` = CRC32('$word')";
            }

            $ids = false;
            if ($this->_searchIds) {
                $ids = "`v`.`id` IN (" . implode(',', $this->_searchIds) . ")";
            }

            if ($fields) {
                $where[] = "(" . implode($this->_searchFieldsMode, $fields) . ")";
            }

            if ($words) {
                $where[] = "(" . implode($this->_searchWordsMode, $words) . ")";
            }

            if ($ids) {
                $where[] = "(" . $ids . ")";
            }

            if ($where) {
                $whereCondition = "AND " . implode(" AND ", $where);
            } else {
                $whereCondition = false;
            }

            if (false !== $this->_nsfw) {
                $having[] = "(SELECT MAX(`wNsfw`.`nsfw`) FROM  `value` AS `vNsfw`
                                                         JOIN  `word` AS `wNsfw` ON (`wNsfw`.`wordId` = `vNsfw`.`wordId`)
                                                         WHERE `vNsfw`.`id` = `v`.`id`) = '{$this->_nsfw}'";
            }

            if ($having) {
                $havingCondition = "HAVING " . implode(" AND ", $having);
            } else {
                $havingCondition = false;
            }

            $query = $this->_db->prepare("SELECT `v`.`id`, (SUM(`v`.`words`) * SUM(`f`.`weight`)) AS `relevance`

                                          FROM `value` AS `v`
                                          JOIN `word` AS `w` ON (`w`.`wordId` = `v`.`wordId`)
                                          JOIN `field` AS `f` ON (`f`.`fieldId` = `v`.`fieldId`)
                                          JOIN `index` AS `i` ON (`i`.`indexId` = `f`.`indexId`)

                                          WHERE `i`.`hash` = CRC32('{$index}')

                                          {$whereCondition}

                                          GROUP BY `v`.`id`

                                          {$havingCondition}

                                          ORDER BY `relevance` DESC

                                          LIMIT :start,:limit");

            $query->bindValue(':start', $start, PDO::PARAM_INT);
            $query->bindValue(':limit', $limit, PDO::PARAM_INT);
            $query->execute();

            return $query->rowCount() ? $query->fetchAll() : [];

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    private function _total($index) {

        try {

            $where  = [];
            $having = [];

            $fields = [];
            foreach ($this->_searchFields as $field) {
                $fields[] = "`f`.`hash` = CRC32('$field')";
            }

            $words = [];
            foreach ($this->_searchWords as $word) {
                $words[] = "`w`.`hash` = CRC32('$word')";
            }

            $ids = false;
            if ($this->_searchIds) {
                $ids = "`v`.`id` IN (" . implode(',', $this->_searchIds) . ")";
            }

            if ($fields) {
                $where[] = "(" . implode($this->_searchFieldsMode, $fields) . ")";
            }

            if ($words) {
                $where[] = "(" . implode($this->_searchWordsMode, $words) . ")";
            }

            if ($ids) {
                $where[] = "(" . $ids . ")";
            }

            if ($where) {
                $whereCondition = "AND " . implode(" AND ", $where);
            } else {
                $whereCondition = false;
            }

            if (false !== $this->_nsfw) {
                $having[] = "(SELECT MAX(`wNsfw`.`nsfw`) FROM  `value` AS `vNsfw`
                                                         JOIN  `word` AS `wNsfw` ON (`wNsfw`.`wordId` = `vNsfw`.`wordId`)
                                                         WHERE `vNsfw`.`id` = `v`.`id`) = '{$this->_nsfw}'";
            }

            if ($having) {
                $havingCondition = "HAVING " . implode(" AND ", $having);
            } else {
                $havingCondition = false;
            }

            $query = $this->_db->prepare("SELECT NULL

                                          FROM `value` AS `v`
                                          JOIN `word` AS `w` ON (`w`.`wordId` = `v`.`wordId`)
                                          JOIN `field` AS `f` ON (`f`.`fieldId` = `v`.`fieldId`)
                                          JOIN `index` AS `i` ON (`i`.`indexId` = `f`.`indexId`)

                                          WHERE `i`.`hash` = CRC32('{$index}')

                                          {$whereCondition}

                                          GROUP BY `v`.`id`

                                          {$havingCondition}

                                          ");

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }

    private function _flush($fieldId, $id) {
        try {
            $query = $this->_db->prepare('DELETE FROM `value` WHERE `fieldId` = :fieldId AND `id` = :id');

            $query->bindValue(':fieldId', $fieldId, PDO::PARAM_INT);
            $query->bindValue(':id', $id, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount();

        } catch (PDOException $e) {
            trigger_error($e->getMessage());
            return false;
        }
    }
}
