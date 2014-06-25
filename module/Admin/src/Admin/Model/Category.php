<?php
namespace Admin\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Paginator\Adapter\DbSelect;

class Category
{
    protected $adapter;
    
    function __construct($adapter)
    {
        $this->adapter = $adapter;
    }

    public function getCategories()
    {
        $sql    = new Sql($this->adapter);
        $select = $sql->select()->from('category')->order('name ASC');
        
        $sqlString = $sql->getSqlStringForSqlObject($select);
        $results   = $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        $results   = $results->toArray();           
        return $results;
    }   
    public function GetCategory($id)
    {
        $sql    = new Sql($this->adapter);
        $select = $sql->select()->from('category')->where('category_id =' . $id);
        
        $sqlString = $sql->getSqlStringForSqlObject($select);
        $results   = $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        $results   = $results->toArray();
            
        return $results[0];

     }
    public function AddCategory($data)
    {

        $sql    = new Sql($this->adapter);
        $insert = $sql->insert('category'); 
        $newData = array('name'=> $data['name']);
        $insert->values($newData);
        $sqlString = $sql->getSqlStringForSqlObject($insert);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);  
        $category_id = $this->adapter->getDriver()->getLastGeneratedValue();

        return TRUE;
    }   

     public function EditCategory($data)
    {
       $sql    = new Sql($this->adapter);
        $update = $sql->update();
        $update->table('category');
         $update->set(array(
             'name'=>htmlspecialchars($data['name'])
         ));
        $update->where(array('category_id'=>$data['id']));
        $sqlString = $sql->getSqlStringForSqlObject($update);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);

        return TRUE;
    } 

    public function DeleteCategory($id)
    {
        $sql    = new Sql($this->adapter);
        $delete = $sql->delete()->from('category')->where('category_id='.$id);
        $sqlString = $sql->getSqlStringForSqlObject($delete);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        $delete = $sql->delete()->from('book_to_cat')->where('category_id='.$id);
        $sqlString = $sql->getSqlStringForSqlObject($delete);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    }    

}