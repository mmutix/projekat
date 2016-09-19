<?php
class Admin_ProductsController extends Zend_Controller_Action {
    public function indexAction() {
        $flashMessenger = $this->getHelper('FlashMessenger');
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        
        $cmsProductsDbTable = new Application_Model_DbTable_CmsProducts();
        $products = $cmsProductsDbTable->search(array(

            'orders' => array(
                'order_number'=>'ASC'
            ),
            //'limit' => 2,
            //'page' => 2
        ));
        $this->view->products = $products; 
        $this->view->systemMessages = $systemMessages;
    }
    public function addAction() {
        $request = $this->getRequest(); 
        $flashMessenger = $this->getHelper('FlashMessenger');
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        $form = new Application_Form_Admin_ProductAdd();
        //default form data
        $form->populate(array(
        ));
        if ($request->isPost() && $request->getPost('task') === 'save') {
            try {
                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid form data was sent for new product');
                }
                //get form data
                $formData = $form->getValues(); 
                  unset($formData['product_photo']);
                //die(print_r($formData, true));
                $cmsProductsTable = new Application_Model_DbTable_CmsProducts();
                
                
                
                //insert product returns ID of the new product
                $productId =  $cmsProductsTable->insertProduct($formData);
                
                if($form->getElement('product_photo')->isUploaded()) {
                //photo is uploaded
                    $fileInfos = $form->getElement('product_photo')->getFileInfo('product_photo');
                    $fileInfo=$fileInfos['product_photo'];
                    
                    try{
                      //open uploaded photo in temporary directory
                     $productPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                     $productPhoto->fit(150, 150);
                     
                     $productPhoto->save(PUBLIC_PATH . '/uploads/products/' . $productId . '.jpg');
                     
                    } catch (Exception $ex) {
                        $flashMessenger->addMessage('Product has been saved, but error occured during image processing', 'errors'); 
                //redirect to same or another page
                        $redirector = $this->getHelper('Redirector'); 
                        $redirector->setExit(true)
                            ->gotoRoute(array(
                                'controller' => 'admin_products',
                                'action' => 'edit',
                                'id' => $productId
                                    ), 'default', true);  
                    }
                }

                $flashMessenger->addMessage('Product has been saved', 'success'); 
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector'); 
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_products',
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
            throw new Zend_Controller_Router_Exception('Invalid product id: ' . $id, 404);
        }
        $cmsProductsTable = new Application_Model_DbTable_CmsProducts();
        $product = $cmsProductsTable->getProductById($id);
        if (empty($product)) {
            throw new Zend_Controller_Router_Exception('No product is found with id: ' . $id, 404);
        }
        //$this->view->product = $product;
        $flashMessenger = $this->getHelper('FlashMessenger');
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        $form = new Application_Form_Admin_ProductAdd();
        //default form data
        $form->populate($product);
        
        if ($request->isPost() && $request->getPost('task') === 'update') {
            try {
                //check form is valid
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid form data was sent for product');
                }
                //get form data
                $formData = $form->getValues(); 
                
                unset($formData['product_photo']);
                if($form->getElement('product_photo')->isUploaded()) {
                //photo is uploaded
                    $fileInfos = $form->getElement('product_photo')->getFileInfo('product_photo');
                    $fileInfo=$fileInfos['product_photo'];
                    
                    try{
                      //open uploaded photo in temporary directory
                     $productPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                     $productPhoto->fit(150, 150);
                     
                     $productPhoto->save(PUBLIC_PATH . '/uploads/products/' . $product['id'] . '.jpg');
                     
                    } catch (Exception $ex) {
                        
                        throw new Application_Model_Exception_InvalidInput('Error occured during image processing');
                        
                    }
                }
                $cmsProductsTable->updateProduct($product['id'], $formData);
                // do actual task
                //save to database etc
                //set system message
                $flashMessenger->addMessage('Product has been updated', 'success'); 
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector'); 
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_products',
                            'action' => 'index'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }
        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
        $this->view->product = $product;
    }

    public function deleteAction() {
        $request = $this->getRequest();
        if (!$request->isPost() || $request->getPost('task') != 'delete') {
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
       try  {
            // read $_POST['id']
            $id = (int) $request->getPost('id'); 
            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid product id: ' . $id);
                
            }
            $cmsProductsTable = new Application_Model_DbTable_CmsProducts();
            $product = $cmsProductsTable->getProductById($id);
            if (empty($product)) {
                throw new Application_Model_Exception_InvalidInput('No product is found with id: ' . $id);
            }
            $cmsProductsTable->deleteProduct($id);
            $flashMessenger->addMessage('Product : ' . $product['first_name'] . ' ' . $product['last_name'] . ' has been deleted', 'success');
            $redirector = $this->getHelper('Redirector'); 
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); 
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
        } 
    }
    public function disableAction() {
        $request = $this->getRequest();
        if (!$request->isPost() || $request->getPost('task') != 'disable') {
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        try {
            $id = (int) $request->getPost("id");
            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput("Invalid product id: " . $id);
            }
            $cmsProductsTable = new Application_Model_DbTable_CmsProducts;
            $product = $cmsProductsTable->getProductById($id);
            if (empty($product)) {
                throw new Application_Model_Exception_InvalidInput("No product is found with id: " . $id);
            }
            $cmsProductsTable->disableProduct($id);
            
            
            $flashMessenger->addMessage('Product : ' . $product['first_name'] . ' ' . $product['last_name'] . ' has been disabled', 'success');
            $redirector = $this->getHelper('Redirector'); 
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); 
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
        } 
    }
    public function enableAction() {
        $request = $this->getRequest();
        if (!$request->isPost() || $request->getPost('task') != 'enable') {
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        try {
            $id = (int) $request->getPost("id");
            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput("Invalid product id: " . $id);
            }
            $cmsProductsTable = new Application_Model_DbTable_CmsProducts;
            $product = $cmsProductsTable->getProductById($id);
            if (empty($product)) {
                throw new Application_Model_Exception_InvalidInput("No product is found with id: " . $id);
            }
            $cmsProductsTable->enableProduct($id);
            
           $flashMessenger->addMessage('Product : ' . $product['first_name'] . ' ' . $product['last_name'] . ' has been enabled', 'success');
            $redirector = $this->getHelper('Redirector'); 
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); 
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
        } 
    }
//    public function resetpasswordAction() {
//        $request = $this->getRequest();
//        if ($request->isPost() && $request->getPost('task') != 'reset') {
//            $redirector = $this->getHelper('Redirector');
//            $redirector->setExit(true)
//                    ->gotoRoute(array(
//                        'controller' => 'admin_products',
//                        'action' => 'index'
//                            ), 'default', true);
//        }
//        $flashMessenger = $this->getHelper('FlashMessenger');
//        try {
//            $id = (int) $request->getPost('id');
//            if ($id <= 0) {
//                throw new Application_Model_Exception_InvalidInput('Invalid product id: ' . $id);
//            }
//            $cmsProductsTable = new Application_Model_DbTable_CmsProducts();
//            $product = $cmsProductsTable->getProductById($id);
//            if (empty($product)) {
//                throw new Application_Model_Exception_InvalidInput('No product is found with id: ' . $id);
//            }
//            $loggedInProduct = Zend_Auth::getInstance()->getIdentity();
//            if ($id == $loggedInProduct['id']) {
//                $redirector = $this->getHelper('Redirector');
//                $redirector->setExit(true)
//                        ->gotoRoute(array(
//                            'controller' => 'admin_profile',
//                            'action' => 'changepassword'
//                                ), 'default', true);
//            }
//            $cmsProductsTable->resetPassword($id);
//            
//            $request instanceof Zend_Controller_Request_Http;
//            
//            //ispitivanje da li je request Ajax
//            if($request->isXmlHttpRequest()){
//                //request je Ajax
//                //send response as json
//                
//                $responseJson=array(
//                    'status'=>'ok',
//                    'statusMessage'=>'Password is successfully reseted to default value for productname: ' . $product['productname']
//                );
//                
//                //send json as response
//                $this->getHelper('Json')->sendJson($responseJson);
//                
//            }else{
//                //request nije Ajax
//                //send message over session
//                //and do not redirect
//                
//            $flashMessenger->addMessage('Password is successfully reseted to default value for productname: ' . $product['productname'], 'success');
//            $redirector = $this->getHelper('Redirector');
//            $redirector->setExit(true)
//                    ->gotoRoute(array(
//                        'controller' => 'admin_products',
//                        'action' => 'index'
//                            ), 'default', true);
//            }
//            
//            
//        } catch (Application_Model_Exception_InvalidInput $ex) {
//            if($request->isXmlHttpRequest()){
//                //request is ajax
//                
//                $responseJson = array(
//                    'status'=>'error',
//                    'statusMessage'=>$ex->getMessage()
//                    
//                );
//                //send json as response
//                $this->getHelper('Json')->sendJson($responseJson);
//                
//            }else{
//                //request is not ajax
//            $flashMessenger->addMessage($ex->getMessage());
//            $redirector = $this->getHelper('Redirector');
//            $redirector->setExit(true)
//                    ->gotoRoute(array(
//                        'controller' => 'admin_products',
//                        'action' => 'index'
//                            ), 'default', true);
//            }
//            
//        }
//    }
   public function updateorderAction(){
       $request = $this->getRequest(); 
        
        if(!$request->isPost() || $request->getPost('task') != 'saveOrder'){
            //request is not post
            //or task is not saveOrder
            //redirect to index page
            $redirector = $this->getHelper('Redirector'); 
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger'); 
        
        try{
           $sortedIds =  $request->getPost('sorted_ids'); 
            if(empty($sortedIds)){
                
                throw new Application_Model_Exception_InvalidInput('Sorted ids are not sent');
                
            }
            $sortedIds = trim($sortedIds, ' ,');
            
            
            
            if(!preg_match('/^[0-9]+(,[0-9]+)*$/', $sortedIds)){
                throw new Application_Model_Exception_InvalidInput('Invalid sorted ids: ' . $sortedIds);
            }
            
            $sortedIds = explode(',', $sortedIds);
            
            $cmsProductsTable = new Application_Model_DbTable_CmsProducts();
            
            $cmsProductsTable->updateOrderOfProducts($sortedIds);
            
            $flashMessenger->addMessage('Order is successfully saved', 'success');
            
            $redirector = $this->getHelper('Redirector'); 
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
            
            
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); 
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
        }
    }
    public function dashboardAction() {
        
        $cmsProductsDbTable = new Application_Model_DbTable_CmsProducts();
        $enabled = $cmsProductsDbTable->count(array(
        'status'=>Application_Model_DbTable_CmsProducts::STATUS_ENABLED,
        ));
        
        $allProducts =$cmsProductsDbTable->count();
        
        $this->view->enabledProducts = $enabled;
        $this->view->allProducts = $allProducts;
    }
}