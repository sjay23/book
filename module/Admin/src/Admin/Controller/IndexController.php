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
        return new ViewModel();
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

    public function authorAction()
    {
		if (!$this->zfcUserAuthentication()->hasIdentity()) {
           return $this->redirect()->toRoute(static::ROUTE_LOGIN);
        }
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
                    rename(DIR . $bookImage, DIR . $new_image);
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
