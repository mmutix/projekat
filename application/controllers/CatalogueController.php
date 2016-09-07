
<?php
class CatalogueController extends Zend_Controller_Action {
    
    public function indexAction() {
           $this->view->dontShowPrice = true;
           
    }

    public function productAction() {
        $this->view->dontShowPrice = false;
    }
    
    
}