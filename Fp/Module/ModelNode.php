<?php
namespace Fp\Module;
use Fp\Core\Core;
use Fp\Core\Date;
use Fp\Core\Filter;
use Fp\Permission\Permission;
use Fp\Db\Db;
use Fp\Table\Table;
use Fp\Table\Query as Table_Query;
use Fp\Module\Utils as Module_Utils;
use \FpModule\Media\Model as MediaModel;


abstract class ModelNode {
    /**
     * @var Table_query
     */
    public $dbNode;

    /**
     * @var Table_query
     */
    public $dbNodeRelation;

    /**
     * @var Table_query
     */
    public $dbNodeMedia;

    /**
     * @var Table_query
     */
    public $dbNodeLink;

    /**
     * @var Table_query
     */
    public $dbNodeTags;

    /**
     * @var Table_query
     */
    public $dbNodeRank;

    /**
     * @var Table_query
     */
    public $dbNodeRevisions;
    public $type_node;

    /**
     * @var Core
     */
    public $O;

    public $namespace = 'mod_Node_Model';

    abstract public function config();
    abstract protected function updateNodeExtend($id_node, $data, $old_node);

    public function __construct(Core $O) {
        $this->O = $O;
        $this->type_node = get_called_class();

        /* Db 2 */
        $tabledbNode   = $this->O->glob('prefix').'node';
        $columndbNode  = array('id_node','type_node','uid','gid','zid','etat','rank','data','date_creation','date_modification', 'date_publication');
        $this->tableNode  = Table::set(Db::get_link(), $tabledbNode, $columndbNode);
        $this->tableNode->setPrimary('id_node');
        $this->tableNode->setUnique(array());
        $this->tableNode->setSortable(array('id_node','uid','gid','zid','etat','rank','type_node','date_creation','date_modification','date_publication'));
        $this->tableNode->setSearchable(array (
                'id_node' => 'bigint',
                'uid' => 'bigint',
                'gid' => 'bigint',
                'type_node' => 'varchar',
                'date_creation' => 'timestamp',
                'date_modification' => 'timestamp',
                'date_publication' => 'timestamp'
        ));
        $this->tableNode->setAutoIncrement(true);
        $this->dbNode = new Table_query($this->tableNode,'n');

        //relation 1:n
        $tableRelation 	 = $this->O->glob('prefix').'node_relation';
        $columnRelation  = array('id_node_parent','id_node_enfant','position');
        $this->tableRelation  = Table::set(Db::get_link(), $tableRelation, $columnRelation);
        $this->tableRelation->setPrimary('id_node_parent');
        $this->tableRelation->setUnique(array());
        $this->tableRelation->setSortable(array('id_node_parent','position','id_node_enfant'));
        $this->tableRelation->setSearchable(array (
                'id_node_parent' => 'bigint',
                'position' => 'int',
                'id_node_enfant' => 'bigint',
        ));
        $this->tableRelation->setAutoIncrement();
        $this->dbNodeRelation = new Table_query($this->tableRelation,'r');

        $this->dbNodeRelation->innerJoin($this->tableNode, 'n', ' n.id_node=r.id_node_parent ');
        $this->dbNodeRelation->selectColumn("n.*");

        //link 1:1
        $tableLink 	 = $this->O->glob('prefix').'node_link';
        $columnLink  = array('id_node_1','id_node_2', 'position');
        $this->tableLink  = Table::set(Db::get_link(), $tableLink, $columnLink);
        $this->tableLink->setPrimary('id_node_1');
        $this->tableLink->setUnique(array());
        $this->tableLink->setSortable(array('id_node_1', 'id_node_2', 'position'));
        $this->tableLink->setSearchable(array (
                'id_node_1' => 'bigint',
                'id_node_2' => 'bigint',
                'position'  => 'int'
        ));
        $this->tableLink->setAutoIncrement();
        $this->dbNodeLink = new Table_query($this->tableLink,'l');
        $this->dbNodeLink->selectColumn("l.*");

        //table media
        $tableMedia 	 = $this->O->glob('prefix').'media_files';
        $columnMedia  = array('id_media','file_protocol','file_name','file_type','file_subtype','file_size', 'file_md5','file_date','ip','uid');
        $this->tableMedia  = Table::set(Db::get_link(), $tableMedia, $columnMedia);
        $this->tableMedia->setPrimary('id_media');
        $this->tableMedia->setUnique(array());
        $this->tableMedia->setSortable(array('id_media','file_name','type','subtype','date','uid','size'));
        $this->tableMedia->setSearchable(array (
                'id_media' => 'bigint',
                'file_name' => 'varchar',
                'type' => 'varchar',
                'subtype' => 'varchar',
                'date' => 'date',
                'uid' => 'bigint',
                'size' => 'bigint',
        ));
        $this->tableMedia->setAutoIncrement(true);
        $this->dbMedia = new Table_query($this->tableMedia,'media');

        $tableMedia 	 = $this->O->glob('prefix').'node_media';
        $columnMedia  = array('id','id_node','id_media','position');
        $this->tableNodeMedia  = Table::set(Db::get_link(), $tableMedia, $columnMedia);
        $this->tableNodeMedia->setPrimary('id');
        $this->tableNodeMedia->setUnique(array('id_node','id_media'));
        $this->tableNodeMedia->setSortable(array('id_node','position','id_media'));
        $this->tableNodeMedia->setSearchable(array (
                'id_node' => 'bigint',
                'position' => 'int',
                'id_media' => 'bigint',
        ));
        $this->tableNodeMedia->setAutoIncrement();

        $this->dbNodeMedia = new Table_query($this->tableNodeMedia,'nm');
        $this->dbNodeMedia->innerJoin($this->tableMedia, 'media', ' media.id_media=nm.id_media ');

        // node Tags
        $tableNodeTags 	 = $this->O->glob('prefix').'node_tags';
        $columnNodeTags  = array('id_node', 'tag', 'priority');
        $this->tableNodeTags  = Table::set(Db::get_link(), $tableNodeTags, $columnNodeTags);
        $this->tableNodeTags->setPrimary('tags');
        $this->tableNodeTags->setUnique(array());
        $this->tableNodeTags->setSortable(array('id_node','tag'));
        $this->tableNodeTags->setSearchable(array(
                'tag' => 'varchar',
                'id_node' => 'bigint',
                'priority'=> 'int'
        ));
        $this->tableNodeTags->setAutoIncrement();
        $this->dbNodeTags = new Table_query($this->tableNodeTags, 'tags');


        // node revisions
        $table 	 = 'node_revisions';
        $column  = array('id_version', 'id_node', 'uid', 'label', 'comment', 'md5', 'date_revision', 'data');
        $this->tableNodeRevisions = Table::set(Db::get_link(), $table, $column);
        $this->tableNodeRevisions->setPrimary('id_version');
        $this->tableNodeRevisions->setUnique(array());
        $this->tableNodeRevisions->setSortable(array('id_version',  'id_node', 'uid', 'label', 'date_revision'));
        //$this->tableNodeRevisions->setFilterRules('idesc', function() { return (time())*-1; });
        $this->tableNodeRevisions->setSearchable(array(
                'id_version'	=> 'bigint',
                'id_node'		=> 'bigint',
                'uid'			=> 'bigint',
                'label'		=> 'varchar',
                'date_revision' => 'timestamp'
        ));
        $this->tableNodeRevisions->setAutoIncrement(true);
        $this->dbNodeRevisions = new Table_query($this->tableNodeRevisions, 'revisions');

        $this->config();
    }

    /**
     * @param string $where
     * @return mixed
     */
    public function deleteNode($where) {
        $q = $this->dbNode->duplicate();
        $q->andWhere($where);
        // $where is not id
        //if ( $r = $q->delete() ) $this->O->event()->trigger($this->namespace.'.change');
        return $q->delete();
    }


    /**
     * @param string $start
     * @param string $end
     * @param unknown $orderBy
     * @param string $where
     * @param unknown $orSearch
     * @param unknown $andSearch
     * @param callable $function function args [Query $dbNode, array $args]
     * @return Ambigous <multitype:, multitype:array unknown_type number Ambigous <unknown_type, number> Ambigous <number, unknown> >
     */
    public function listNode($start=null, $end=null, $orderBy=array(), $where=null, $orSearch=array(), $andSearch=array(), $function=null) {
        if ( !$where ) $where = true;
        $q = $this->dbNode->duplicate()
        ->limitSelect($start, $end)
        ->orderBy($orderBy);
        $condition = $q->andWhere($where);



        if ( $function && is_callable($function) ) {
            $function($q, func_get_args());
        }
        else if ( $function ) { // backwards deprecated
            $q->groupBy($function);
        }

        if ( is_array($andSearch) && !empty($andSearch) ) {
            $condition->andWhere()->andSearch($andSearch);
        }

        // recherche texte
        if ( is_array($orSearch) && !empty($orSearch) ) {
            foreach ( $orSearch as $column => $s ) {
                $q->andWhere()->orSearch(array($column => $s));
            }
        }
        $total = 0;
        if ( $rows = $q->getAll() ) {
            $total = $q->foundRows();
            $rows  = $this->unserializeDataList($rows);
        }

        return Module_Utils::formatList($rows, $start, $end, $total);
    }

    /**
     * @param int $id_node
     * @param array $data
     */
    protected function updateNode($id_node, $data) {
        if ( !$old_node = $n = $this->getById($id_node) ) return;
        $tid = Db::startTransaction();
        $q = $this->dbNode->duplicate();
        $q->noJoin(true);
        $q->orderBy(array());
        $q->andWhere($id_node);
        if ( array_key_exists('id_node', $data) ) unset($data['id_node']);
        if ( array_key_exists('data',$data) ) {
            $data['data'] = array_merge((array) $n['data'], (array) $data['data']);
            $data['data'] = $this->serializeData($data['data']);
        }
        $r  = $q->update($data);
        $r2 = $this->updateNodeExtend($id_node,$data, $old_node);
        // only when change
        if ( $r || $r2 ) {
            $d = array('date_modification' => Date::fromUnixTime(time())->mysqlDateTime());
            $q->update($d);
            $this->onNodeChange($id_node, $data, $old_node);
        }
        Db::endTransaction($tid);
        return ( !$r || $r < $r2 ) ? $r2 : $r;
    }



    /**
     * @param int $id
     */
    public function getById($id_node) {
        if ( !$id_node ) return null;
        $q = $this->dbNode->duplicate()->limitSelect(1);
        $q->andWhere($id_node);
        	
        $rows = $q->getAssoc();
        $data =  $this->unserializeData($rows);
        if ( !empty($data) ) {
            $data['tags']				= $this->getTags($id_node);
            $data['media']				= $this->getMedia($id_node);
            $data['relation_link']		= $this->getLinks($id_node);
            $data['relation_parents']	= $this->getParents($id_node);
            $data['relation_children']	= $this->getChildren($id_node);
        }
        return $data;
    }

    /**
     * @param string $where
     * @param array $orderBy
     * @param int $start
     * @param int $end
     * @return mixed
     */
    public function getByWhere($where,$orderBy=array()) {
        $q = $this->dbNode->duplicate()
        ->limitSelect(1)
        ->orderBy($orderBy);
        $q->andWhere($where);
        $rows = $q->getAssoc();
        return $this->unserializeData($rows);
    }

    protected function serializeData($data) {
        return base64_encode(serialize($data));
    }

    protected function unserializeData($line) {
        if( is_string($line['data']) AND $d = base64_decode($line['data']) ) {
            try {
                $line['data'] = @unserialize($d);
            } catch (\Exception $e) {
                $line['data'] = array();
            }
        }
        return $line;
    }

    public function unserializeDataList($lines) {
        foreach ( $lines as $k => $v ) {
            $lines[$k] = $this->unserializeData($lines[$k]);
        }
        return $lines;
    }

    /**
     * @param int $id_node
     * @param int $uid
     * @param filename $group
     * @param filename $zone
     * @param int $etat
     * @param int $rank
     * @param array $data
     * @return int id_node
     * @throws Exception
     * create node, if id_node is specified it replace the existing
     */
    protected function createNode($id_node=null, $uid=null, $group=null, $zone=null, $etat=0, $rank=500, $data=array(), $date_publication=null) {
        if ( !$rank ) $rank = 500;
        if ( !$etat ) $etat = 0;
        if ( !trim($this->type_node) ) {
            throw new Exception('type_node is missing',500);
        }
        
        
        // transaction -> addGroupe
        try {
            
            try  {
                
                $tid = Db::startTransaction();
                	
                $permission = new Permission($this->O);

                //if ( $uid==null ) $uid = 'null';
                $gid = null;
                if ( $group ) {
                    if ( $permission->existGroup($group) )	$gid = $permission->idGroup($group);
                }

                $zid = null;
                if ( $zone ) {
                    if ( $permission->existGroup($group) )	$zid = $permission->idGroup($zone);
                }
                $date_creation = Date::fromUnixTime(time())->mysqlDateTime();
                	
                if ( !$date_publication ) $date_publication = $date_creation;

                $data = array (
                        'type_node'  	    => $this->type_node,
                        'uid'  			    => $uid,
                        'gid'  			    => $gid,
                        'zid'  			    => $zid,
                        'rank'			    => $rank,
                        'etat'  		    => $etat,
                        'data'  		    => filter::DbSafe($this->serializeData($data)),
                        'date_creation'     => $date_creation,
                        'date_modification' => $date_creation,
                        'date_publication' => $date_publication
                );
                	
                $req = $this->dbNode->duplicate();
                $r = null;
                if ( $id_node ) {
                    $r = $this->updateNode($id_node, $data);
                }
                	
                	
                if( !$r ) {
                    if ( $id_node ) $data['id_node'] = $id_node;
                    if ( $id = $req->insert($data) ) {
                        $id_node = ( $id_node ) ? $id_node : $id;
                    }
                }
               
                
               // echo '--- ??';
            } catch (\Exception $e ) {
               throw $e;
            }
            //echo '----- bug';
            Db::endTransaction($tid);
            //return $id_node;
        } catch (\Exception $e ) {
           // die('---');
            Db::rollback();
            throw $e;
        }
    }

    // Link
    private function id_ord($id_1,$id_2) {
        return ( $id_1 < $id_2 ) ? array($id_1,$id_2) : array($id_2,$id_1);
    }

    /**
     * @param int $id_node
     * @param int $id_node_2
     */
    public function removeLink($id_node,$id_node_2) {
        //@todo trigger seulement si le changement est fait
        $this->O->event()->trigger($this->namespace.'.change');

        $l = $this->id_ord($id_node,$id_node_2);
        $ids = array('id_node_1'  => $l[0],
                'id_node_2'  => $l[1]);

        $q = $this->dbNodeLink->duplicate();
        $q->andWhere($ids);
        return $q->delete();
    }

    /**
     * @param int $id_node_1
     * @param int $id_node_2
     */
    public function addLink($id_node_1, $id_node_2, $position=0) {
        $l = $this->id_ord($id_node_1,$id_node_2);
        $w = array('id_node_1'  => $l[0],
                'id_node_2'  => $l[1]);
        $req = $this->dbNodeLink->duplicate();
        $req->andWhere($w);
        $req->orderBy(array('position' => 'DESC'));
        if ( !$r = $req->getColumn()) {
            $w['position'] = 0;
            $req->insert($w);
            $this->setLinkPosition($id_node_1, $id_node_2);
            $this->O->event()->trigger($this->namespace.'.change');
        }
    }

    /**
     * @param int $id_node
     * @param classname $type_node
     */
    public function getLinks($id_node,$type_node=null) {
        $q = $this->dbNodeLink->duplicate();
        $q->selectColumn('n1.*');
        $q->orderBy(array('position' => 'ASC'));
        if ( $type_node ) {
            $q->andWhere("n1.type_node='$type_node' ");
        }
        $q->innerJoin($this->tableNode, 'n1 ', "n1.id_node=IF(l.id_node_1='$id_node', l.id_node_2, l.id_node_1) ");
        $q->andWhere(" l.id_node_1='$id_node'  OR  l.id_node_2='$id_node'  ");
        return $q->getAll();
    }

    /**
     * réordonne les relations, renvois la position max
     * @param int $id_node_1
     * @param int $id_node_2
     * @deprecated n'a pas de sens sur les liens
     */
    private function setLinkPosition($id_node_1, $id_node_2) {
        //@todo trigger seulement si le changement est fait
        $this->O->event()->trigger($this->namespace.'.change');
        $l = $this->id_ord($id_node_1, $id_node_2);

        $set = array('position'=>'@a:=@a+1');
        $q = $this->dbNodeLink->duplicate();
        $q->andWhere(array('id_node_1'=> $l[0], 'id_node_2'=> $l[1]));
        $q->orderBy(array('position'  => 'ASC'));
        $q->query('SET @a=-1');
        $q->noJoin(true);
        return $q->update($set, 'position');
    }

    /**
     * @param int $id_node_1
     * @param int $id_node_2
     * @param int $position
     * @param int $position_origine
     * @deprecated n'a pas de sens sur les liens
     */
    public function reOrderLink($id_node_1, $id_node_2, $position, $position_origine) {
        return true;
        if ( $position_origine == $position ) return;
        $this->setLinkPosition($id_node_1, $id_node_2);

        $l = $this->id_ord($id_node_1, $id_node_2);

        //@todo trigger seulement si le changement est fait
        $this->O->event()->trigger($this->namespace.'.change');

        $q = $this->dbNodeLink->duplicate();
        if ( $position_origine == $position ) return true;

        $qposition = $this->dbNode->quote($position);
        $qposition_origine = $this->dbNode->quote($position_origine);

        $t = $q->duplicate();
        $t->andWhere(array('id_node_1'=> $l[0], 'id_node_2'=> $l[1]));
        $t->noJoin(true);
        $t->orderBy(array());

        if ( $position_origine > $position ) {
            $set = array('position'=>'position+1');
            $t->andWhere(" ( position>=$qposition AND position<$qposition_origine ) ");
            $t->update($set,'position');
        }
        else {
            $set = array('position'=>'position-1');
            $t->andWhere(" ( position<=$qposition AND position>$qposition_origine ) ");
            $t->update($set,'position');
        }

        $t = $q->duplicate();
        $set = array('position'=>"$position");
        $t->limitUpdate(1);
        $t->andWhere(array('id_node_1'=> $l[0], 'id_node_2'=> $l[1]));
        $t->noJoin(true);
        $t->update($set);
        return true;
    }



    // RELATION

    /**
     * @param int $id_node
     * @param int $id_node_enfant
     */
    public function addChildren($id_node,$id_node_enfant) {
        $w = array('id_node_parent'  => $id_node,
                'id_node_enfant'  => $id_node_enfant);
        $data= array('position' => 0);
        $req = $this->dbNodeRelation->duplicate();
        $req->andWhere($w);
        $r = null;
        if ( $mfa = $req->getAssoc() ) {
            //if ( $mfa['position'] != $position ) { pourquoi ?
            $r = $req->update($data);
            //}
        }
        else {
            $data =array_merge($data, $w);
            $r = $req->insert($data);
        }
        if ( $r ) {
            $this->setRelationPosition($id_node);
            $this->O->event()->trigger($this->namespace.'.change');
        }
        return $r;
    }

    /**
     * @param int $id_node
     * @param int $id_node_enfant
     */
    public function removeChildren($id_node,$id_node_enfant) {
        $w = array('id_node_parent'  => $id_node,
                'id_node_enfant'  => $id_node_enfant);
        $q = $this->dbNodeRelation->duplicate();
        $q->andWhere($w);
        $r = $q->delete();
        if ( $r ) {
            $this->setRelationPosition($id_node);
            $this->O->event()->trigger($this->namespace.'.change');
        }
        return $r;
    }

    /**
     * @param int $id_node
     */
    public function removeAllChildren($id_node) {
        $w = array('id_node_parent'  => $id_node);
        $q = $this->dbNodeRelation->duplicate();
        $q->andWhere($w);
        $r = $q->delete();
        if ( $r ) $this->O->event()->trigger($this->namespace.'.change');
        return $r;
    }

    /**
     * @param int $id_node
     * @param classname $type_node
     */
    public function getParents($id_node,$type_node=null) {
        $q = $this->dbNodeRelation->duplicate();
        if ( $type_node ) $q->andWhere(" n.type_node='$type_node' ");
        $q->andWhere(" r.id_node_enfant='$id_node' ");
        return $q->getAll();
    }

    /**
     * @param int $id_node
     * @param classname $type_node
     */
    public function getChildren($id_node,$type_node=null, $function=null) {
        $q = $this->dbNode->duplicate();
        $q->innerJoin($this->tableRelation, 'r', ' n.id_node=r.id_node_enfant ');

        if ( $function && is_callable($function) ) {
            $function($q, func_get_args());
        }

        if ( $type_node ) $q->andWhere(array('n.type_node'=>$type_node));
        $q->andWhere(array('r.id_node_parent'=>$id_node));
        $q->orderBy(array('r.position'=>'asc'));

        return $q->getAll();
    }

    /**
     * réordonne les relations, renvois la position max
     * @param int $id_node
     */
    private function setRelationPosition($id_node) {
        //@todo trigger seulement si le changement est fait
        $this->O->event()->trigger($this->namespace.'.change');

        $set = array('position'=>'@a:=@a+1');
        $q = $this->dbNodeRelation->duplicate();
        $q->noJoin(true);
        $q->andWhere(array('id_node_parent'=> $id_node));
        $q->orderBy(array('position'=>'ASC'));
        $q->query('SET @a=-1');
        $q->noJoin(true);
        return $q->update($set,'position');
    }

    /**
     * @param int $id_node
     * @param int $id_node_enfant
     * @param int $position
     * @param int $position_origine
     */
    public function reOrderRelation($id_node, $id_node_enfant, $position, $position_origine) {

        if ( $position_origine == $position ) return;
        $this->setRelationPosition($id_node);


        $q = $this->dbNodeRelation->duplicate();
        if ( $position_origine == $position ) return true;
        $qid_node = $this->dbNode->quote($id_node);
        $qid_node_enfant = $this->dbNode->quote($id_node_enfant);
        $qposition = $this->dbNode->quote($position);
        $qposition_origine = $this->dbNode->quote($position_origine);

        if ( $position_origine > $position ) {
            $t = $q->duplicate('position');
            $set = array('position'=>'position+1');
            $t->andWhere(" id_node_parent=$qid_node AND ( position>=$qposition AND position<$qposition_origine ) ");
            $t->noJoin(true);
            $t->update($set,'position');
        }
        else {
            $t = $q->duplicate('position');
            $set = array('position'=>'position-1');
            $q->orderBy(array());
            $t->andWhere(" id_node_parent=$qid_node AND ( position<=$qposition AND position>$qposition_origine ) ");
            $t->noJoin(true);
            $t->update($set,'position');
        }

        $set = array('position'=>"$position");
        $t = $this->dbNodeRelation->duplicate();
        $t->limitUpdate(1);
        $t->andWhere(" id_node_parent=$qid_node AND id_node_enfant=$qid_node_enfant ");
        $t->noJoin(true);
        $t->update($set);

        //@todo trigger seulement si le changement est fait
        $this->O->event()->trigger($this->namespace.'.change');
        return true;
    }

    //MEDIA
    /**
    * @param int $id_node
    */
    public function getMedia($id_node) {
        $q = $this->dbNodeMedia->duplicate();
        $q->orderBy(array('position'=>'ASC'));
        $q->andWhere(array('nm.id_node'=>$id_node));
        return $q->getAll();
    }

    /**
     * @param int $id_node
     * @param int $id_media
     * @param int $position
     */
    public function addMedia($id_node,$id_media,$position=null) {
        //@todo trigger seulement si le changement est fait
        $this->O->event()->trigger($this->namespace.'.change');

        $media = new MediaModel($this->O);
        if( !$position ) $position = $this->setMediaPosition($id_node);
        $media->referenceIncrement($id_media, $id_node, $this->tableNode->table);
        $data = array('id' => '',
                'id_node'=>$id_node,
                'id_media' =>$id_media,
                'position' =>$position);
        $w = array('id_node'=>$id_node,
                'id_media' =>$id_media);

        $req = $this->dbNodeMedia->duplicate();
        $req->andWhere($w);
        if ( !$req->getColumn() ) {
            $req->insert($data);
        }
    }

    /**
     * @param int $id_node
     * @param int $id_media
     */
    public function deleteMedia($id_node, $id_media) {
        //@todo trigger seulement si le changement est fait
        $this->O->event()->trigger($this->namespace.'.change');

        $media = new MediaModel($this->O);
        $q = $this->dbNodeMedia->duplicate();
        $w = array('nm.id_node'=>$id_node,'nm.id_media'=>$id_media);
        $q->andWhere($w);
        if ( $r = $q->delete() ) $media->referenceDecrement($id_media, $id_node, $this->tableNode->table);
        return $r;
    }

    // réordonne les media, renvois la position max
    /**
    * @param int $id_node
    */
    private function setMediaPosition($id_node) {
        //@todo trigger seulement si le changement est fait
        $this->O->event()->trigger($this->namespace.'.change');

        $set = array('position'=>'@a:=@a+1');
        $q = $this->dbNodeMedia->duplicate();
        $q->query('SET @a=-1');
        $q->andWhere(array('id_node'=>$id_node));
        $q->orderBy(array('position'=>'ASC'));
        $q->noJoin(true);
        return $q->update($set, 'position');
    }

    /**
     * @param int $id_node
     * @param int $id_media
     * @param int $position
     * @param int $position_origine
     */
    public function reOrderMedia($id_node, $id_media, $position, $position_origine) {
        if ( $position_origine == $position ) return true;

        //@todo trigger seulement si le changement est fait
        $this->O->event()->trigger($this->namespace.'.change');

        $this->setMediaPosition($id_node);
        $q = $this->dbNodeMedia->duplicate();
        $qid_node = $this->dbNodeMedia->quote($id_node);
        $qid_media = $this->dbNodeMedia->quote($id_media);
        $qposition = $this->dbNodeMedia->quote($position);
        $qposition_origine = $this->dbNodeMedia->quote($position_origine);
        if ( $position_origine > $position ) {
            $t = $q->duplicate();
            $set = array('position'=> 'position+1');
            $t->andWhere(" id_node=$qid_node AND ( position>=$qposition AND position<$qposition_origine ) ");
            $t->noJoin(true);
            $t->update($set, 'position');
        }
        else {
            $t = $q->duplicate();
            $set = array('position'=> 'position-1');
            $t->andWhere(" id_node=$qid_node AND ( position<=$qposition AND position>$qposition_origine ) ");
            $t->noJoin(true);
            $t->update($set, 'position');
        }
        $t = $q->duplicate()->limitUpdate(1);
        $set = array('position'=> $position);
        $t->andWhere(" id_node=$qid_node AND id_media=$qid_media ");
        $t->noJoin(true);
        $t->update($set);
        return true;
    }

    /*
     *  NODE TAGS
    */

    public function addTag($id_node, $tag) {
        $data = array('tag'=>$tag,'id_node'=>$id_node);
        return $this->dbNodeTags->insertIgnore($data);
    }

    public function deleteTag($id_node,$tag) {
        $req = $this->dbNodeTags->duplicate();
        $req->andWhere(array('tag'=>$tag,'id_node'=>$id_node));
        return $req->delete();
    }

    public function getTags($id_node, $start=0, $end=null) {
        $req = $this->dbNodeTags->duplicate();
        $req->andWhere(array('id_node'=>$id_node));
        $req->selectColumn('tag');
        $req->selectFunction("count('tags') as ct");
        $req->limitSelect($start,$end);
        $req->groupBy('tags.tag');
        $req->orderBy(array('priority'=>'DESC'));
        return $req->getAllColumn();
    }

    public function listTagsByNode($where, $start=0, $end=null) {
        $orderBy = array('tags.priority'=>'DESC');
        if ( !$where ) $where = true;
        $q = $this->dbNode->duplicate()
        ->innerJoin($this->dbNodeTags->dbTable, 'tags', 'tags.id_node=n.id_node')
        ->selectColumn('tags.*',false)
        ->groupBy('tags.tag')
        ->limitSelect($start, $end)
        ->orderBy($orderBy);
        $q->andWhere($where);

        $rows  = $q->getAll(array('sql_calc_found_rows'));
        $total = $q->foundRows();
        return Module_Utils::formatList($rows, $start, $end, $total);
    }

    public function attachTags($id_node, array $tags) {
        $tag_list = array();
        foreach ( $tags as $v ) {
            if ( $v = trim(mb_strtolower($v,'UTF-8')) ) {
                $tag_list[]	= $v;
            }
        }

        $TagsToDelete = array();
        $old_t = $this->getTags($id_node);
        if (  is_array($old_t) ) {
            $TagsToDelete = array_diff($old_t, $tag_list);
            foreach ( $TagsToDelete as $tag) {
                $this->deleteTag($id_node, Filter::dbSafe($tag));
            }
            $TagsToAdd = array_diff($tag_list,$old_t);
        }
        else {
            $TagsToAdd = $tag_list;
        }

        foreach ( $TagsToAdd as $tag ) {
            $this->addTag($id_node, $tag);
        }
    }

    public function listNodeByTags(array $tags, $start=null, $end=null, $orderBy = array('n.date_modification' => 'DESC','n.date_creation' => 'DESC'), $where=null, $orSearch = array(), $andSearch = array(), $groupBy=null) {
        if ( !$where ) $where = true;
        $q = $this->dbNode->duplicate()
        ->limitSelect($start, $end)
        ->orderBy($orderBy);
        $q->innerJoin($this->dbNodeTags->dbTable, 'tags', 'tags.id_node=n.id_node');

        if ( $groupBy ) {
            $q->groupBy($groupBy);
        }

        if ( empty($tags) && empty($where) ) {
            $q->andWhere(true);
        }
        else {
            $q->andWhere($where);
            foreach ($tags as $v ) {
                $q->andWhere(array('tag'=> $v));
            }
        }
        	
        $total = 0;
        if ( $rows  = $q->getAll() ) {
            $total = $q->foundRows();
            $rows = $this->unserializeDataList($rows);
        }
        return Module_Utils::formatList($rows, $start, $end, $total);
    }


    /*
     *  NODE REVISIONS
    */
    protected function onNodeChange($id_node, $node, $old_node) {
        $this->O->event()->trigger($this->namespace.'.node.change');
        $uid = $this->O->auth()->uid();

        $label    = Filter::text('node.revision.label', $node, 'revision');
        $comment  = Filter::text('node.revision.comment', $node);

        $this->addRevision($id_node, $uid, $label, $comment, Date::fromStrtotime(time())->mysqlDateTime(), $old_node);
    }

    public function restoreRevision($id_version) {
        if ( $d = $this->getRevision($id_version) ) {
            $d['data']['node.revision.label']   = 'restore '.$id_version;
            $d['data']['node.revision.comment'] = 'restore '.$id_version;
            $this->updateNode($d['id_node'], $d['data']);
        }
    }

    public function addRevision($id_node, $uid=null, $label="revision", $comment=null, $date_revision=null, $data=array()) {
        $data = array(
                'id_node'  		=> $id_node,
                'uid'			=> $uid,
                'label'  		=> $label,
                'comment'  		=> $comment,
                'date_revision' => $date_revision,
                'data'  		=> $s = json_encode($data),
                'md5'			=> md5($s) );
        return $this->dbNodeRevisions->insert($data);
    }

    public function getRevisionById($id_version) {
        $r = $this->dbNodeRevisions->duplicate();
        $r->andWhere($id_version);
        if ( $r = $r->getAssoc() ) {
            $r['data'] = json_decode($r['data'], true);
            return $r;
        }
    }

    /**
     * @param unknown $id_node
     * @param number $index
     */
    public function getRevision($id_node, $index=0) {
        $r = $this->dbNodeRevisions->duplicate()
        ->orderBy(array('id_version'=>'DESC'))
        ->limitSelect($index,1);
        $r->andWhere(array('id_node'=>$id_node));
        if ( $r = $r->getAssoc() ) {
            $r['data'] = json_decode($r['data'], true);
            return $r;
        }
    }

    /**
     * @deprecated use getRevision
     */
    public function getRevisionByIndex($id_node, $index=0) {
        $this->getRevision($id_node, $index);
    }

    public function removeRevision($id_version) {
        $r = $this->dbNodeRevisions->duplicate();
        $r->andWhere($id_version);
        return $r->remove();
    }

    public function listRevisions($id_node, $start=0, $end=10, $search=array()) {
        $req = $this->dbNodeRevisions->duplicate();
        $req->orderBy(array('id_version'=>'DESC'));
        $req->andWhere(array('id_node'=>$id_node));
        $req->limitSelect($start,$end);
        $rows  = $req->getAll();
        $total = $req->foundRows();
        return Module_Utils::formatList($rows, $start, $end, $total);
    }


    // ACCESS
    /*
    public function canView($arrayOfid_Tags) {

    }
    public function canEdit($arrayOfid_Tags) {

    }
    public function setUid($id_node,$uid) {

    }
    public function setZid($id_node,$zid) {

    }
    public function setEtat($id_node,$etat) {

    }*/
}