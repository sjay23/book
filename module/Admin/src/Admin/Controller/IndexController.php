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
use ZfcUser\Service\User as UserService;
 use Zend\Db\Adapter\Adapter;

class IndexController extends AbstractActionController
{
	const ROUTE_LOGIN        = 'zfcuser/login';

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
        $adapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $book  = new Book($adapter);       
        $books = $book->getBooks();

        return new ViewModel();
    }

    public function categoryAction()
    {
		if (!$this->zfcUserAuthentication()->hasIdentity()) {
           return $this->redirect()->toRoute(static::ROUTE_LOGIN);
        }
        return new ViewModel();
    }
    public function authorAction()
    {
		if (!$this->zfcUserAuthentication()->hasIdentity()) {
           return $this->redirect()->toRoute(static::ROUTE_LOGIN);
        }
        return new ViewModel();
    }
}
