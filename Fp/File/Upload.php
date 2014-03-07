<?php
namespace Fp\File;
use Fp\Core\Filter;
use \Exception;

/* USAGE
 try {
  $file = new Upload($tmp_name,$name,$type,$size,$error);
  $file->allowType(array(),array('jpg','gif','png'));
  if ( $file->isUploaded('my_file') {
   $file->set('my_file',upload/dir/");
   $file->move();
   $nom_fichier = $image->getName();
  }
 } catch  (Exception  $e) {
 echo $e->getMessage();
 }
 */
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
class Upload {
	protected $upload;
	protected $file_path;
	protected $upload_dir;
	protected $extension;
	protected $maxSize = 20000000; # octets
	protected $whiteListType = null;
	protected $blackListType = null;

	protected $file     = null;
	protected $tmp_name = null;
	protected $name		= null;
	protected $typeMime = null;
	protected $fileSize	= null;
	protected $error	= null;

	public function __construct($tmp_name,$name,$error=null) {
		$this->file = $this->tmp_name = $tmp_name;
		$this->name 	= $name;
		$this->error 	= $error;
		if ( $error ) $this->codeToMessage($error);
		$this->extension = $this->fileExt($name);
		 
		$this->whiteListType = array('type'=>array(),'subtype'=>array());
		$this->blackListType = array('type'=>array(),'subtype'=>array());
		$this->disallowType(array('application') ,array('php','pl','js','htm','xml','pif','exe'));
	}

	private function verifMime() {
	
		$t = new Info($this->file);
		$this->typeMime = $t->mime();
		$t = explode('/',$this->typeMime);
		$type = $t[0];
		$subtype = $t[1];
			
		// explicitement autorisée
		if ( !empty($this->whiteListType['type']) ) {
			if ( in_array($type,$this->whiteListType['type']) ) { 
				return true;
			}
		}
		if ( !empty($this->whiteListType['subtype']) ) {			
			if ( in_array($subtype,$this->whiteListType['subtype']) ) { 				
				return true;
			}
		}
				
		// le mode liste blanche n'est pas permissif
		if ( !empty($this->whiteListType['type']) || !empty($this->whiteListType['subtype']) ) {
			throw new Exception("type de fichier interdit :".$type.'/'.$subtype);
		}
		// explicitement interdit
		if ( !empty($this->blackListType['type']) ) {
			 if ( in_array($type,$this->blackListType['type']) ) {
				throw new Exception("type de fichier interdit :".$type.'/'.$subtype);
			 }
		}
		if ( !empty($this->blackListType['subtype']) ) {
			if ( in_array($subtype, $this->whiteListType['subtype']) ) {
				throw new Exception("type de fichier interdit :".$type);
			}
		}
		// si c'est pas interdit ...
		return true;
	}
	
	private function inByte($val) { 
		$val = trim($val);
	    $last = strtolower(substr($val,-1));
	    switch ($last) {
	       case 'm': return (int)$val * 1048576;
      	   case 'k': return (int)$val * 1024;
       	   case 'g': return (int)$val * 1073741824;
	    }	
	    return (int) $val;
	}
	
	public function maxSize($size) { 
		$this->maxSize = $this->inByte($size);
	}
	
	public function allowType(array $type,array $subtype) {
		$this->whiteListType = array('type'=>$type,'subtype'=>$subtype);
	}
	public function disallowType(array $type,array $subtype) {
		$this->blackListType = array('type'=>$type,'subtype'=>$subtype);
	}

	private function displayFileSize($size){
		if (is_numeric($size)){
			$decr = 1024; $step = 0;
			$prefix = array('Octets','Kilo Octets','Mega Octets','Giga octets','Tera Octets','Peta Octets');
			while(($size / $decr) > 0.9) {
				$size = $size / $decr;
				$step++;
			}
			return round($size,2).' '.$prefix[$step];
		}
		return 'NaN';
	}
	
	protected function setMaxSize($octetSize) { 
		$this->maxSize = $octetSize;
	}
	
	private function verifSize() {
		$s = $this->getFileSize();
		if (  $s > $this->maxSize ) {
			throw new Exception("fichier trop gros ! (octets) max: ".$this->displayFileSize($this->maxSize)." fichier: ".$this->displayFileSize($s));
		}
	}
	
	public function checkUpload() { 
		if ( !empty($this->error) ) {
			$e = array();
			$e[1] = 'Le fichier excède le poids autorisé par la directive upload_max_filesize de php.ini ';
			$e[2] = 'Le fichier excède le poids autorisé par le champ MAX_FILE_SIZE s\'il a été donné ';
			$e[3] = 'Le fichier n\'a été uploadé que partiellement';
			$e[4] = 'Aucun fichier n\'a été uploadé';
			
			if ( isset($e[$this->error]) ) $t = "Erreur : ".$e[$this->error];
			else $t = "Erreur code: ".$this->error;
			throw new Exception($t);
		}
		$this->verifSize();
		$this->verifMime();
	}

	public function isUploaded() {		
		if ( is_uploaded_file($this->tmp_name) ) return true;
		return false;
	}

	public function setUploadDir($dir=false) {
		if ( $dir ) $this->upload_dir = $this->trail_slash($dir);
		//echo $this->upload_dir;
		if ( !is_dir($this->upload_dir) ) {
			if ( !@mkdir($this->upload_dir, '0777', true) ) { 
				throw new Exception("répertoire de destination invalide: $this->upload_dir");
			}
		}
		if ( !is_writable($this->upload_dir) ) {
			throw new Exception('l\'écriture est interdite dans: '.$this->upload_dir);
		}
	}

	public function setName($name=false,$noOverwrite=0,$timestamp=null) {
		if ( $name ) {
			$sanit_name = Filter::fileName($name);
			$sanit_name = substr($sanit_name, 0,200);
			$this->extension = $this->fileExt($name);

			$pref='';
			if ( $timestamp ) $timestamp = date("YmdGi_");
			else $timestamp = '';
			if ( $noOverwrite ) {
				$c=0;
				while ( is_file($this->upload_dir.$timestamp.$pref.$sanit_name) ) {
					$pref = $c++.'_' ;
				}
			}
			$this->name = $timestamp.$pref.$sanit_name;			
			return $this->name;
		}
		if ( empty($this->name) ) throw new Exception("Le nom $name est invalide");
	}

	public function set($upload_dir=false,$name=false) {
		$this->setUploadDir($upload_dir);
		$this->setName($name);
	}

	public function move($name=false, $upload_dir=false, $checkUploaded=1) {
		$this->checkUpload();
		if ( $checkUploaded AND !$this->isUploaded() ) { 
			throw new Exception("fichier : $this->tmp_name , non trouvé sur le serveur");
		}
		$this->setUploadDir($upload_dir);
		if ( !is_writable($this->upload_dir) ) {
			throw new Exception('l\'écriture est interdite dans le repertoire de destination ');
		}		
		if ( $checkUploaded ) {
			if (!move_uploaded_file($this->tmp_name,$this->upload_dir.$this->name)) {
				@unlink($this->tmp_name);
				throw new Exception('Erreur pendant le déplacement du fichier!!');
			}
		}
		else { 
			if (!rename($this->tmp_name,$this->upload_dir.$this->name)) {
				@unlink($this->tmp_name);
				throw new Exception('Erreur pendant le déplacement du fichier!!');
			}
		}
		//@unlink($this->tmp_name);
		$this->file = $this->upload_dir.$this->name;
		$this->tmp_name = null;
		return $this->name;
	}

	private function trail_slash($dir) {
		return ( substr($dir, -1, 1) != '/' ) ? $dir.'/' : $dir;
	}
	//TODO fixit
	public function fileExt($filename) {
		$ext = explode(".", $filename);
		return array_pop($ext);
	}

	public function getPath() {
	    return $this->file;
	}
	
	public function getName() {
		return $this->name;
	}
	/*
	public function getTmp() {
		return $this->tmp_name;
	}*/
	public function getTypeMime() {
		if ( !$this->typeMime ) { 
			$t = new Info($this->file);
			$this->typeMime = $t->mime();
		}
		return $this->typeMime;
	}	
	
	public function getFileSize() {
		if ( !$this->fileSize ) $this->fileSize = filesize($this->file);
		return $this->fileSize;
	}
	
	public function getFileMd5() {
		return md5_file($this->file);
	}
	
	// found on php.net
	private function codeToMessage($code)  {
		if ( empty($code) ) return false;
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "The uploaded file exceeds the upload_max_filesize directive in php.ini";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "The uploaded file was only partially uploaded";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "No file was uploaded";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Missing a temporary folder";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Failed to write file to disk";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "File upload stopped by extension";
                break;
            default:
                $message = "Unknown upload error code: ".$code;
                break;
        }
        throw new Exception($message, 500);
    } 
}