<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Model\Book;
use Admin\Model\Category;
use Admin\Model\Author;
use Zend\Db\Adapter\Adapter;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $data     = array();
        $adapter    = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $category = new Category($adapter);
        $book     = new Book($adapter);
        $author   = new Author($adapter);
        
        $categories = $category->getCategories();
        
        $data['categories'] = array();
        foreach ($categories as $cat) {
            $data['categories'][] = array(
                'category_id' => $cat['category_id'],
                'name' 		  => $cat['name'],
                'href' 		  => '/cat?id=' . $cat['category_id']
            );
        }
        
        $authors = $author->getAuthors();

        $data['authors'] = array();
        foreach ($authors as $author1) {
            $data['authors'][] = array(
                'author_id' => $author1['author_id'],
                'name' 		=> $author1['name'],
                'href' 		=> '/author?id=' . $author1['author_id']
            );
        }
        $books = $book->getBooks();

        $data['books'] = array();
        foreach ($books as $book) {
           # $this->image($book['data']['image'], 200, 200);
            $data['books'][] = array(
                'book_id' => $book['data']['book_id'],
                'name' 	  => $book['data']['name'],
                'image'   => 'cache' . $book['data']['image'],
                'author'  => $book['authors'],
                'cat'	  => $book['categories'],
                'href' 	  => '/book?id=' . $book['data']['book_id']
            );
        }

        return new ViewModel($data);
    }

    public function bookAction()
    {
        $data     = array();
        $adapter    = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $category = new Category($adapter);
        $book     = new Book($adapter);
        $author   = new Author($adapter);
        
        $categories = $category->getCategories();
        $data['categories'] = $categories;
        
        $authors = $author->getAuthors();
        $data['authors'] = $authors;
        
        if (isset($_GET['id'])) {
            //панель админа

			if ($this->zfcUserAuthentication()->hasIdentity()) {
	           $data['panel_admin'] = true;    
	        }
  

            $book_id = $_GET['id'];
            $books   = $book->getBook($book_id);
            if (isset($books['data'][0])) {
                if (!file_exists(DIR . 'cache' . $books['data'][0]['image'])) {
                    #$this->image($books['data'][0]['image'], 200, 200);
                }
                $authors = array();
                if (isset($books['authors'][0])) {
                    foreach ($books['authors'] as $key => $author) {
                        $authors[] = array(
                            'author_id' => $author['author_id'],
                            'name'		=> $author['name'],
                            'last_name' => $author['last_name'],
                            'href' 		=> '/author?id=' . $author['author_id']
                        );
                    }
                }
                $categories = array();
                if (isset($books['cats'][0])) {
                    foreach ($books['cats'] as $key => $cat) {
                        $categories[] = array(
                            'author_id' => $cat['category_id'],
                            'name' 		=> $cat['name'],
                            'href'		=> '/cat?id=' . $cat['category_id']
                        );
                    }
                }
                $data['book'] = array(
                    'book_id' => $books['data'][0]['book_id'],
                    'name' 	  => $books['data'][0]['name'],
                    'image'   =>  $books['data'][0]['image'],
                    'author'  => $authors,
                    'cat' 	  => $categories,
                    'href'    => '/book?id=' . $books['data'][0]['book_id'],
                    'href_edit'    => '/admin/book?id=' . $books['data'][0]['book_id'],
                    'href_delete'    => '/admin/deletebook?id=' . $books['data'][0]['book_id'].'&r'
                );
            } else {
               # HTTP::redirect('404');
            }
        } else {
            $books = $book->getBooks();
            foreach ($books as $book) {
                if (!file_exists(DIR . 'cache' . $book['data']['image'])) {
                    #$this->image($book['data']['image'], 200, 200);
                }
                $authors = array();
                if (isset($book['authors'][0])) {
                    foreach ($book['authors'] as $key => $author) {
                        $authors[] = array(
                            'author_id' => $author['author_id'],
                            'name' 		=> $author['name'],
                            'last_name' => $author['last_name'],
                            'href' 		=> '/author?id=' . $author['author_id']
                        );
                    }
                }
                $data['books'][] = array(
                    'book_id' => $book['data']['book_id'],
                    'name' 	  => $book['data']['name'],
                    'image'   =>  $book['data']['image'],
                    'author'  => $authors,
                    'cat' 	  => $book['categories'],
                    'href'    => '/book?id=' . $book['data']['book_id']
                );
            }
            $data['books_all'] = '';
        }
			return new ViewModel($data);
    }

	public function authorAction()
    {
        $data   = array();
        $adapter    = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $book   = new Book($adapter);
        $author = new Author($adapter);
        
        //выборка авторов
        $authors = $author->getAuthors();
        $data['authors'] = array();
        foreach ($authors as $author1) {
            $data['authors'][] = array(
                'author_id' => $author1['author_id'],
                'name'      => $author1['name'],
                'last_name' => $author1['last_name'],
                'href'      => '/author?id=' . $author1['author_id']
            );
        }
        
        $data['book'] = array();
        
        if (isset($_GET['id'])) {
            $author_id = $_GET['id'];

            $authores = $author->GetAuthor($author_id);
            if (isset($authores['name'])) {
                $data['author'] = array(
                'name' => $authores['name'] . ' ' . $authores['last_name'],
                'href' => '/author?id='.$authores['author_id']
                );
            }

            $res = $book->GetAuthorBook($author_id); //получаем все книги автора
            if (isset($res[0])) {
                foreach ($res as $book) {
                    if (!file_exists(DIR . 'cache' . $book['data']['image'])) {
                        #$this->image($book['data']['image'], 200, 200);
                    }
                    $authors = array();
                    if (isset($book['authors'][0])) {
                        foreach ($book['authors'] as $key => $author) {
                            $authors[] = array(
                                'author_id' => $author['author_id'],
                                'name'      => $author['name'],
                                'last_name' => $author['last_name'],
                                'href'      => '/author?id=' . $author['author_id']
                            );
                        }
                    }
                    $data['books'][] = array(
                        'book_id' => $book['data']['book_id'],
                        'name'    => $book['data']['name'],
                        'image'   =>  $book['data']['image'],
                        'href'    => '/book?id=' . $book['data']['book_id'],
                        'author'  => $authors
                    );
                }
            }
        } else {
            $data['author_all'] = ''; 
        }
		return new ViewModel($data);
    }

    public function catAction()
    {
        $data     = array();
        $adapter    = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $category = new Category($adapter);
        $book     = new Book($adapter);
        $author   = new Author($adapter);
        
        $categories = $category->getCategories();

        $data['categories'] = array();
        foreach ($categories as $cat) {
            $data['categories'][] = array(
                'category_id' => $cat['category_id'],
                'name'        => $cat['name'],
                'href'        => '/cat?id=' . $cat['category_id']
            );
        }
        
        $authors = $author->getAuthors();
        $data['authors'] = $authors;
        
        $data['book'] = array();
        if (isset($_GET['id'])) {
            $cat_id = $_GET['id'];

            $cat = $category->GetCategory($cat_id);
            if (isset($cat['name'])) {
                $data['category'] = array(
                'name' => $cat['name'],
                'href' => '/cat?id='.$cat['category_id']
                );
            }

            $res = $book->GetCatBook($cat_id);
            if (isset($res[0])) {
                foreach ($res as $book) {
                    if (!file_exists(DIR . 'cache' . $book['data']['image'])) {
                        #$this->image($book['data']['image'], 200, 200);
                    }
                    $authors = array();
                    if (isset($book['authors'][0])) {
                        foreach ($book['authors'] as $key => $author) {
                            $authors[] = array(
                                'author_id' => $author['author_id'],
                                'name'      => $author['name'],
                                'last_name' => $author['last_name'],
                                'href'      => '/author?id=' . $author['author_id']
                            );
                        }
                    }
                    $data['books'][] = array(
                        'book_id' => $book['data']['book_id'],
                        'name'    => $book['data']['name'],
                        'image'   =>  $book['data']['image'],
                        'href'    => '/book?id=' . $book['data']['book_id'],
                        'author'  => $authors
                    );
                }
            }
        } else {
            
            $data['cat_all'] = '';
        }
		return new ViewModel($data);
    }
}
