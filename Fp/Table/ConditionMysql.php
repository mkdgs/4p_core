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
                    $stopWords = array('alors', 'au', 'aucuns', 'aussi'
                        , 'autre', 'avant', 'avec', 'car', 'ce', 'cela', 'ces'
                        , 'ceux', 'ci', 'dans', 'des', 'du'
                        , 'doit', 'donc', 'début', 'elle', 'elles', 'en', 'encore', 'essai'
                        , 'est', 'et', 'eu', 'il', 'ils', 'je', 'la', 'le', 'les'
                        , 'leur', 'là', 'ma', 'mot', 'même', 'ni', 'nous', 'ou', 'où', 'par'
                        , 'parce', 'pas', 'peu', 'pour', 'pourquoi', 'quand', 'que', 'quel', 'quelle'
                        , 'quelles', 'quels', 'qui', 'sa', 'sans', 'ses', 'si', 'sien', 'son', 'sont'
                        , 'sous', 'soyez', 'sujet', 'sur', 'ta', 'tels', 'tes', 'ton', 'tous', 'tout'
                        , 'trop', 'très', 'tu', 'vont', 'votre', 'vous', 'vu', 'ça', 'de',
                        'avec', 'à', 'a', "l'", "d'", 'aux', 'se', 'un', 'une', '&', '!', '?', '.', '\'', '"', ';', ':', '+', '-'
                    );

                    if (count($sp) > 1) {
                        $nb_case = count($sp);
                        foreach ($sp as $sp_search) {
                            $sp_search = trim($sp_search, '"');
                            if ($poss === 1 &&  ( $nb_case < 3 ) ) { // si le premier match le début avec une recherche de moins de 3 mots 
                                $v = $this->quote("$sp_search%");
                                $this->searchCase[] = $tmp[] = "$column COLLATE utf8_general_ci LIKE $v ";
                            }

                            if ( ( strlen($sp_search) > 1 ) && !in_array(mb_strtolower($sp_search, 'UTF-8'), $stopWords)) {// si il est contenu et n'est pas un stop words
                                $v = $this->quote("%$sp_search%");
                                $this->searchCase[] = $tmp[] = "$column COLLATE utf8_general_ci LIKE $v ";
                            }

                            if ($poss === count($nb_case) && ( $nb_case < 3 ) ) { // si le dernier mach la fin avec une recherche de moins de 3 mots 
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
