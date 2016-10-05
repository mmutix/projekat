<?php
class Application_Form_Frontend_Contact extends Zend_Form
{
    public function init() {
        $name = new Zend_Form_Element_Text('name');
        //$firstName->addFilter(new Zend_Filter_StringTrim());
        //$firstName->addValidator(new Zend_Validate_StringLength(array('min'=>3, 'max'=>255)));
        $name->addFilter('StringTrim')
                ->addFilter('StripTags')
                ->addValidator('StringLength', false, array('min'=>3, 'max'=>255))
                ->setRequired(true);
        $this->addElement($name);
        
        $email = new Zend_Form_Element_Text('email');
        $email->addFilter('StringTrim')
                ->addFilter('StripTags')
                ->addValidator('EmailAddress', false, array('domain'=>false))
                ->setRequired(true);
        $this->addElement($email);
        
        $message = new Zend_Form_Element_Textarea('message');
        $message->addFilter('StringTrim')
                ->addFilter('StripTags')
               ->setRequired(true);
        $this->addElement($message);
    }
}