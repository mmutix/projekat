<?php

class Zend_View_Helper_CatalogUrl extends Zend_View_Helper_Abstract {

    public function catalogUrl($catalog) {

        return $this->view->url(array(
                    'id' => $catalog['id'],
                    'catalog_slug' => $catalog['title']
                        ), 'catalog-route', true);
    }

}
