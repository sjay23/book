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
        $sql    = new Sql($this->adapter);
        $select = $sql->select()->from('book')->order('data_added DESC');
        
        $sqlString = $sql->getSqlStringForSqlObject($select);
        $results   = $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        $results   = $results->toArray();
        if (!isset($results[0]['book_id']))
            return FALSE;
        
        foreach ($results as $res) {
            $select = $sql->select()->from('book_to_cat')
                                    ->join('category', 'book_to_cat.category_id = category.category_id')
                                    ->where('book_to_cat.book_id =' . $res['book_id']);
            
            $sqlString = $sql->getSqlStringForSqlObject($select);
            $cats      = $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
            $cats      = $cats->toArray();
            
            $select = $sql->select()->from('book_to_author')
                                    ->join('author', 'book_to_author.author_id = author.author_id')
                                    ->where('book_to_author.book_id =' . $res['book_id']);
            
            $sqlString = $sql->getSqlStringForSqlObject($select);
            $authors   = $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
            $authors   = $authors->toArray();
            $result[]  = array(
                'data' => $res,
                'categories' => $cats,
                'authors' => $authors
            );
        }
        
        
        return $result;
    }
    
    
    
}