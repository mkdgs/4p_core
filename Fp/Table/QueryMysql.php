<?php
namespace Fp\Table;

use \Exception;

class QueryMysql extends QueryAbstract implements QueryInterface {
	/**
	 * @var Table_condition
	 */
	public $condition;
	/**
	 * @var Table
	 */
	public $dbTable;
	public $limit;
	public $orderBy;
	public $groupBy;
	
	protected $join = true;

	protected $limitSelect  = '';
	protected $limitUpdate  = '';
	protected $table_alias  = '';

	protected $lastInsertId = null;
	protected $rowCount     = null;
	/**
	 * @var unknown_type
	 * @deprecated
	 */
	public $calc_found_rows = false;

	public $tableJoin   = array();

	protected $selectFunction = array();
	protected $selectedColumn = array();
	
	protected $cacheSanitizeSelectedColumn = array();
	
	/**
	 * @return Table_query
	 */
	public function __construct(Table $table, $table_alias=null) {
		$this->table_alias =  ( $table_alias ) ? $table_alias : $table->table;
		$this->dbTable = $table;		
		$this->condition = new ConditionMysql($this);
	}

	
	protected function sanitzeSelectColumn($col) {
		$kc = trim($col);
		if ( !isset($this->cacheSanitizeSelectedColumn[$kc]) ) {				
			$col = trim(str_replace('`', '', $col));
			$col = explode(' ', $col);
			$r = array('table_alias' =>'', 'name' =>'', 'as' => '');
			foreach ( $col as $k => $v ) {
				$v = trim($v);
				if ( $k == 0 ) {
					$t = explode('.', $v);
					if( count($t) == 2 ) {
						$r['table_alias'] = $t[0];
						$r['name'] = $t[1];
					}				
					else $r['name'] = $t[0];
				}
				if ( $k == 1 && $v == 'as' )  {
					$r['as'] = $col[2];
				}
			}
			$this->cacheSanitizeSelectedColumn[$kc] = $r;
		}
		return $this->cacheSanitizeSelectedColumn[$kc];
	}
	
	// full qualified column name
	protected function fqcn($table_alias, $column, $fqcn=true) { 
		if ( $fqcn && $table_alias ) return "`$table_alias`.`$column`";
		return "`$column`";
	}

	public function buildColumn($sanCol) {
		$r =  ( $sanCol['table_alias'] ) ? "`{$sanCol['table_alias']}`." : '';
		$r .= ( $sanCol['name'] != '*')  ? "`{$sanCol['name']}`" : '*';
		$r .= ( $sanCol['as'] ) ?  "as `{$sanCol['as']}`" : '';
		return $r;
	}

	protected function prepareWhere() {
		$where = $this->condition->build();
		if ( empty($where) ) $where = 'true';
		return $where;
	}

	/**
	 * @return PDOStatement
	 */
	public function getStatement(array $options=array(),$simule=false) {
		$r = array();
		foreach ( $this->selectedColumn as $v ) $r[] = $this->buildColumn($v);
		$select = implode(',', $r);
		$where = $this->prepareWhere();		
		if ( !$select ) $select = '';		
		$this->setStatementOptions($options);	
		
		$distinct = ( array_key_exists('distinct', $this->statementOptions) && (bool) $this->statementOptions['distinct'] )  ? 'DISTINCT' : '';

		// deprecated
		$calc_found_rows = ( array_key_exists('sql_calc_found_rows', $this->statementOptions) && intval($this->statementOptions['sql_calc_found_rows']) ) 
					? 'SQL_CALC_FOUND_ROWS' : '';

		
		$join  = $this->mkJoinTable();
		$table = ( $this->table_alias ) ? "{$this->dbTable->table} as $this->table_alias" : $this->dbTable->table;
		
		$s = array();
		if ( $select ) $s[] = $select;
		if ( !empty($this->selectFunction) ) $s[] = implode(',',$this->selectFunction);
				
		$s = implode(',', $s);	
		if ( !$s ) $s='*';	
		
		$sql   = "SELECT $calc_found_rows $distinct $s FROM $table $join WHERE $where $this->groupBy \r\n $this->orderBy $this->limitSelect ";
		
		if ( $simule ) return $sql;
		//echo "/*$sql*/\r\n";
		return $this->query($sql);
	}

	public function update(array $set, $raw=null, $simule=false) {
		$where = $this->prepareWhere();
		if ( !$set = $this->set($set, $raw) ) return 0;
		$table = ( $this->table_alias ) ? "{$this->dbTable->table} as $this->table_alias" : $this->dbTable->table;
		$join = $this->mkJoinTable();
		$sql = "UPDATE $table $join SET $set WHERE $where $this->orderBy $this->limitUpdate";
		if ( $simule ) return $sql;
		return $this->query($sql)->rowCount();
	}

	/**
	 * @return int
	 */
	public function foundRows() {
		if ( isset($this->statementOptions) && array_key_exists('sql_calc_found_rows', $this->statementOptions)  ) {
			return $this->query("SELECT FOUND_ROWS()")->fetchColumn();	
		}
		
		$req = $this->duplicate();
		$req->selectColumn('',false);
		if ( !empty($this->dbTable->primary) ) {
			$column = $this->fqcn($this->table_alias, current($this->dbTable->primary));			
		}
		else {
			$column = $this->fqcn($this->table_alias, $this->dbTable->column[0]);
		}
		$count = "COUNT(".$column.")";
		$req->selectFunction($count, false);
		$req->orderBy(array());
		$req->limitSelect(false, false);
		return $req->getColumn();
		
		// sql_calc_found row tricks
		// http://en.wikibooks.org/wiki/Converting_MySQL_to_PostgreSQL
		//return $this->query("SELECT FOUND_ROWS()")->fetchColumn();
	}
	
	public function delete($simule=false) {
		$where = $this->prepareWhere();
		$table = ( $this->table_alias ) ? "{$this->dbTable->table} as $this->table_alias" : $this->dbTable->table;
		$join = $this->mkJoinTable();
		$tablemultidelete = ( $this->table_alias ) ? $this->table_alias : $this->dbTable->table; 
		$sql = "DELETE $tablemultidelete FROM $table $join WHERE $where $this->orderBy $this->limitUpdate ";
                if ( $simule ) return $sql;
		return $this->query($sql)->rowCount();
	}

	/**
	 * @param array $data association colonnes/valeurs
	 * @param string $raw liste de colonnes, séparée par des virgules, ou les valeurs ne doivent pas être entourer de quote
	 * @return int retourne l'id si il y a un champs auto_increment, sinon le nombre de lignes affectées
	 */
	public function insert(array $data, $raw_column=null) {
		$values  = $this->insertBuildValues($data, $raw_column);
		$column = $this->insertBuildColumn($data);
		$sql = "INSERT INTO {$this->dbTable->table} $column $values";
		return $this->insertResult($sql);
	}

	/**
	 * effectue un onduplicate update, si une clef uniques existe en ignorant les clé presentes dans $this->unique
	 * @param array $data association colonnes/valeurs
	 * @param string $raw liste de colonnes, séparée par des virgules, ou les valeurs ne doivent pas être entourer de quote
	 * @return int retourne l'id si il y a un champs auto_increment, sinon le nombre de lignes affectées
	 * @deprecated
	 */
	public function insertUpdate(array $data, $raw_column=null, $data_update=null, $update_raw_column=null) {
		$values  = $this->insertBuildValues($data, $raw_column);
		$column = $this->insertBuildColumn($data);
		
		if ( is_array($data_update) ) { 
			$data_update = array();
			foreach ( $data as $k => $v ) {
				if ( !in_array($k, $this->dbTable->unique) AND !in_array($k, $this->dbTable->primary)  ) $data_update[$k] = $v;
			}
		} 
		else { 
			$update_raw_column 	= $data_update;
			$data_update 		= $data;
		}
		if ( !empty($data_update) ) {
			$sql = "INSERT INTO {$this->dbTable->table} $column $values
						ON DUPLICATE KEY UPDATE ". $this->set($data_update, $update_raw_column, false);
		}
		else $sql = "INSERT IGNORE INTO $this->table_name $column $values";
		return $this->insertResult($sql);
	}

	/**
	 * ignore l'insertion si une contrainte d'unicité existe
	 * @param array $data association colonnes/valeurs
	 * @param string $raw liste de colonnes, séparée par des virgules, ou les valeurs ne doivent pas être entourer de quote
	 * @return int retourne l'id si il y a un champs auto_increment, sinon le nombre de lignes affectées
	 * @deprecated
	 */
	public function insertIgnore(array $data, $raw_column=null) {
		$values  = $this->insertBuildValues($data, $raw_column);
		$column = $this->insertBuildColumn($data);
		$sql = "INSERT IGNORE INTO {$this->dbTable->table} $column $values ";
		return $this->insertResult($sql);
	}

	private function insertBuildColumn(array $data) {
		$c = array();
		foreach ( $data as $k => $v ) {
			if ( !in_array($k, $this->dbTable->column ) ) continue;
			$c[] = $this->fqcn($this->dbTable->table, $k);
		}
		return ' ('.implode(',', $c).') ';
	}

	private function insertBuildValues(array $data, $raw_column=null) {
		$r = array();
		$raw_column = explode(',', $raw_column);
		foreach ( $data as $k => $v ) {
			if ( in_array($k, $this->dbTable->column) ) {
				if ( $this->dbTable->validate($k, $data[$k]) ) {
					$r[] = $this->quoteData($k, $data[$k],  in_array($k, $raw_column));
				}
			}
		}
		return " VALUES(".implode(',', $r).") ";
	}

	private function insertResult($sql) {
		$r = $this->query($sql);
		$res = $r->rowCount();
		if ( $res && $this->dbTable->auto_increment ) {
			return $this->lastInsertId = $r->DBC->link->lastInsertId();
		}
		return $res;
	}
	


	/**
	 * si $set est un array la fonction ignore les éléments dont les clefs ne sont pas présentes dans la liste des colonnes
	 * @param array $set tableau dont les clefs sont colonnes associées aux valeurs à affecter
	 * @param string $raw_column chaine contenant les nom de colonnes, séparée par des virgules, dont les valeurs doivent être traitée comme commande sql
	 * @return string retourne une clause SET mysql
	 */
	protected function set( array $set, $raw_column=null, $fqcn=true) {
		if ( !is_array($set) ) throw new Exception(" no data to set in ".$this->dbTable->table);
		$w = array();
		$raw_column = explode(',', $raw_column);		
		foreach ( $set as $k => $v  ) {
			if ( !$col = $this->existColumn($k, $fqcn) )	continue;			
			if ( $this->dbTable->validate($k, $v) ) {	
				$w[] = " $col=".$this->quoteData($k, $v,  in_array($k, $raw_column));
			}
		}		
		$w = implode(',', $w);
		return $w;
	}

	/**
	 * si $order est un array la fonction vérifie la présence des clefs dans la liste des colonnes
	 * @param array $order column[ASC | DESC | RAND] tableau dont les clefs sont colonnes associées aux sens de trie
	 * @return Table_query
	 */
	public function orderBy($order) {
		$this->orderBy = '';
		$o = array();
		if (is_array($order))  {
			foreach ( $order as $k => $v  ) {
				if ( !$col = $this->existColumn($k) ) { 
					foreach ( $this->selectedColumn as $sc ) { 
						// si c'est un alias de colonne 						
						if ( $sc['as'] == $k ) $col = $k;
					}
					if ( !$col ) throw new Exception(" undeclared column $k:$v in ".$this->dbTable->table);
				}
				$v = strtoupper($v);
				switch ($v) {
					case 'RAND':
						$o[] = " RAND()";
						break;
					case 'DESC':
					case 'ASC' :
						$o[] = " $col $v";
				}
			}
			if ( !empty($o) ) $this->orderBy = ' ORDER by'.implode(',', $o);
		}	
		else if ( !empty($order) ) {
			$this->orderBy = ' ORDER by '.$order;
		} 
				
		return $this;
	}
	
	/**
	 * si $group peut être un array, la fonction vérifie la présence des clefs dans la liste des colonnes	 
	 * @return Table_query
	 */
	public function groupBy($group) {
		$this->groupBy = '';
		$o = array();
		$group = (array) $group;
		foreach ( $group as $v  ) {
				if ( !$col = $this->existColumn($v) ) { 						
					foreach ( $this->selectedColumn as $sc ) { 
						// si c'est un alias de colonne 						
						if ( $sc['as'] == $v ) $col = $v;
					}
					if ( !$col ) throw new Exception(" undeclared column  $v in ".$this->dbTable->table);
				}	
				$o[] = $col;
		}
		if ( !empty($o) ) $this->groupBy  = 'GROUP by '.implode(',', $o);
		
		return $this;
	}

	/**
	 * @return Table_query
	 */
	public function limitSelect($start=false, $end=false) {		
		if ( !intval("$start") && !intval("$end") ) 	$this->limitSelect = '';
		else if (  ctype_digit("$start") && ctype_digit("$end")  )     $this->limitSelect = "LIMIT $start, $end";
		else if (  ctype_digit("$start") || ctype_digit("$end")  ) {
			$l = ( ctype_digit("$start")  ) ? $start : $end;
			$this->limitSelect = "LIMIT $l";
		} 
		else $this->limitSelect ='';
		return $this;
	}
	
	/**
	 * @return Table_query
	 */
	public function limitUpdate($start=false) {
		$this->limitUpdate ='';
		if ( !ctype_digit("$start") ) 	$this->limitUpdate = '';
		else  $this->limitUpdate = "LIMIT $start";
		return $this;
	}
}
