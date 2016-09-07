<?php

class Zend_View_Helper_AboutUsUrl extends Zend_View_Helper_Abstract {

    public function aboutUsUrl($aboutUs) {

        return $this->view->url(array(
                    'id' => $aboutUs['id'],
                    'aboutus_slug' => $aboutUs['title']
                        ), 'aboutus-route', true);
    }

}
