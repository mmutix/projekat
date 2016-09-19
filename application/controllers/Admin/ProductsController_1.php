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
            //'filters' => array(//filtriram tabelu po
            //'status'=>Application_Model_DbTable_CmsProducts::STATUS_DISABLED,
            //'work_title' =>  	'PHP Developer',
           // 'first_name' => array('Aleksandar', 'Aleksandra', 'Bojan')
            
            //),
            'orders' => array(//sortiram tabelu po
                'order_number'=>'ASC'
            ),
            //'limit' => 4,
            //'page' => 2
        ));
        $this->view->products = $products;
        $this->view->systemMessages = $systemMessages;
    }
    public function addAction() {
        $request = $this->getRequest(); //objekat koji cuva inputdata podatke unete preko forme to je getter za post podatke
        $flashMessenger = $this->getHelper('FlashMessenger');
        $systemMessages = array(//niz za poruke o uspesno ili neuspesno unetim podacima u formu
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors')
        );
        $form = new Application_Form_Admin_ProductAdd();
        //default form data
        $form->populate(array(
        ));
        if ($request->isPost() && $request->getPost('task') === 'save') {//ovo znaci ukoliko je forma pokrenuta da li je form zahtev POST i da li je yahtev pokrenut na formi, asocijativni niz ciji su kljucevi atributi iz polja forme a vrednosti unos korisnika u formu
            try {
                //check form is valid
                if (!$form->isValid($request->getPost())) {//validacija forme ukoliko nisu validni/dobri podaci iz forme bacamo exception i idemo na catch
                    throw new Application_Model_Exception_InvalidInput('Invalid form data was sent for new product');
                }
                //get form data
                $formData = $form->getValues(); //ovo treba da se upise u bazu(podaci iz forme)
                
                
                //remove key product_photo form because there is no column product_photo in cms_product
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
                     //dimenzionise sliku
                     $productPhoto->fit(150, 150);
                     
                     $productPhoto->save(PUBLIC_PATH . '/uploads/products/' . $productId . '.jpg');
                     
                    } catch (Exception $ex) {
                        $flashMessenger->addMessage('Product has been saved, but error occured during image processing', 'errors'); //u sesiju upisujemo poruku product has been saved
                //redirect to same or another page
                        $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
                        $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _products
                            ->gotoRoute(array(
                                'controller' => 'admin_products',
                                'action' => 'edit',
                                'id' => $productId
                                    ), 'default', true);  
                    }
                    
//                    print_r($fileInfo);
//                    die();
                    
                    //isto kao $fileInfo=$_FILES['product_photo'];
                }
                
                
                // do actual task
                //save to database etc
                //set system message
                $flashMessenger->addMessage('Product has been saved', 'success'); //u sesiju upisujemo poruku product has been saved
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
    }
    public function editAction() {
        $request = $this->getRequest(); //dohvatamo request objekat
        $id = (int) $request->getParam('id'); //iscitavamo parametar id filtriramo ga da bude int
        if ($id <= 0) {
            //prekida se izvrsavanje programa i prikazuje se "Page not found"
            throw new Zend_Controller_Router_Exception('Invalid product id: ' . $id, 404);
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
        $form = new Application_Form_Admin_ProductAdd();
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
                
                unset($formData['product_photo']);
                if($form->getElement('product_photo')->isUploaded()) {
                //photo is uploaded
                    $fileInfos = $form->getElement('product_photo')->getFileInfo('product_photo');
                    $fileInfo=$fileInfos['product_photo'];
                    
                    try{
                      //open uploaded photo in temporary directory
                     $productPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                     //dimenzionise sliku
                     $productPhoto->fit(150, 150);
                     
                     $productPhoto->save(PUBLIC_PATH . '/uploads/products/' . $product['id'] . '.jpg');
                     
                    } catch (Exception $ex) {
                        
                        throw new Application_Model_Exception_InvalidInput('Error occured during image processing');
                        
                    }
                }
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
    public function deleteAction(){
        $request = $this->getRequest(); //dohvatamo request objekat
        
        if(!$request->isPost() || $request->getPost('task') != 'delete'){
            //request is not post
            //or task is not delete
            //redirect to index page
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _products
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try  {
            // read $_POST['id']
            $id = (int) $request->getPost('id'); //iscitavamo parametar id filtriramo ga da bude int
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
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _products
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _products
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
        } 
    }
    public function disableAction(){
        $request = $this->getRequest(); //dohvatamo request objekat
        
        if(!$request->isPost() || $request->getPost('task') != 'disable'){
            //request is not post
            //or task is not delete
            //redirect to index page
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _products
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try  {
            // read $_POST['id']
            $id = (int) $request->getPost('id'); //iscitavamo parametar id filtriramo ga da bude int
            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid product id: ' . $id);
                
            }
            $cmsProductsTable = new Application_Model_DbTable_CmsProducts();
            $product = $cmsProductsTable->getProductById($id);
            if (empty($product)) {
                throw new Application_Model_Exception_InvalidInput('No product is found with id: ' . $id);
            }
            $cmsProductsTable->disableProduct($id);
            $flashMessenger->addMessage('Product : ' . $product['first_name'] . ' ' . $product['last_name'] . ' has been disabled', 'success');
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _products
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _products
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
        } 
    }
   
    public function enableAction(){
        $request = $this->getRequest(); //dohvatamo request objekat
        
        if(!$request->isPost() || $request->getPost('task') != 'enable'){
            //request is not post
            //or task is not delete
            //redirect to index page
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _products
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        
        try  {
            // read $_POST['id']
            $id = (int) $request->getPost('id'); //iscitavamo parametar id filtriramo ga da bude int
            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid product id: ' . $id);
                
            }
            $cmsProductsTable = new Application_Model_DbTable_CmsProducts();
            $product = $cmsProductsTable->getProductById($id);
            if (empty($product)) {
                throw new Application_Model_Exception_InvalidInput('No product is found with id: ' . $id);
            }
            $cmsProductsTable->enableProduct($id);
            $flashMessenger->addMessage('Product : ' . $product['first_name'] . ' ' . $product['last_name'] . ' has been enabled', 'success');
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _products
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _products
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
        } 
    }
public function updateorderAction(){
       $request = $this->getRequest(); //dohvatamo request objekat
        
        if(!$request->isPost() || $request->getPost('task') != 'saveOrder'){
            //request is not post
            //or task is not saveOrder
            //redirect to index page
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _products
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger'); 
        
        try{
           $sortedIds =  $request->getPost('sorted_ids'); //iscitavamo parametar id filtriramo ga da bude int
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
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _products
                    ->gotoRoute(array(
                        'controller' => 'admin_products',
                        'action' => 'index'
                            ), 'default', true);
            
            
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            
            $redirector = $this->getHelper('Redirector'); //redirect je samo i uvek get zahtev i nemoze biti post, radi se samo za get metodu
            $redirector->setExit(true)//ukoliko je uspesan unos u formu redirektujemo na tu stranu admin _products
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
        //'type_search' => 'kiu'
        ));
        
        $allProducts =$cmsProductsDbTable->count();
        
         
   
        $this->view->enabledProducts = $enabled;
        $this->view->allProducts = $allProducts;
    }
}