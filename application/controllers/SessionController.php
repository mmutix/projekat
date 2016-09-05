<?php
class SessionController extends Zend_Controller_Action {
    
    public function init() {
        $this->view->dontShowPrice = true;
        $this->view->dontShowInformation = false;
    }
    
    public function loginAction() {
        
    }
    
    public function logoutAction() {
        
    }
    
    public function passwordAction() {
        
    }
    
    
    public function registrationAction() {
        
    }
    
    public function userpanelAction() {
        $this->view->dontShowInformation = true;
    }
}