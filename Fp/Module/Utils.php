<?php
namespace Fp\Module;
use Fp\Core\Core;

class Utils { 
	/**
	 * @param unknown_type $rows
	 * @param unknown_type $start
	 * @param unknown_type $end
	 * @param unknown_type $total
	 * @param unknown_type $sortable
	 * @param unknown_type $searchable
	 * @param unknown_type $sortedBy
	 * @return array
			'list' => array $rows,
			'start'=> $start,
			'end'  => $end,
			'page' => $page,
			'total_page' => $total_page,
			'total'		 => $total,
			'sortedBy'   => $sortedBy,
			'sortable'   => $sortable,
			'searchable' => $searchable					
	 */	
	public static function formatList($rows, $start, $end, $total, $sortable = array(), $searchable=array(), $sortedBy=array() ) { 
		$start 	= intval(strval($start));
		if ( !$end = intval(strval($end)) ) $end = 999;
		$total	= intval(strval($total));
		$total_page = ceil($total / $end);
		$total_page= ( $total_page ==0 ) ? 1 : $total_page;
		# la page demander	
		$page = floor($start/$end)+1;
		if ( !is_array($rows)) $rows = array();
		$r = array(
			'list' => (array) $rows,
			'start'=> $start,
			'end'  => $end,
			'page' => $page,
			'total_page' => $total_page,
			'total'		 => $total,
			'sortedBy'   => $sortedBy,
			'sortable'   => $sortable,
			'searchable' => $searchable					
		);
		return $r;
	}
	
	/*
	 * 	protected static $externalCall;
	 * 	// http://www.php.net/manual/fr/function.method-exists.php#65405
		// module must only call public methods
		// case insensitive
		if ( !self::$externalCall ) {
			self::$externalCall = create_function('$t, $m',
				'if ( in_array(mb_strtolower($m), array_map(\'mb_strtolower\', get_class_methods($t))) ) {			
					return true;
			}');
		}
	 */
	public static function externalCallTest($t, $m) {
		if ( in_array(mb_strtolower($m), array_map('mb_strtolower', get_class_methods($t))) ) {
			return true;
		}
	}
	
	public static function getWebPath(\Fp\Core\Init $O, $dir) {
		return $O->route()->getWebPath($dir);
	}
	
}