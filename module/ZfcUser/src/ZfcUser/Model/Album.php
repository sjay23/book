<?php
namespace ZfcUser\Model;

 // Add these import statements
 use Zend\Db\TableGateway\TableGateway;
 use Zend\Db\Sql\Sql;
 use Zend\Db\Sql\Select;
 use Zend\Db\Adapter\Adapter;
 use Zend\Paginator\Adapter\DbSelect;
 use Zend\Paginator\Paginator;
 use Zend\Db\ResultSet\ResultSet;

 class Album 
 {
     public function getAlbums($adapter,$limit,$start)
     {
        $sql = new Sql($adapter);
        $select = $sql->select()->from('album')->limit($limit)->offset($start)->order('id DESC');

        $sqlString = $sql->getSqlStringForSqlObject($select);
        $results = $adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        $results=$results->toArray();
        return $results;
     }
 }
