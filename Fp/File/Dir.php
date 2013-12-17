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
class Dir {
	Private static $dir_separator = '/';
	
	static public function create($dir,$chmod=700)  { 
		if ( !mkdir($dir,$chmod,true) ) { 
			throw new Exception('impossible de créer le repertoire: '.$dir,500);
		}
		return true;
	}
	
	static public function emptyDir($dir) {
	    $it = new \RecursiveDirectoryIterator($dir);
	    $files = new \RecursiveIteratorIterator($it,
	            \RecursiveIteratorIterator::CHILD_FIRST);
	    foreach($files as $file) {
	        if ($file->getFilename() === '.' || $file->getFilename() === '..') {
	            continue;
	        }
	        if ($file->isDir()){
	            rmdir($file->getRealPath());
	        } else {
	            unlink($file->getRealPath());
	        }
	    }
	}

	static public function remove($remove_path) {
		if (!file_exists($remove_path)) {
			return;
		}
		if (($path = realpath($remove_path)) !== FALSE) {
			if (@chmod($path, 0777) === FALSE) {
				throw new Exception(__CLASS__.'::'.__METHOD__.'()'." Can't delete directory $path. Permission denied.");
			}
			try {
				if (is_dir($path)) {
					$dh = opendir($path);
				} else {
					return;
				}
			}
			catch(Exception $e) {
				return;
			}
			while (($file = readdir($dh)) !== false) {
				if ($file != '.' && $file != '..') {
					if (is_dir($path . self::$dir_separator . $file)) {
						self::removeDirectory($path . self::$dir_separator . $file);
					}
					else {
						if (chmod($path . self::$dir_separator . $file, 0777) === FALSE) {
							throw new Exception(__CLASS__.'::'.__METHOD__.'()'." Can't delete file ".$path . self::$dir_separator . $file);
						}
						unlink($path . self::$dir_separator . $file);
					}
				}
			}
			closedir($dh);
			if (@rmdir($path) === FALSE) {
				throw new Exception(__CLASS__.'::'.__METHOD__.'()'."Can't delete directory ".$path);
			}
		}
	}

	/**
	 * List the first level of a directory
	 *
	 * @param string $path
	 * @todo add option to this method (level to scan, exclude file or not, juste directory, get more infomations...
	 * @return array
	 */
	static public function ls($path)	{
		$res = array();
		$dh = opendir($path);
		if (!$dh) {
			throw new Exception(__CLASS__.'::'.__METHOD__.'()'." Can't read directory ".$path);
		}
		while (($subDir = readdir($dh)) !== false) {
			if ($subDir != '.' && $subDir != '..') {
				array_push($res, $subDir);
			}
		}
		return $res;
	}


	/**
	 * Return true if the first path is include into the other path
	 *
	 * @param string $path1
	 * @param string $path2
	 * @return boolean
	 */
	static public function isIn($path1, $path2)	{
		$tmp1 = realpath($path1);
		$tmp2 = realpath($path2);
		$tmp1 = str_replace('\\', '/', $tmp1);
		$tmp2 = str_replace('\\', '/', $tmp2);
		if (!strncmp($tmp1, $tmp2, strlen($tmp2))) {
			return true;
		}
		return false;
	}

	/**
	 * Copy a file, or recursively copy a folder and its contents
	 *
	 * @author      Aidan Lister <aidan@php.net>
	 * @version     1.0.1
	 * @link        http://aidanlister.com/repos/v/function.copyr.php
	 * @param       string   $source    Source path
	 * @param       string   $dest      Destination path
	 * @return      bool     Returns TRUE on success, FALSE on failure
	 */
	static public function copyr($source, $dest)	{
		// Check for symlinks
		if (is_link($source)) {
			return symlink(readlink($source), $dest);
		}
		// Simple copy for a file
		if (is_file($source)) {
			return copy($source, $dest);
		}
		// Make destination directory
		if (!is_dir($dest)) {
			mkdir($dest);
		}
		// Loop through the folder
		$dir = dir($source);
		while (false !== $entry = $dir->read()) {
			// Skip pointers
			if ($entry == '.' || $entry == '..') {
				continue;
			}
			// Deep copy directories
			self::copyr("$source/$entry", "$dest/$entry");
		}
		// Clean up
		$dir->close();
		return true;
	}
	/**
	 *
	 * Calculate the size of a directory by iterating its contents
	 *
	 * @author      Aidan Lister <aidan@php.net>
	 * @version     1.2.0
	 * @link        http://aidanlister.com/repos/v/function.dirsize.php
	 * @param       string   $directory    Path to directory
	 */
	static function size($path)	{
		// O
		$size = 0;

		// Trailing slash
		if (substr($path, -1, 1) !== self::$dir_separator) {
			$path .= self::$dir_separator;
		}

		// Sanity check
		if (is_file($path)) {
			return filesize($path);
		} elseif (!is_dir($path)) {
			return false;
		}

		// Iterate queue
		$queue = array($path);
		for ($i = 0, $j = count($queue); $i < $j; ++$i)
		{
			// Open directory
			$parent = $i;
			if (is_dir($queue[$i]) && $dir = @dir($queue[$i])) {
				$subdirs = array();
				while (false !== ($entry = $dir->read())) {
					// Skip pointers
					if ($entry == '.' || $entry == '..') {
						continue;
					}

					// Get list of directories or filesizes
					$path = $queue[$i] . $entry;
					if (is_dir($path)) {
						$path .= self::$dir_separator;
						$subdirs[] = $path;
					} elseif (is_file($path)) {
						$size += filesize($path);
					}
				}

				// Add subdirectories to start of queue
				unset($queue[0]);
				$queue = array_merge($subdirs, $queue);

				// Recalculate stack size
				$i = -1;
				$j = count($queue);

				// Clean up
				$dir->close();
				unset($dir);
			}
		}

		return $size;
	}
}