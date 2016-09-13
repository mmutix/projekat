<?php
class Application_Form_Admin_ServiceAdd extends Zend_Form
 {
    public function init() {
        $title = new Zend_Form_Element_Text('title');
        $title->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min' => 3, 'max' => 255))
                ->setRequired(true);
        $this->addElement($title);
        $icon = new Zend_Form_Element_Text('icon');
        $icon->addFilter('StringTrim')
                ->addValidator('StringLength', false, array('min' => 3, 'max' => 255))
                ->setRequired(true);
        $this->addElement($icon);
        $description = new Zend_Form_Element_Textarea('description');
        $description->addFilter('StringTrim')
                ->setRequired(true);
        $this->addElement($description);
        $servicePhoto = new Zend_Form_Element_File('service_photo');
        $servicePhoto->addValidator('Count', true, 1)
                ->addValidator('MimeType', true, array('image/jpeg', 'image/gif', 'image/png'))
                ->addValidator('ImageSize', false, array(
                    'minwidth' => 160,
                    'minheight' => 160,
                    'maxwidth' => 2000,
                    'maxheight' => 2000
                ))
                ->addValidator('Size', false, array(
                    'max' => '10MB'
                ))
                ->setValueDisabled(true)
                ->setRequired(false);
        $this->addElement($servicePhoto);
    }
}
