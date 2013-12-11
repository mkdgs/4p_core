<?php
namespace Module\Sample;
use Fp\Core\Core;
use Fp\Core\Filter;
use Fp\Db\Db;
use Fp\Table\Table;
use Fp\Table\Query as Table_Query;
use Fp\Module\Utils as Module_Utils;
use Fp\Template\TemplateData;

use \Exception;

class Model extends \Fp\Module\Model {

	public function __construct(Core $O) {}	
	
	
	
	
	public $table_action = 'campaign_action';
	
	public function listAction() {
	
	}
	
	
	/* delay action */
	
	
	public function actionGetOffset($action) {
	    $req = "SELECT offset FROM $this->table_action WHERE action='$action' limit 0,1";
	    return Db::query($req)->fetchColumn();
	}
	
	// ttl de l'action 10 minutes sans update est considéré comme terminé
	public function actionGetExpire($action) {
	    $expire=time()-(60*10);
	    $req = "SELECT time FROM $this->table_action WHERE action='$action' AND `time`<$expire limit 0,1";
	    return Db::query($req)->fetchColumn();
	}
	
	public function actionGet($action) {
	    $req = "SELECT * FROM $this->table_action WHERE action='$action' limit 0,1";
	    return Db::query($req)->fetchAssoc();
	}
	
	public function actionReset($action) {
	    $t = time();
	    $req = "UPDATE $this->table_action SET `offset`=0,`time`='$t' WHERE action='$action' limit 1";
	    return Db::query($req)->rowCount();
	}
	
	public function launch($action) {
	    $offset = $this->actionGetOffset($action);
	    $expire = $this->actionGetExpire($action);
	    $actionData  = $this->actionGet($action);
	    $max 	= $actionData['nb'];
	    $delay  = $actionData['delay'];
	    $exec   = $actionData['exec'];
	    $end    = $actionData['end'];
	    $offset = $actionData['offset']++;
	    $count  = 1;
	
	    if ( !$expire || !is_numeric($offset) ) {
	        //echo "error $action  offset: $offset expire: $expire \r\n";
	    }
	    $r =  "SELECT inc_count FROM $this->table_action WHERE action='$action' limit $offset,$max";
	    $r = Db::query($r);
	    while ( $offset <= $end && $count <= $max ) {
	        usleep($delay);
	        $this->exec($exec,$actionData);
	        $offset = $actionData['offset']++;
	        $count++;
	        Db::exec("UPDATE $this->table_action SET `offset`='$offset', `time`='".time()."', `inc_count`=inc_count+1 WHERE action='$action' limit 1");
	    }
	    return array(
	            'action' =>$action,
	            'offset' => $offset,
	            'count'	 => $count
	    );
	}
	
	public function register($action,$end,$exec,$delay=1000,$nb=1000) {
	    $id   = '';
	    $nom  = $action;
	    $exec = $exec;
	    $offset = 0;
	    $end = $end;
	    $inc_count = 1;
	    $time = time();
	    $req = "INSERT INTO $this->table_action VALUES('$nom','$exec','$offset','$end','$inc_count','$delay','$nb','$time')";
	    return Db::query($req)->rowCount();
	    // offset = 1
	    // inc = 1
	}
	
	public function exec($function,$actionData) {
	    try {
	        call_user_func_array($function, array($actionData));
	    } catch (Exception $e) {
	        echo $e->getMessage();
	    }
	}
}