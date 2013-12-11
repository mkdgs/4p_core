<?php
namespace Fp\Core;
use \DateTime;
use \Exception;
/**
* Copyright Desgranges Mickael 
* mickael@4publish.com
* 
* Ce logiciel est un programme informatique servant à la création d'application web. 
* 
* Ce logiciel est régi par la licence CeCILL-B soumise au droit français et
* respectant les principes de diffusion des logiciels libres. Vous pouvez
* utiliser, modifier et/ou redistribuer ce programme sous les conditions
* de la licence CeCILL-B telle que diffusée par le CEA, le CNRS et l'INRIA 
* sur le site "http://www.cecill.info".
* 
* En contrepartie de l'accessibilité au code source et des droits de copie,
* de modification et de redistribution accordés par cette licence, il n'est
* offert aux utilisateurs qu'une garantie limitée.  Pour les mêmes raisons,
* seule une responsabilité restreinte pèse sur l'auteur du programme,  le
* titulaire des droits patrimoniaux et les concédants successifs.
* 
* A cet égard  l'attention de l'utilisateur est attirée sur les risques
* associés au chargement,  à l'utilisation,  à la modification et/ou au
* développement et à la reproduction du logiciel par l'utilisateur étant 
* donné sa spécificité de logiciel libre, qui peut le rendre complexe à 
* manipuler et qui le réserve donc à des développeurs et des professionnels
* avertis possédant  des  connaissances  informatiques approfondies.  Les
* utilisateurs sont donc invités à charger  et  tester  l'adéquation  du
* logiciel à leurs besoins dans des conditions permettant d'assurer la
* sécurité de leurs systèmes et ou de leurs données et, plus généralement, 
* à l'utiliser et l'exploiter dans les mêmes conditions de sécurité. 
* 
* Le fait que vous puissiez accéder à cet en-tête signifie que vous avez 
* pris connaissance de la licence CeCILL-B, et que vous en avez accepté les
* termes.
*
* @package		4_publish
* @subpackage	core
* @author		Desgranges Mickael
* @license		CeciLL-B
* @link			http://4publish.com
*/
class Date {
	/**
	 * Enter description here ...
	 * @var DateTime
	 */
	private $dateTime;
	protected $lang = 'fr';
	protected $intl = array(
		'fr' => array(
				'days' => array('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'),
				'days_short' => array('Lun','Mar','Mer','Jeu','Ven','Sam','Dim'),
				'month' => array('Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Aout','Septembre','Octobre','Novembre','Décembre'),
				'month_short' => array('Jan','Fév','Mars','Avr','Mai','Juin','Juil','Aout','Sep','Oct','Nov','Déc')
		)
	);
	protected $timezone = 'Europe/Paris';
	
	/**
	 * @param mysql date
	 * @return Date
	 */
	public static function fromMysqlDate($timestamp) {
		$class = __CLASS__; 
		return new $class($timestamp, 'mysql_date');
	}
	
	/**
	 * @param string strtotime
	 * @return Date
	 */
	public static function fromStrtotime($time) { 
		$class = __CLASS__;
		return new $class($time, 'strtotime');
	} 
	
	/**
	 * @param mysql datetime
	 * @return Date
	 */
	public static function fromMysqlDateTime($timestamp) { 
		$class = __CLASS__;
		return new $class($timestamp, 'mysql_datetime');
	} 
	
	/**
	 * @param mysql unix time
	 * @return Date
	 */
	public static function fromUnixTime($timestamp) { 
		$class = __CLASS__;		
		return new $class($timestamp, 'unix_time');
	} 
	
	
	public function __construct($timestamp=null,$type=null, $timeZone=null) {	
		if( $timeZone ) {
			$this->timezone = $timeZone;
		}
		$timeZone = new \DateTimeZone($this->timezone);
		switch ($type) {
			case 'mysql_date':
				if ( !$timestamp || $timestamp == '0000-00-00')	$timestamp = '1900-01-01';	
				$timestamp = preg_replace('#0000-00-00#', '1900-01-01',  $timestamp);			
				$this->dateTime = date_create_from_format('!Y-m-d', $timestamp);				
				break;
				
			case 'mysql_datetime':
				$timestamp = preg_replace('#0000-00-00#', '1900-01-01',  $timestamp);				
				if ( !$timestamp ) $timestamp = '1900-01-01 00:00:00';				
				$this->dateTime = date_create_from_format('!Y-m-d H:i:s', $timestamp);				
				break;
							
			case 'strtotime':				
				$timestamp = strtotime($timestamp);			
				if ( !$timestamp ) $timestamp = time();	
				$this->dateTime = date_create_from_format('U',(string) $timestamp);
				break;
				
			case 'unix_time':				
				if ( !$timestamp ) $timestamp = time();	
				if ( intval($timestamp) <0 ) $timestamp 	= time() - intval($timestamp);		
				$this->dateTime = date_create_from_format('U',(string) $timestamp);
				break;				
		}	
		if ( !$timestamp && !$type ) {
			$timestamp = time();
			$this->dateTime = date_create_from_format('U',(string) $timestamp);			
		}
		else if ( !$this->dateTime ) throw new \Exception(__METHOD__.' unknow date '.print_r($timestamp, true));
		$this->dateTime->setTimezone($timeZone);
	}
	
	protected function getIntl($key) {
		return $this->intl[$this->lang][$key];
	}
	/**
	 * @deprecated
	 */
	public function day_short() {
		$jour = $this->getIntl('days_short');
		return $jour[$this->dateTime->format('N')-1];
	}
	public function day_fr_short() {
		return $this->day_short();
	}

	public function day() {
		$jour = $this->getIntl('days');
		return $jour[$this->dateTime->format('N')-1];
	}
	/**
	 * @deprecated
	 */
	public function day_fr() {
		return $this->day();
	}
	
	/**
	 * @return Days since monday (1-7)
	 */
	public function dayNumInWeek() { 
		return $this->dateTime->format('N');
	}
	public function dayWeekNum() { 
		return $this->dayNumInWeek();
	}
	
	public function month() {
		$mois = $this->getIntl('month');
		return $mois[$this->dateTime->format('n')-1];
	}
	/**
	 * @deprecated
	 */
	public function month_fr() {
		return $this->month();
	}
	
	public function month_short() {
		$mois = $this->getIntl('month_short');
		return $mois[$this->dateTime->format('n')-1];
	}

	public function year() {
		return $this->dateTime->format('Y');
	}
	public function second($pad=null) {
		return $this->pad($this->dateTime->format('s'), $pad);
	}
	public function min($pad=null) {
		return $this->pad($this->dateTime->format('i'), $pad);
	}
	public function hour($pad=null) {
		return $this->pad($this->dateTime->format('H'), $pad);
	}
	public function month_num($pad=null) {
		return $this->pad($this->dateTime->format('m'), $pad);
	}
	public function day_num($pad=null) {
		return $this->pad($this->dateTime->format('d'), $pad);
	}	
	
	public function minuteNumInDay() { 
		$min= 0;		
		if ( $m = intval($this->dateTime->format('i')) ) { 
			$min = $m /  60;
			$min = round($min,2);
		}
		return (intval($this->dateTime->format('G')) *60)+$min;
	}
	private function pad($r,$pad) {
		if  ( !$pad ) return ltrim($r, '0');
		return $r;
	}

	public function unixTime() {
		return $this->dateTime->format('U');
	}	
	public function format($format) {
		return $this->dateTime->format($format);
	}
	public function iso8601() {		
		return $this->dateTime->format(DateTime::ISO8601);
	} 	
	public function rfc2822() {
		return $this->dateTime->format(DateTime::RFC2822);
	}
	public function rfc3339() {
		return $this->dateTime->format(DateTime::RFC3339);
	}
	public function mysqlDateTime() { 	
		return $this->dateTime->format('Y-m-d H:i:s');
	}
	
	public function mysqlDate() { 
		return $this->dateTime->format('Y-m-d');
	}

	/**
	 * Enter description here ...
	 * @return number
	 * @deprecated a recoder
	 */
	public function getAge() {
		$annee = $this->dateTime->format('Y');
		$mois  = $this->dateTime->format('n');
		$jour  = $this->dateTime->format('j');
		$today['mois'] = date('n');
		$today['jour'] = date('j');
		$today['annee'] = date('Y');
		$annees = $today['annee'] - $annee;
		if ($today['mois'] <= $mois) {
			if ($mois == $today['mois']) {
				if ($jour > $today['jour']) $annees--;
			}
			else $annees--;
		}
		return $annees;			
	}
	
	public function isToday() { 
		return ( $this->mysqlDate() == Date::fromUnixTime(time())->mysqlDate() );
	}
	
	public function isTomorrow() { 
		return ( $this->mysqlDate() == Date::fromUnixTime(strtotime('today +1 day'))->mysqlDate() );
	}
	
	public function remainingTime() { 
		return $this->unixTime() - time();
	}
	
	public function remainingDays() { 
		$time = $this->remainingTime();		
		$d = floor($time/(3600*24));
		$h = floor( ($time - ( $d*(3600*24)  ))/3600 );		
		$m = floor( ($time - (( $d*3600*24  )+($h*3600)))/60  );		
		return array(
			'days'	  => $d,
			'hours'	  => $h,
			'minutes' => $m,
			'd'	  	  => $d,
			'h'	  	  => $h,
			'm' 	  => $m
		);
	}
	
	public function humanize() { 
		$lang = 'fr_FR.UTF8';
		setlocale(LC_TIME, $lang);
		$i18n = array();
		$i18n[$lang] = array(
					  'last_minute'  => 'il y a %S minute',
					  'last_minutes' => 'il y a %S minutes',
					  'last_hour'    => 'il y a %k heure',
					  'last_hours'   => 'il y a %k heures',
					  'today'	 	 => "aujourd'hui, %Hh%S",
					  'yesterday'	 => 'hier, %H:%S',
					  'last_day' 	 => 'il y a {rd}, %Hh%S',
					  'tomorrow' 	 => 'demain, %Hh%S',
					  'other'		 => '%A %e %B, %Hh%S'
		);
		 $inTimestamp = $this->unixTime();
		 $now = time();
		 $timeDiff = $inTimestamp-$now;
		
		 $formatDate = function ($timestamp, $subject) {
		 	return $s = strftime($subject, abs($timestamp));
		 };
		 
		 if( abs($timeDiff) < 86400 ) {		
		 	
		    if( date('zY',$now)==date('zY',$inTimestamp) ) {		    	 
		    	 if ( abs($timeDiff) < 2  ) {		    	 	
		    	 	return $formatDate(abs($timeDiff), $i18n[$lang]['last_minute'] );
		    	 }
		    	 if ( abs($timeDiff) < 3600  ) {		    	 	
		    	 	return $formatDate($timeDiff, $i18n[$lang]['last_minutes'] );
		    	 }
		    	 if ( abs($timeDiff) < (3600*2)  ) {  	 	
		    	 	return $formatDate($timeDiff, $i18n[$lang]['last_hour'] );
		    	 }
		     	 if ( abs($timeDiff) < (3600*8)  ) {		    	 	
		    	 	return $formatDate($timeDiff, $i18n[$lang]['last_hour'] );
		    	 }		    	
		    	 return $formatDate($inTimestamp, $i18n[$lang]['today'] );
		    }
		    if( $inTimestamp>$now ) return $formatDate($inTimestamp, $i18n[$lang]['tomorrow'] );
		    return $formatDate($inTimestamp, $i18n[$lang]['yesterday'] );
		 }
		 if( $timeDiff >0 ) {
		    if( $timeDiff < 604800 ) # Within the next 7 days
		      return $formatDate($inTimestamp, $i18n[$lang]['other'] );
		    if( $timeDiff < 1209600 ) # Within the next 14, but after the next 7 days
		      return $formatDate($inTimestamp, $i18n[$lang]['other'] );
		} else {
		    if( $timeDiff > 604800 ) # Within the last 7 days
		      return $formatDate($inTimestamp, $i18n[$lang]['other'] );
		}
		# Some other day
		return $formatDate($inTimestamp, $i18n[$lang]['other'] );		
	}
}