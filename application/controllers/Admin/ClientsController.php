<?php

class Admin_ClientsController extends Zend_Controller_Action
{
	
	public function indexAction() {
		$flashMessenger = $this->getHelper('FlashMessenger');
		
		$systemMessages = array(
			
			'success' => $flashMessenger->getMessages('success'),
			'errors' => $flashMessenger->getMessages('errors')
		);
		
		// prikaz svih client-a
		$cmsClientsDbTable = new Application_Model_DbTable_CmsClients();
		
		// $select je objekat klase Zend_Db_Select
		$select = $cmsClientsDbTable->select();
		
		$select->order('order_number');
		
		//degug za db select - vraca se sql upit
		//die($select->assemble());
		
		$clients = $cmsClientsDbTable->fetchAll($select);
		
		$this->view->clients = $clients;
		$this->view->systemMessages = $systemMessages;
		
	}
	
	public function addAction() {
		
		$request = $this->getRequest();
		
		$flashMessenger = $this->getHelper('FlashMessenger');
		
		$systemMessages = array(
			'success' => $flashMessenger->getMessages('success'),
			'errors' => $flashMessenger->getMessages('errors'),
		);
		
		
		$form = new Application_Form_Admin_ClientAdd();
		//default form data
		$form->populate(array(	
		));
		if ($request->isPost() && $request->getPost('task') === 'save') {
			try {
				//check form is valid
				if (!$form->isValid($request->getPost())) {
					throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new client');
				}
				//get form data
				$formData = $form->getValues();
				
                                 unset($formData['client_photo']);
                                 
				//Insertujemo novi zapis u tabelu
				$cmsClientsTable = new Application_Model_DbTable_CmsClients();
				
				
            //insert client returns ID of the new client
                $clientId =  $cmsClientsTable->insertClient($formData);
                
                if($form->getElement('client_photo')->isUploaded()) {
                   
                    //photo is uploaded 
                    $fileInfos = $form->getElement('client_photo')->getFileInfo('client_photo');
                    $fileInfo = $fileInfos['client_photo'];
                    //$fileInfo = $_FILES["member_photo"];

                    try {
                        //open uploaded photo in temporary directory
                     $clientPhoto =  Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                     
                     $clientPhoto->fit(170, 70);
                     $clientPhoto->save(PUBLIC_PATH . '/uploads/clients/' . $clientId . '.jpg');
                     
                    } catch (Exception $ex) {
                        
                         $flashMessenger->addMessage('Client has been saved, but error occured during image processing', 'errors');
                        //redirect to same or another page
                        $redirector = $this->getHelper('Redirector');
                        $redirector->setExit(true)
                                ->gotoRoute(array(
                                    'controller' => 'admin_clients',
                                    'action' => 'edit',
                                    'id' => $clientId
                                       ), 'default', true);
                        
                    }
                }
            
                $flashMessenger->addMessage('Client has been saved', 'success');
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }
        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
    }
	
	public function editAction() {
		
		$request = $this->getRequest();
		
		$id = (int) $request->getParam('id');
		
		if ($id <= 0) {
			
			//prekida se izvrsavanje programa i prikazuje se "Page not found"
			throw new Zend_Controller_Router_Exception('Invalid client id: ' . $id, 404);
		}
		
		$cmsClientsTable = new Application_Model_DbTable_CmsClients();
		
		$client = $cmsClientsTable->getClientById($id);
		
		if (empty($client)) {
			throw new Zend_Controller_Router_Exception('No client is found with id: ' . $id, 404);
		}
		
		
		$flashMessenger = $this->getHelper('FlashMessenger');
		
		$systemMessages = array(
			'success' => $flashMessenger->getMessages('success'),
			'errors' => $flashMessenger->getMessages('errors'),
		);
		
		
		$form = new Application_Form_Admin_ClientEdit();
		//default form data
		$form->populate($client);
		if ($request->isPost() && $request->getPost('task') === 'update') {
			try {
				//check form is valid
				if (!$form->isValid($request->getPost())) {
					throw new Application_Model_Exception_InvalidInput('Invalid data was sent for client');
				}
				//get form data
				$formData = $form->getValues();
				
				 unset($formData['client_photo']);
                
                if($form->getElement('client_photo')->isUploaded()) {
                    //photo is uploaded 
                    
                    $fileInfos = $form->getElement('client_photo')->getFileInfo('client_photo');
                    $fileInfo = $fileInfos['client_photo'];
                    //$fileInfo = $_FILES["member_photo"];
                    
                    
                    try {
                        //open uploaded photo in temporary directory
                     $clientPhoto =  Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                     
                     $clientPhoto->fit(170, 70);
                     
                     $clientPhoto->save(PUBLIC_PATH . '/uploads/clients/' . $client['id'] . '.jpg');
                     
                    } catch (Exception $ex) {
                        
                        throw new Application_Model_Exception_InvalidInput('Error occured during image processing');
                      
                    }

                }
               
                
                $cmsClientsTable->updateClient($client['id'], $formData);
                
                // do actual task
                //save to database etc
                //set system message
                $flashMessenger->addMessage('Client has been updated', 'success');
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }
        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
        $this->view->client = $client;
	}
        
        public function deleteAction() {
        $request = $this->getRequest();
        if (!$request->isPost() || $request->getPost('task') != 'delete') {
            //request is not post redirect to index page
            //or task is not delete
            //redirect to index page
            //redirect to same or another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_clients',
                        //po default-u je index action ako se ne stavi drugacije
                        'action' => 'index',
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        try {
            //(int) sve sto nije integer pretvara se u nulu
            //read $_POST['id']
            $id = (int) $request->getPost('id');
            if ($id <= 0) {
                
                throw new Application_Model_Exception_InvalidInput('Invalid client id: ' . $id);
            }
            $cmsClientsTable = new Application_Model_DbTable_CmsClients();
            $client = $cmsClientsTable->getClientById($id);
            if (empty($client)) {
                throw new Application_Model_Exception_InvalidInput('No client is found with id: ' . $id, 'errors');
            }
            $cmsClientsTable->deleteClient($id);
            $flashMessenger->addMessage('Client ' . $client['first_name'] . ' ' . $client['last_name'] . 'has been deleted', 'success');
            //redirect on another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_clients',
                        'action' => 'index',
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_clients',
                        'action' => 'index',
                            ), 'default', true);
        }
    }

    public function disableAction(){
      
         $request = $this->getRequest();
         
         if(!$request->isPost()|| $request->getPost('task') != 'disable'){
             
             $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                                ), 'default', true);
         }
         
         $flashMessenger = $this->getHelper('FlashMessenger');
         
         try {
           //read $_POST['id']
           $id = (int) $request->getPost('id');
           if ($id <= 0) {
               throw new Application_Model_Exception_InvalidInput('Invalid client id: ' . $id, 'errors');
           }
           $cmsClientsTable = new Application_Model_DbTable_CmsClients();
           $client = $cmsClientsTable->getClientById($id);
           if (empty($client)) {
               throw new Application_Model_Exception_InvalidInput('No client is found with id: ' . $id, 'errors');
           }
           
           $cmsClientsTable->disableClient($id);
        
        
            $flashMessenger->addMessage('Client: ' . $client['name']  . ' has been disabled', 'success');
            
            //redirect on another page
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                                ), 'default', true);
             
         } catch (Application_Model_Exception_InvalidInput $ex) {
             
             $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            //redirect on another page
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                                ), 'default', true);
         }
    }
    
    public function enableAction(){
      
         $request = $this->getRequest();
         
         if(!$request->isPost()|| $request->getPost('task') != 'enable'){
          
             $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                                ), 'default', true);
         }
         
         $flashMessenger = $this->getHelper('FlashMessenger');
         
         try {
             //read $_POST['id']
           $id = (int) $request->getPost('id');
 
           if ($id <= 0) {
               throw new Application_Model_Exception_InvalidInput('Invalid client id: ' . $id, 'errors');
           }
           
        
           $cmsClientsTable = new Application_Model_DbTable_CmsClients();
           
           $client = $cmsClientsTable->getClientById($id);
           
           //ukoliko dobijeni service ne postoji
           if (empty($client)) {
               throw new Application_Model_Exception_InvalidInput('No client is found with id: ' . $id, 'errors');
           }
           
            $cmsClientsTable->enableClient($id);
        
        
            $flashMessenger->addMessage('Client: ' . $client['name'] . ' has been enabled', 'success');
            
            //redirect on another page
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                                ), 'default', true);
             
         } catch (Application_Model_Exception_InvalidInput $ex) {
             
             $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            //redirect on another page
            $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_clients',
                            'action' => 'index'
                                ), 'default', true);
         }
    }
    
    public function updateorderAction() {

        $request = $this->getRequest();

        if (!$request->isPost() || $request->getPost('task') != 'saveOrder') {

            //request is not post, 
            //or task is not saveOrder
            //redirecting to index page

            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_clients',
                        'action' => 'index'
                            ), 'default', true);
        }

        $flashMessenger = $this->getHelper('FlashMessenger');


        try {

            $sortedIds = $request->getPost('sorted_ids');


            if (empty($sortedIds)) {

                throw new Application_Model_Exception_InvalidInput('Sorted ids are not sent');
            }

            $sortedIds = trim($sortedIds, ' ,');


            if (!preg_match('/^[0-9]+(,[0-9]+)*$/', $sortedIds)) {
                throw new Application_Model_Exception_InvalidInput('Invalid  sorted ids ' . $sortedIds);
            }

            $sortedIds = explode(',', $sortedIds);

            $cmsClientsTable = new Application_Model_DbTable_CmsClients();
            
            $cmsClientsTable->updateOrderOfClients($sortedIds);

            $flashMessenger->addMessage('Order is successfully saved', 'success');

            //redirect on another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_clients',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {

            $flashMessenger->addMessage($ex->getMessage(), 'errors');

            //redirect on another page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_clients',
                        'action' => 'index'
                            ), 'default', true);
        }
    }
    
     public function dashboardAction() {

        $cmsClientsDbTable = new Application_Model_DbTable_CmsClients();
           
//        $select = $cmsClientsDbTable->select();
//        $clients = $cmsClientsDbTable->fetchAll($select);
        
        $totalNumberOfClients = $cmsClientsDbTable->count();
        $activeClients = $cmsClientsDbTable->count(array(
            'status' => Application_Model_DbTable_CmsClients::STATUS_ENABLED
        ));
                
        $this->view->activeClients = $activeClients;
        $this->view->totalNumberOfClients = $totalNumberOfClients;
      
    }

}

