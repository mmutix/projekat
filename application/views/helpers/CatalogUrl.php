<?php

class Zend_View_Helper_CatalogueUrl extends Zend_View_Helper_Abstract {

    public function catalogueUrl($catalogue) {

        return $this->view->url(array(
                    'id' => $catalogue['id'],
                    'catalogue_slug' => $catalogue['title']
                        ), 'catalogue-route', true);
    }
    public function productItemUrl($productItem) {
        
        return $this->view->url(array(
            'id' => $productItem['id'],
            'product_item_slug' => $productItem['title']
            
        ), 'product-item-route', true);
        
    }
    
}
