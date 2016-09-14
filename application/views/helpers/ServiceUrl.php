<?php

class Zend_View_Helper_ServiceUrl extends Zend_View_Helper_Abstract {

    public function serviceUrl($service) {

        return $this->view->url(array(
                    'id' => $service['id'],
                    'service_slug' => $service['title']
                        ), 'service-route', true);
    }

}
