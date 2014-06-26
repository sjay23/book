<?php
namespace Admin\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Paginator\Adapter\DbSelect;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Category
{
    protected $adapter;
    protected $inputFilter;      
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
    public function GetCategoryobj($id)
    {
        $sql    = new Sql($this->adapter);
        $select = $sql->select()->from('category')->where('category_id =' . $id);
        $statement = $this->adapter->createStatement();
        $select->prepareStatement($this->adapter, $statement);
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());
            
        return $resultSet->current();

     }

    public function exchangeArray($data)
    {
        $this->id     = (isset($data['id']))     ? $data['id']     : null;
        $this->name = (isset($data['name'])) ? $data['name'] : null;
    }
 
    // Add the following method:
    public function getArrayCopy()
    {
        return get_object_vars($this);
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
        $update->where(array('category_id'=>$data['category_id']));
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

     public function getInputFilter()
     {
         if (!$this->inputFilter) {
             $inputFilter = new InputFilter();

             $inputFilter->add(array(
                 'name'     => 'category_id',
                 'required' => true,
                 'filters'  => array(
                     array('name' => 'Int'),
                 ),
             ));

             $inputFilter->add(array(
                 'name'     => 'name',
                 'required' => true,
                 'filters'  => array(
                     array('name' => 'StripTags'),
                     array('name' => 'StringTrim'),
                 ),
                 'validators' => array(
                     array(
                         'name'    => 'StringLength',
                         'options' => array(
                             'encoding' => 'UTF-8',
                             'min'      => 1,
                             'max'      => 64,
                         ),
                     ),
                 ),
             ));


             $this->inputFilter = $inputFilter;
         }

         return $this->inputFilter;
     }

}