<?php
namespace Admin\Form;

 use Zend\Form\Form;

 class CategoryForm extends Form
 {
     public function __construct($name = null)
     {
         // we want to ignore the name passed
         parent::__construct('category');

         $this->add(array(
             'name' => 'category_id',
             'attributes' => array(
             'class' => 'span4',
                ),
             'type' => 'Hidden',
         ));
         $this->add(array(
             'name' => 'name',
             'type' => 'Text',
             
             'attributes' => array(
                'id' =>'appendedInputButton',
                'class' => 'span3',
                'placeholder' => 'Введите название рубрики',
                ),
         ));
         $this->add(array(
             'name' => 'btn_addcat',
             'type' => 'Submit',
             
             'attributes' => array(
                 'value' => 'Добавить',
                 'id' => 'submitbutton',
                 'class' => 'btn btn-primary',
             ),
         ));
     }
 }