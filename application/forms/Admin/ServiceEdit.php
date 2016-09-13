<?php
class Application_Form_Admin_ServiceEdit extends Zend_Form
{
    public function init() {
        $title = new Zend_Form_Element_Text('title');
        
        $title->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min'=>3, 'max'=>255))
                ->setRequired(true);
        $this->addElement($title);
        
        $icon = new Zend_Form_Element_Text('icon');
        $icon->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min'=>3, 'max'=>255))
                ->setRequired(true);
        $this->addElement($icon);
        
        $description = new Zend_Form_Element_Textarea('description');
        $description->addFilter('StringTrim')
               ->setRequired(true);
        $this->addElement($description);
    }
 
}
?>
