<?php
namespace Admin\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Paginator\Adapter\DbSelect;
 class Book 
 {
    protected $adapter;

    function __construct($adapter)
    {
        $this->adapter = $adapter;
    }

    public function getBooks()
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select()->from('book');

        $sqlString = $sql->getSqlStringForSqlObject($select);
        $results = $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        $results=$results->toArray();
        return $results;
    }

 }
