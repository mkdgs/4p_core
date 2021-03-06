<?php
namespace Fp\Permission;
use Fp\Core;

require_once __DIR__.'/Permission.php';
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
class PermissionUser extends Permission { 
	private $uid = 0;
	 
	public function __construct(\Fp\Core\Init $O) {
		parent::__construct($O);		
		if ( $O->auth()->isAuth() ) $this->uid   = $O->auth()->uid();			
	}

	/*
	 *   _____
	 *   ZONES
	 * 
	 */
	
	/* can */
	public function canAdministrationZone($zone) { 
		return $this->hasAccessAdministration($c_gids = null, $zone, $this->uid);
	}

	public function canGestionZone($zone) { 

		return $this->hasAccessGestion($c_gids = null, $zone, $this->uid);
	}
	
	public function canEditionZone($zone) { 
		return $this->hasAccessEdition($c_gids = null, $zone, $this->uid);
	}
	
	public function canPublicationZone($zone) { 
		return $this->hasAccessPublication($c_gids = null, $zone, $this->uid);
	}
	
	public function canConsultationZone($zone) { 
		return $this->hasAccessConsultation($c_gids = null, $zone, $this->uid);
	}
		
	/* ban */	
	public function isBanZone($zone) { 
		return $this->hasAccessBan($c_gids = null, $zone, $this->uid);
	}
 		
	
	/*
	 * 
	 *  a refaire
	 */
	/*
	 *   ______
	 *   GROUPS
	 * 
	 */
	public function addToGroup($group, $level=null) {
		if ( !$level ) {
			$level = Permission::levelConsultation();
		}
		return parent::setUserInGroup($this->uid, $group, $level);
	}
	
	public function isInGroup($group) { 
		return parent::isUserInGroup($this->uid,$group);
	}
	
	public function removeFromGroup($group) { 
		return parent::removeUserFromGroup($this->uid, $group);
	}
	
	
	/* can */
	public function canAdministrationGroup($group) { 
		$gid = $this->sanitizeIdGroup($group);
		return $this->hasAccessAdministration($gid);	
	}
	
	public function canGestionGroup($group) { 
		$gid = $this->sanitizeIdGroup($group);
	 	return $this->hasAccessGestion($gid);		
	}
	
	public function canEditionGroup($group) { 
		$gid = $this->sanitizeIdGroup($group);
		return $this->hasAccessEdition($gid);
	}
	
	public function canPublicationGroup($group) { 
		$gid = $this->sanitizeIdGroup($group);
		return $this->hasAccessPublication($gid);
	}
	
	public function canConsultationGroup($group) { 
		$gid = $this->sanitizeIdGroup($group);
		return $this->hasAccessConsultation($gid);
	}
		
	/* ban */	
	public function isBanGroup($group) { 
	    $gid = $this->sanitizeIdGroup($group);
	    return $this->hasAccessBan($gid);
	}			
}