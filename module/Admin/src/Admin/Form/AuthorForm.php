<?php
namespace Admin\Form;

 use Zend\Form\Form;

 class AuthorForm extends Form
 {
     public function __construct($name = null)
     {
         // we want to ignore the name passed
         parent::__construct('author');

         $this->add(array(
             'name' => 'author_id',
             'attributes' => array(
             'class' => 'span4',
                ),
             'type' => 'Hidden',
         ));
         $this->add(array(
             'name' => 'name',
             'type' => 'Text',
             
             'attributes' => array(
                'id' =>'appendedInputname',
                'class' => 'input-large',
                'placeholder' => 'Введите имя автора',
                ),
         ));         
         $this->add(array(
             'name' => 'last_name',
             'type' => 'Text',
             
             'attributes' => array(
                'id' =>'appendedInputprice',
                'class' => 'input-large',
                'placeholder' => 'Введите фамилию автора',
                ),
         ));
         $this->add(array(
             'name' => 'btn_addauthor',
             'type' => 'Submit',
             
             'attributes' => array(
                 'value' => 'Добавить',
                 'id' => 'submitbutton',
                 'class' => 'btn btn-primary',
             ),
         ));
     }
 }