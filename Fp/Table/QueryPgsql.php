<?php
namespace Fp\Table;

use \Exception;

class QueryPgsql extends QueryAbstract {
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

	protected $limitSelect = '';
	protected $limitUpdate = '';
	protected $table_alias = '';

	protected $lastInsertId = null;
	protected $rowCount     = null;
	/**
	 * Enter description here ...
	 * @var unknown_type
	 * @deprecated
	 */
	public $calc_found_rows = false;

	public $tableJoin   = array();

	protected $selectFunction = '';
	protected $selectedColumn = array();
	
	/**
	 * @return Table_query
	 */
	public function __construct(Table $table, $table_alias=null) {
		$this->table_alias =  ( $table_alias ) ? $table_alias : $table->table;
		$this->dbTable = $table;
		$this->condition = new ConditionPgsql($this);
	}

	protected function sanitzeSelectColumn($col) {
		$col = trim(str_replace('', '', $col));
		$col = explode(' ', $col);
		$r = array('table_alias' =>'', 'name' =>'', 'as' => '');
		foreach ( $col as $k => $v ) {
			$v = trim($v);
			if ( $k == 0 ) {
				$t = explode('.',$v);
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
		return $r;
	}

	
	// full qualified column name
	protected function fqcn($table_alias, $column, $fqcn=true) { 
		if ( $fqcn && $table_alias ) return "$table_alias.$column";
		return "$column";
	}
	
	public function buildColumn($sanCol) {
		$r =  ( $sanCol['table_alias'] ) ? "{$sanCol['table_alias']}." : '';
		$r .= ( $sanCol['name'] != '*')  ? "{$sanCol['name']}" : '*';
		$r .= ( $sanCol['as'] ) ?  "as {$sanCol['as']}" : '';
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
	public function  getStatement(array $options=array(),$simule=false) {
		$r = array();
		foreach ( $this->selectedColumn as $v ) $r[] = $this->buildColumn($v);
		$select = implode(',', $r);
		$where = $this->prepareWhere();
		if ( !$select ) $select = '';
		
		$this->setStatementOptions($options);	
			
		$distinct = ( array_key_exists('distinct', $this->statementOptions) && (bool) $this->statementOptions['distinct'] ) 
					? 'DISTINCT' : '';

		// deprecated
		$calc_found_rows = ( array_key_exists('sql_calc_found_rows', $this->statementOptions) && intval($this->statementOptions['sql_calc_found_rows']) ) 
					? 'SQL_CALC_FOUND_ROWS' : '';

		$join  = $this->mkJoinTable();
		$table = ( $this->table_alias ) ? "{$this->dbTable->table} as $this->table_alias" : $this->dbTable->table;
		
		$s = array();
		if ( $select ) $s[] = $select;
		if ( $this->selectFunction ) $s[] = $this->selectFunction;
				
		$s = implode(',', $s);	
		if ( !$s ) $s='*';	
		
		$sql   = "SELECT $calc_found_rows $distinct $s FROM $table $join WHERE $where $this->groupBy $this->orderBy $this->limitSelect ";
		
		if ( $simule ) return $sql;
		//echo "/*$sql*/\r\n";
		return $this->query($sql);
	}

	public function update(array $set, $raw=null, $simule=false) {	
		$where = $this->prepareWhere();
		$table = ( $this->table_alias ) ? "{$this->dbTable->table} as $this->table_alias" : $this->dbTable->table;
		$tableAlias = ( $this->table_alias ) ? $this->table_alias : $this->dbTable->table;
		if ( !$set = $this->set($set, $raw, false) ) return 0;		
		$join = $this->mkJoinTable();		
		// update "order by fix"
		$sql   = "(SELECT * FROM $table $join WHERE $where $this->groupBy $this->orderBy $this->limitSelect) $tableAlias" ;
		$sql = "UPDATE {$this->dbTable->table} SET $set FROM $sql";
		if ( $simule ) return $sql;
		return $this->query($sql)->rowCount();
	}

	/**
	 * @return int
	 */
	public function foundRows() {
		// http://en.wikibooks.org/wiki/Converting_MySQL_to_PostgreSQL
		//return $this->query("SELECT FOUND_ROWS()")->fetchColumn();		
		$req = $this->duplicate();
		$req->selectColumn('',false);
		$count = "COUNT(*)";
		$req->selectFunction($count, false);
		$req->orderBy(array());
		$req->limitSelect(false, false);
		return $req->getColumn();
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
	 * effectue un onduplicate update, si une clef uniques existe en ignorant les clé presentes dans $this->unique
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

	private function insertBuildColumn(array $data) {
		$c = array();
		foreach ( $data as $k => $v ) {
			if ( !in_array($k, $this->dbTable->column ) ) continue;
			$c[] = "$k";
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
		// work for postgre 8.2 !!
		if( $this->dbTable->auto_increment ) {
			$sql = $sql." RETURNING ".$this->dbTable->primary[0];
		}
		$r = $this->query($sql);
		
		if ( !$this->dbTable->auto_increment ) {
			return $r->rowCount();
		}
		else { 
			return $this->lastInsertId = $r->fetchColumn();
		}		
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
					if ( !$col ) throw new Exception(" undeclared column  $k:$v in ".$this->dbTable->table);
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
			if ( count($o) ) $this->orderBy = ' ORDER by'.implode(',', $o);
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
		if (!is_array($group)) {
			$group =array($group);
		}
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
			if ( count($o) ) $this->groupBy  = 'GROUP by '.implode(',', $o);
		
		return $this;
	}

	/**
	 * @return Table_query
	 * @TODO à placer dans abstract, cette syntaxe (OFFSET) est aussi supporté par mysql
	 */
	public function limitSelect($start=false, $end=false) {		
		if ( !intval("$start") && !intval("$end") ) 	$this->limitSelect = '';
		else if (  ctype_digit("$start") && ctype_digit("$end")  )     $this->limitSelect = "LIMIT $end OFFSET $start";
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
