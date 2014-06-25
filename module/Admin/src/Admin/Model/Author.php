<?php
namespace Admin\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Paginator\Adapter\DbSelect;

class Author
{
    protected $adapter;
    
    function __construct($adapter)
    {
        $this->adapter = $adapter;
    }

    public function getAuthors()
    {
        $sql    = new Sql($this->adapter);
        $select = $sql->select()->from('author')->order('last_name ASC');
        
        $sqlString = $sql->getSqlStringForSqlObject($select);
        $results   = $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        $results   = $results->toArray();           
        return $results;
    }   
    public function GetAuthor($id)
    {
        $sql    = new Sql($this->adapter);
        $select = $sql->select()->from('author')->where('author_id =' . $id);
        
        $sqlString = $sql->getSqlStringForSqlObject($select);
        $results   = $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        $results   = $results->toArray();
            
        return $results[0];

     }
    public function AddAuthor($data)
    {

        $sql    = new Sql($this->adapter);
        $insert = $sql->insert('author'); 
        $newData = array('name'=> $data['name'],'last_name'=> $data['last_name']);
        $insert->values($newData);
        $sqlString = $sql->getSqlStringForSqlObject($insert);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);  
        $category_id = $this->adapter->getDriver()->getLastGeneratedValue();

        return TRUE;
    }   

     public function EditAuthor($data)
    {
       $sql    = new Sql($this->adapter);
        $update = $sql->update();
        $update->table('author');
         $update->set(array(
             'name'=>htmlspecialchars($data['name']),
             'last_name'=>htmlspecialchars($data['last_name']),
         ));
        $update->where(array('author_id'=>$data['id']));
        $sqlString = $sql->getSqlStringForSqlObject($update);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);

        return TRUE;
    } 

    public function DeleteAuthor($id)
    {
        $sql    = new Sql($this->adapter);
        $delete = $sql->delete()->from('author')->where('author_id='.$id);
        $sqlString = $sql->getSqlStringForSqlObject($delete);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        $delete = $sql->delete()->from('book_to_author')->where('author_id='.$id);
        $sqlString = $sql->getSqlStringForSqlObject($delete);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    }    
    
    
}