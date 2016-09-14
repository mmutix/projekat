<?php

class Zend_View_Helper_ServiceImgUrl extends Zend_View_Helper_Abstract {

    public function serviceImgUrl($service) {

        $serviceImgFileName = $service['id'] . '.jpg';

        $serviceImgFilePath = PUBLIC_PATH . '/uploads/services/' . $serviceImgFileName;
        if (is_file($serviceImgFilePath)) {
            return $this->view->baseUrl('/uploads/services/' . $serviceImgFileName);
        } else {
            return $this->view->baseUrl('/uploads/services/no-image.jpg');
        }
    }

}
