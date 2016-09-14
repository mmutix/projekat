<?php

class Zend_View_Helper_NewsImgUrl extends Zend_View_Helper_Abstract {

    public function newsImgUrl($news) {

        $newsImgFileName = $news['id'] . '.jpg';

        $newsImgFilePath = PUBLIC_PATH . '/uploads/news/' . $newsImgFileName;
        if (is_file($newsImgFilePath)) {
            return $this->view->baseUrl('/uploads/news/' . $newsImgFileName);
        } else {
            return $this->view->baseUrl('/uploads/news/no-image.jpg');
        }
    }

}
