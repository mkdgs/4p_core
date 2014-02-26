<?php
namespace Fp\Table;

use \Exception;

class ConditionPgsql extends ConditionAbstract {

	public function orSearch(array $arraySearch = array()) {
		$r = array();
		foreach ( $arraySearch as $k => $v ) {
			$column = $this->existColumn($k);
			if ( $column && "$v" ) {
				$type   = $this->typeColumn($k);

				if (  $type == 'varchar' ) {
					$v = $this->quote("%$v%");
					$r[] = "$column COLLATE utf8_general_ci LIKE $v ";
				}
				else {
					$v = $this->quote("$v");
					$r[] = 	" $column=$v ";
				}
			}
		}
		if ( !count($r) ) return $this;
		else {
			$this->searchCase = array_merge($r, $this->searchCase);
			$r = ' '.implode(" OR ", $r).' ' ;
		}
		return $this->orWhere($r);
	}
	
	public function andSearch(array $arraySearch = array()) {
		$r = array();
		foreach ( $arraySearch as $k => $v ) {
			$column = $this->existColumn($k);
			if ( $column && "$v") {
				$type   = $this->typeColumn($k);
				if (  $type == 'varchar' ) {
					$v = $this->quote("%$v%");
					$r[] = "$column COLLATE utf8_general_ci LIKE $v ";
				}
				else {
					$v = $this->quote("$v");
					$r[] = 	" $column=$v ";
				}
			}
		}
		if ( !count($r) ) return $this;
		else {
			$this->searchCase = array_merge($r, $this->searchCase);
			$r = ' '.implode(" AND ", $r).' ' ;
		}
		return $this->andWhere($r);
	}
}