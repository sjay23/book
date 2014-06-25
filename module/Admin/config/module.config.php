<?
return array(
     'controllers' => array(
         'invokables' => array(
             'Admin\Controller\Admin' => 'Admin\Controller\IndexController'
         ),
     ),

     // The following section is new and should be added to your file
     'router' => array(
         'routes' => array(
             'admin' => array(
                 'type'    => 'segment',
                 'options' => array(
                     'route'    => '/admin[/][:action][/:id]',
                     'constraints' => array(
                         'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                         'id'     => '[0-9]+',
                     ),
                     'defaults' => array(
                         'controller' => 'Admin\Controller\Admin',
                         'action'     => 'index',
                     ),
                 ),
             ),
         ),
     ),

     'view_manager' => array(
        'template_map' => array(
            'adminLayout'             => __DIR__ . '/../view/layout/layout.phtml',
            'admin/index/index' => __DIR__ . '/../view/admin/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),   
         'template_path_stack' => array(
             'admin' => __DIR__ . '/../view',
         ),
     ),
 );