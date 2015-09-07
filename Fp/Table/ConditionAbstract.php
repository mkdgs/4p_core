<?php

namespace Fp\Table;

use \Exception;

abstract class ConditionAbstract {

    public $child = array();
    public $condition = null;
    public $type = null;
    protected $build = null;
    protected $searchCase = array();
    protected $operator = array('=', '>=', '<=', '!=', 'LIKE', 'BETWEEN', 'NOT LIKE', 'NOT BETWEEN', 'IS', 'IS NOT');

    /**
     * @var Table_query_abstract
     */
    protected $query;

    final public function __construct(QueryAbstract $query, $type = null) {
        $this->type = $type;
        $this->query = $query;
    }

    /**
     * applique la condition à une requete
     * 
     * @param QueryAbstract $query
     * @return \Fp\Table\ConditionAbstract
     */
    final public function setQuery(QueryAbstract $query) {
        $this->query = $query;
        foreach ($this->child as $v) {
            $v->setQuery($this->query);
        }
        return $this;
    }

    /**
     * retourne un array contenant les conditions orSearch et andWhere
     * utilisé pour calculer les scores des résultat de recherches
     * 
     * @return multitype:
     */
    final public function getSearchCase() {
        $sc = $this->searchCase;
        foreach ($this->child as $c) {
            $sc = array_merge($sc, $c->getSearchCase());
        }
        return $sc;
    }

    final protected function makeOperator($column, $operator, $values, $type, $quote) {
        // 'column' => [ 'value 1', ... ] alias for 'column' => [ '=' => 'value 1', ... ] 
        // 'column' => [ '>=' =>  'value 1' ]
        // 'column' => [ '>=' => [ 'value 1', ... ]
        // 'column' => [ 'BETWEEN' =>  ['value 1', 'value 2'] ]
        $c = array();
        $operator = strtoupper($operator);
        if (!in_array($operator, $this->operator)) {
            if (ctype_digit($operator))
                $operator = '=';
            else
                throw new Exception('unknow operator:' . $operator);
        }
        
        switch ($operator) {
            case 'BETWEEN':
            case 'NOT BETWEEN':
                $values = (array) $values;
                if (!is_array($values[0]))
                    $values = array($values);
                foreach ($values as $val) {
                    if (count($val) != 2)
                        throw new Exception('bad number arguments (2) for operator:' . $operator . '. number of arguments found:' . count($val));
                    $c[] = $column . ' ' . $operator . ' ' . $this->quote($val[0], $quote) . ' AND ' . $this->quote($val[1], $quote);
                }
                break;

            case 'IS':
            case 'IS NOT':
                $ar = ['NULL', 'TRUE', 'FALSE', 'UNKNOW'];              
                $values = strtoupper($values);
                if (!in_array($values, $ar))
                    throw new Exception('bad number arguments for' . $operator . ' :' . $values);
                $c[] = $column . ' ' . $operator . ' ' . $values;
                break;

            default:
                $values = (array) $values;
                foreach ($values as $val) {
                    $c[] = $column . ' ' . $operator . ' ' . $this->quote($val, $quote);
                }
                break;
        }
        
        if ( !empty($c) ) return ' ( ' . implode(" $type ", $c) . ' ) ';
    }

    final protected function _where_($where = null, $type = null, $data = null) {
        $w = null;
        $quote = true;
        if ( is_array($where) ) {
           
            if (!empty($data)) {
                if (array_key_exists('quote', $data)) {
                    $quote = (bool) $data['quote'];
                }
            }

            if ( empty($where) ) return 'true';
            else {
                $c = array();
                foreach ($where as $k => $v) {                    
                    if ( $col = $this->existColumn($k)) {     
                        if ( is_scalar($v) || $v === null ) {                            
                            $v = $this->quote($v);
                            $c[] = "$col=$v";
                        } 
                        else if (is_array($v)) {                            
                            foreach ($v as $operator => $values) {
                                $mo = $this->makeOperator($col, $operator, $values, $type, $quote);
                                if ( $mo ) $c[] = $mo;  
                            }
                        }
                    }                    
                    else  $c[] = $this->_where_($v, $type);
                }

                if ( !empty($c) ) {
                    return  ' ( ' . implode(" $type ", $c) . ' ) ';
                }
            }
        }

        // like prepared :statement
        // @todo implement prepared statement
        // array('column LIKE :data', array('data'=> 'val'))
        else if (is_scalar($where)) {             
            if (is_array($data)) {
                foreach ($data as $k => $v) {
                    $where = preg_replace("#:$k\b#", $this->quote($v), $where);
                }
                $w = $where;
            } else if ($data) {
                $w = str_replace("?", $this->quote($data), $where);
            } else if ($where === true)
                $w = 'true';
            else if (ctype_digit("$where")) {
                if ($primary = $this->query->getTable()->getprimary()) {
                    // if multiple, first is used
                    $p = $this->query->fullyQualifiedColumnName(current($primary));
                    $w = $p . "='$where' ";
                } else
                    trigger_error('primary column not found', E_USER_NOTICE);
            } else
                $w = "$where";
        }

        if ( empty($w))  $w = '0';
        
        return "$w";
    }

    final public function quote($string, $quote = true) {
        if (!$quote)
            return $string;
        return $this->query->quote($string);
    }

    final public function typeColumn($column) {
        return $this->query->typeColumn($column);
    }

    final public function existColumn($column) {
        return $this->query->existColumn($column);
    }

    final public function hasChild() {
        return !empty($this->child);
    }

    /**
     * Construit la clause WHERE SQL
     * @param string $parent
     * @return string
     */
    final public function build($parent = false) {
        $r = '';
        $i = 0;
        foreach ($this->child as $c) {
            $t = $c->build($c);           
            if ( !empty($t) || $t === '0' ) {
                if ($i != 0) { // ignore le type de la première condition
                    $r .= ' ' . $c->type;
                } else if ($parent && $parent->condition) { // si le parent est un nouveau groupe on ajoute le type de la condition
                    $r .= ' ' . $c->type;
                }
                $r .= " ( $t ) ";
                $i++; // on ne compte que les condition non null
            }           
        }
        $this->build = $r;
        return $this->condition . $this->build;
    }

    final public function addCondition($type, $where, $data) {
        $r = $this->child[] = new ConditionMysql($this->query, $type);
        if ($where !== null) {
            $r->condition = $this->_where_($where, $type, $data);
        }
        return $r;
    }

    final public function andWhere($where = NULL, $data = null) {
        return $this->addCondition('AND', $where, $data);
    }

    final public function orWhere($where = NULL, $data = null) {
        return $this->addCondition('OR', $where, $data);
    }

    final public function search(array $arraySearch = array()) {
        return $this->orSearch($arraySearch);
    }

    abstract public function orSearch(array $arraySearch = array());

    abstract public function andSearch(array $arraySearch = array());
}
