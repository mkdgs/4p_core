<?php

namespace Fp\Table;

use \Exception;

class ConditionMysql extends ConditionAbstract {

    /**
     * 
     * @param unknown $search
     * @return multitype:
     */
    protected function searchParser($search) {
        $rs = array();
        $rs = preg_split('#\s*"([^"]*)"\s*|\s+#', $search, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        return $rs;
    }

    protected function makeSearchColumn($column, $search, $type = 'OR') {
        $column = $this->existColumn($column);
        $r = null;
        if ($column) {
            if (is_scalar($search)) {
                $t = $this->typeColumn($column);
                if ($t == 'varchar') {
                    $sp = $this->searchParser($search);
                    $tmp = array();
                    $search_str = trim($search, '"');
                    $this->searchCase[] = $tmp[] = "$column COLLATE utf8_general_ci LIKE " . $this->quote("$search_str");
                    $this->searchCase[] = $tmp[] = "$column COLLATE utf8_general_ci LIKE " . $this->quote("$search_str%");
                    $this->searchCase[] = $tmp[] = "$column COLLATE utf8_general_ci LIKE " . $this->quote("%$search_str%");
                    $this->searchCase[] = $tmp[] = "$column COLLATE utf8_general_ci LIKE " . $this->quote("%$search_str");

                    $poss = 1;
                    $stopWords = ['le', 'la', 'les', 'l', "d'", 'aux', 'ce', 'se', 'un', 'une', 'et', 'en', '&'];

                    if (count($sp) > 1) {
                        foreach ($sp as $sp_search) {
                            $sp_search = trim($sp_search, '"');
                            if ($poss === 1) { // si le premier match le début
                                $v = $this->quote("$sp_search%");
                                $this->searchCase[] = $tmp[] = "$column COLLATE utf8_general_ci LIKE $v ";
                            }

                            if (( strlen($v) > 1 ) && !in_array($v, $stopWords)) {// si il est contenu et n'est pas un stop words
                                $v = $this->quote("%$sp_search%");
                                $this->searchCase[] = $tmp[] = "$column COLLATE utf8_general_ci LIKE $v ";
                            }
                            
                            if ( $poss === count($sp)  ) { // si le dernier mache la fin 
                                $v = $this->quote("$sp_search%");
                                $this->searchCase[] = $tmp[] = "$column COLLATE utf8_general_ci LIKE $v ";
                            }
                            $poss++;
                        }
                    }
                    $r = implode(" $type ", $tmp);
                } else if ($t == 'date' || $t == 'datetime') {
                    if ($sdate = \Fp\Core\Filter::mysqlDateTime($search)) {
                        $v = $this->quote("$sdate");
                        $this->searchCase[] = $r = " $column=$v";
                    } else { // fix searching date (ex year 2014-)                                        
                        if ($search) {
                            $v = $this->quote("%$search%");
                            $this->searchCase[] = $r = "$column LIKE $v ";
                        }
                    }
                } else {
                    if ($search) {
                        $v = $this->quote("$search");
                        $this->searchCase[] = $r = " $column=$v ";
                    }
                }
            } elseif (is_array($search)) {
                foreach ($search as $sub) {
                    if ($type == 'OR')
                        $this->orSearch(array($column => $sub));
                    else
                        $this->andSearch(array($column => $sub));
                }
            }
        }
        return $r;
    }

    public function orSearch(array $arraySearch = array()) {
        $r = array();
        foreach ($arraySearch as $k => $v) {
            $s = $this->makeSearchColumn($k, $v, 'OR');
            if (!empty($s))
                $r[] = $s;
        }
        if (empty($r))
            return $this;
        return $this->orWhere($r);
    }

    public function andSearch(array $arraySearch = array()) {
        $r = array();
        foreach ($arraySearch as $k => $v) {
            $s = $this->makeSearchColumn($k, $v, 'AND');
            if (!empty($s))
                $r[] = $s;
        }
        if (empty($r))
            return $this;
        return $this->andWhere($r);
    }

}
