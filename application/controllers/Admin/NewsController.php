<?php
class Admin_NewsController extends Zend_Controller_Action {
    
    public function init() {
        
    }
    
    public function indexAction() {
        
        $flashMessenger = $this->getHelper('FlashMessenger');
		
        $systemMessages = array(
                'success' => $flashMessenger->getMessages('success'),
                'errors' => $flashMessenger->getMessages('errors')
        );
        
        $newsCmsTable = new Application_Model_DbTable_CmsNews();
        
        $news = $newsCmsTable->search( array(
                'filters' => array(
                    'status' => Application_Model_DbTable_CmsNews::STATUS_ENABLED
                ),
                'orders' => array(
                    'date_posted' => 'ASC'
                )
            )
        );
        
        $this->view->news = $news;
    }
    
    public function addAction() {
        
    }
    
    public function editAction() {
        
    }
    
    public function deleteAction() {
        
    }
    
    
    
}
