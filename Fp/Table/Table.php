<?php
namespace Fp\Table;

use \Exception;

class Table { 		
	/**	
	 * @var Db
	 */
	public $DbLink			= null;
	public $table			= null;
	public $column	 		= array();
	public $columnType	 	= array();
	public $columnSortable	= array();
	public $columnSearchable = array();
	public $columnNotNull	 = array();
	public $primary			= array();
	
	public $auto_increment	= false;
	public $unique 			= array();
	public $validateRules   = array();
	public $filterRules     = array();	
	
	protected function __construct(\Fp\Db\PDO $DbLink, $table, $column=null) {
		$this->DbLink  = $DbLink;		
		if ( !$this->defineTable($table) ) {
			if ( !is_array($column) ) $column = array_filter(explode(',',$column));
			foreach ($column as $v ) {
				$this->column[$v] = $v;
			}			
			$this->table   = $table;		
		}
		return $this;	
	}
	
	/**
	 * @param unknown_type $DbLink
	 * @param unknown_type $shema
	 * @return Table
	 */
	public static function setTable($DbLink, $shema) {
		return new Table($DbLink, $shema);
	}
	
	/**
	 * 
	 * @param unknown $sheme 
	 * array( 'table'  => 'name',
	 * 		  'column' => array(
	 * 				'column_1' => array( 'type'=> 'int', 'sortable' => 1, 'primary' => 1 ),
	 *  	  )
	 * )
	 */
	protected function defineTable($sheme) {
		if ( !is_array($sheme) || !array_key_exists('table', $sheme) ) return;		
		$this->table   = $sheme['table'];
		if ( array_key_exists('options', $sheme) && is_array($sheme['options']) ) {
			if ( array_key_exists('auto_increment', $sheme['options']) ) {
				$this->auto_increment = $sheme['options']['auto_increment'];
			}
		}
		foreach ( $sheme['column'] as $column => $value) {	
			$this->columnType[$column] = $value['type'];
			$this->column[$column] = $column;
			if( array_key_exists('searchable', $value) && $value['searchable']) {
				$this->columnSearchable[$column] = true;
			}
			if( array_key_exists('sortable', $value) && $value['sortable'] ) {
				$this->columnSortable[] = $column;
			}
			if( array_key_exists('primary', $value) && $value['primary']) {
				$this->primary[$column] = $column;
			}
			if( array_key_exists('unique', $value) && $value['unique']) {
				$this->unique[$column] = $column;
			}
			if( array_key_exists('notNull', $value) && $value['notNull']) {
				$this->columnNotNull[$column] = $column;
			}			
		}
		return $this;
	}
	
	public function setValidateRules($row, $function) { 	
		if ( isset($this->column[$row]) ) {		
			$this->validateRules[$row] = $function;
		}
		return $this; 
	}
	
	public function validate($row, $value) { 	
		if ( isset($this->validateRules[$row]) AND is_callable($this->validateRules[$row]) ) {			
			return $this->validateRules[$row]($value);
		}
		return true;
	}
	
	public function setFilterRules($row, $function) { 
		if ( isset($this->column[$row]) ) {
			$this->filterRules[$row] = $function;
		}
		return $this; 
	}
	
	public function filter($row, $value) {	    
		if ( isset($this->filterRules[$row]) AND is_callable($this->filterRules[$row]) ) { 
			return $this->filterRules[$row]($value);
		}
		return $value;		
	}	
	
	/**
	 * @param unknown_type $DbLink
	 * @param unknown_type $table
	 * @param unknown_type $column use setColumn()
	 * @return Table
	 */
	public static function set($DbLink, $table, $column) { 
		return new Table($DbLink, $table, $column);		
	}
	
	public function setUnique($unique) {
		$this->unique  = ( is_array($unique) ) ? $unique : array_filter(explode(',',$unique));
		return $this; 
	}
    public function setAutoIncrement($auto_increment=null) { 
    	$this->auto_increment = $auto_increment;
    	return $this; 
    }
	public function setPrimary($primary) {
		$this->primary = ( is_array($primary) ) ? $primary : array_filter(explode(',',$primary));
		return $this; 
	}
	public function setSortable($sortable = array()) {
		$this->columnSortable =  $sortable;
		return $this; 		
	}
	
	/**
	 * Enter description here ...
	 * @param unknown_type $searchable
	 * @return Table
	 * @deprecated use setColumn
	 */
	public function setSearchable($searchable=array()) {
		$this->columnType = $searchable;
		return $this; 
	} 	
	public function setColumn($column=array()) {
		$this->column[$column] = $this->columnType = $column;
		return $this; 
	} 	
	
	public function existColumn($column) {			
		return isset($this->column["$column"]);
	}
	
	public function getTypeColumn($column) { 		
		if ( isset($this->columnType[$column]) ) return $this->columnType[$column];
	}
	
	public function quote($string) {
		return $this->DbLink->quote($string);
	}
	
	
	/**
	 * retourne les clé primaires de la table
	 * @return array:
	 */
	public function getPrimary() {
		return $this->primary;
	}
	
	/**
	 * retorune le nom de la table
	 * @return string
	 */
	public function getTableName() {
		return $this->table;
	}
	
}