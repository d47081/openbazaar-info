<?php

class ModelSphinx {

    public function __construct($host, $port) {
        try {

            $this->_db = new PDO('mysql:host=' . $host . ';port=' . $port . ';charset=utf8', false, false, [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8']);
            $this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->_db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);


        } catch(PDOException $e) {
            trigger_error($e->getMessage());
        }
    }

    public function getTotalFound() {

        $query = $this->_db->query('SHOW META');

        if ($query->rowCount()) {
            foreach ($query->fetchAll() as $value) {
                if ($value['Variable_name'] == 'total_found') {
                    return (int) $value['Value'];
                }
            }
        }

        return 0;
    }

    public function search($index, $request, $start, $limit, array $filters = [], $sort = false, $order = false) {

        try {

            $where = [];

            // Set ordering
            if (!in_array($order, ['asc','desc'])) {
                $order = false;
            }

            // Set index
            switch ($index) {
                case 'listing':

                    $index = 'listing';
                    $removed = 'AND `removed` = 0';

                    // Sort mode
                    if (!in_array($sort, ['online','added','price'])) {
                        $sort = false;
                    } else if ($sort == 'price') {
                        $sort = 'pricebtc';
                    }

                    // Filter mode
                    foreach ($filters as $field => $value) {

                        switch ($field) {
                            case 'id':

                                $field = 'peerid';

                                $values = [];
                                foreach ($value as $data) {
                                    $values[] = $this->_prepare($data);
                                }

                                if ($values) {
                                    $where[] = '`' . $field . '` IN (' . implode(',', $values) . ')';
                                }

                            break;
                            case 'm':

                                $field = 'moderators';

                                if ($value == 'true') {
                                    $where[] = '`' . $field . '` <> 0';
                                } else {
                                    $where[] = '`' . $field . '` = 0';
                                }

                            break;
                            case 'lf':

                                $field = 'lf';

                                $values = [];
                                foreach ($value as $data) {
                                    if (2 == strlen($data)) {
                                        $values[] = $this->_prepare($data);
                                    }
                                }

                                if ($values) {
                                    $where[] = '`' . $field . '` IN (' . implode(',', $values) . ')';
                                }

                            break;
                            case 'ps':

                                $field = 'ps';

                                $values = [];
                                foreach ($value as $data) {
                                    if (in_array($data, [ 'online',
                                                          'active',
                                                          'passive'])) {

                                        $values[] = $this->_prepare($data);
                                    }
                                }

                                if ($values) {
                                    $where[] = '`' . $field . '` IN (' . implode(',', $values) . ')';
                                }

                            break;
                            case 'pr':

                                $field = 'pr';

                                $values = [];
                                foreach ($value as $data) {
                                    if (in_array($data, [1,2,3,4,5])) {
                                        $values[] = (int) $data;
                                    }
                                }

                                if ($values) {
                                    $where[] = '`' . $field . '` IN (' . implode(',', $values) . ')';
                                }

                            break;
                            case 'lt':

                                $field = 'lt';

                                $values = [];
                                foreach ($value as $data) {
                                    if (in_array($data, [ 'service',
                                                          'physical_good',
                                                          'digital_good',
                                                          'cryptocurrency'])) {

                                        $values[] = $this->_prepare($data);
                                    }
                                }

                                if ($values) {
                                    $where[] = '`' . $field . '` IN (' . implode(',', $values) . ')';
                                }

                            break;
                            case 'lc':

                                $field = 'lc';

                                $values = [];
                                foreach ($value as $data) {
                                    if (in_array($data, [ 'new',
                                                          'used',
                                                          'used_good',
                                                          'used_excelent',
                                                          'used_poor',
                                                          'refurbished'])) {

                                        $values[] = $this->_prepare($data);
                                    }
                                }

                                if ($values) {
                                    $where[] = '`' . $field . '` IN (' . implode(',', $values) . ')';
                                }

                            break;
                            case 'tor':

                                $field = 'tor';

                                if ($value == 'true') {
                                    $where[] = '`' . $field . '` <> 0';
                                } else {
                                    $where[] = '`' . $field . '` = 0';
                                }

                            break;
                            default:
                                trigger_error(_('Unknown field ' . $field));
                        }
                    }

                break;
                case 'profile':

                    $index   = 'profile';
                    $removed = false;

                    // Sort mode
                    if (!in_array($sort, ['online','added'])) {
                        $sort = false;
                    }

                    // Filter mode
                    foreach ($filters as $field => $value) {

                        switch ($field) {
                            case 'm':

                                $field = 'moderator';

                                if ($value == 'true') {
                                    $where[] = '`' . $field . '` = 1';
                                } else {
                                    $where[] = '`' . $field . '` = 0';
                                }

                            break;
                            case 'lf':

                                $field = 'lf';

                                $values = [];
                                foreach ($value as $data) {
                                    if (2 == strlen($data)) {
                                        $values[] = $this->_prepare($data);
                                    }
                                }

                                if ($values) {
                                    $where[] = '`' . $field . '` IN (' . implode(',', $values) . ')';
                                }

                            break;
                            case 'ps':

                                $field = 'ps';

                                $values = [];
                                foreach ($value as $data) {
                                    if (in_array($data, [ 'online',
                                                          'active',
                                                          'passive'])) {

                                        $values[] = $this->_prepare($data);
                                    }
                                }

                                if ($values) {
                                    $where[] = '`' . $field . '` IN (' . implode(',', $values) . ')';
                                }

                            break;
                            case 'pr':

                                $field = 'pr';

                                $values = [];
                                foreach ($value as $data) {
                                    if (in_array($data, [1,2,3,4,5])) {
                                        $values[] = (int) $data;
                                    }
                                }

                                if ($values) {
                                    $where[] = '`' . $field . '` IN (' . implode(',', $values) . ')';
                                }

                            break;
                            case 'tor':

                                $field = 'tor';

                                if ($value == 'true') {
                                    $where[] = '`' . $field . '` <> 0';
                                } else {
                                    $where[] = '`' . $field . '` = 0';
                                }

                            break;
                            default:
                                trigger_error(_('Unknown field ' . $field));
                        }
                    }

                break;
                default:
                    trigger_error(_('Index not found!'));
                    return [];
            }

            if ($where) {
                $where = ' AND ' . implode(' AND ', $where);
            } else {
                $where = false;
            }

            if ($sort && $order) {
                $sortOrder = $sort . ' ' . $order . ',';
            } else {
                $sortOrder = false;
            }

            $query = $this->_db->prepare("SELECT * FROM `{$index}`
                                                   WHERE MATCH(:request) {$removed} {$where}
                                                   ORDER BY {$sortOrder} WEIGHT() DESC
                                                   LIMIT :start, :limit");

            $query->bindValue(':request', $request, PDO::PARAM_STR);
            $query->bindValue(':start', $start, PDO::PARAM_INT);
            $query->bindValue(':limit', $limit, PDO::PARAM_INT);

            $query->execute();

            return $query->rowCount() ? $query->fetchAll() : [];

        } catch (PDOException $e) {

            trigger_error($e->getMessage());
            return [];

        }
    }

    private function _prepareString($value) {

        $string = preg_replace("/[^-_\d\w]/ui", " ", $value);
        $string = preg_replace("/\s+/ui", " ",$value);
        $string = trim($value);

        return "'" . $value . "'";
    }

    private function _prepareInt($value) {
        return (int) $value;
    }

    private function _prepareFloat($value) {
        return (float) $value;
    }

    private function _prepare($value) {
        switch (true) {
            case is_string($value):
                return $this->_prepareString($value);
            break;
            case is_int($value):
                return $this->_prepareInt($value);
            break;
            case is_float($value):
                return $this->_prepareFloat($value);
            break;
            default:
                trigger_error(_('Unknown value data type!'));
                return false;
        }
    }
}
