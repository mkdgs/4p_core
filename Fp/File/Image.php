<?php
namespace Fp\File;
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
class Image {
	protected $functionCreate;
	protected $functionSave;
	public  $resTmp;
	public  $srcFile;

	
	public function upgrade_memory_limit($size) {
		$old = intval(ini_get('memory_limit'));
		$new = $old + $size;
		ini_set('memory_limit', $new.'M');
	}
	
	public function __construct($file,$typeMime=null) {
		if ( intval(ini_get('memory_limit')) < 32 ) ini_set('memory_limit', '32M');
		if ( !is_file($file) ) throw new Exception('fichier inexistant');			

		if ( !$typeMime ) { 
			$file_info = new Info($file);
			$this->type = $file_info->mime();
		}
		else $this->type = $typeMime;
		
		$this->srcFile = $file;
		$this->type = $typeMime;

		switch($this->type) {
			case "image/pjpeg":
			case "image/jpeg":
			case "image/jpg":
				$this->functionCreate = 'ImageCreateFromJpeg';
				$this->functionSave = 'ImageJpeg';
				break;
			case 'image/png':
			case "image/x-png":
				$this->functionCreate = 'ImageCreateFromPng';
				$this->functionSave = 'ImagePNG';
				break;
			case 'image/gif':
				$this->functionCreate = 'ImageCreateFromGif';
				$this->functionSave = 'ImageGif';
				break;
			default:
				throw new Exception('type de fichier non supporté');
		}
		$my=$this->functionCreate;
		if ( !$this->resTmp = $my($this->srcFile) ) throw new Exception(__METHOD__.' oops ');
		$this->getSize();
		imagesavealpha($this->resTmp, true);
	}
	
	private function createImage($width,$height) {
		$width = ( $width < 1 ) ? 1 : $width;
		$height = ( $height < 1 ) ? 1 : $height;
		$this->upgrade_memory_limit(ceil(2*round($width * $height/64)));
		$new = ImageCreateTrueColor($width,$height);	
		
		$bg_transparent_color = array('red' => 255, 'green' => 255, 'blue' => 253, 'alpha' => 127);
		$bg_transparent_color = imagecolorallocatealpha($new, $bg_transparent_color['red'], $bg_transparent_color['green'], $bg_transparent_color['blue'], $bg_transparent_color['alpha']);
		imagefill($new, 0, 0, $bg_transparent_color);
 		
 		imagesavealpha($new, true);
		imagealphablending($new, false);
		
		return $new;
	}
	
	private function setWorkImg($img) { 
		imagedestroy($this->resTmp);
		$this->resTmp=$img;
		$this->getSize();	
	}
	
	private function getSize() { 
		$this->width=imagesx($this->resTmp);
		$this->height=imagesy($this->resTmp);
	}
	

	
	public function __unsets() {
		@imagedestroy($this->resTmp);
		$this->resTmp=false;
	}

	public function watermark($watermark=false,$top=5,$left=5) {
		$this->watermarkImage($watermark,$left=5,$top=5);
	}

	public function set_type($type=false) {
		if ( $type ) {
			switch($type){
				case 'jpg':
				case 'jpeg':
					$this->functionSave = 'ImageJpeg';
					break;
				case 'png':
					$this->functionSave = 'ImagePNG';
					break;
				case 'gif':
					$this->functionSave = 'ImageGif';
					break;
				default:
					throw new Exception('type de fichier non supporté');
			}
		}
	}

	/**
	 * @TODO $name first and $dir second
	 * make fix separate of name and dir for testDir()
	 */
	public function save($dir=false,$name=false,$type=false, $quality=75) {
		imageinterlace($this->resTmp, true);
		$this->set_type($type);
		$my=$this->functionSave;
		if ( $my == 'ImagePNG' ) {
		    $quality = 9-round(( 9/100 )*$quality);
		}
		else if ( $my == 'ImageJpeg' ) {
		    $quality = $quality;
		}
		if ( !$my($this->resTmp, $dir.$name, $quality) ) throw new Exception($dir.$name);
	}
	
	protected function testDir($dir, $name) {
		if ( !"$dir" ) return;
		if ( !"$name" ) {
			$dir = explode('/', $dir);
			array_pop($dir);
			$dir = implode('/', $dir);
		}
		if ( !is_dir($dir) ) {
			if ( !mkdir($dir, '0777', true) ) {
				throw new Exception("répertoire de destination invalide: $dir");
			}
		}
		if ( !is_writable($dir) ) {
			throw new Exception('l\'écriture est interdite dans: '.$dir);
		}
	}

	public function delete_source() {
		@unlink($this->srcFile);
	}

	public function watermarkImage($watermark,$left=5,$top=5) {
		if ( !$watermark = imagecreatefrompng($watermark) ) {
			throw new Exception('erreur: image watermark');
		}
		$watermark_width = imagesx($watermark);
		$watermark_height = imagesy($watermark);
		$dest_x = imagesx($this->resTmp)-($watermark_width + $left);
		$dest_y = imagesy($this->resTmp)-($watermark_height + $top);
			
		imagecopymerge($this->resTmp, $watermark, $dest_x, $dest_y, 0, 0, $watermark_width, $watermark_height, 100);
		imagedestroy($watermark);
	}
	
	public function ratio_resize($width=0,$height=null) {
		if( !$height ) $height=$width;
		
		// paysage
		if ($this->height < $this->width) {
			$this->ratioResizeWidth($width);
		}
		// portrait
		elseif ($this->height > $this->width)	{
			$this->ratioResizeHeight($height);
		}
		// carre
		else {
			if ($height < $width) {
				$this->ratioResizeWidth($width);
			}
			else if ($height > $width) {
				$this->ratioResizeHeight($height);
			}
			else {
				$this->resize($width, $height);
			}
		}
	}

	public function ratioResizeHeight($height) {
		if ( !$height ) $height = 1;
		$ratio  = $this->width/$this->height;		
		$width  = ceil($height*$ratio);
		$this->resize($width,$height);
	}
	
	public function ratioResizeWidth($width) {
		if ( !$width ) $width = 1;
		$ratio  = $this->height/$this->width;		
		$height = ceil($width*$ratio);
		$this->resize($width,$height);		
	}
	
	function best_resize($width,$height) {		
		$max = ( intval($height) && $height < $width ) ? $height : $width;
		$this->ratio_resize($max);		
	}
	
	public function crop($x,$y,$width,$height) {
		$this->imageCopyResampled(0, 0, $x, $y, $width, $height, $width, $height);
	}

	public function resize($width=120,$height=120) {
		$this->imageCopyResampled(0, 0, 0, 0, $width, $height);
	}
	
	
	public function bestBYcenter($width,$height) {
		$max = ( intval($height) && $height < $width ) ? $height : $width;
		$this->ratio_resize($max);
	}
		
	public function centered_resize($width,$height) {


		
		if ( !$height ) $height = $this->height;
		if ( !$width )  $width = $this->height;
		$max = ( $height > $width ) ? $height : $width;	
		$this->ratio_resize($max);
		
		$d_x = round(($width - $this->width) /2);
		$d_y = round(($height - $this->height) /2);
		
		$s_x = 0;
		$s_y = 0;
		
		$img = $this->createImage($width, $height);
		ImageCopyResampled($img, $this->resTmp, $d_x, $d_y, $s_x, $s_y, $this->width, $this->height, $this->width, $this->height);
		$this->setWorkImg($img);
	}
	
	private function imageCopyResampled($d_x, $d_y, $s_x, $s_y, $d_width, $d_height, $s_width=null , $s_height=null) {
		if ( !$s_width  )  $s_width  = $this->width;
		if ( !$s_height )  $s_height = $this->height;
		$img = $this->createImage($d_width,$d_height);
		ImageCopyResampled($img, $this->resTmp, $d_x, $d_y, $s_x, $s_y, $d_width, $d_height, $s_width, $s_height);
		//imagecopy($img, $this->resTmp, $d_x, $d_y, $s_x, $s_y, $this->height, $this->width);
		$this->setWorkImg($img);
	}
}

	
	
	/*
	 * obsolete
	 * 
	function centered_resize($width,$height) {
		$max = ( $height > $width ) ? $height : $width;
		$this->ratio_resize($max);		
		//$this->resizeFromCenter($width,$height);
		$this->resizeCentered($width,$height);
	}
	public function resizeFromCenter($width,$height=false) {		
		$x_center =  round($this->width/2);
		$y_center =  round($this->height/2);
		$this->centerHere($x_center,$y_center,$width,$height);
	}
	public function centerHere($x_center,$y_center,$width,$height=false) {
		$dst_x = 0;
		$dst_y = 0;
		if ( !$height ) $height = $width;
		
		$at_x = $x_center - round($this->width/2);
		$at_y = $y_center - round($this->height/2);
		//$max_at_x =  $at_x + $width;
		//$max_at_y =  $at_y + $height;

		if ( $width > $this->width ) $at_x = round(($this->width-$width)/2);
		elseif ( $at_x < 0 ) $at_x = 0;
		
		if (  $height > $this->height ) $at_y= round(($this->height-$height)/2);
		elseif ( $at_y < 0 ) $at_y= 0;
	
		$this->imageCopyResampled( $at_x, 10, $dst_x, $dst_y, $width, $height, 10, $this->height);
	}*/
/*
 * 
 * 
 * 	
 * 	
 * public function zoomHere($x_center,$y_center,$width,$height,$resize_x,$resize_y=false) {
		if ( !$resize_y ) $resize_y = $resize_x;

		$at_x = $x_center - round($resize_x/2);
		$at_y = $y_center - round($resize_y/2);
		$max_at_x =  $at_x + $resize_x;
		$max_at_y =  $at_y + $resize_y;

		$this->getSize();	

		if ( $max_at_x > $this->width) {
			$at_x = $this->width-$resize_x;
		}
		elseif ( $at_x <= 0 ) $at_x = 0;
		
		if ( $max_at_y > $this->height ) 	$at_y= $this->height-$resize_y;
		elseif ( $at_y <= 0 ) $at_y= 0;

		$this->imageCopyResampled(0, 0, $at_x, $at_y, $width, $height , $resize_x ,$resize_y);
	}
USAGE
 $img = new Image($fichier,$image->get_type());

 $img->thumb(200);
 $img->watermark('watermark.png');
 $img->save('/dir/','thumb_'.$fichier);

 $img->thumb(550);
 $img->watermark('watermark.png');
 $img->save('/dir/',$fichier);
 */
//    function watermarkText ($file,$text,$top=5,$left=5) {
//
//          $image_p = imagecreatetruecolor($this->width, $this->height);
//          $my=$this->functionCreate;
//          $image = $my($file);
//          imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width, $height);
//          $bgcolor = imagecolorallocate($image_p, 0, 0, 0);
//          $font = 'arial.ttf';
//          $font_size = 10;
//          imagettftext($image_p, $font_size, 0, 10, 20, $bgcolor , $font, $text);
//          if ($DestinationFile<>'') {
//             imagejpeg ($image_p, $DestinationFile, 100);
//          } else {
//             header('Content-Type: image/jpeg');
//             imagejpeg($image_p, null, 100);
//          };
//    }