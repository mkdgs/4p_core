<?php
namespace Fp\Core;
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
// php 5.2
// mbstring
//$sendmail = new Mail();
//$sendmail->from($C_glob['mail_from']);
//$sendmail->replyTo($C_glob['mail_reply']);
//$sendmail->returnPath($C_glob['mail_reply']);
//$sendmail->subject('Confirmer votre inscription');
//$sendmail->attachement('./images/logo.png');
//$sendmail->attachement('./images/bande.png');
//$sendmail->htmlAttachement('./images/logo.png','image/png','http://www.site.com/logo.png');
//$sendmail->text($mail_texte);
//$sendmail->html($mail_texte);
//$sendmail->to($mail,$pseudo);
class Mail {

	private  $subject;
	private  $hfrom;
	private  $hbcc;
	private  $hcc;
	private  $Xsender;
	private  $ErrorsTo;
	private  $XMailer = 'Ke_Mail';
	private  $XPriority = 3;
	private  $text;
	private  $html;
	private  $attachement     = array();
	private  $htmlattachement = array();
	private  $to              = array();
	private  $returnpath	  = ''; 
	private  $replyto		  = '';
	private  $recipient;
	private  $body;
	private  $headers;
	private  $charset = 'UTF-8';
	private  $boundary_related = '0020112009classmail_related';
	private  $boundary_alternative = '0020112009classmail_alternative';
	private  $boundary_mixed = '0020112009classmail_mixed';

	public function __construct() { }
	 
	public function to($to,$name='') {
		$tmp=$this->makeNamePlusAddress($to,$name);
		$this->to[] = array( 'mail'=>$to, 'nameplusmail' => $tmp );
		if ( !$this->replyto ) { 
			$this->replyTo($to);
		}
	}

	public function bcc($bcc,$name='') {
		$tmp=$this->makeNamePlusAddress($bcc,$name);
		if ( !empty($this->hbcc)) $this->hbcc.= ",";
		$this->hbcc.= $tmp;
	}

	public function cc($cc,$name='') {
		$tmp=$this->makeNamePlusAddress($cc,$name);
		if (!empty($this->hcc)) $this->hcc.= ",";
		$this->hcc.= $tmp;
	}

	public function subject($subject) {
		$this->subject = $subject;
	}
	public function text($text) {
		$this->text = mb_convert_encoding($text,'quoted-printable',$this->charset);
	}
	public function html($html) {
		$this->html = $html;
	}
	public function from($from,$name=null) {
		$tmp=$this->makeNamePlusAddress($from,$name);
		$this->hfrom = $tmp;
		if ( !$this->returnpath ) { 
			$this->returnpath = $this->hfrom;
		}
	}

	public function returnPath($return) {
		$this->returnpath = $return;
	}

	public function replyTo($replyto,$name=null) {
		$tmp=$this->makeNamePlusAddress($replyto,$name);
		$this->replyto = $tmp;
	}
	// les attachements
	public  function attachement($filename) {
		array_push ( $this -> attachement , array ( 'filename'=> $filename ) );
	}

	// les attachements html
	public  function htmlAttachement($filename,$contenttype='',$contentLocation='',$cid='') {
		 
		array_push($this->htmlattachement ,
		array ( 'filename'=>$filename ,
                    'cid'=>$cid ,
                    'cl'=>$contentLocation,
                    'contenttype'=>$contenttype )
		);
	}

	private function makeNamePlusAddress($address,$name) {
		if ( empty($name) ) {
			return $address;
		}
		return $name." <".$address.">";
	}
	
	private function writeattachement($attachement,$B) {
		$message = '';
		if ( !empty($attachement) ) {
			foreach($attachement as $AttmFile){
				$patharray = explode("/", $AttmFile['filename']);
				$FileName = $patharray[count($patharray)-1];
				$message .= "\n--".$B."\n";

				if ( !empty($AttmFile['cid']) || !empty($AttmFile['cl']) ) {
					$message .= "Content-Type: {$AttmFile['contenttype']};\n name=\"".$FileName."\"\n";
					$message .= "Content-Transfer-Encoding: base64\n";
					if ( $AttmFile['cid'] ) {
						$message .= "Content-ID: <{$AttmFile['cid']}>\n";
					}
					if ( $AttmFile['cl'] ) {
						$message .= "Content-Location: {$AttmFile['cl']}\n";
					}
					$message .= "Content-Disposition: inline;\n filename=\"".$FileName."\"\n\n";
				} else {
					$message .= "Content-Type: application/octetstream;\n name=\"".$FileName."\"\n";
					$message .= "Content-Transfer-Encoding: base64\n";
					$message .= "Content-Disposition: attachment;\n filename=\"".$FileName."\"\n\n";
				}

				$fd=fopen ($AttmFile['filename'], "rb");
				$FileContent=fread($fd,filesize($AttmFile['filename']));
				fclose ($fd);

				$FileContent = chunk_split(base64_encode($FileContent));
				$message .= $FileContent;
				$message .= "\n\n";
			}

		}
		return $message;
	}

	private function makebody() {
		$message='';
		if ( $this->text && !$this->html && empty($this->attachement) && empty($this->htmlattachement)  ) {
			$message.=$this->text."\n\n";
			$this->body = $message;
			return true;
		}
		else {
			$message .="\n--".$this->boundary_mixed."\n";
			$message .= "Content-Type: multipart/alternative;\r\n\t boundary=\"".$this->boundary_alternative."\"\r\n";
			if ( $this->text ) {
				$message .="\n--".$this->boundary_alternative."\n";
				$message.="Content-Type: text/plain; charset=\"$this->charset\"\n";
				$message.="Content-Transfer-Encoding: quoted-printable\n\n";
				$message.=$this->text."\n\n";
			}
			if ( $this->html ) {
				$message.="\n--".$this->boundary_alternative."\n";
				$message .= "Content-Type: multipart/related;\r\n\t boundary=\"".$this->boundary_related."\"\r\n";
				$message .="\n--".$this->boundary_related."\n";
				 
				$message.="Content-Type: text/html; charset=\"$this->charset\"\n";
				$message.="Content-Transfer-Encoding: base64\n\n";
				$message.=chunk_split(base64_encode($this->html))."\n\n";
				if (!empty($this->htmlattachement)) {
					$message.=$this->writeattachement( $this->htmlattachement,$this->boundary_related);
				}
				$message.="\n--".$this->boundary_related."--\n";
				 
			}
			$message.="\n--".$this->boundary_alternative."--\n";

			if (!empty($this->attachement)) {
				$message.=$this->writeattachement($this->attachement,$this->boundary_mixed);
			}
			$message.="\n--".$this->boundary_mixed."--\n";
		}
		$this->body = $message;
	}

	private function MakeHeaderField($Field,$Value) {
		return wordwrap($Field.": ".$Value, 78, "\n ")."\r\n";
	}

	private function AddField2Header($Field,$Value) {
		$this->headers .= $this->MakeHeaderField($Field,$Value);
	}

	private function makeheader() {
		$this->headers = '';
		if ( empty($this->to) ) {
			throw new Exception('Mail::makeheader() destinataire manquant');
		}
		if ( empty($this->subject) ) {
			throw new Exception('Mail::makeheader() sujet manquant');
		}

		# Date: Mon, 03 Nov 2003 20:48:06 +0100
		$this->AddField2Header("Date", date ('r'));

		if ( !empty($this->Xsender) ) { $this->AddField2Header("X-Sender",$this->Xsender); }
		else { $this->AddField2Header("X-Sender",$this->hfrom); }

		if ( !empty($this->ErrorsTo) ) { $this->AddField2Header("Errors-To",$this->ErrorsTo); }
		else { $this->AddField2Header("Errors-To",$this->hfrom); }

		if ( !empty($this->XMailer) ) $this->AddField2Header("X-Mailer",$this->XMailer);

		if ( !empty($this->XPriority) ) $this->AddField2Header("X-Priority",$this->XPriority);

		if ( !empty($this->hfrom) ) $this->AddField2Header("From",$this->hfrom);

		if ( !empty($this->returnpath) ) $this->AddField2Header("Return-Path",$this->returnpath);

		if ( !empty($this->replyto) ) $this->AddField2Header("Reply-To",$this->replyto);

		if ( !empty($this->hcc) ) $this->AddField2Header("Cc",$this->hcc);

		if ( !empty($this->hbcc) ) $this->AddField2Header("Bcc",$this->hbcc);
		$this->headers .= "MIME-Version: 1.0\n";
		if ( $this->text && !$this->html && empty($this->attachement) && empty($this->htmlattachement)  ) {
			$this->headers .= 'Content-Transfer-Encoding: quoted-printable'."\n";
			$this->headers .='Content-Type: text/plain; charset='.$this->charset."\n";
		}
		else {
			//$this->headers .= 'This is a multi-part message in MIME format.';
			$this->headers .= "Content-Type: multipart/mixed;\r\n\t boundary=\"".$this->boundary_mixed."\"\r\n";
		}

	}

	public function send() {
		$this->makebody();
		$this->makeheader();
		return $this->phpmail();
	}

	private function phpmail() {
		$i=0;		
		foreach( $this->to as $v ) {
			$this->recipient = $v['nameplusmail'];
			if ( mail($v['nameplusmail'], $this->subject, $this->body, $this->headers,'-f'.$this->returnpath ) ) {
				$i++;
			} 
		}
		return $i;
	}
}