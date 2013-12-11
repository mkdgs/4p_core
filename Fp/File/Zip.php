<?php
namespace Fp\File;
use \Exception;

class Zip {

	/**
	 * @param string $file zip archive to extract
	 * @param string $path where the archive is extracted (directory of zip archive if null)
	 * @throws \Exception
	 */
	public function unzip($file, $path=null) {
		if (!is_file($file) ) {
			throw new \Exception($file.' is not a file');
		}

		if( !is_readable($file) ) {
			throw new \Exception($file.' is not readable');
		}

		if ( $path === null ) {
			// get the absolute path to $file
			$path = pathinfo(realpath($file), PATHINFO_DIRNAME);
		}

		if ( !is_dir($path) ) {
			throw new \Exception($path.' is not a directory');
		}

		if ( !is_writeable($path) ) {
			throw new \Exception($path.' is not writeable');
		}

		$zip = new ZipArchive;
		$res = $zip->open($file);
		if ($res !== TRUE) {
			switch ($res) {
				case ZIPARCHIVE::ER_EXISTS:
					$msg = "Le fichier existe déjà.";
					break;

				case ZIPARCHIVE::ER_INCONS:
					$msg = "L'archive ZIP est inconsistante.";
					break;

				case ZIPARCHIVE::ER_INVAL:
					$msg = "Argument invalide.";
					break;

				case  ZIPARCHIVE::ER_MEMORY:
					$msg = "Erreur de mémoire.";
					break;

				case  ZIPARCHIVE::ER_NOENT:
					$msg = "Le fichier n'existe pas.";
					break;

				case  ZIPARCHIVE::ER_NOZIP:
					$msg = "N'est pas une archive ZIP.";
					break;

				case  ZIPARCHIVE::ER_OPEN:
					$msg = "Impossible d'ouvrir le fichier.";
					break;

				case  ZIPARCHIVE::ER_READ:
					$msg = "Erreur lors de la lecture.";
					break;

				case   ZIPARCHIVE::ER_SEEK:
					$msg = "Erreur de position.";
					break;
					 
				default:
					$msg = "unknow error";
					break;
			}
			throw new \Exception($msg);
		}

		$zip->extractTo($path);
		$zip->close();
	}

}