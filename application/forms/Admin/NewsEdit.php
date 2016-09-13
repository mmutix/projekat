<?php
class Application_Form_Admin_NewsEdit extends Zend_Form
{ 
    public function init() {
        $title = new Zend_Form_Element_Text('title');
        
        $title->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min'=>3, 'max'=>255))
                ->setRequired(true);
        $this->addElement($title);
        
        $description = new Zend_Form_Element_Textarea('description');
        $description->addFilter('StringTrim')
               ->setRequired(true);
        $this->addElement($description);
    }
 
}
?>

