<?php

class Application_Form_Admin_ProfileEdit extends Zend_Form
{
	public function init() {
		
		$firstName = new Zend_Form_Element_Text('first_name');
		$firstName->addFilter('StringTrim')
			->addValidator('StringLength', false, array('min' => 3, 'max' => 255))
			->setRequired(true);
		
		$this->addElement($firstName);
		
		$lastName = new Zend_Form_Element_Text('last_name');
		$lastName->addFilter('StringTrim')
			->addValidator('StringLength', false, array('min' => 3, 'max' => 255))
			->setRequired(true);
		$this->addElement($lastName);
		
		$email = new Zend_Form_Element_Text('email');
		$email->addFilter('StringTrim')
			->addValidator('EmailAddress', false, array('domain' => false))
			->setRequired(true);
		$this->addElement($email);
	}
}