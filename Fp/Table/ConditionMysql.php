<?php
namespace Fp\Table;
class ConditionMysql extends ConditionAbstract {
	
	/**
	 * 
	 * @param unknown $search
	 * @return multitype:
	 */
	protected function searchParser($search) {
		$rs = array();			
		$rs =  preg_split('#\s*"([^"]*)"\s*|\s+#', $search, -1 , PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);			
		return $rs;
	}
	
	protected function makeSearchColumn($column, $search, $type='OR') {
		$column = $this->existColumn($column);
		$r = null;
		if ( $column ) {
			if( is_scalar($search) ) {					
				$t   = $this->typeColumn($column);
				if (  $t == 'varchar' ) {
					$sp = $this->searchParser($search);
					$tmp = array();
					foreach ( $sp  as $search ) {
						if ( $search ) {		
							/* score pertinence ? 
							$v = $this->quote("$search %");
							$tmp[] = "$column COLLATE utf8_general_ci LIKE $v ";
							
							$v = $this->quote("% $search %");
							$tmp[] = "$column COLLATE utf8_general_ci LIKE $v ";	
							*/
							$v = $this->quote("%$search%");
							$tmp[] = "$column COLLATE utf8_general_ci LIKE $v ";							
						}
					}
					$r = implode(" $type ", $tmp);
				}
				else {
					if ( $search ) {
						$v = $this->quote("$search");
						$r = " $column=$v ";		
					}			
				}
			}
			elseif ( is_array($search) ) {
				foreach ($search as $sub) {					
					if( $type  == 'OR')	$this->orSearch(array($column => $sub));
					else	$this->andSearch(array($column => $sub));
				}
			}
		}			
		$this->searchCase[] = $r;
		return $r;
	}
	
	public function orSearch(array $arraySearch = array()) {
		$r = array();
		foreach ( $arraySearch as $k => $v ) {
			$s = $this->makeSearchColumn($k, $v, 'OR');
			if ( !empty($s) ) $r[] = $s;
		}	
		if ( empty($r) ) return $this;			
		return $this->orWhere($r);
	}
	
	public function andSearch(array $arraySearch = array()) {
		$r = array();
		foreach ( $arraySearch as $k => $v ) {
			$s = $this->makeSearchColumn($k, $v, 'AND');
			if ( !empty($s) ) $r[] = $s;
		}
		if ( empty($r) ) return $this;		
		return $this->andWhere($r);
	}
}