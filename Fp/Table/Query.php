<?php 
namespace Fp\Table;

use \Exception;
use Fp\Db\PDOStatement;

require_once __DIR__.'/QueryInterface.php';
require_once __DIR__.'/QueryAbstract.php';
require_once __DIR__.'/ConditionAbstract.php';
//require_once 'core_Query_interface.php';
//require_once 'core_QueryAbstract.php';
class Query implements QueryInterface {

	/**
	 * @return QueryAbstract
	 */
	public function __construct(Table $table, $table_alias=null) {
		$this->table_alias = $table_alias;
		$this->dbTable = $table; 
		if ( $table->DbLink->type == 'mysql') {
			require_once __DIR__.'/QueryMysql.php';
                        require_once __DIR__.'/ConditionMysql.php';
			$this->instance = new QueryMysql($table, $table_alias);
		}
		else if ( $table->DbLink->type == 'pgsql') {
			require_once __DIR__.'/QueryPgsql.php';
                        require_once __DIR__.'/ConditionPgsql.php';
			$this->instance = new QueryPgsql($table, $table_alias);
		}		
	}
	
	public function setTableAlias($name) {
		return $this->instance->setTableAlias($name);		
	}
	
	public function getTable() {
		return $this->dbTable;
	}
	
	public function __get($name) {		
		if ( isset($this->instance->$name) ) { return $this->instance->$name; }
	}
	
	public function __call($name, $params) {
		return call_user_func_array(array($this->instance, $name), $params);
	}	

	public function typeColumn($column) {
		return $this->instance->typeColumn($column);
	}

	public function existColumn($column, $fqcn=true) {
		return $this->instance->existColumn($column, $fqcn);		
	}
	
	/**
	 * @return QueryAbstract
	 */
	public function innerJoin(Table $table, $alias, $condition) {
		return $this->instance->innerJoin($table, $alias, $condition);
	}
	/**
	 * @return QueryAbstract
	 */
	public function outerJoin(Table $table, $alias, $condition) {
		return $this->instance->outerJoin($table, $alias, $condition);
	}
	/**
	 * @return QueryAbstract
	 */
	public function leftJoin(Table $table, $alias, $condition) {
		return $this->instance->outerJoin($table, $alias, $condition);
	}
	/**
	 * @return QueryAbstract
	 */
	public function rightJoin(Table $table, $alias, $condition) {
		return $this->instance->rightJoin($table, $alias, $condition);
	}
	
	/**
	 * @return QueryAbstract
	 */
	public function join(Table $table, $alias) {
		return $this->instance->join($table, $alias);
	}
        
        /**
	 * @return QueryAbstract
	 */
	public function subJoin($type, Table $table, $alias, $condition) {
		return $this->instance->subJoin($type, $table, $alias, $condition);
	}
        
	/**
	 * @return QueryAbstract
	 */
	public function noJoin($bool) {
		return $this->instance->noJoin($bool);
	}

	final public function getSearchCase() {
		return $this->instance->getSearchCase();		
	}
	
	/**
	 * @param unknown_type $where
	 * @return ConditionAbstract
	 */
	public function andWhere($where=NULL) {
		return $this->instance->andWhere($where);		
	}
	
	/**
	 * @param array $arraySearch
	 * @return ConditionAbstract
	 */
	public function search($arraySearch) {
		return $this->instance->search($arraySearch);
	}
	
	/**
	 * @param array $arraySearch
	 * @return ConditionAbstract
	 */
	public function orSearch($arraySearch) {
		return $this->instance->orSearch($arraySearch);
	}
	
	/**
	  * @param array $arraySearch
	 * @return ConditionAbstract
	 */
	public function andSearch($arraySearch) {
		return $this->instance->andSearch($arraySearch);
	}
	
	/**
	 * @param unknown_type $where
	 * @return ConditionAbstract
	 */
	public function orWhere($where=NULL) {
		return $this->instance->orWhere($where);
	}

	/**
	 * query sur la connection courante
	 * @param string $query
	 * @return Db_Extend_PDOStatement
	 */
	public function query($req) {
		return $this->instance->query($req);
	}

	public function rowCount() {
		return $this->instance->rowCount();
	}
	
	public function lastInsertId() {
		return $this->instance->lastInsertId();
	}

	/**
	 * @return Query 
	 */
	public function selectColumn($column=null, $append=true) {
		return $this->instance->selectColumn($column, $append);
	}
	
	public function unselectColumn($column=null) {
		return $this->instance->unselectColumn($column);
	}
	
	public function buildColumn($sanCol) {
		return $this->instance->buildColumn($sanCol);
	}
	
	public function selectFunction($sql) {
		return $this->instance->selectFunction($sql);
	}
	
	/**
	 * @return PDOStatement
	 */
	public function getStatement(array $options=array(),$simule=false) {
		return $this->instance->getStatement($options, $simule);
	}
	
	public function update(array $set, $raw=null, $simule=false) {
		return $this->instance->update($set, $raw, $simule);
	}

	/**
	 * @return int
	 */
	public function foundRows() {
		return $this->instance->foundRows();
	}
	
	public function delete() {
		return $this->instance->delete();
	}

	/**
	 * @param array $data association colonnes/valeurs
	 * @param string $raw liste de colonnes, séparée par des virgules, ou les valeurs ne doivent pas être entourer de quote
	 * @return int retourne l'id si il y a un champs auto_increment, sinon le nombre de lignes affectées
	 */
	public function insert(array $data, $raw_column=null) {
		return $this->instance->insert($data, $raw_column);
	}

	/**
	 * si $order est un array la fonction vérifie la présence des clefs dans la liste des colonnes
	 * @param array $order column[ASC | DESC | RAND] tableau dont les clefs sont colonnes associées aux sens de trie
	 * @return Query
	 */
	public function orderBy($order) {
		return $this->instance->orderBy($order);
	}
	
	/**
	 * si $group peut être un array, la fonction vérifie la présence des clefs dans la liste des colonnes	 
	 * @return Query
	 */
	public function groupBy($group) {
		return $this->instance->groupBy($group);
	}
	/**
	 * @return Query
	 */
	public function limitSelect($start=false, $end=false) {
		return $this->instance->limitSelect($start, $end);
	}
	
	/**
	 * @return Query
	 */
	public function limitUpdate($start=false) {
		return $this->instance->limitUpdate($start);
	}
	
	
	
	
	public function quote($string) {
		return $this->instance->quote($string);
	}

	/**
	 * @return Query
	 */
	public function duplicate() {
		return $this->instance->duplicate();
	}
	
	/**
	 * Enter description here ...
	 * @param array $options options array('distinct','sql_calc_found_rows')
	 */
	public function getAll(array $options=array()) {
		return $this->instance->getAll($options);
	}
	
	/**
	 * Enter description here ...
	 * @param array $options options array('distinct','sql_calc_found_rows')
	 */
	public function getAllColumn(array $options=array()) {
		return $this->instance->getAllColumn($options);
	}
	/**
	 * Enter description here ...
	 * @param array $options options array('distinct','sql_calc_found_rows')
	 */
	public function getAssoc(array $options=array()) {
		return $this->instance->getAssoc($options);
	}
	/**
	 * Enter description here ...
	 * @param array $options options array('distinct','sql_calc_found_rows')
	 */
	public function getObject(array $options=array()) {
		return $this->instance->getObject($options);
	}
	/**
	 * Enter description here ...
	 * @param array $options options array('distinct','sql_calc_found_rows')
	 */
	public function getColumn(array $options=array()) {
		return $this->instance->getColumn($options);
	}
	
	public function showSelectQuery(array $options=array()) { 
		return $this->instance->showSelectQuery($options);		
	}
	
	public function showUpdateQuery($set, $raw=null) { 
		return $this->instance->showUpdateQuery($set, $raw, 1);	
	}
	/** 
	 * alias pour delete -> rpc javascript
	 */	
	public function remove() {
		return $this->instance->remove();
	}
}
