<?php

$this->raw();
$column_login = array('uid','time', 'status');
$table_login = 'login';

$login = Table::set(Db::get_link(), $table_login, $column_login);


$table 	 = 'user';
$column  = array('uid','alias','last_name','first_name','birthday','date');
$dbTable  = Table::set(Db::get_link(), $table, $column);
$dbTable->setPrimary('uid');
$dbTable->setUnique(array());
$dbTable->setSortable(array('uid','last_name','first_name','date','alias'));
$dbTable->setSearchable(array (
  'uid' => 'bigint',
  'last_name' => 'varchar',
  'first_name' => 'varchar',
  'date' => 'datetime',
  'alias' => 'varchar',
));
$dbTable->setAutoIncrement(true);



$req = new Table_query($dbTable,'u');
$req->outerJoin($login, 'l', 'l.uid=u.uid');
$n = clone $req;
/* 
 $req->selectColumn('*')->orWhere(1)->andWhere()->orWhere(array('1',2 ,3 ,4))->andWhere('1');
 $req->orderBy(array('uid'=>'ASC'));
 $req->limitSelect(0,21);
 $r = $req->getObject();
 $n->limitUpdate(1);
 $test->setValidateRules('alias', function($v) { echo "validate --> $v <br />"; });
 echo $n->update(array('alias'=>'sOO----Ouper'));
 */

$n->selectColumn('u.uid');
$n->orWhere(array(8,9 ))->orWhere()->andWhere('1')->orWhere(2);
$n->orWhere()->andWhere(4)->orWhere(5)->orWhere()->search(array('alias'=>'a'));
$n->orderBy(array('uid'=>'ASC'));


$r = $n->getObject();


echo '<br />-----------';