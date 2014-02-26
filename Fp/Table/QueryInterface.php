<?php
namespace Fp\Table;

use \Exception;

/**
 * 
 * Enter description here ...
 * @author mickael
 * @TODO ajouter les methodes final de Table_query Abstract
 *
 */
interface QueryInterface {

	/**
	 * @return Table_query
	 */
	public function __construct(Table $table, $table_alias=null);
	public function typeColumn($column);	
	public function existColumn($column, $fqcn=true);
	public function innerJoin(Table $table, $alias, $condition);
	public function outerJoin(Table $table, $alias, $condition);
	public function leftJoin(Table $table, $alias, $condition);
	public function rightJoin(Table $table, $alias, $condition);
	public function join(Table $table, $alias);
	public function noJoin($bool);
	
	/**
	 * @param unknown_type $where
	 * @return Table_condition
	 */
	public function andWhere($where=NULL);
	/**
	 * @param unknown_type $where
	 * @return Table_condition
	 */
	public function search($arraySearch);
	
	/**
	 * @param unknown_type $where
	 * @return Table_condition
	 */
	public function orWhere($where=NULL);

	/**
	 * query sur la connection courante
	 * @param string $query
	 * @return Db_Extend_PDOStatement
	 */
	public function query($req);
	public function rowCount();
	public function lastInsertId();

	/**
	 * @return Table_query
	 */
	public function selectColumn($column=null, $append=true);
	public function buildColumn($sanCol);
	public function selectFunction($sql);
	/**
	 * @return PDOStatement
	 */
	public function getStatement(array $options=array(),$simule=false);
	public function update(array $set, $raw=null, $simule=false);

	/**
	 * @return int
	 */
	public function foundRows();
	public function delete();

	/**
	 * effectue un onduplicate update, si une clef uniques existe en ignorant les clé presentes dans $this->unique
	 * @param array $data association colonnes/valeurs
	 * @param string $raw liste de colonnes, séparée par des virgules, ou les valeurs ne doivent pas être entourer de quote
	 * @return int retourne l'id si il y a un champs auto_increment, sinon le nombre de lignes affectées
	 */
	public function insert(array $data, $raw_column=null);

	/**
	 * si $order est un array la fonction vérifie la présence des clefs dans la liste des colonnes
	 * @param array $order column[ASC | DESC | RAND] tableau dont les clefs sont colonnes associées aux sens de trie
	 * @return Table_query
	 */
	public function orderBy($order);
	
	/**
	 * si $group peut être un array, la fonction vérifie la présence des clefs dans la liste des colonnes	 
	 * @return Table_query
	 */
	public function groupBy($group);
	
	/**
	 * @return Table_query
	 */
	public function limitSelect($start=false, $end=false);
	
	/**
	 * @return Table_query
	 */
	public function limitUpdate($start=false);	
	

}