<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Admin\Model\Book;
use Admin\Model\Category;
use Admin\Model\Author;
use ZfcUser\Service\User as UserService;
use Zend\Db\Adapter\Adapter;
use Admin\Form\CategoryForm;   
use Zend\Validator\AbstractValidator;
use Zend\Validator\NotEmpty;
use Zend\Session\Container;

class IndexController extends AbstractActionController
{
    const ROUTE_LOGIN        = 'zfcuser/login';
	const ROUTE_BOOK       = 'admin';

    public function indexAction()
    {
		if (!$this->zfcUserAuthentication()->hasIdentity()) {
           return $this->redirect()->toRoute(static::ROUTE_LOGIN);
        }
        return new ViewModel();
    }

    public function bookAction()
    {
		if (!$this->zfcUserAuthentication()->hasIdentity()) {
           return $this->redirect()->toRoute(static::ROUTE_LOGIN);
        }
        $session = new Container('book_delete');
        $data['book_delete'] = $session->success;

        $session = new Container('success');
        $data['success'] = $session->success;

        $data=array();
        $adapter    = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $book    = new Book($adapter);  

        $request = $this->getRequest();
        if ($request->isPost()) {
            $bookName           = $request->getPost('name');
            $data['bookName']   = $bookName;
            $bookImage          = $request->getPost('image');
            $data['bookImage']  = $bookImage;
            $bookAuthor         = $request->getPost('author');
            $data['bookAuthor'] = $bookAuthor;
            $bookCat            = $request->getPost('cat');
            $data['bookCat']    = $bookCat;

            $validator = new NotEmpty();
            if ($validator->isValid($bookName)) {
            } else {
                foreach ($validator->getMessages() as $messageId => $message) {
                    $errors[]="Название не должно быть пустым";
                }
                 $data['errors']   = $errors;
            }
            if ($validator->isValid($bookImage)) {
            } else {
                foreach ($validator->getMessages() as $messageId => $message) {
                    $errors[]="Картинку не загрузили";
                }
                 $data['errors']   = $errors;
            }
            if ($validator->isValid($bookAuthor)) {
            } else {
                foreach ($validator->getMessages() as $messageId => $message) {
                    $errors[]="Выберите автора";
                }
                 $data['errors']   = $errors;
            }
            if ($validator->isValid($bookCat)) {
            } else {
                foreach ($validator->getMessages() as $messageId => $message) {
                    $errors[]="Выберите категорию";
                }
                 $data['errors']   = $errors;
            }

            if (isset($bookAuthor) && !empty($bookAuthor)) {
                $bookAuthor = implode(",", $bookAuthor);
            }
            
            if (isset($bookCat) && !empty($bookCat)) {
                $bookCat = implode(",", $bookCat);
            }
            if (!empty($bookImage)) {
                if (file_exists(DIR . $bookImage)) {
                    $image = DIR . $bookImage;
                } else {
                    $image = false;
                }
            }
            if(!isset($data['errors'])){
                $res = $book->AddBook(array(
                    'name'   => trim(htmlspecialchars($bookName)),
                    'image'  => $bookImage,
                    'author' => $bookAuthor,
                    'cat'    => $bookCat
                ));
                if ($res) {
                    $type_image = substr(strrchr($bookImage, '.'), 1);
                    $new_image  = '/upload/book/book-' . $res . '.' . $type_image;
                    rename(DIR .'/public'. $bookImage, DIR .'/public'. $new_image);
                    $book->EditImageBook($new_image, $res);
                    $session = new Container('success');
                    $session->success = 'success';
                   $this->redirect()->toUrl('/admin/book');
                } else {
                    $data['errors'] = $book->getErrors();
                }
            }

        }
        
             
        $books      = $book->getBooks();
        $category   = new Category($adapter);       
        $categories = $category->getCategories();
        $author     = new Author($adapter);       
        $authors    = $author->getAuthors();
        $data['books']   = $books;
        $data['categories']   = $categories;
        $data['authors']   = $authors;

        return new ViewModel($data);
    }

    public function categoryAction()
    {
		if (!$this->zfcUserAuthentication()->hasIdentity()) {
           return $this->redirect()->toRoute(static::ROUTE_LOGIN);
        }
        $form = new CategoryForm();
        $data     = array();
        $request = $this->getRequest();
        $adapter    = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');       
        $category   = new Category($adapter);  
        if ($request->isPost()) {

            $form->setInputFilter($category->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $categoryName = $_POST['name'];
                
                $res = $category->AddCategory(array(
                    'name' => trim(htmlspecialchars($categoryName))
                ));
                
                if ($res) {
                    $data['success'] = '';
                } else {
                    $data['errors'] = '';
                }
            }
        }
        $categories         = $category->getCategories();
        $data['categories'] = $categories;
        $data['form']=  $form;

        return new ViewModel($data);
       
    }


    
    public function deletecategoryAction()
    {
        if (!$this->zfcUserAuthentication()->hasIdentity()) {
           return $this->redirect()->toRoute(static::ROUTE_LOGIN);
        }

        $data     = array();
        $adapter    = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');       
        $category   = new Category($adapter); 
        
        if (isset($_GET['id'])) {
            $catid = $_GET['id'];
                
            $res = $category->DeleteCategory($catid);
            $this->redirect()->toUrl('/admin/category');
            return new ViewModel($data);
        }
        $this->redirect()->toUrl('/admin/category');
    }
    
    public function getcategoryAction()
    {
        $data = array();
        $adapter    = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $category    = new Category($adapter); 
        $json=''; 
        if (isset($_GET['id'])) {
            $categoryid = $_GET['id'];
            $category   = $category->GetCategory($categoryid);
            $json=json_encode($category);
        }
        $view    = new ViewModel(array('json'=>$category));
        $view->setTerminal(true);
        return $view;
    }
    
    public function editcategoryAction()
    {

        $data = array();
        $adapter    = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $category    = new Category($adapter); 
        $form = new CategoryForm();
        if (isset($_GET['category_id'])) {
            $categoryid = $_GET['category_id'];
            $category1   = $category->GetCategoryobj($categoryid);
            $data['category'] = $category1;
            $form->bind($category1);
        }
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $form->setData($request->getPost());
            $form->setInputFilter($category->getInputFilter());
           
            if ($form->isValid()) { 
                $data_cat = array(
                    'category_id'   => $_POST['category_id'],
                    'name' => trim(htmlspecialchars($_POST['name']))
                );
                
                $res = $category->EditCategory($data_cat);
                
                if ($res) {
                    $json['success'] = '';
                } else {
                    $json['errors'] = '';
                }
                $json=json_encode($json);
                $data['json'] = $json;
            }
            $data['form'] = $form;
        }else{
            
            
            
            $data['form'] = $form;
        }

        
        $view    = new ViewModel($data);
        $view->setTerminal(true);
        return $view;
    }

    public function AuthorAction()
    {
        if (!$this->zfcUserAuthentication()->hasIdentity()) {
           return $this->redirect()->toRoute(static::ROUTE_LOGIN);
        }
        
        $data     = array();
        $adapter    = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');       
        $author   = new Author($adapter);  
        if (isset($_POST['btn_addauthor'])) {
            $authorName = $_POST['name'];
            $authorlast_name = $_POST['last_name'];
            
            $res = $author->AddAuthor(array(
                'name' => trim(htmlspecialchars($authorName)),
                'last_name' => trim(htmlspecialchars($authorlast_name))
            ));
            
            if ($res) {
                $data['success'] = '';
            } else {
                $data['errors'] = '';
            }
        }
        
        $authors         = $author->getAuthors();
        $data['authors'] = $authors;


        return new ViewModel($data);
       
    }


    
    public function deleteauthorAction()
    {
        if (!$this->zfcUserAuthentication()->hasIdentity()) {
           return $this->redirect()->toRoute(static::ROUTE_LOGIN);
        }

        $data     = array();
        $adapter    = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');       
        $author   = new Author($adapter); 
        
        if (isset($_GET['id'])) {
            $authorid = $_GET['id'];
                
            $res = $author->DeleteAuthor($authorid);
            $this->redirect()->toUrl('/admin/author');
            return new ViewModel($data);
        }
        $this->redirect()->toUrl('/admin/author');
    }
    
    public function getauthorAction()
    {
        $data = array();
        $adapter    = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $author    = new Author($adapter); 
        $json=''; 
        if (isset($_GET['id'])) {
            $authorid = $_GET['id'];
            $author   = $author->GetAuthor($authorid);
            $json=json_encode($author);
        }
        $view    = new ViewModel(array('json'=>$json));
        $view->setTerminal(true);
        return $view;
    }
    
    public function editauthorAction()
    {

        $data = array();
        $adapter    = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $author    = new Author($adapter); 

        if (isset($_POST['id']) && isset($_POST['name'])) {
            $authorid    = $_POST['id'];
            $authorname  = $_POST['name'];
            $authorlast_name  = $_POST['last_name'];
            $data_author = array(
                'id'   => $authorid,
                'name' => trim(htmlspecialchars($authorname)),
                'last_name' => trim(htmlspecialchars($authorlast_name))
            );
            
            $res = $author->EditAuthor($data_author);
            
            if ($res) {
                $data['success'] = '';
            } else {
                $data['errors'] = '';
            }
            
        }

        $json=json_encode($data);
        $view    = new ViewModel(array('json'=>$json));
        $view->setTerminal(true);
        return $view;
    }

    public function deletebookAction()
    {
        if (!$this->zfcUserAuthentication()->hasIdentity()) {
           return $this->redirect()->toRoute(static::ROUTE_LOGIN);
        }

        $data = array();
        $adapter    = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $book = new Book($adapter);
        
        if (isset($_GET['id'])) {
            $bookid = $_GET['id'];
            
            $res = $book->DeleteBook($bookid);
            
            if ($res) {
                if (isset($_GET['r'])) {
                    $this->redirect()->toRoute(static::ROUTE_LOGIN);
                }else{
                    $session = new Container('success');
                    $session->success = 'book_delete';
                    $this->redirect()->toUrl('/admin/book');
                }               
            } else {
               # $data['errors'] = $book->getErrors();
            }
             return new ViewModel(array('data'=>$data));
        }
        $this->redirect()->toRoute(static::ROUTE_LOGIN);
        return new ViewModel();
    }


    public function saveimageAction()
    {
        if (!$this->zfcUserAuthentication()->hasIdentity()) {
           return $this->redirect()->toRoute(static::ROUTE_LOGIN);
        }
        if (!empty($_FILES)) {
            // Проверяем загружен ли файл
            if (is_uploaded_file($_FILES['image']['tmp_name'])) {
                // Поверка картинки на валидность
                $allowed_filetypes = array(
                    'png'  => 'image/png',
                    'jpg'  => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'gif'  => 'image/gif'
                );
                
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                
                if (!array_key_exists($ext, $allowed_filetypes)) {
                    echo json_encode(array(
                        'error' => 'Неподдерживаемый формат файла!'
                    ));
                    die();
                }
                
                $imageinfo = getimagesize($_FILES['image']['tmp_name']);
                
                // Проверяем MIME-тип файла
                if (!in_array($imageinfo['mime'], $allowed_filetypes)) {
                    echo json_encode(array(
                        'error' => 'Неподдерживаемый тип файла!'
                    ));
                    die();
                }
                
                // Проверяем размер файла
                if ($_FILES['image']['size'] > 1 * 1024 * 1024) {
                    echo json_encode(array(
                        'error' => 'Размер файла превышает 1MB!'
                    ));
                    die();
                }
                
                // Похоже файл таки загружен успешно
                $ext       = array_search($imageinfo['mime'], $allowed_filetypes);
                $image     = 'temp_images.' . $ext;
                $new_image = DIR . '/public/upload/images/' . $image;
                move_uploaded_file($_FILES['image']['tmp_name'], $new_image);
                
                // Обрезаем по размерам
                $image =  '/upload/images/' . $image;
                $data  = array(
                    'success' => TRUE,
                    'image' => $image,
                    'filename' => $new_image
                );
                echo json_encode($data);
                die();
            } else {
                $data = array(
                    'error' => 'Ошибка при загрузке файла!'
                );
                echo json_encode($data);
                die();
            }
        }
       
    }

    public function getbookAction()
    {
        $data = array();
        $adapter    = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $book    = new Book($adapter); 
        $json=''; 
        if (isset($_GET['id'])) {
            $bookid = $_GET['id'];
            $book   = $book->GetBook($bookid);
            $json=json_encode($book);
        }
        $view    = new ViewModel(array('json'=>$json));
        $view->setTerminal(true);
        return $view;
    }

    public function editbookAction()
    {
        $data = array();
        $adapter    = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $book    = new Book($adapter); 
        
        if (isset($_POST['id']) && isset($_POST['name'])) {
            $bookid     = $_POST['id'];
            $bookname   = $_POST['name'];
            $bookAuthor = $_POST['author'];
            $bookCat    = $_POST['cat'];
            $bookImage  = $_POST['image'];
            $image      = false;
            
            if (!empty($bookImage)) {
                if (file_exists(DIR .'/public'. $bookImage)) {
                    $image = DIR .'/public'. $bookImage;
                } else {
                    $image = false;
                }
            }
            
            $data_book = array(
                'id'     => $bookid,
                'name'   => trim(htmlspecialchars($bookname)),
                'image'  => $image,
                'cat'    => $bookCat,
                'author' => $bookAuthor
            );
            
            $res = $book->EditBook($data_book);
            
            if ($res) {
                $type_image = substr(strrchr($bookImage, '.'), 1);
                $new_image  = '/upload/book/book-' . $res . '.' . $type_image;
                if ($bookImage != $new_image) {
                    rename(DIR .'/public'. $bookImage, DIR .'/public'. $new_image);
                }
                $book->EditImageBook($new_image, $res);
                $data['success'] = '';
            } else {
                #$data['errors'] = $book->getErrors();
            }
            
        }
        $json=json_encode($data);
        $view    = new ViewModel(array('json'=>$json));
        $view->setTerminal(true);
        return $view;
    }
}
