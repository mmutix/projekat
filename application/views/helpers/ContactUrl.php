<?php

class Zend_View_Helper_ContactUrl extends Zend_View_Helper_Abstract {

    public function contactUrl($contact) {

        return $this->view->url(array(
                    'id' => $contact['id'],
                    'contact_slug' => $contact['title']
                        ), 'contact-route', true);
    }

}
