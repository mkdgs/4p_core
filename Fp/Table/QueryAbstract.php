<?php 
namespace Fp\Table;

use \Exception;


abstract class QueryAbstract {
	/**
	 * @deprecated
	 */
	public $calc_found_rows = false;
	
	/**
	 * @var ConditionAbstract
	 */
	public $condition;
	public $limit;
	public $orderBy;
	public $groupBy;	
	protected $join = true;
	protected $limitSelect = '';
	protected $limitUpdate = '';
	protected $table_alias = '';
	protected $lastInsertId = null;
	protected $rowCount     = null;
	public $tableJoin   = array();
	protected $selectFunction = '';
	protected $selectedColumn = array();
	protected $statementOptions = array();
	
	protected $cacheExistColumn = array();
	
	/**
	 * @var Table
	 */
	protected $dbTable;
	
	/**
	 * @return Table_query
	 */
	public function __construct(Table $table, $table_alias=null) {}
		
	/**
	 * @return \Fp\Table\Table
	 */
	public function getTable() {
		return $this->dbTable;		
	}
	

	/**
	 * défini l'alias de la table, utilisé dans les requetes pour identifier les colonnes 
	 * @param string $table_alias
	 * @return \Fp\Table\QueryAbstract
	 */
	public function setTableAlias($table_alias) {
		$this->table_alias = $table_alias;
		return $this;
	}
	
	
	/**
	 * escape string 
	 * @param unknown $string
	 */
	final public function quote($string) {
		return $this->dbTable->quote($string);
	}
	
	/**
	 * protege les champs selon le type défini dans le shema
	 * (effet sur update et insert)
	 * @param unknown $column
	 * @param unknown $data
	 * @param string $raw
	 * @return unknown|string
	 */
	protected function quoteData($column, $data, $raw=null) {
		$data = $this->dbTable->filter($column , $data);
		if ( $raw ) return $data;
		if ( is_null($data) ) return 'NULL';
		if	( ctype_digit("$data")  ) {
			$type = $this->dbTable->getTypeColumn($column);
			switch (mb_strtolower($type)) {
				// numeric myssql
				case 'bigint':
				case 'mediumint':
				case 'tinyint':
				case 'int':
				case 'integer':
				// numeric pgsql 
				case 'bigserial':
				case 'smallint':
				case 'int2':
				case 'int4':
				case 'int8':
				return $data;
			}
		}
		return $this->dbTable->DbLink->quote($data);
	}

	/**
	 * @return Table_query
	 */
	final public function duplicate() {
		$r = clone $this;
		return $r;
	}	
	
	/**
	 * set and normalize options
	 */
	final protected function setStatementOptions(array $options=array()) {
		if ( !empty($options) ) {
			foreach ( $options as $k => $v ) {
				if ( ctype_digit($k) ) $options[strtolower($v)] = 1;
				else $options[strtolower($k)] = $v;
			}
			$this->statementOptions = array_merge($this->statementOptions, $options);
		}		
	}
	
	/**
	 * Enter description here ...
	 * @param array $options options array('distinct','sql_calc_found_rows')
	 */
	final public function getAll(array $options=array()) {
		$ro = $this->getStatement($options);
		return $ro->fetchAll();
	}
	
	/**
	 * Enter description here ...
	 * @param array $options options array('distinct','sql_calc_found_rows')
	 */
	final public function getAllColumn(array $options=array()) {
		$r = $this->getStatement($options);
		return $r->fetchAllColumn();
	}
	/**
	 * Enter description here ...
	 * @param array $options options array('distinct','sql_calc_found_rows')
	 */
	final public function getAssoc(array $options=array()) {
		$r = $this->getStatement($options);
		return $r->fetchAssoc();
	}
	
	/**
	 * @param string string $class_name = "stdClass" nom de la classe
	 * @param array $options options array('distinct','sql_calc_found_rows')
	 */
	final public function getObject($class_name = 'stdClass', array $options=array()) {
		$r = $this->getStatement($options);
		return $r->fetchObject($class_name);
	}
	
	/**
	 * @param array $options options array('distinct','sql_calc_found_rows')
	 */
	final public function getColumn(array $options=array()) {
		$r = $this->getStatement($options);
		return $r->fetchColumn();
	}
	
	final public function showSelectQuery(array $options=array()) { 
		return $this->getStatement($options, 1);
	}
	
	final public function showUpdateQuery($set, $raw=null) { 
		return $this->update($set, $raw, 1);
	}
	
	/** 
	 * alias pour delete 
	 * -> rpc javascript
	 */
	final public function remove() {
		return $this->delete();
	}	
	
	final public function __clone() {
		$this->condition = clone $this->condition;
		return $this->condition->setQuery($this);
	}

	/**
	 * @param unknown_type $where
	 * @return ConditionAbstract
	 */
	final public function andWhere($where=NULL, $data = null) {
		return $this->condition->andWhere($where, $data);		
	}
	
	final public function getSearchCase() {
		return $this->condition->getSearchCase();
	}
	
	
	/**
	 * @param unknown_type $where
	 * @return ConditionAbstract
	 */
	final public function orWhere($where=NULL, $data = null) {
		return $this->condition->orWhere($where, $data);
	}
	
	/**
	 * @param unknown_type $where
	 * @return ConditionAbstract
	 */
	final public function search($arraySearch) {
		return $this->condition->search($arraySearch);
	}
	

	/**
	 * @param unknown_type $where
	 * @return ConditionAbstract
	 */
	final public function andSearch($arraySearch) {
		return $this->condition->andSearch($arraySearch);
	}
	
	/**
	 * @param unknown_type $where
	 * @return ConditionAbstract
	 */
	final public function orSearch($arraySearch) {
		return $this->condition->orSearch($arraySearch);
	}
	
	/**
	 * 
	 * @return ConditionAbstract
	 */
	final public function getCondition() {
		return $this->condition;
	}

	/**
	 * query sur la connection courante
	 * @param string $query
	 * @return Query
	 */
	final public function query($req) {
		$r = $this->dbTable->DbLink->query($req);
		//$this->lastInsertId = $r->lastInsertId();
		$this->rowCount     = $r->rowCount();
		return $r;
	}
	
	public function rowCount() {
		return $this->rowCount;
	}
	
	public function lastInsertId() {
		return $this->lastInsertId;
	}
	
	final public function noJoin($bool) {
		$this->join = !$bool;
		return $this;
	}
	
	final public function innerJoin(Table $table, $alias, $condition) {
		$this->tableJoin[$alias] = array( 'table' => $table, 'alias' => $alias, 'condition' => $condition, 'joinType' => 'INNER');
		return $this;
	}
	final public function outerJoin(Table $table, $alias, $condition) {
		$this->tableJoin[$alias] = array( 'table' => $table, 'alias' => $alias, 'condition' => $condition, 'joinType' => 'LEFT OUTER');
		return $this;
	}
	
	final public function leftJoin(Table $table, $alias, $condition) {
		$this->tableJoin[$alias] = array( 'table' => $table, 'alias' => $alias, 'condition' => $condition, 'joinType' => 'LEFT OUTER');
		return $this;
	}
	
	final public function rightJoin(Table $table, $alias, $condition) {
		$this->tableJoin[$alias] = array( 'table' => $table, 'alias' => $alias, 'condition' => $condition, 'joinType' => 'RIGHT OUTER');
		return $this;
	}
	
	final public function join(Table $table, $alias) {
		$this->tableJoin[$alias] = array( 'table' => $table, 'alias' => $alias, 'condition' => '', 'joinType' => '');
		return $this;
	}
        
        final public function subJoin($type, $subquery, $alias, $condition) {
		$this->tableJoin[$alias] = array( 'table' => $subquery, 'alias' => $alias, 'condition' => $condition, 'joinType' => $type);
		return $this;
	}
	
	protected function mkJoinTable() {
		$table = '';
		if ( $this->join ) {
			foreach ( $this->tableJoin as $v ) {
				$c = ( $v['condition'] ) ? "ON {$v['condition']} " : '';                                
                                if ( !is_string($v['table']) ) {
                                    $table  .= "{$v['joinType']} JOIN {$v['table']->table} as {$v['alias']} ".$c." \r\n";
                                }
                                else {
                                    $table  .= "{$v['joinType']} JOIN {$v['table']} as {$v['alias']} ".$c." \r\n";
                                }
			}
		}
		return $table;
	}
	
	/**
	 *
	 * @param string $column
	 * @param bool $append defaut true
	 * @return Table_query
	 */
	final public function selectColumn($column=null, $append=true) {
		if ( !$append) $this->selectedColumn =array();
		if ( "$column" ) {
			$column = explode(',', $column);
			foreach ( $column as $v ) {
				if ( trim($v) && $v = $this->sanitzeSelectColumn($v) )	$this->selectedColumn[] = $v;
			}
		}
		return $this;
	}
	
	/**
	 * remove column in select expression
	 * @param string $column
	 * @return Table_query
	 */
	final public function unselectColumn($column=null) {		
		if ( "$column" ) {			
			$column = explode(',', $column);
			foreach ( $column as $v ) {				
				if ( trim($v) && $v = $this->sanitzeSelectColumn($v) )	{
					if ( $keys = array_keys($this->selectedColumn, $v, true) ) {
						foreach ($keys as $rk) {						
							unset($this->selectedColumn[$rk]);
						}
					}					
				}
			}
		}
		return $this;
	}
	
	/**
	 *
	 * @param string $sql string sql function on column
	 * @param bool $append defaut true
	 * @return Table_query
	 */
	final public function selectFunction($sql, $append=true) {
		if ( !$append ) $this->selectFunction = array();
		$this->selectFunction[] = $sql;
		return $this;
	}
	
	final public function typeColumn($column) {
		$col = $this->sanitzeSelectColumn($column);
		if ( $col['table_alias'] ) {
			if ( $col['table_alias'] == $this->table_alias  )  {
				return $this->dbTable->getTypeColumn($col['name']);
			}
			if ( array_key_exists($col['table_alias'], $this->tableJoin) ) {
				return $this->tableJoin[$col['table_alias']]['table']->getTypeColumn($col['name']);
			}
		}
		else if ( $type = $this->dbTable->getTypeColumn($col['name']) )  {
			return $type;
		}
	}
		

	
	/**
	 * retourne le nom complet de la colonne
	 *  
	 * @param unknown $columnName
	 * @param unknown $tableName table alias est utilisé par défaut, sinon le nom de la table sera $tableName
	 */
	final public function fullyQualifiedColumnName($columnName, $tableName=null) {
		if( !$tableName ) {
			if ( !($tableName=$this->table_alias ) ) $tableName=$this->getTable()->getTableName();
		}
		return $this->fqcn($tableName, $columnName);
	}
	
	/**
	 * vérifie que la colonne existe et retourne son nom complet (table.colone) 
	 * @param unknown $column
	 * @param string $fqcn si false ne retoune que le nom de la colonne (sans alias ni table)
	 * @return string
	 */
	final public function existColumn($column, $fqcn=true) {
		$k=$column.' '.$fqcn;		
		if ( !isset($this->cacheExistColumn[$k]) ) {			
				$this->cacheExistColumn[$k] = $this->internalExistColumn($column, $fqcn);				
		}	
		return $this->cacheExistColumn[$k];		
	}
	
	private function internalExistColumn($column, $fqcn=true) {		
		
		$col = $this->sanitzeSelectColumn($column);		

		
		if ( $col['table_alias'] ) {			
			if ( $col['table_alias'] == $this->table_alias && $this->dbTable->existColumn($col['name']) )  {				
				return $this->fqcn($col['table_alias'], $col['name'], $fqcn);
			}
			// si on a des jointures
			if ( $this->join && array_key_exists($col['table_alias'], $this->tableJoin) ) {				
				if (  $type = $this->tableJoin[$col['table_alias']]['table']->existColumn($col['name']) ) {					
					return $this->fqcn($col['table_alias'], $col['name'], $fqcn);
				}
			}			
		}
		else if ( $this->dbTable->existColumn($col['name']) )  {
			$alias = ( $this->table_alias ) ? $this->table_alias : $this->dbTable->table;
			return $this->fqcn($alias,$col['name'], $fqcn);
		}
		
		// si l'alias correspond au nom réel de la table
		if ( $col['table_alias'] == $this->dbTable->table && $this->dbTable->existColumn($col['name']) )  {
		    return $this->fqcn($col['table_alias'], $col['name'], $fqcn);
		}
	
	
		else  {
			// déclaré par selectFunction as column || pour order By + group
			foreach ( $this->selectFunction as $v ) {
				if ( preg_match('#as[\s]+'.$col['name'].'[\s,]{0,1}#i', $v)) {
					return $col['name'];
				}
			}
			// peut poser problème si le nom de colonne est ambigu
			// la première correspondance est retournée
			// si on a des jointures
			if ( $this->join ) {
				foreach ( $this->tableJoin as $t ) {
					if ( $t['table']->existColumn($col['name']) ) {
						return $this->fqcn($t['alias'], $col['name'], $fqcn);
					}
				}
			}
		}	
	}	

}