<?php 
namespace Fp\Table;

abstract class ConditionAbstract {
	public $child   	 	= array();
	public $condition		= null;
	public $type 	 		= null;
	protected $build    	= null;
	protected $searchCase	= array();
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
		foreach ( $this->child as $v ) {
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
		foreach ( $this->child as $c) {
			$sc = array_merge($sc, $c->getSearchCase());
		}
		return $sc;
	}

	final protected  function _where_($where=null, $type=null, $data=null) {
		$w = null;
		if ( is_array($where) ) {
			if ( empty($where) ) $w = 'true';
			else {
				$c = array();
				foreach ( $where as $k => $v  ) {	
					//if 	( is_array($v) ) $this->addCondition($type, $v); // not supported
					if ( $col = $this->existColumn($k) ) {
					 	$v   = $this->quote($v);
						$c[] = 	"$col=$v";
					}
					else $c[] = $this->_where_($v);					
				}
				if ( !empty($c) ) $w = ' ( '.implode(" $type ", $c).' ) ';
			}
		}
		
		// like prepared :statement
		// @todo implement prepared statement
		// array('column LIKE :data', array('data'=> 'val'))
		else if ( is_scalar($where) ) {
			if ( is_array($data) ) {
				foreach ($data as $k => $v ) {
					$where = preg_replace("#:$k\b#", $this->quote($v), $where);
				}
				$w = $where;
			}
			else if ( $data ) {
				$w = str_replace("?", $this->quote($data), $where);
			}
			else if ( $where===true ) $w = 'true';
			else if ( ctype_digit("$where") ) {
				if ( $primary = $this->query->getTable()->getprimary() ) {
					// if multiple, first is used
					$p = $this->query->fullyQualifiedColumnName(current($primary));
					$w = $p."='$where' ";
				}
				else trigger_error('primary column not found', E_USER_NOTICE);
			}
			else $w = "$where";
		}
				
		if ( empty($w) ) $w = '0';		
		return "$w";
	}

	final  public function quote($string) {
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
		foreach ( $this->child as $c) {
			if ( $t = $c->build($c) ) {
				if ( $i !=0) { // ignore le type de la première condition
					$r .= $c->type;
				}
				else if( $parent && $parent->condition ) {  // si le parent est un nouveau groupe on ajoute le type de la condition
					$r .= $c->type;
				}
				$r .= " ( $t ) ";
				$i++; // on ne compte que les condition non null
			}
		}
		$this->build = $r;
		return $this->condition.$this->build;
	}

	final public function addCondition($type, $where, $data) {
		$r =  $this->child[] = new ConditionMysql($this->query, $type);
		if ( $where != null ) {
			$r->condition = $this->_where_($where, $type, $data);
		}
		return $r;
	}

	final public function andWhere($where=NULL, $data=null) {
		if ( is_array($where) && func_num_args() > 1 ) {
			$where = func_get_args();
		}
		return $this->addCondition('AND', $where, $data);
	}

	final public function orWhere($where=NULL, $data=null) {
		if ( is_array($where) && func_num_args() > 1 ) {
			$where = func_get_args();
		}
		return $this->addCondition('OR', $where, $data);
	}

	final public  function search(array $arraySearch = array()) {
		return $this->orSearch($arraySearch);
	}

	abstract public function orSearch(array $arraySearch = array());
	abstract public function andSearch(array $arraySearch = array());
}