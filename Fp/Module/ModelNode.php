<?php

namespace Fp\Module;

use Fp\Core;
use Fp\Core\Date;
use Fp\Core\Filter;
use Fp\Permission\Permission;
use Fp\Db\Db;
use Fp\Table\Table;
use Fp\Table\Query as Table_query;
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
     * @var Table_query
     */
    public $dbNodeDataBlob;

    /**
     * @var Table_query
     */
    public $dbNodeDataChar;

    /** @var \Fp\Core\Init */
    public $O;
    public $namespace;

    /**
     * @param Core\Init $O init instance
     * @return static
     * */
    public static function getInstance(\Fp\Core\Init $O) {
        $classname = get_called_class();
        if (!$c = $O->getInstance($classname)) {
            $c = new $classname($O);
            $O->setInstance($classname, $c);
        }
        return $c;
    }

    abstract public function config();

    abstract protected function updateNodeExtend($id_node, $data, $old_node);

    public function __construct(\Fp\Core\Init $O) {
        $this->O = $O;
        $this->namespace = $this->type_node = get_called_class();

        $dbLink = $O->db();

        /* Db 2 */
        $tabledbNode = $this->O->glob('prefix') . 'node';
        $columndbNode = array('id_node', 'type_node', 'uid', 'gid', 'zid', 'etat', 'rank', 'data', 'date_creation', 'date_modification', 'date_publication');
        $this->tableNode = Table::set($dbLink, $tabledbNode, $columndbNode);
        $this->tableNode->setPrimary('id_node');
        $this->tableNode->setUnique(array());
        $this->tableNode->setSortable(array('id_node', 'uid', 'gid', 'zid', 'etat', 'rank', 'type_node', 'date_creation', 'date_modification', 'date_publication'));
        $this->tableNode->setSearchable(array(
            'id_node' => 'bigint',
            'uid' => 'bigint',
            'gid' => 'bigint',
            'type_node' => 'varchar',
            'date_creation' => 'timestamp',
            'date_modification' => 'timestamp',
            'date_publication' => 'timestamp'
        ));
        $this->tableNode->setAutoIncrement(true);
        $this->dbNode = new Table_query($this->tableNode, 'n');

//relation 1:n
        $tableRelation = $this->O->glob('prefix') . 'node_relation';
        $columnRelation = array('id_node_parent', 'id_node_enfant', 'position');
        $this->tableRelation = Table::set($dbLink, $tableRelation, $columnRelation);
        $this->tableRelation->setPrimary('id_node_parent');
        $this->tableRelation->setUnique(array());
        $this->tableRelation->setSortable(array('id_node_parent', 'position', 'id_node_enfant'));
        $this->tableRelation->setSearchable(array(
            'id_node_parent' => 'bigint',
            'position' => 'int',
            'id_node_enfant' => 'bigint',
        ));
        $this->tableRelation->setAutoIncrement();
        $this->dbNodeRelation = new Table_query($this->tableRelation, 'r');

        $this->dbNodeRelation->innerJoin($this->tableNode, 'n', ' n.id_node=r.id_node_parent ');
        $this->dbNodeRelation->selectColumn("n.*");

//link 1:1
        $tableLink = $this->O->glob('prefix') . 'node_link';
        $columnLink = array('id_node_1', 'id_node_2', 'position');
        $this->tableLink = Table::set($dbLink, $tableLink, $columnLink);
        $this->tableLink->setPrimary('id_node_1');
        $this->tableLink->setUnique(array());
        $this->tableLink->setSortable(array('id_node_1', 'id_node_2', 'position'));
        $this->tableLink->setSearchable(array(
            'id_node_1' => 'bigint',
            'id_node_2' => 'bigint',
            'position' => 'int'
        ));
        $this->tableLink->setAutoIncrement();
        $this->dbNodeLink = new Table_query($this->tableLink, 'l');
        $this->dbNodeLink->selectColumn("l.*");

//table media
        $tableMedia = $this->O->glob('prefix') . 'media_files';
        $columnMedia = array('id_media', 'file_protocol', 'file_name', 'file_type', 'file_subtype', 'file_size', 'file_md5', 'file_date', 'ip', 'uid');
        $this->tableMedia = Table::set($dbLink, $tableMedia, $columnMedia);
        $this->tableMedia->setPrimary('id_media');
        $this->tableMedia->setUnique(array());
        $this->tableMedia->setSortable(array('id_media', 'file_name', 'type', 'subtype', 'date', 'uid', 'size'));
        $this->tableMedia->setSearchable(array(
            'id_media' => 'bigint',
            'file_name' => 'varchar',
            'type' => 'varchar',
            'subtype' => 'varchar',
            'date' => 'date',
            'uid' => 'bigint',
            'size' => 'bigint',
        ));
        $this->tableMedia->setAutoIncrement(true);
        $this->dbMedia = new Table_query($this->tableMedia, 'media');

        $tableMedia = $this->O->glob('prefix') . 'node_media';
        $columnMedia = array('id', 'id_node', 'id_media', 'position');
        $this->tableNodeMedia = Table::set($dbLink, $tableMedia, $columnMedia);
        $this->tableNodeMedia->setPrimary('id');
        $this->tableNodeMedia->setUnique(array('id_node', 'id_media'));
        $this->tableNodeMedia->setSortable(array('id_node', 'position', 'id_media'));
        $this->tableNodeMedia->setSearchable(array(
            'id_node' => 'bigint',
            'position' => 'int',
            'id_media' => 'bigint',
        ));
        $this->tableNodeMedia->setAutoIncrement();

        $this->dbNodeMedia = new Table_query($this->tableNodeMedia, 'nm');
        $this->dbNodeMedia->innerJoin($this->tableMedia, 'media', ' media.id_media=nm.id_media ');

// node Tags
        $tableNodeTags = $this->O->glob('prefix') . 'node_tags';
        $columnNodeTags = array('id_node', 'tag', 'priority');
        $this->tableNodeTags = Table::set($dbLink, $tableNodeTags, $columnNodeTags);
        $this->tableNodeTags->setPrimary('tags');
        $this->tableNodeTags->setUnique(array());
        $this->tableNodeTags->setSortable(array('id_node', 'tag'));
        $this->tableNodeTags->setSearchable(array(
            'tag' => 'varchar',
            'id_node' => 'bigint',
            'priority' => 'int'
        ));
        $this->tableNodeTags->setAutoIncrement();
        $this->dbNodeTags = new Table_query($this->tableNodeTags, 'tags');


// node revisions
        $table = 'node_revisions';
        $column = array('id_version', 'id_node', 'uid', 'label', 'comment', 'md5', 'date_revision', 'data');
        $this->tableNodeRevisions = Table::set($dbLink, $table, $column);
        $this->tableNodeRevisions->setPrimary('id_version');
        $this->tableNodeRevisions->setUnique(array());
        $this->tableNodeRevisions->setSortable(array('id_version', 'id_node', 'uid', 'label', 'date_revision'));

        $this->tableNodeRevisions->setSearchable(array(
            'id_version' => 'bigint',
            'id_node' => 'bigint',
            'uid' => 'bigint',
            'label' => 'varchar',
            'date_revision' => 'timestamp'
        ));
        $this->tableNodeRevisions->setAutoIncrement(true);
        $this->dbNodeRevisions = new Table_query($this->tableNodeRevisions, 'revisions');


        $shemaNodeDataBlob = array('table' => 'node_data_blob',
            'column' => array(
                'id_node_data_blob' => array('type' => 'bigint', 'primary' => 1, 'sortable' => 1, 'searchable' => 1),
                'id_node' => array('type' => 'bigint',  'sortable' => 1, 'searchable' => 1),
                'key_name' => array('type' => 'varchar', 'sortable' => 1, 'searchable' => 1),
                'data' => array('type' => 'longblob'),
        ));

        $this->tableNodeDataBlob = Table::setTable($dbLink, $shemaNodeDataBlob);
        $this->tableNodeDataBlob->setPrimary('id_node_data_blob');
        $this->tableNodeDataBlob->setAutoIncrement(true);
        $this->dbNodeDataBlob = new Table_query($this->tableNodeDataBlob);

        $shemaNodeDataChar = array('table' => 'node_data_char',
            'column' => array(
                'id_node_data_char' => array('type' => 'bigint', 'primary' => 1, 'sortable' => 1, 'searchable' => 1),
                'id_node' => array('type' => 'bigint',  'sortable' => 1, 'searchable' => 1),
                'key_name' => array('type' => 'varchar', 'sortable' => 1, 'searchable' => 1),
                'data' => array('type' => 'varchar', 'sortable' => 1, 'searchable' => 1),
        ));
        $this->tableNodeDataChar = Table::setTable($dbLink, $shemaNodeDataChar);
        $this->tableNodeDataChar->setPrimary('id_node_data_char');
        $this->tableNodeDataChar->setAutoIncrement(true);
        $this->dbNodeDataChar = new Table_query($this->tableNodeDataChar);

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
    public function listNode($start = null, $end = null, $orderBy = array(), $where = null, $orSearch = array(), $andSearch = array(), $function = null) {

        $q = $this->dbNode->duplicate()
                ->limitSelect($start, $end)
                ->orderBy($orderBy);

        if ($where) {
            $condition = $q->andWhere($where);
        }


        if ($function && is_callable($function)) {
            $function($q, func_get_args());
        } else if ($function) {
            $q->groupBy($function); // backwards deprecated
        }

        if (is_array($andSearch) && !empty($andSearch)) {
            $q->andWhere()->andSearch($andSearch);
        }

        if (is_array($orSearch) && !empty($orSearch)) {
            $q->andWhere()->orSearch($orSearch);
        }

        /*
          // recherche texte
          if ( is_array($orSearch) && !empty($orSearch)) {
          foreach ($orSearch as $column => $s) {
          $q->andWhere()->orSearch(array($column => $s));
          }
          }
         */
        $total = 0;

        if ($rows = $q->getAll()) {
            $total = $q->foundRows();
            $rows = $this->unserializeDataList($rows);
        }

        return Module_Utils::formatList($rows, $start, $end, $total);
    }

    /**
     * filtre les brouillons
     * 
     * @param \Fp\Table\QueryInterface $nodeQuery
     * @param string $etat
     */
    public function filtreEtat(\Fp\Table\QueryInterface $nodeQuery, $etat = true) {
        if ($etat === true) {
            $nodeQuery->andWhere('n.etat != 2'); // tout sauf les brouillon
        } else if (ctype_digit("$etat"))
            $nodeQuery->andWhere(['n.etat' => $etat]);
    }

    /**
     * filtre les publication planifiée
     * 
     * @param \Fp\Table\QueryInterface $nodeQuery
     * @param string $date_publication
     */
    public function filtreDatePublication(\Fp\Table\QueryInterface $nodeQuery, $date_publication = true) {
        if ($date_publication === true) {
            $today = \Fp\Core\Date::fromStrtotime(time())->mysqlDateTime();
            $nodeQuery->andWhere("n.date_publication <= '$today' OR n.date_publication = 0 OR n.date_publication IS NULL ");
        }
        if ($date_publication === 1) { // uniquement les publication planifiées
            $today = \Fp\Core\Date::fromStrtotime(time())->mysqlDateTime();
            $nodeQuery->andWhere("n.date_publication >= '$today' ");
        }
    }

    /**
     * filtre rank
     * 
     * @param \Fp\Table\QueryInterface $nodeQuery
     * @param string $rank A=0 B=750 C=500 D=750 E=1000
     */
    public function filtreRank(\Fp\Table\QueryInterface $nodeQuery, $rank = null) {
        if ($rank) {
            if (!ctype_digit($rank)) {
                $ranking = ['A' => 0, 'B' => 250, 'C' => 500, 'D' => 750, 'E' => 1000];
                $rank = (int) $ranking[strtoupper($rank)];
            }
            $nodeQuery->andWhere(['n.rank' => ['<=', $rank]]); // tout ce qui a une note égale ou meilleur
        }
    }

    /**
     * @param int $id_node
     * @param array $data
     */
    protected function updateNode($id_node, $data) {
        if (!$old_node = $n = $this->getById($id_node))
            return;
        $tid = Db::startTransaction();
        $q = $this->dbNode->duplicate();
        $q->noJoin(true);
        $q->orderBy(array());
        $q->andWhere($id_node);
        if (array_key_exists('id_node', $data))
            unset($data['id_node']);
        if (array_key_exists('data', $data)) {
            $data['data'] = array_merge((array) $n['data'], (array) $data['data']);
            $this->updateNodeDataBlob($id_node, 'node', $data['data']);
        }
        $du = $data;
        $du['data'] = null;
        ;
        $r = $q->update($du);
        $r2 = $this->updateNodeExtend($id_node, $data, $old_node);
// only when change
        if ($r || $r2) {
            $d = array('date_modification' => Date::fromUnixTime(time())->mysqlDateTime());
            $q->update($d);
            $this->onNodeChange($id_node, $data, $old_node);
        }
        Db::endTransaction($tid);
        return (!$r || $r < $r2 ) ? $r2 : $r;
    }

    /**
     * @param int $id
     */
    public function getById($id_node) {
        if (!$id_node)
            return null;
        $q = $this->dbNode->duplicate()->limitSelect(1);
        $q->andWhere($id_node);
        $data = $q->getAssoc();
        if (!empty($data)) {
            $data = $this->unserializeData($data);
            $data['tags'] = $this->getTags($id_node);
            $data['media'] = $this->getMedia($id_node);
            $data['relation_link'] = $this->getLinks($id_node);
            $data['relation_parents'] = $this->getParents($id_node);
            $data['relation_children'] = $this->getChildren($id_node);
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
    public function getByWhere($where, $orderBy = array()) {
        $q = $this->dbNode->duplicate()
                ->limitSelect(1)
                ->orderBy($orderBy);
        $q->andWhere($where);
        $rows = $q->getAssoc();
        return $this->unserializeData((array) $rows);
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
    protected function createNode($id_node = null, $uid = null, $group = null, $zone = null, $etat = 0, $rank = 500, $data = array(), $date_publication = null) {
        if (!$rank)
            $rank = 500;
        if (!$etat)
            $etat = 0;
        if (!trim($this->type_node)) {
            throw new Exception('type_node is missing', 500);
        }

        try {
            $tid = Db::startTransaction();
            $permission = new Permission($this->O);

            $gid = null;
            if ($group) {
                if ($permission->existGroup($group))
                    $gid = $permission->idGroup($group);
            }

            $zid = null;
            if ($zone) {
                if ($permission->existGroup($group))
                    $zid = $permission->idGroup($zone);
            }
            $date_creation = Date::fromUnixTime(time())->mysqlDateTime();

            if (!$date_publication)
                $date_publication = $date_creation;

            $d = array(
                'type_node' => $this->type_node,
                'uid' => $uid,
                'gid' => $gid,
                'zid' => $zid,
                'rank' => $rank,
                'etat' => $etat,
                'data' => null,
                'date_creation' => $date_creation,
                'date_modification' => $date_creation,
                'date_publication' => $date_publication
            );

            $req = $this->dbNode->duplicate();
            $r = null;
            if ($id_node)
                $r = $this->updateNode($id_node, $d);

            if (!$r) {
                if ($id_node)
                    $d['id_node'] = $id_node;
                if ($id = $req->insert($d))
                    $id_node = ( $id_node ) ? $id_node : $id;
            }

            if ($id_node)
                $this->updateNodeDataBlob($id_node, 'node', $data);

            Db::endTransaction($tid);
            return $id_node;
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }

// Link
    private function id_ord($id_1, $id_2) {
        return ( $id_1 < $id_2 ) ? array($id_1, $id_2) : array($id_2, $id_1);
    }

    /**
     * @param int $id_node
     * @param int $id_node_2
     */
    public function removeLink($id_node, $id_node_2) {
//@todo trigger seulement si le changement est fait
        $this->O->event()->trigger($this->namespace . '.change');

        $l = $this->id_ord($id_node, $id_node_2);
        $ids = array('id_node_1' => $l[0],
            'id_node_2' => $l[1]);

        $q = $this->dbNodeLink->duplicate();
        $q->andWhere($ids);
        return $q->delete();
    }

    /**
     * @param int $id_node_1
     * @param int $id_node_2
     */
    public function addLink($id_node_1, $id_node_2, $position = 0) {
        $l = $this->id_ord($id_node_1, $id_node_2);
        $w = array('id_node_1' => $l[0],
            'id_node_2' => $l[1]);
        $req = $this->dbNodeLink->duplicate();
        $req->andWhere($w);
        $req->orderBy(array('position' => 'DESC'));
        if (!$r = $req->getColumn()) {
            $w['position'] = 0;
            $req->insert($w);
            $this->setLinkPosition($id_node_1, $id_node_2);
            $this->O->event()->trigger($this->namespace . '.change');
        }
    }

    /**
     * @param int $id_node
     * @param classname $type_node
     */
    public function getLinks($id_node, $type_node = null) {
        $q = $this->dbNodeLink->duplicate();
        $q->selectColumn('n1.*');
        $q->orderBy(array('position' => 'ASC'));
        if ($type_node)
            $q->andWhere("n1.type_node='$type_node' ");

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
        $this->O->event()->trigger($this->namespace . '.change');
        $l = $this->id_ord($id_node_1, $id_node_2);

        $set = array('position' => '@a:=@a+1');
        $q = $this->dbNodeLink->duplicate();
        $q->andWhere(array('id_node_1' => $l[0], 'id_node_2' => $l[1]));
        $q->orderBy(array('position' => 'ASC'));
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
        if ($position_origine == $position)
            return;
        $this->setLinkPosition($id_node_1, $id_node_2);
        $l = $this->id_ord($id_node_1, $id_node_2);

//@todo trigger seulement si le changement est fait
        $this->O->event()->trigger($this->namespace . '.change');

        $q = $this->dbNodeLink->duplicate();
        if ($position_origine == $position)
            return true;

        $qposition = $this->dbNode->quote($position);
        $qposition_origine = $this->dbNode->quote($position_origine);

        $t = $q->duplicate();
        $t->andWhere(array('id_node_1' => $l[0], 'id_node_2' => $l[1]));
        $t->noJoin(true);
        $t->orderBy(array());

        if ($position_origine > $position) {
            $set = array('position' => 'position+1');
            $t->andWhere(" ( position>=$qposition AND position<$qposition_origine ) ");
            $t->update($set, 'position');
        } else {
            $set = array('position' => 'position-1');
            $t->andWhere(" ( position<=$qposition AND position>$qposition_origine ) ");
            $t->update($set, 'position');
        }

        $t = $q->duplicate();
        $set = array('position' => "$position");
        $t->limitUpdate(1);
        $t->andWhere(array('id_node_1' => $l[0], 'id_node_2' => $l[1]));
        $t->noJoin(true);
        $t->update($set);
        return true;
    }

// RELATION

    /**
     * @param int $id_node
     * @param int $id_node_enfant
     */
    public function addChildren($id_node, $id_node_enfant) {
        $w = array('id_node_parent' => $id_node,
            'id_node_enfant' => $id_node_enfant);
        $data = array('position' => 0);
        $req = $this->dbNodeRelation->duplicate();
        $req->andWhere($w);
        $r = null;
        if ($mfa = $req->getAssoc()) {
            $r = $req->update($data);
        } else {
            $data = array_merge($data, $w);
            $r = $req->insert($data);
        }
        if ($r) {
            $this->setRelationPosition($id_node);
            $this->O->event()->trigger($this->namespace . '.change');
        }
        return $r;
    }

    /**
     * @param int $id_node
     * @param int $id_node_enfant
     */
    public function removeChildren($id_node, $id_node_enfant) {
        $w = array('id_node_parent' => $id_node,
            'id_node_enfant' => $id_node_enfant);
        $q = $this->dbNodeRelation->duplicate();
        $q->andWhere($w);
        $r = $q->delete();
        if ($r) {
            $this->setRelationPosition($id_node);
            $this->O->event()->trigger($this->namespace . '.change');
        }
        return $r;
    }

    /**
     * @param int $id_node
     */
    public function removeAllChildren($id_node) {
        $w = array('id_node_parent' => $id_node);
        $q = $this->dbNodeRelation->duplicate();
        $q->andWhere($w);
        $r = $q->delete();
        if ($r)
            $this->O->event()->trigger($this->namespace . '.change');
        return $r;
    }

    /**
     * @param int $id_node
     * @param classname $type_node
     */
    public function getParents($id_node, $type_node = null) {
        $q = $this->dbNodeRelation->duplicate();
        if ($type_node)
            $q->andWhere(" n.type_node='$type_node' ");
        $q->andWhere(" r.id_node_enfant='$id_node' ");
        return $q->getAll();
    }

    /**
     * @param int $id_node
     * @param classname $type_node
     */
    public function getChildren($id_node, $type_node = null, $function = null) {
        $q = $this->dbNode->duplicate();
        $q->innerJoin($this->tableRelation, 'r', ' n.id_node=r.id_node_enfant ');

        if ($function && is_callable($function))
            $function($q, func_get_args());

        if ($type_node)
            $q->andWhere(array('n.type_node' => $type_node));
        $q->andWhere(array('r.id_node_parent' => $id_node));
        $q->orderBy(array('r.position' => 'asc'));

        return $q->getAll();
    }

    /**
     * réordonne les relations, renvois la position max
     * @param int $id_node
     */
    private function setRelationPosition($id_node) {
//@todo trigger seulement si le changement est fait
        $this->O->event()->trigger($this->namespace . '.change');

        $set = array('position' => '@a:=@a+1');
        $q = $this->dbNodeRelation->duplicate();
        $q->noJoin(true);
        $q->andWhere(array('id_node_parent' => $id_node));
        $q->orderBy(array('position' => 'ASC'));
        $q->query('SET @a=-1');
        $q->noJoin(true);
        return $q->update($set, 'position');
    }

    /**
     * @param int $id_node
     * @param int $id_node_enfant
     * @param int $position
     * @param int $position_origine
     */
    public function reOrderRelation($id_node, $id_node_enfant, $position, $position_origine) {
        if ($position_origine == $position)
            return;
        $this->setRelationPosition($id_node);


        $q = $this->dbNodeRelation->duplicate();
        if ($position_origine == $position)
            return true;
        $qid_node = $this->dbNode->quote($id_node);
        $qid_node_enfant = $this->dbNode->quote($id_node_enfant);
        $qposition = $this->dbNode->quote($position);
        $qposition_origine = $this->dbNode->quote($position_origine);

        if ($position_origine > $position) {
            $t = $q->duplicate('position');
            $set = array('position' => 'position+1');
            $t->andWhere(" id_node_parent=$qid_node AND ( position>=$qposition AND position<$qposition_origine ) ");
            $t->noJoin(true);
            $t->update($set, 'position');
        } else {
            $t = $q->duplicate('position');
            $set = array('position' => 'position-1');
            $q->orderBy(array());
            $t->andWhere(" id_node_parent=$qid_node AND ( position<=$qposition AND position>$qposition_origine ) ");
            $t->noJoin(true);
            $t->update($set, 'position');
        }

        $set = array('position' => "$position");
        $t = $this->dbNodeRelation->duplicate();
        $t->limitUpdate(1);
        $t->andWhere(" id_node_parent=$qid_node AND id_node_enfant=$qid_node_enfant ");
        $t->noJoin(true);
        $t->update($set);
//@todo trigger seulement si le changement est fait
        $this->O->event()->trigger($this->namespace . '.change');
        return true;
    }

//MEDIA
    /**
     * @param int $id_node
     */
    public function getMedia($id_node) {
        $q = $this->dbNodeMedia->duplicate();
        $q->orderBy(array('position' => 'ASC'));
        $q->andWhere(array('nm.id_node' => $id_node));
        return $q->getAll();
    }

    /**
     * @param array $where
     * */
    public function getAllMedia($where) {
        $q = $this->dbNodeMedia->duplicate();
        $q->andWhere($where);
        return $r = $q->getAll();
    }

    /**
     * @param int $id_node
     * @param int $id_media
     * @param int $position
     */
    public function addMedia($id_node, $id_media, $position = null) {
//@todo trigger seulement si le changement est fait
        $this->O->event()->trigger($this->namespace . '.change');

        $media = new MediaModel($this->O);
        if (!$position)
            $position = $this->setMediaPosition($id_node);
        $media->referenceIncrement($id_media, $id_node, $this->tableNode->table);
        $data = array('id' => '',
            'id_node' => $id_node,
            'id_media' => $id_media,
            'position' => $position);
        $w = array('id_node' => $id_node,
            'id_media' => $id_media);

        $req = $this->dbNodeMedia->duplicate();
        $req->andWhere($w);
        if (!$req->getColumn())
            $req->insert($data);
    }

    /**
     * @param int $id_node
     * @param int $id_media
     */
    public function deleteMedia($id_node, $id_media) {
//@todo trigger seulement si le changement est fait
        $this->O->event()->trigger($this->namespace . '.change');

        $media = new MediaModel($this->O);
        $q = $this->dbNodeMedia->duplicate();
        $w = array('nm.id_node' => $id_node, 'nm.id_media' => $id_media);
        $q->andWhere($w);
        if ($r = $q->delete())
            $media->referenceDecrement($id_media, $id_node, $this->tableNode->table);
        return $r;
    }

// réordonne les media, renvois la position max
    /**
     * @param int $id_node
     */
    private function setMediaPosition($id_node) {
//@todo trigger seulement si le changement est fait
        $this->O->event()->trigger($this->namespace . '.change');

        $set = array('position' => '@a:=@a+1');
        $q = $this->dbNodeMedia->duplicate();
        $q->query('SET @a=-1');
        $q->andWhere(array('id_node' => $id_node));
        $q->orderBy(array('position' => 'ASC'));
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
        if ($position_origine == $position)
            return true;

//@todo trigger seulement si le changement est fait
        $this->O->event()->trigger($this->namespace . '.change');

        $this->setMediaPosition($id_node);
        $q = $this->dbNodeMedia->duplicate();
        $qid_node = $this->dbNodeMedia->quote($id_node);
        $qid_media = $this->dbNodeMedia->quote($id_media);
        $qposition = $this->dbNodeMedia->quote($position);
        $qposition_origine = $this->dbNodeMedia->quote($position_origine);
        if ($position_origine > $position) {
            $t = $q->duplicate();
            $set = array('position' => 'position+1');
            $t->andWhere(" id_node=$qid_node AND ( position>=$qposition AND position<$qposition_origine ) ");
            $t->noJoin(true);
            $t->update($set, 'position');
        } else {
            $t = $q->duplicate();
            $set = array('position' => 'position-1');
            $t->andWhere(" id_node=$qid_node AND ( position<=$qposition AND position>$qposition_origine ) ");
            $t->noJoin(true);
            $t->update($set, 'position');
        }
        $t = $q->duplicate()->limitUpdate(1);
        $set = array('position' => $position);
        $t->andWhere(" id_node=$qid_node AND id_media=$qid_media ");
        $t->noJoin(true);
        $t->update($set);
        return $t->showUpdateQuery($set);
    }

    /*
     *  NODE TAGS
     */

    public function addTag($id_node, $tag) {
        $data = array('tag' => $tag, 'id_node' => $id_node);
        return $this->dbNodeTags->insertIgnore($data);
    }

    public function deleteTag($id_node, $tag) {
        $req = $this->dbNodeTags->duplicate();
        $req->andWhere(array('tag' => $tag, 'id_node' => $id_node));
        return $req->delete();
    }

    public function getTags($id_node, $start = 0, $end = null, $options = array()) {
        $req = $this->dbNodeTags->duplicate();
        $req->andWhere(array('id_node' => $id_node));
        $req->selectColumn('tag');
        $req->selectFunction("count('tags') as ct");
        $req->limitSelect($start, $end);
        $req->groupBy('tags.tag');
        $req->orderBy(array('priority' => 'DESC'));
        return $req->getAllColumn();
    }

    public function attachTags($id_node, array $tags) {
        $tag_list = array();
        foreach ($tags as $v) {
            if ($v = trim(mb_strtolower($v, 'UTF-8')))
                $tag_list[] = $v;
        }

        $TagsToDelete = array();
        $old_t = $this->getTags($id_node);
        if (is_array($old_t)) {
            $TagsToDelete = array_diff($old_t, $tag_list);
            foreach ($TagsToDelete as $tag) {
                $this->deleteTag($id_node, Filter::dbSafe($tag));
            }
            $TagsToAdd = array_diff($tag_list, $old_t);
        } else {
            $TagsToAdd = $tag_list;
        }

        foreach ($TagsToAdd as $tag) {
            $this->addTag($id_node, $tag);
        }
    }

    /**
     *  Liste les tags associés a certain node (ex tout les tags associés a un type node)
     * 
     * @param boolean $where
     * @param type $start
     * @param type $end
     * @return type
     */
    public function listTagsByNode($where, $start = 0, $end = null) {
        $orderBy = array('tags.priority' => 'DESC');
        if (!$where)
            $where = true;
        $q = $this->dbNode->duplicate()
                ->innerJoin($this->dbNodeTags->dbTable, 'tags', 'tags.id_node=n.id_node')
                ->selectColumn('tags.*', false)
                ->groupBy('tags.tag')
                ->limitSelect($start, $end)
                ->orderBy($orderBy);
        $q->andWhere($where);
        $rows = $q->getAll(array('sql_calc_found_rows'));
        $total = $q->foundRows();
        return Module_Utils::formatList($rows, $start, $end, $total);
    }

    public function listNodeByTags(array $tags, $start = null, $end = null, $orderBy = array('n.date_modification' => 'DESC', 'n.date_creation' => 'DESC'), $where = null, $orSearch = array(), $andSearch = array(), $groupBy = null) {
        if (!$where)
            $where = true;
        $q = $this->dbNode->duplicate()
                ->limitSelect($start, $end)
                ->orderBy($orderBy);
        $q->innerJoin($this->dbNodeTags->dbTable, 'tags', 'tags.id_node=n.id_node');

        if ($groupBy)
            $q->groupBy($groupBy);

        if (empty($tags) && empty($where))
            $q->andWhere(true);
        else {
            $q->andWhere($where);
            foreach ($tags as $v) {
                $q->andWhere(array('tag' => $v));
            }
        }

        $total = 0;
        if ($rows = $q->getAll()) {
            $total = $q->foundRows();
            $rows = $this->unserializeDataList($rows);
        }
        return Module_Utils::formatList($rows, $start, $end, $total);
    }

    /*
     *  NODE REVISIONS
     */

    protected function onNodeChange($id_node, $node, $old_node) {
        $this->O->event()->trigger($this->namespace . '.node.change');
        $uid = $this->O->auth()->uid();
        $label = Filter::text('node.revision.label', $node, 'revision');
        $comment = Filter::text('node.revision.comment', $node);

        $this->addRevision($id_node, $uid, $label, $comment, Date::fromStrtotime(time())->mysqlDateTime(), $old_node);
    }

    public function restoreRevision($id_version) {
        if ($d = $this->getRevision($id_version)) {
            $d['data']['node.revision.label'] = 'restore ' . $id_version;
            $d['data']['node.revision.comment'] = 'restore ' . $id_version;
            $this->updateNode($d['id_node'], $d['data']);
        }
    }

    public function addRevision($id_node, $uid = null, $label = "revision", $comment = null, $date_revision = null, $data = array()) {
        $data = array(
            'id_node' => $id_node,
            'uid' => $uid,
            'label' => $label,
            'comment' => $comment,
            'date_revision' => $date_revision,
            'data' => $s = json_encode($data),
            'md5' => md5($s));
        return $this->dbNodeRevisions->insert($data);
    }

    public function getRevisionById($id_version) {
        $r = $this->dbNodeRevisions->duplicate();
        $r->andWhere($id_version);
        if ($r = $r->getAssoc()) {
            $r['data'] = json_decode($r['data'], true);
            return $r;
        }
    }

    /**
     * @param unknown $id_node
     * @param number $index
     */
    public function getRevision($id_node, $index = 0) {
        $r = $this->dbNodeRevisions->duplicate()
                ->orderBy(array('id_version' => 'DESC'))
                ->limitSelect($index, 1);
        $r->andWhere(array('id_node' => $id_node));
        if ($r = $r->getAssoc()) {
            $r['data'] = json_decode($r['data'], true);
            return $r;
        }
    }

    /**
     * @deprecated use getRevision
     */
    public function getRevisionByIndex($id_node, $index = 0) {
        return $this->getRevision($id_node, $index);
    }

    public function removeRevision($id_version) {
        $r = $this->dbNodeRevisions->duplicate();
        $r->andWhere($id_version);
        return $r->remove();
    }

    public function listRevisions($id_node, $start = 0, $end = 10, $search = array()) {
        $req = $this->dbNodeRevisions->duplicate();
        $req->orderBy(array('id_version' => 'DESC'));
        $req->andWhere(array('id_node' => $id_node));
        $req->limitSelect($start, $end);
        $rows = $req->getAll();
        $total = $req->foundRows();
        return Module_Utils::formatList($rows, $start, $end, $total);
    }

    /*
     * Node Data
     */

    public function serialize($data) {
        return json_encode($data);
    }

    public function unserialize($data) {
        if (empty($data))
            return $data;

        if ($encoded = base64_decode($data, true)) { // support ancien format base64+ php serialise
            try {
                $data = @unserialize($encoded);
            } catch (\Exception $e) {
                
            }
        } else {
            $data = @json_decode($data, true);
            // if ( json_last_error() !== JSON_ERROR_NONE)             
        }
        return $data;
    }

    public function serializeData($data) {
        return $this->serialize($data);
    }

    /**
     * @deprecated since 7.0 backward compatibility 
     * @param type $line
     * @return array 
     */
    public function unserializeData($line) {
        if ( empty($line['data'])  ) {
            $d = $this->getNodeDataBlob($line['id_node'], 'node');
            if( !empty($d) ) {
                $line['data'] = $d['data'];
            }
        }
        else {
            $d = $line['data'];
            try {
                $line['data'] = $this->unserialize($d);
            } catch (\Exception $e) {
                $line['data'] = array();
            }
        }
        return $line;
    }

    public function unserializeDataList($lines, $key_name = 'node') {
        $id_nodes = [];
        foreach ($lines as $v) {
            $id_nodes[] = $v['id_node'];
        }
        $ldata = $this->batchGetAllNodeDataBlob($id_nodes, $key_name);
        foreach ($lines as $k => $v) {
            $lines[$k]['data_blob'] = [];
            if (array_key_exists($v['id_node'], $ldata)) {
                foreach ($ldata[$v['id_node']] as $key_name => $list) {                   
                        $lines[$k]['data_blob'][$key_name] = $list;
                        if ($key_name === 'node' && isset($lines[$k]['data_blob'][$key_name][0])) {
                            $lines[$k]['data'] = &$lines[$k]['data_blob'][$key_name][0];                          
                        }                   
                }
            }
        }
        return $lines;
    }

    /**
     * @param int $start
     * @param int $end
     * @param array $orderBy
     * @param array $search
     */
    public function listNodeDataBlob($start = 0, $end = 30, $orderBy = array(), $search = array()) {
        $req = $this->dbNodeDataBlob->duplicate();
        $req->orderBy($orderBy);
        if (empty($search)) {
            $req->andWhere(true);
        } else {
            $req->search($search);
        }
        $req->limitSelect($start, $end);
        $rows = $req->getAll();
        foreach ($rows as $k => $v) {
            $rows[$k]['data'] = $this->unserialize($rows[$k]['data']);
        }
        $total = $req->foundRows();
        return Module_Utils::formatList($rows, $start, $end, $total);
    }

    public function createNodeDataBlob($id_node, $key, $data) {
        $req = $this->dbNodeDataBlob->duplicate();
        $d = array('id_node' => $id_node,
            'key_name' => $key,
            'data' => $this->serialize($data));
        return $req->insert($d);
    }

    /**
     * @param int $id_node	 
     * @param array $data
     */
    public function updateNodeDataBlob($id_node, $key, $data) {
        if ($oldData = $this->getNodeDataBlob($id_node, $key)) {
            if (!empty($oldData['data'])) {
                if (empty($data)) {
                    
                    return $this->removeNodeDataBlob(['id_node_data_blob' => $oldData['id_node_data_blob']]);
                } else {                    
                    $d = ['data' => $this->serialize($data)];
                    $q = $this->dbNodeDataBlob->duplicate();
                    $q->andWhere(['id_node_data_blob' => $oldData['id_node_data_blob']]);
                    return $q->update($d);
                }
            }
        } else if (!empty($data)) {
            return $this->createNodeDataBlob($id_node, $key, $data);
        }
    }
    
   

    /**
     * @param int|array $id_node int with key_name or single array
     * @param string key_name 
     * */
    public function getNodeDataBlob($id_node, $key_name = 'node') {
        $q = $this->dbNodeDataBlob->duplicate();
        $q->limitSelect(1);

        if (!is_array($id_node)) {
            $q->andWhere(['id_node' => $id_node, 'key_name' => $key]);
        } else {
            $q->andWhere($id_node);
        }
        $r = $q->getAssoc();
        if (!empty($r)) {
            $r['data'] = $this->unserialize($r['data']);
            return $r;
        }
    }

    public function batchGetAllNodeDataBlob(array $id_nodes, $key_name) {
        $q = $this->dbNodeDataBlob->duplicate();
        $q->andWhere(['key_name' => $key_name])->andWhere()->orWhere(['id_node' => $id_nodes]);
        $l = $q->getStatement();
        $list = [];
        while ($r = $l->fetchAssoc()) {
            if (!array_key_exists($r['id_node'], $list)) {
                $list[$r['id_node']] = [];
            }
            if (!array_key_exists($r['key_name'], $list[$r['id_node']])) {
                $list[$r['id_node']][$r['key_name']] = [];
            }
            $list[$r['id_node']][$r['key_name']][] = $this->unserialize($r['data']);
        }
        return $list;
    }

    /**
     * @param int $id_node
     * */
    public function getAllNodeDataBlob($where) {
        $q = $this->dbNodeDataBlob->duplicate();
        $q->andWhere($where);

        $l = $q->getStatement();
        $list = [];
        while ($r = $l->fetchAssoc()) {
            if (!isset($list[$r['key_name']]))
                $list[$r['key_name']] = [];
            $list[$r['key_name']][] = $this->unserialize($r['data']);
        }
        return $list;
    }

    /**
     * @param int $id_node	
     * */
    public function removeNodeDataBlob($id_node, $key) {
        $q = $this->dbNodeDataBlob->duplicate();
        if (!is_array($id_node)) {
            $q->andWhere(['id_node' => $id_node, 'key_name' => $key]);
        } else {
            $q->andWhere($id_node);
        }

        return $q->remove();
    }

    
    
    
    public function getDataChar($id_node) {
        $d = $this->getNodeDataChar($id_node, 'node');
        if ($d) {
            return $d['data'];
        }
        return array();
    }

    /**
     * @param int $start
     * @param int $end
     * @param array $orderBy
     * @param array $search
     */
    public function listNodeDataChar($start = 0, $end = 30, $orderBy = array(), $search = array()) {
        $req = $this->dbNodeDataChar->duplicate();
        $req->orderBy($orderBy);
        if (empty($search)) {
            $req->andWhere(true);
        } else {
            $req->search($search);
        }
        $req->limitSelect($start, $end);
        $rows = $req->getAll();
        $total = $req->foundRows();
        return Module_Utils::formatList($rows, $start, $end, $total);
    }

    public function createNodeDataChar($id_node, $key, $data) {
        $req = $this->dbNodeDataChar->duplicate();
        $d = array('id_node' => $id_node,
            'key_name' => $key,
            'data' => $data);
        return $req->insert($d);
    }

    /**
     * @param int $id_node	 
     * @param array $data
     */
    public function updateNodeDataChar($id_node, $key, $data) {
        if ($oldData = $this->getNodeDataChar($id_node, $key)) {
            if (!empty($oldData['data'])) {
                if (empty($data)) {
                    return $this->removeNodeDataChar(['id_node_data_char' => $oldData['id_node_data_char']]);
                } else {
                    $d = ['data' => $this->serialize($data)];
                    $q = $this->dbNodeDataChar->duplicate();
                    $q->andWhere(['id_node_data_char' => $oldData['id_node_data_char']]);
                    return $q->dbNodeDataChar->update($d);
                }
            }
        } else if (!empty($data)) {
            return $this->createNodeDataChar($id_node, $key, $data);
        }        
    }

    /**
     * @param int|array $id_node int with key_name or single array
     * @param string key_name 
     * */
    public function getNodeDataChar($id_node, $key = null) {
        $q = $this->dbNodeDataChar->duplicate();
        $q->limitSelect(1);
        if (!is_array($id_node)) {
            $q->andWhere(['id_node' => $id_node, 'key_name' => $key]);
        } else {
            $q->andWhere($id_node);
        }
        return $q->getAssoc();
    }

    /**
     * @param array $where   
     * */
    public function getAllNodeDataChar($where) {
        $q = $this->dbNodeDataChar->duplicate();
        $q->andWhere($where);
        $l = $q->getStatement();
        $list = [];
        while ($r = $l->fetchAssoc()) {
            if (!isset($list[$r['key_name']]))
                $list[$r['key_name']] = [];
            $list[$r['key_name']][] = $this->unserialize($r['data']);
        }
    }

    /**
     * @param int $id_node	 */
    public function removeNodeDataChar($id_node, $key) {
        $q = $this->dbNodeDataChar->duplicate();
        if (!is_array($id_node)) {
            $q->andWhere(['id_node' => $id_node, 'key_name' => $key]);
        } else {
            $q->andWhere($id_node);
        }
        return $q->remove();
    }

    /*
     * Fusionne deux node, les données de $id_node_from sont ajouté a $id_node_to si elle n'existe pas
     * 
     */

    public function fusion($id_node_to, $id_node_from, $delete = true) {

        if ("$id_node_to" === "$id_node_from") {
            throw new \Exception('id_node_to cannot be equal to id_node_from');
        }

        $tid = \Fp\Db\Db::startTransaction();
        $node_from = $this->getById($id_node_from);
        $node_to = $this->getById($id_node_to);

        if (empty($node_to)) {
            throw new \Exception('id_node_to not found');
        }

        if (empty($node_from)) {
            throw new \Exception('id_node_from not found');
        }

        $udata = [];
        foreach ($node_from as $k => $v) {
            if (empty($node_to[$k])) {
                $udata[$k] = $v;
            }
        }
        if (!empty($udata)) {
            $this->updateNode($id_node_to, $udata);
        }


        $data_char = (array) $this->getAllNodeDataChar(['id_node' => $id_node_from]);
        foreach ($data_char as $d) {
            if (!$this->getNodeDataChar($id_node_to, $d['key_name'])) {
                $this->createNodeDataChar($id_node_to, $d['key_name'], $d['data']);
            }
        }

        $data_blob = (array) $this->getAllNodeDataBlob(['id_node' => $id_node_from]);
        foreach ($data_blob as $d) {
            if (!$this->getNodeDataBlob($id_node_to, $d['key_name'])) {
                $this->createNodeDataBlob($id_node_to, $d['key_name'], $d['data']);
            }
        }


        $medias_old = (array) $this->getAllMedia(['id_node' => $id_node_from]);
        $medias = (array) $this->getAllMedia(['id_node' => $id_node_to]);
        foreach ($medias as $k => $v) {
            $medias[$v['id_media']] = $v;
        }
        foreach ($medias_old as $k => $v) {
            if (!array_key_exists($v['id_media'], $medias)) {
                $this->addMedia($id_node_to, $v['id_media']);
            }
        }

        $tags_old = (array) $this->getTags($id_node_from);
        $tags = (array) $this->getTags($id_node_to);
        $tags_to = [];
        foreach ($tags_old as $v) {
            $tags_to[$v] = $v;
        }
        foreach ($tags as $v) {
            $tags_to[$v] = $v;
        }
        if (!empty($tags)) {
            $this->attachTags($id_node_to, $tags_to);
        }

        $links = (array) $this->getLinks($id_node_from);
        foreach ($links as $v) {
            $this->addLink($id_node_to, $v['id_node']);
        }

        $parents = (array) $this->getParents($id_node_from);
        foreach ($parents as $v) {
            $this->addChildren($v['id_node_parent'], $id_node_to);
        }

        $children = (array) $this->getChildren($id_node_from);
        foreach ($children as $v) {
            $this->addChildren($id_node_to, $v['id_node_enfant']);
        }

        if ($delete) {
            $this->deleteNode(['id_node' => $id_node_from]);
            //$this->createNode($id_node_from, null, null, null, 301, 0, []);
        }


        \Fp\Db\Db::endTransaction($tid);
        return $this->getById($id_node_to);
    }

    public function fusionList($id_node_to, array $list_id_node_from = array()) {
        $tid = \Fp\Db\Db::startTransaction();
        $n = null;
        foreach ($list_id_node_from as $id_node_from) {
            $n = $this->fusion($id_node_to, $id_node_from);
        }
        \Fp\Db\Db::endTransaction($tid);
        return $n;
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

      } */
}
