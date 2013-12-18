<?php
namespace Fp\Core;
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
class Filter {
	private function __construct() {}

	private static function getVal($var=null, $array=null) {
		// @todo get val by array index
		//if ( is_array($var) ) {}		
		if (  is_array($array)  ) {
			if ( !array_key_exists($var, $array) ) return null;
			$var = $array[$var];
		}
		if ( get_magic_quotes_gpc() && is_string($var) ) return stripslashes($var);
		return $var;
	}
	
	// @todo get val by array index 
	private function getValIndex(array $var, $array) {
		foreach ( $var as $v ) {
			if ( is_array($array) AND array_key_exists($v, $array) ) $val = $array[$val];
			else return null;
		}
		return $val;
	}

	private static function setVal($var, $default) {
		if ( $var === null  )	return $default;
		if ( strlen((string) $var) ) return $var;
		return $default;
	}

	private static function filter_var($var, $filter,array $option = null) {
		return filter_var($var, $filter, $option);
	}

	public static function custom($var, $array=null, $function) {
	    $var = self::getVal($var, $array);	   
	    if ( is_callable($function) ) {	       
	        return $function($var, $array);
	    }
	}
	
	public static function addSlashes($var, $array=null) {
		$var = (string) self::getVal($var, $array);
		return addslashes(trim($var));
	}

	public static function fixUtf8($dirt) {
		$replace = array(
				"Å " => "Š", "Å¡" => "š", "Å'" => "Œ", "Å\"" => "œ",
				"Å¸" => "Ÿ", "Ã¿" => "ÿ", "Ã€" => "À", "Ã " => "à",
				"Ã" => "Á", "Ã¡" => "á", "Ã‚" => "Â", "Ã¢" => "â",
				"Ãƒ" => "Ã", "Ã£" => "ã", "Ã„" => "Ä", "Ã¤" => "ä",
				"Ã…" => "Å", "Ã¥" => "å", "Ã†" => "Æ", "Ã¦" => "æ",
				"Ã‡" => "Ç", "Ã§" => "ç", "Ãˆ" => "È", "Ã¨" => "è",
				"Ã‰" => "É", "Ã©" => "é", "ÃŠ" => "Ê", "Ãª" => "ê",
				"Ã‹" => "Ë", "Ã«" => "ë", "ÃŒ" => "Ì", "Ã¬" => "ì",
				"Ã" => "Í", "Ã­" => "í", "ÃŽ" => "Î", "Ã®" => "î",
				"Ã" => "Ï", "Ã¯" => "ï", "Ã" => "Ð", "Ã°" => "ð",
				"Ã'" => "Ñ", "Ã±" => "ñ", "Ã'" => "Ò", "Ã²" => "ò",
				"Ã\"" => "Ó", "Ã³" => "ó", "Ã\"" => "Ô", "Ã´" => "ô",
				"Ã•" => "Õ", "Ãµ" => "õ", "Ã–" => "Ö", "Ã˜" => "Ø",
				"Ã¸" => "ø", "Ã™" => "Ù", "Ã¹" => "ù", "Ãš" => "Ú",
				"Ãº" => "ú", "Ã›" => "Û", "Ã»" => "û", "Ãœ" => "Ü",
				"Ã¼" => "ü", "Ã" => "Ý", "Ã½" => "ý", "Ãž" => "Þ",
				"Ã¾" => "þ", "ÃŸ" => "ß", "Ã¶" => "ö",
				"â" => "'",	"Ã" => "é" ,"Â"=>"œ",
				"Ã " => "à",
				''=> "|", 	''=>"'", "Å" => 'œ', "Ã" => 'à' 
				);
				$dirt =  str_replace(array_keys($replace), array_values($replace), $dirt);
				// invisible || unasigned control code
				$dirt = preg_replace('~[\p{Co}\p{Cs}\p{Cn}]+~u', '', $dirt);

				//avoid W3C error: Text run is not in Unicode Normalization Form C.
				//http://stackoverflow.com/questions/7931204/what-is-normalized-utf-8-all-about
				//http://php.net/manual/en/class.normalizer.php				
				if ( class_exists('\Normalizer') ) {
					$intl = new \Normalizer();
					return $intl->normalize($dirt);
				}
				return $dirt;
	}

	public static function utf8($var, $array=null) {
		$var = (string) self::getVal($var, $array);
		if ( !strlen($var) ) return '';
		// détecter utf-8, le résultat de mb_detect_encoding est souvent incorrect, même en changeant l'orde de détection
		if ( preg_match('%(?:
        [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
        |\xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
        |[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} # straight 3-byte
        |\xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
        |\xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
        |[\xF1-\xF3][\x80-\xBF]{3}         # planes 4-15
        |\xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
        )+%xs', 
		$var) ) { return self::fixUtf8($var); }
		mb_substitute_character("none");
		$enc = mb_detect_encoding($var, "ISO-8859-1,  Windows-1251, ASCII, UTF-16, UTF-16LE, UTF-16BE, UNICODE,  JIS,  EUC-JP, SJIS", true);

		$var = mb_convert_encoding ($var , "UTF-8" , $enc );
		return $var;
	}

	/**
	 * @param unknown_type $var
	 * @return NULL|unknown|string
	 */
	public static function iso8859_1($var, $array=null) {
		$var = (string) self::getVal($var, $array);
		mb_substitute_character("none");
		if ( !strlen($var) ) return '';
		$enc = mb_detect_encoding($var, " UTF-16LE, UTF-8,  UTF-16, UTF-16BE, UNICODE, Windows-1251, ISO-8859-1, ASCII, JIS,  EUC-JP, SJIS", true);
		if ( $enc == "ISO-8859-1" ) return $var;
		else return mb_convert_encoding($var, "ISO-8859-1" , $enc);
	}

	public static function int($var, $array=null, $default=null, $min=null, $max=null) {
		$var = trim((string) self::getVal($var, $array));	
		$opt = array();
		if ( is_int($min) ) $opt['min_range'] = $min;
		if ( is_int($max) ) $opt['max_range'] = $max;
		$var = filter_var($var, FILTER_SANITIZE_NUMBER_INT, $opt);
		if ( $var === null  )	return $default;
		if ( strlen((string) $var) ) return (int) $var; // on ne perturbe pas la valeur par defaut
		return $default;
	}
	
	public static function float($var, $array=null, $default=null) {
		$var = trim((string) self::getVal($var, $array));	
		$var = str_replace(',','.', $var); // traduction de la virgule
		$var = filter_var($var, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_SCIENTIFIC);
		if ( $var === null  ) return $default;
		if ( strlen((string) $var) ) return (float) $var; // on ne perturbe pas la valeur par defaut
		return $default;
	}	

	public static function intList($var, $array=null, $default='') {
		$var = (string) self::getVal($var, $array);
		$var = preg_replace('#[^0-9,]#i','', $var);
		return self::setVal($var, $default);
	}

	public static function alnum($var, $array=null, $default='') {
		$var = (string) self::getVal($var, $array);
		$var = self::accent($var);
		$var = preg_replace('#[^a-z0-9]#i','', $var);
		return self::setVal($var, $default);
	}

	public static function classname($var, $array=null, $default='') {
		$var = (string) self::getVal($var, $array);
		$var = self::accent($var);
		$var = preg_replace('#[^a-z0-9_\\\]*#i','', $var);
		return self::setVal($var, $default);
	}
	
	public static function variablename($var, $array=null, $default='') {
		$var = (string) self::getVal($var, $array);
		$var = self::accent($var);
		$var = preg_replace('#[^a-z][^a-z0-9_]*#i','', $var);
		return self::setVal($var, $default);
	}

	public static function id($var, $array=null, $default='') {
		$var = (string) self::getVal($var, $array);
		$var = self::accent($var);
		$var = preg_replace('#[^a-z0-9_-]#i','', $var);
		return self::setVal($var, $default);
	}
	
	/**
	 * @deprecated use b64WebDecode
	 */
	public static function b64Decode($var, $array=null, $default='') {
		return self::b64WebDecode($var, $array, $default);
	}
	/**
	 * @deprecated use b64WebEncode
	 */
	public static function b64Encode($var, $array=null, $default='') {
		return self::b64WebEncode($var, $array, $default);
	}

	// https://developer.mozilla.org/en-US/docs/DOM/window.btoa
	// must be the same of $4p.b64WebDecode(()
	public static function b64WebDecode($var, $array=null, $default='') {
		$var = (string) self::getVal($var, $array);
		$var = urldecode(base64_decode(strtr($var, '-_,', '+/='))); // see urlDecode 
		return self::setVal($var, $default);
	}
	// must be the same of $4p.b64WebEncode(()
	public static function b64WebEncode($var, $array=null, $default='') {
		$var = (string) self::getVal($var, $array);
		$var =  strtr(base64_encode(urlencode($var)), '+/=', '-_,'); // see urlDecode 
		return self::setVal($var, $default);
	}
/*
	public static function b64encryptId($var, $array=null, $default='', $salt='') {
		$var = (string) self::getVal($var, $array);
		$HashedChecksum = substr(sha1($salt.$var), 0, 6);
		return base64url_encode($HashedChecksum.dechex($int));
	}

	public static function b64decryptId($var, $array=null, $default='', $salt='') {
		$var = (string) self::getVal($var, $array);
		$parts = base64url_decode($var);
		$var   = substr(hexdec($parts), 6);
		$part1 = substr($parts, 0, 6);
		if ( substr(sha1($salt.$var), 0, 6) === $part1  ) return $var;
		return self::setVal(null, $default);
	}
*/
	public static function b64Compress($var, $array=null, $default='') {
		$var = self::getVal($var, $array);
		if ( $var ) return strtr(base64_encode(gzdeflate(serialize($var))), '+/=', '-_,');
		return self::setVal($var, $default);
	}

	public static function b64Decompress($var, $array=null, $default='') {
		$var = (string) self::getVal($var, $array);
		if ( $var ) return unserialize(gzinflate(base64_decode(strtr($var, '-_,', '+/='))));
		return self::setVal($var, $default);
	}

	/*
	 * remember:
	 * Apache denies all URLs with %2F in the path part, for security reasons:
	 * scripts can't normally (ie. without rewriting) tell the difference between %2F and / 
	 * due to the PATH_INFO environment variable being automatically URL-decoded 
	 * (is a part of the CGI specification so there's nothing can be done about it).
	 */
    public static function urlDecode($var, $array=null, $default='') {
		$var = trim((string) self::getVal($var, $array, $default));
		$var = urldecode($var);
		return self::setVal($var, $default);
	}

	public static function urlEncode($var, $array=null, $default='') {
		$var = trim((string) self::getVal($var, $array, $default));
		$var = urlencode($var);
		return self::setVal($var, $default);
	}

	public static function url($var, $array=null, $default='') {
		$var = trim((string) self::getVal($var, $array, $default));
		$var = self::filter_var($var,FILTER_SANITIZE_URL);
		return self::setVal($var, $default);
	}

	public static function email($var, $array=null, $default='') {
		$var = trim((string) self::getVal($var, $array, $default));
		$var = self::filter_var($var,FILTER_VALIDATE_EMAIL);
		return self::setVal($var, $default);
	}

	public static function mysqlDate($var, $array=null, $default='') {
		$var = trim((string) self::getVal($var, $array, $default));
		$var = str_replace('/', '-', $var);
		if ( !preg_match('#([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})#', $var, $m) ) return $default;
		// validate day
		if ( intval($m[1]) < 1 || intval($m[2]) > 12  ) return $default;
		//validate month
		if ( intval($m[2]) < 1 || intval($m[3]) > 31  ) return $default;
		return self::setVal($m[0], $default);
	}

	public static function mysqlDateTime($var, $array=null, $default='') {
		$var = trim((string) self::getVal($var, $array, $default));
		$rgx = '(\d{2}|\d{4})(?:\-)?([0]{1}\d{1}|[1]{1}[0-2]{1})(?:\-)?([0-2]{1}\d{1}|[3]{1}[0-1]{1})(?:\s)?([0-1]{1}\d{1}|[2]{1}[0-3]{1})(?::)?([0-5]{1}\d{1})(?::)?([0-5]{1}\d{1})';
		if ( !preg_match("#$rgx#", $var, $m) ) {
			if ( !preg_match('#[0-9]{2,4}-([0-9]{1,2})-([0-9]{1,2})#', $var, $m) ) return $default;
			else $var = $var.' 00:00:00';
		}
		return self::setVal($var, $default);
	}

	public static function text($var, $array=null, $default='') {
		$var = (string) self::getVal($var, $array, $default);
		$var = Filter::encodeHtmlChars($var);
		$var = filter::DbSafe($var);
		return self::setVal($var, $default);
	}

	public static function decodeHtmlChars($var, $array=null, $default='') {
		$var = (string) self::getVal($var, $array, $default);
		//$var = Filter::utf8($var);
		return html_entity_decode($var, ENT_QUOTES,'UTF-8');
		return self::setVal($var, $default);
	}

	// réencode les entité pour pouvoir afficher du code source
	public static function encodeHtmlChars($var, $array=null, $default='') {
		$var = (string) self::getVal($var, $array, $default);
		//$var = Filter::utf8($var);
		return htmlspecialchars($var, ENT_NOQUOTES,'UTF-8',true);
		return self::setVal($var, $default);
	}

	public static function htmlDoubleQuote($var, $array=null, $default='') {
		$var = (string) self::getVal($var, $array, $default);
		//$var = Filter::utf8($var);
		$var = preg_replace('/"/i','&quot;', $var);
		return self::setVal($var, $default);
	}

	// on ne réencode pas les entités car on veux jamais afficher du code dans les attribut.
	public static function htmlAttr($var, $array=null, $default='') {
		$var = (string) self::getVal($var, $array, $default);
		return self::addSlashes(htmlspecialchars($var, ENT_QUOTES,'UTF-8',false));
		return self::setVal($var, $default);
	}

	// TODO fix Xss and make a white list tag/attr
	public static function html($var, $array=null, $default='') {
		$var = (string) self::getVal($var, $array, $default);
		//$var = Filter::utf8($var);		
		if ( !preg_match('/\S/', $var) )	return $default;
		// corrige les </li>&#13;
		$var = preg_replace('#>[\r\n]+#m', '>',$var);
		
		
		//ignore error
		libxml_use_internal_errors(true);
		$dom = new \DomDocument('1.0', 'UTF-8');
		$dom->recover = true;
		// this is the trick
		$var = '<div>'.$var.'</div>';
		try { // hack to preserve UTF-8 characters
			  //http://stackoverflow.com/questions/3548880/php-dom-utf-8-problem
			  $dom->loadHTML('<?xml encoding="UTF-8">'.$var); // hack fix utf-8 
			  $dom->encoding = 'UTF-8';
		} catch ( Exception $e) { }
		if ( !function_exists('filterHtmlCleanChild') ) {
			function filterHtmlCleanChild(DOMElement $p, $removeTag, $removeAttr) {
				if ($p->hasChildNodes()) {
					foreach ($p->childNodes as $c) {
						// on delete ce fils
						if ( in_array($c->nodeName, $removeTag ) )  $p->removeChild($c);
						elseif ($c->nodeType == XML_ELEMENT_NODE ) filterHtmlCleanChild($c, $removeTag, $removeAttr);
					}
				}
				foreach ( $removeAttr as $ra ) {
					$p->removeAttribute($ra);
					/**		 	test
					 if ( $ra == 'onkeydown' ) {
					 if ( $va = $p->getAttribute('onkeydown') ) {
					 $p->removeAttribute($ra);
					 $rgx = '#*media\{id:([0-9]*)\}*#';
					 preg_match($rgx, $va, $m);
					 $r = array();
					 if ( isset($m[1]) ) { $p->setAttribute($ra,"media{id:".$m[1]."}"); }
					 }
					 */
				}
				return $p;
			}
		}
		$removeAttr = array('onclick', 'ondblclick', 'onkeydown', 'onkeypress', 'onkeyup', 'onload', 'onmousedown', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onunload');
		$removeTag  = array('script');
		foreach ($dom->childNodes as $p) {
			if ($p->nodeType == XML_PI_NODE) $dom->removeChild($p); // remove hack for utf-8
			elseif ($p->hasChildNodes()) filterHtmlCleanChild($p, $removeTag, $removeAttr);
		}
		$dom->normalizeDocument();
	
		$r = $dom->saveXML($dom->childNodes->item(1)->childNodes->item(0)->childNodes->item(0), LIBXML_NOEMPTYTAG);
		$r = preg_replace('#^<div>#','', $r);
		$var = preg_replace('#</div>$#','', $r);
		// fix certain navigateur compte 2 <br> pour <br></br>, et n'accepte pas les <iframe />
		$var = preg_replace('#<br></br>#','<br />',$var);
		
		return self::setVal($var, $default);
	}

	public static function htmlToText($var, $array=null, $default='') {
		$var = (string) self::getVal($var, $array, $default);
		//$var = Filter::utf8($var);

		$text = '';
		if ( !preg_match('/\S/', $var) ) return $default;
		//ignore error
		libxml_use_internal_errors(true);
		$dom = new \DomDocument('1.0', 'UTF-8');
		$dom->recover = true;
		// hack to preserve UTF-8 characters
		//http://stackoverflow.com/questions/3548880/php-dom-utf-8-problem
		$d = '<?xml encoding="UTF-8"><div>';
		try { $dom->loadHTML($d.$var.'</div>');	} catch ( Exception $e) { }

		// remove hack
		$dom->encoding = 'UTF-8';
		foreach ($dom->childNodes as $c) {
			if ( $c->nodeType != XML_PI_NODE) {
				switch (strtolower($c->nodeName)) {
					case 'p' :
						$text .= $c->textContent."\r\n";
						break;
					case 'br':
						$text .= "\r\n";
						break;
					case 'a' :
						$attr = '';
						if ( $c->hasAttributes() ) { 
							$attr = $c->getAttribute('href');
						}
						$text .= $c->textContent." (".$attr.")";
						break;
					default:
						$text .= $c->textContent;
				}
			}
		}
		$text = html_entity_decode( $text , ENT_QUOTES,'UTF-8');
		return self::setVal($text, $default);
	}

	/**
	 * @deprecated
	 */
	public static function repetition($var, $array=null, $default='') {
		$var = (string) self::getVal($var, $array, $default);
		//$var = Filter::utf8($var);
		return preg_replace('/([^0-9])\\1{2,}/i','$1', $var);
	}

	/**
	 * @deprecated
	 */
	public static function spam($var, $array=null, $default='') {
		$var = (string) self::getVal($var, $array, $default);
		//$var = Filter::utf8($var);
		$var = preg_replace('/id=[0-9]*/i','**##**', $var);
		$var = preg_replace('/.[a-z]*\.(biz|com|fr|org|info|be|tv|it|nu|me|pro)/i','**- No Pub -**', $var);
		return self::setVal($var, $default);
	}

	/**
	 * actualy it do nothing
	 * @deprecated
	 */
	public static function DbSafe($var, $array=null, $default='') {
		$var = (string) self::getVal($var, $array, $default);
		//$var = self::addSlashes($var);
		return self::setVal($var, $default);
	}

	public static function raw($var, $array=null, $default='') {
		$var = (string) self::getVal($var, $array, $default);
		return self::setVal($var, $default);
	}

	public static function accent($var, $array=null, $default='') {
		$var = trim((string) self::getVal($var, $array, $default));
		//$var = Filter::utf8($var);
		$table = array(
	        'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
	        'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'AE', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
	        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'œ' => 'oe', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
	        'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
	        'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'ae', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
	        'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
	        'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
	        'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r');  
		$var = strtr($var, $table);
		return self::setVal($var, $default);
	}

	public static function htmlspecialchars($var, $array=null, $default='') {
		$var = trim((string) self::getVal($var, $array, $default));
		//$var = Filter::utf8($var);
		$var = htmlspecialchars($var, ENT_QUOTES,'UTF-8',true);
		return self::setVal($var, $default);
	}

	// @todo regex ne correspond pas au nom de fichiers
	public static function fileName($var, $array=null, $default='') {
		$var = trim((string) self::getVal($var, $array, $default));
		$var = mb_substr(self::accent($var),0,127);
		$var = preg_replace('#\s+#','_', $var);
		$var = preg_replace('#[^0-9a-z+[\]()_\.-]#i','_', $var);
		return self::setVal($var, $default);
	}

	// @todo regex ne correspond pas au nom de dossier
	public static function dirName($var, $array=null, $default='') {
		$var = trim((string) self::getVal($var, $array, $default));
		$var = self::accent($var);
		$var = preg_replace('#\s+#','_', $var);
		$var = preg_replace('#[^0-9a-z_-]#i', '_', $var);
		return self::setVal($var, $default);
	}
	
	/*
	 * ARRAY
	 */
	
	private static function getArrayVal($var=null, $array=null) {
		if (  is_array($array)  ) {
			if ( !array_key_exists($var, $array) ) return null;
			$var = $array[$var];
		}
		return (array) $var;
	}
	
	private static function setArrayVal($var, $default=array()) {
		if ( !is_array($var)  ) return $default;
		if ( !count($var)  )    return $default;
		return (array) $var;
	}

	/**
	 * Enter description here ...
	 * @param unknown_type $var
	 * @param unknown_type $array
	 * @param unknown_type $default
	 * @return array
	 */
	public static function array_($var, $array=null, $default=array()) {
		$var = self::getArrayVal($var, $array);		
		return self::setArrayVal($var, ( $default ) ? $default : array());
	}


	public static function arrayOfInt($var, $array=null, $default=null) {
		$var = self::getArrayVal($var, $array);
		if ( !is_array($var) )	$var = null;
		$f = (array) self::setArrayVal($var, $default);
		$fx = function($v) { return Filter::int($v); };
		return array_map( $fx, $f );
	}
}