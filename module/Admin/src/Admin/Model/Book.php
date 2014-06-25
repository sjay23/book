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
  public function GetBook($id)
  {
        $sql    = new Sql($this->adapter);
        $select = $sql->select()->from('book')->order('data_added DESC')->where('book_id =' . $id);
        
        $sqlString = $sql->getSqlStringForSqlObject($select);
        $results   = $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        $results   = $results->toArray();
        if (!isset($results[0]['book_id']))
            return FALSE;

            $select = $sql->select()->from('book_to_cat')
                                    ->join('category', 'book_to_cat.category_id = category.category_id')
                                    ->where('book_to_cat.book_id =' . $id);
            
            $sqlString = $sql->getSqlStringForSqlObject($select);
            $cats      = $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
            $cats      = $cats->toArray();
            
            $select = $sql->select()->from('book_to_author')
                                    ->join('author', 'book_to_author.author_id = author.author_id')
                                    ->where('book_to_author.book_id =' . $id);
            
            $sqlString = $sql->getSqlStringForSqlObject($select);
            $authors   = $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
            $authors   = $authors->toArray();
            $result  = array(
                'data' => $results,
                'categories' => $cats,
                'authors' => $authors
            );

        
        
        return $result;
  }
  public function AddBook($data)
  {

        $sql    = new Sql($this->adapter);
        $insert = $sql->insert('book'); 
        $newData = array('name'=> $data['name']);
        $insert->values($newData);
        $sqlString = $sql->getSqlStringForSqlObject($insert);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);  
        $book_id = $this->adapter->getDriver()->getLastGeneratedValue();

        $cats = explode(',', $data['cat']);
        foreach ($cats as $cat) {
            $insert = $sql->insert('book_to_cat'); 
            $newData = array('book_id'=> $book_id , 'category_id' => $cat);
            $insert->values($newData);
            $sqlString = $sql->getSqlStringForSqlObject($insert);
            $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        }    

        $authors = explode(',', $data['author']);
        foreach ($authors as $author) {
            $insert = $sql->insert('book_to_author'); 
            $newData = array('book_id'=> $book_id , 'author_id' => $author);
            $insert->values($newData);
            $sqlString = $sql->getSqlStringForSqlObject($insert);
            $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
        }    

    return $book_id;
  } 
  public function DeleteBook($id)
  {
    $sql    = new Sql($this->adapter);
    $delete = $sql->delete()->from('book')->where('book_id='.$id);
    $sqlString = $sql->getSqlStringForSqlObject($delete);
    $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    $delete = $sql->delete()->from('book_to_cat')->where('book_id='.$id);
    $sqlString = $sql->getSqlStringForSqlObject($delete);
    $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    $delete = $sql->delete()->from('book_to_author')->where('book_id='.$id);
    $sqlString = $sql->getSqlStringForSqlObject($delete);
    $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);


    return TRUE;
  }   
  public function EditImageBook($image, $id)
  {
    $sql    = new Sql($this->adapter);
    $update = $sql->update();
    $update->table('book');
     $update->set(array(
          'image' => $image
     ));
    $update->where(array('book_id'=>$id));
    $sqlString = $sql->getSqlStringForSqlObject($update);
    $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    return TRUE;
  }   
  public function EditBook($data)
  {
    $sql    = new Sql($this->adapter);
    $update = $sql->update();
    $update->table('book');
     $update->set(array(
          'name' => $data['name']
     ));
    $update->where(array('book_id'=>$data['id']));
    $sqlString = $sql->getSqlStringForSqlObject($update);
    $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);


    $delete = $sql->delete()->from('book_to_cat')->where('book_id='.$data['id']);
    $sqlString = $sql->getSqlStringForSqlObject($delete);
    $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);

    $delete = $sql->delete()->from('book_to_author')->where('book_id='.$data['id']);
    $sqlString = $sql->getSqlStringForSqlObject($delete);
    $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);   


    $cats = explode(',',$data['cat']);
    foreach ($cats as $cat) {
        $insert = $sql->insert('book_to_cat'); 
        $newData = array('book_id'=> $data['id'] , 'category_id' => $cat);
        $insert->values($newData);
        $sqlString = $sql->getSqlStringForSqlObject($insert);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    }

    $authors = explode(',',$data['author']);
    foreach ($authors as $author) {
        $insert = $sql->insert('book_to_author'); 
        $newData = array('book_id'=> $data['id'] , 'author_id' => $author);
        $insert->values($newData);
        $sqlString = $sql->getSqlStringForSqlObject($insert);
        $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    }    

    return $data['id'];
  } 

 public function GetCatBook($id_cat)
  {
    $sql    = new Sql($this->adapter);
    
    $select = $sql->select()->from('book_to_cat')
                            ->join('book', 'book.book_id = book_to_cat.book_id')
                            ->where('book_to_cat.category_id =' . $id_cat)
                            ->order('data_added DESC');
    $sqlString = $sql->getSqlStringForSqlObject($select);
    $books  = $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    $books   = $books->toArray();               


    if (!isset($books[0]['book_id'])) return FALSE;

    $result=array();
    foreach ($books as $dat) {
      $authors=$this->GetBookAuthor($dat['book_id']);
      $cats=$this->GetBookCategory($dat['book_id']);
      $result[]=array(
        'data'=>$dat,
        'authors'=>$authors,
        'cats'=>$cats
      );
    }      
    return $result;
  }

  public function GetAuthorBook($author)
  {
    $sql    = new Sql($this->adapter);
    
    $select = $sql->select()->from('book_to_author')
                            ->join('book', 'book.book_id = book_to_author.book_id')
                            ->where('book_to_author.author_id =' . $author)
                            ->order('data_added DESC');
    $sqlString = $sql->getSqlStringForSqlObject($select);
    $books  = $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    $books   = $books->toArray(); 

      if (!isset($books[0]['book_id'])) return FALSE;
      $result=array();
      foreach ($books as $dat) {
        $authors=$this->GetBookAuthor($dat['book_id']);
        $cats=$this->GetBookCategory($dat['book_id']);
        $result[]=array(
          'data'=>$dat,
          'authors'=>$authors,
          'cats'=>$cats
        );
      }      
      return $result;
  }

  public function GetBookCategory($id)
  {
    $sql    = new Sql($this->adapter);
    
    $select = $sql->select()->from('book_to_cat')
                            ->join('category', 'book_to_cat.category_id = category.category_id')
                            ->where('book_to_cat.book_id =' . $id);
    $sqlString = $sql->getSqlStringForSqlObject($select);
    $cats  = $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    $cats   = $cats->toArray();    
    return $cats;
  }

  public function GetBookAuthor($id)
  {

    $sql    = new Sql($this->adapter);
    
    $select = $sql->select()->from('book_to_author')
                            ->join('author', 'book_to_author.author_id = author.author_id')
                            ->where('book_to_author.book_id =' . $id);
    $sqlString = $sql->getSqlStringForSqlObject($select);
    $authors  = $this->adapter->query($sqlString, Adapter::QUERY_MODE_EXECUTE);
    $authors   = $authors->toArray();    
       return $authors;
  }

}