<?php
class Admin_ProductsController extends Zend_Controller_Action {
    public function indexAction() {
        $flashMessenger = $this->getHelper('FlashMessenger');
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        $this->view->products = array(); 
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
                $cmsProductsTable = new Application_Model_DbTable_CmsProducts();
                //insert product returns ID of the new product
                $productId = $cmsProductsTable->insertProduct($formData);
                // do actual task
                //save to database etc
                //set system message
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
        $request = $this->getRequest(); //dohvatamo request objekat
        $id = (int) $request->getParam('id'); 
        if ($id <= 0) {
            //prekida se izvrsavanje programa i prikazuje se "Page not found"
            throw new Zend_Controller_Router_Exception('Invalid product id: ' . $id, 404);
        }
        $loggedInProduct = Zend_Auth::getInstance()->getIdentity();
        if ($id == $loggedInProduct['id']) {
            //redirect product to edit profile page
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_profile',
                        'action' => 'edit'
                            ), 'default', true);
        }
        $cmsProductsTable = new Application_Model_DbTable_CmsProducts();
        $product = $cmsProductsTable->getProductById($id);
        if (empty($product)) {
            throw new Zend_Controller_Router_Exception('No product is found with id: ' . $id, 404);
        }
        //$this->view->product = $product;//prosledjujemo $producta prezentacionoj logici
        $flashMessenger = $this->getHelper('FlashMessenger');
        $systemMessages = array(//niz za poruke o uspesno ili neuspesno unetim podacima u formu
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        $form = new Application_Form_Admin_ProductEdit($product['id']);
        //default form data
        $form->populate($product);
        if ($request->isPost() && $request->getPost('task') === 'update') {//ovo znaci ukoliko je forma pokrenuta da li je form zahtev POST i da li je yahtev pokrenut na formi, asocijativni niz ciji su kljucevi atributi iz polja forme a vrednosti unos korisnika u formu
            try {
                //check form is valid
                if (!$form->isValid($request->getPost())) {//validacija forme ukoliko nisu validni/dobri podaci iz forme bacamo exception i idemo na catch
                    throw new Application_Model_Exception_InvalidInput('Invalid form data was sent for product');
                }
                //get form data
                $formData = $form->getValues(); //ovo treba da se upise u bazu(podaci iz forme)
                //die(print_r($formData, true));
                //$cmsProductsTable = new Application_Model_DbTable_CmsProducts();
                //$cmsProductsTable->insert($formData);
                //Radimo update postojeceg zapisa u tabeli
                $cmsProductsTable->updateProduct($product['id'], $formData);
                // do actual task
                //save to database etc
                //set system message
                $flashMessenger->addMessage('Product has been updated', 'success'); //u sesiju upisujemo poruku product has been saved
                //redirect to same or another page
                $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
                $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _products
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
        try {
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
            
            $request instanceof Zend_Controller_Request_Http;
            
            //ispitivanje da li je request Ajax
            if($request->isXmlHttpRequest()){
                //request je Ajax
                //send response as json
                
                $responseJson=array(
                    'status'=>'ok',
                    'statusMessage'=>'Product ' . $product["first_name"] . ' ' . $product["last_name"] . ' has been deleted.'
                );
                
                //send json as response
                $this->getHelper('Json')->sendJson($responseJson);
                
            }else{
                //request nije Ajax
                //send message over session
                //and do not redirect
                
            $flashMessenger->addMessage("Product " . $product["first_name"] . " " . $product["last_name"] . " has been deleted.", "success");
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
            }
            
            
        } catch (Application_Model_Exception_InvalidInput $ex) {
            if($request->isXmlHttpRequest()){
                //request is ajax
                
                $responseJson = array(
                    'status'=>'error',
                    'statusMessage'=>$ex->getMessage()
                    
                );
                //send json as response
                $this->getHelper('Json')->sendJson($responseJson);
                
            }else{
                //request is not ajax
            $flashMessenger->addMessage($ex->getMessage());
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
            }
            
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
            
            
            $request instanceof Zend_Controller_Request_Http;
            
            //ispitivanje da li je request Ajax
            if($request->isXmlHttpRequest()){
                //request je Ajax
                //send response as json
                
                $responseJson=array(
                    'status'=>'ok',
                    'statusMessage'=>'Product ' . $product["first_name"] . ' ' . $product["last_name"] . ' has been disabled.'
                );
                
                //send json as response
                $this->getHelper('Json')->sendJson($responseJson);
                
            }else{
                //request nije Ajax
                //send message over session
                //and do not redirect
                
            $flashMessenger->addMessage("Product " . $product["first_name"] . " " . $product["last_name"] . " has been disabled.", "success");
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
            }
            
            
        } catch (Application_Model_Exception_InvalidInput $ex) {
            if($request->isXmlHttpRequest()){
                //request is ajax
                
                $responseJson = array(
                    'status'=>'error',
                    'statusMessage'=>$ex->getMessage()
                    
                );
                //send json as response
                $this->getHelper('Json')->sendJson($responseJson);
                
            }else{
                //request is not ajax
            $flashMessenger->addMessage($ex->getMessage());
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
            }
            
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
            
            $request instanceof Zend_Controller_Request_Http;
            
            //ispitivanje da li je request Ajax
            if($request->isXmlHttpRequest()){
                //request je Ajax
                //send response as json
                
                $responseJson=array(
                    'status'=>'ok',
                    'statusMessage'=>'Product ' . $product["first_name"] . ' ' . $product["last_name"] . ' has been enabled.'
                );
                
                //send json as response
                $this->getHelper('Json')->sendJson($responseJson);
                
            }else{
                //request nije Ajax
                //send message over session
                //and do not redirect
                
            $flashMessenger->addMessage("Product " . $product["first_name"] . " " . $product["last_name"] . " has been enabled.", "success");
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
            }
            
            
        } catch (Application_Model_Exception_InvalidInput $ex) {
            if($request->isXmlHttpRequest()){
                //request is ajax
                
                $responseJson = array(
                    'status'=>'error',
                    'statusMessage'=>$ex->getMessage()
                    
                );
                //send json as response
                $this->getHelper('Json')->sendJson($responseJson);
                
            }else{
                //request is not ajax
            $flashMessenger->addMessage($ex->getMessage());
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
            }
            
        }
    }
    public function resetpasswordAction() {
        $request = $this->getRequest();
        if ($request->isPost() && $request->getPost('task') != 'reset') {
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        try {
            $id = (int) $request->getPost('id');
            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid product id: ' . $id);
            }
            $cmsProductsTable = new Application_Model_DbTable_CmsProducts();
            $product = $cmsProductsTable->getProductById($id);
            if (empty($product)) {
                throw new Application_Model_Exception_InvalidInput('No product is found with id: ' . $id);
            }
            $loggedInProduct = Zend_Auth::getInstance()->getIdentity();
            if ($id == $loggedInProduct['id']) {
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_profile',
                            'action' => 'changepassword'
                                ), 'default', true);
            }
            $cmsProductsTable->resetPassword($id);
            
            $request instanceof Zend_Controller_Request_Http;
            
            //ispitivanje da li je request Ajax
            if($request->isXmlHttpRequest()){
                //request je Ajax
                //send response as json
                
                $responseJson=array(
                    'status'=>'ok',
                    'statusMessage'=>'Password is successfully reseted to default value for productname: ' . $product['productname']
                );
                
                //send json as response
                $this->getHelper('Json')->sendJson($responseJson);
                
            }else{
                //request nije Ajax
                //send message over session
                //and do not redirect
                
            $flashMessenger->addMessage('Password is successfully reseted to default value for productname: ' . $product['productname'], 'success');
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
            }
            
            
        } catch (Application_Model_Exception_InvalidInput $ex) {
            if($request->isXmlHttpRequest()){
                //request is ajax
                
                $responseJson = array(
                    'status'=>'error',
                    'statusMessage'=>$ex->getMessage()
                    
                );
                //send json as response
                $this->getHelper('Json')->sendJson($responseJson);
                
            }else{
                //request is not ajax
            $flashMessenger->addMessage($ex->getMessage());
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
            }
            
        }
    }
    public function datatableAction() {
        $request = $this->getRequest();
        $datatableParameters = $request->getParams();
//        //print_r($datatableParameters);
//        //die();
//        /*
//          Array
//          (
//          [controller] => admin_products
//          [action] => datatable
//          [module] => default
//          [draw] => 1
//
//
//          [order] => Array
//          (
//          [0] => Array
//          (
//          [column] => 2
//          [dir] => asc
//          )
//
//          )
//
//          [start] => 0//page tj pocetak strane da je druga strana bila bi vrednost 5 da je str 3 vrednost bi bila 10
//          [length] => 3//je limit
//          [search] => Array
//          (
//          [value] =>
//          [regex] => false
//          )
//          )
//         */
        $cmsProductsTable = new Application_Model_DbTable_CmsProducts();
        $loggedInProduct = Zend_Auth::getInstance()->getIdentity();
        $filters = array(
            'id_exclude' => $loggedInProduct,
            
            
        );
        $orders = array();
        $limit = 5;
        $page = 1;
        $draw = 1; //obavezan prilikom slanja
        $columns = array('stock_status', 'model', 'type', 'price', 'part_status', 'quantity' , 'actions'); //ovaj raspored mora da bude isti kao u tabeli u prezentacionoj logici
        //Process datatable parameters
        if (isset($datatableParameters['draw'])) {
            $draw = $datatableParameters['draw'];
            if (isset($datatableParameters['length'])) {
                $limit = $datatableParameters['length'];
                if ($datatableParameters['start']) {
                    $page = floor($datatableParameters['start'] / $datatableParameters['length']) + 1;
                }
            }
            
            if (
                    isset($datatableParameters['order']) && is_array($datatableParameters['order'])
            ) {
                foreach ($datatableParameters['order'] as $datatableOrder) {
                    $columnIndex = $datatableOrder['column']; //daje index iz $column niza
                    $columnDirection = strtoupper($datatableOrder['dir']);
                    if (isset($columns[$columnIndex])) {
                        $orders[$columns[$columnIndex]] = $columnDirection;
                    }
                }
            }
            if (
                    isset($datatableParameters['search']) && is_array($datatableParameters['search']) && isset($datatableParameters['search']['value'])
            ) {
                $filters['model_search'] = $datatableParameters['search']['value'];
            }
        }
        $products = $cmsProductsTable->search(array(
            'filters' => $filters,
            'orders' => $orders,
            'limit' => $limit,
            'page' => $page
        ));
        $productsFilteredCount = $cmsProductsTable->count($filters);
        $productsTotal = $cmsProductsTable->count();
        $this->view->products = $products; //prosledjivanje prezentacionoj logici
        $this->view->productsFilteredCount = $productsFilteredCount; //prosledjivanje prezentacionoj logici
        $this->view->productsTotal = $productsTotal; //prosledjivanje prezentacionoj logici
        $this->view->draw = $draw; //prosledjivanje prezentacionoj logici
        $this->view->columns = $columns;
    }
    public function dashboardAction() {
        
        $cmsProductsDbTable = new Application_Model_DbTable_CmsProducts();
        $enabled = $cmsProductsDbTable->count(array(
        'status'=>Application_Model_DbTable_CmsProducts::STATUS_ENABLED,
        //'last_name_search' => 'kiu'
        ));
        
        $allProducts =$cmsProductsDbTable->count();
        
         
   
        $this->view->enabledProducts = $enabled;
        $this->view->allProducts = $allProducts;
    }
}