<?php

class Admin_SitemapController extends Zend_Controller_Action
{
	public function indexAction() {
		$request = $this->getRequest();
		
		$flashMessenger = $this->getHelper('FlashMessenger');
		
		$systemMessages = array(
			
			'success' => $flashMessenger->getMessages('success'),
			'errors' => $flashMessenger->getMessages('errors')
		);
		
		//if no id parameter, than $parameterId will be 0
		$id = (int) $request->getParam('id', 0);
		
		if ($id < 0) {
			throw new Zend_Controller_Router_Exception('Invalid id for sitemap pages', 404);
		}
		
		$cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages();
		
		if ($id != 0) {
			$sitemapPage = $cmsSitemapPagesDbTable->getSitemapPageById($id);
		
			if (!$sitemapPage) {
				throw new Zend_Controller_Router_Exception('No sitemap page is found', 404);
			}
		}
		
		
		$childSitemapPages = $cmsSitemapPagesDbTable->search(array(
			'filters' => array(
				'parent_id' => $id
			),
			'orders' => array(
				'order_number' => 'ASC'
			),
			//'limit' => 50,
			//'page' => 3
		));
		
		$sitemapPageBreadcrumbs = $cmsSitemapPagesDbTable->getSitemapPageBreadcrumbs($id);
		
		$this->view->currentSitemapPageId = $id;
		$this->view->childSitemapPages = $childSitemapPages;
		$this->view->sitemapPageBreadcrumbs = $sitemapPageBreadcrumbs;
		$this->view->systemMessages = $systemMessages;
	}
	
	public function addAction() {
		
		$request = $this->getRequest();
		
		$parentId = (int) $request->getParam('parent_id', 0);
		
		if ($parentId < 0) {
			throw new Zend_Controller_Router_Exception('Invalid id for sitemap pages', 404);
		}
		
		$parentType = '';
		
		$cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages();
		
		if ($parentId != 0) {
			//check if parent page exists
			$parentSitemapPage = $cmsSitemapPagesDbTable->getSitemapPageById($parentId);
			
			if (!$parentSitemapPage) {
				throw new Zend_Controller_Router_Exception('No sitemap page is found for id:' . $parentId, 404);
			}
			
			$parentType = $parentSitemapPage['type'];
		}
		
		$flashMessenger = $this->getHelper('FlashMessenger');
		
		$systemMessages = array(
			'success' => $flashMessenger->getMessages('success'),
			'errors' => $flashMessenger->getMessages('errors'),
		);
		
		
		$form = new Application_Form_Admin_SitemapPageAdd($parentId, $parentType);

		//default form data
		$form->populate(array(
			
		));

		if ($request->isPost() && $request->getPost('task') === 'save') {

			try {
				//check form is valid
				if (!$form->isValid($request->getPost())) {
					throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new sitemapPage');
				}

				//get form data
				$formData = $form->getValues();
				
				// set parent_id for new page
				$formData['parent_id'] = $parentId;
				
				//remove key sitemap_page_photo form form data because there is no column 'sitemap_page_photo' in cms_sitemapPages table
				//unset($formData['sitemap_page_photo']);
				//Insertujemo novi zapis u tabelu
				
				//insert sitemapPage returns ID of the new sitemapPage
				$sitemapPageId = $cmsSitemapPagesDbTable->insertSitemapPage($formData);
				
//				if ($form->getElement('sitemap_page_photo')->isUploaded()) {
//					//photo is uploaded
//					
//					$fileInfos = $form->getElement('sitemap_page_photo')->getFileInfo('sitemap_page_photo');
//					$fileInfo = $fileInfos['sitemap_page_photo'];
//					
//					
//					try {
//						//open uploaded photo in temporary directory
//						$sitemapPagePhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
//						
//						$sitemapPagePhoto->fit(150, 150);
//						
//						$sitemapPagePhoto->save(PUBLIC_PATH . '/uploads/sitemapPages/' . $sitemapPageId . '.jpg');
//						
//					} catch (Exception $ex) {
//						
//						$flashMessenger->addMessage('SitemapPage has been saved but error occured during image processing', 'errors');
//
//						//redirect to same or another page
//						$redirector = $this->getHelper('Redirector');
//						$redirector->setExit(true)
//							->gotoRoute(array(
//								'controller' => 'admin_sitemapPages',
//								'action' => 'edit',
//								'id' => $sitemapPageId
//							), 'default', true);
//					}
//					//$fileInfo = $_FILES['sitemap_page_photo'];
//				}
//				
				// do actual task
				//save to database etc
				
				
				//set system message
				$flashMessenger->addMessage('Sitemap page has been saved', 'success');

				//redirect to same or another page
				$redirector = $this->getHelper('Redirector');
				$redirector->setExit(true)
					->gotoRoute(array(
						'controller' => 'admin_sitemap',
						'action' => 'index',
						'id' => $parentId
						), 'default', true);
			} catch (Application_Model_Exception_InvalidInput $ex) {
				$systemMessages['errors'][] = $ex->getMessage();
			}
		}
		
		$sitemapPageBreadcrumbs = $cmsSitemapPagesDbTable->getSitemapPageBreadcrumbs($parentId);
		
		$this->view->parentId = $parentId;
		$this->view->systemMessages = $systemMessages;
		$this->view->sitemapPageBreadcrumbs = $sitemapPageBreadcrumbs;
		$this->view->form = $form;
	}
	
	public function editAction() {
		$request = $this->getRequest();
		
		$id = (int) $request->getParam('id');
		
		if ($id <= 0) {
			
			//prekida se izvrsavanje programa i prikazuje se "Page not found"
			throw new Zend_Controller_Router_Exception('Invalid sitemap page id: ' . $id, 404);
		}
		
		$cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();
		
		$sitemapPage = $cmsSitemapPagesTable->getSitemapPageById($id);
		
		if (empty($sitemapPage)) {
			throw new Zend_Controller_Router_Exception('No sitemap page is found with id: ' . $id, 404);
		}
		
		$parentType = '';
		if ($sitemapPage['parent_id'] != 0) {
			
			$parentSitemapPage = $cmsSitemapPagesTable->getSitemapPageById($sitemapPage['parent_id']);
			
			$parentType = $parentSitemapPage['type'];
		}
		
		$flashMessenger = $this->getHelper('FlashMessenger');
		
		$systemMessages = array(
			'success' => $flashMessenger->getMessages('success'),
			'errors' => $flashMessenger->getMessages('errors'),
		);
		
		
		$form = new Application_Form_Admin_SitemapPageEdit($sitemapPage['id'], $sitemapPage['parent_id'], $parentType);

		//default form data
		$form->populate($sitemapPage);

		if ($request->isPost() && $request->getPost('task') === 'update') {

			try {

				//check form is valid
				if (!$form->isValid($request->getPost())) {
					throw new Application_Model_Exception_InvalidInput('Invalid data was sent for sitemapPage');
				}

				//get form data
				$formData = $form->getValues();
				
//				unset($formData['sitemapPage_photo']);
//				
//				if ($form->getElement('sitemapPage_photo')->isUploaded()) {
//					//photo is uploaded
//					
//					$fileInfos = $form->getElement('sitemapPage_photo')->getFileInfo('sitemapPage_photo');
//					$fileInfo = $fileInfos['sitemapPage_photo'];
//					
//					
//					try {
//						//open uploaded photo in temporary directory
//						$sitemapPagePhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
//						
//						$sitemapPagePhoto->fit(150, 150);
//						
//						$sitemapPagePhoto->save(PUBLIC_PATH . '/uploads/sitemapPages/' . $sitemapPage['id'] . '.jpg');
//						
//					} catch (Exception $ex) {
//						
//						throw new Application_Model_Exception_InvalidInput('Error occured during image processing');
//						
//					}
//					//$fileInfo = $_FILES['sitemapPage_photo'];
//				}
//				//Radimo update postojeceg zapisa u tabeli
				
				$cmsSitemapPagesTable->updateSitemapPage($sitemapPage['id'], $formData);
				
				//set system message
				$flashMessenger->addMessage('SitemapPage has been updated', 'success');

				//redirect to same or another page
				$redirector = $this->getHelper('Redirector');
				$redirector->setExit(true)
					->gotoRoute(array(
						'controller' => 'admin_sitemap',
						'action' => 'index',
						'id' => $sitemapPage['parent_id']
						), 'default', true);
			} catch (Application_Model_Exception_InvalidInput $ex) {
				$systemMessages['errors'][] = $ex->getMessage();
			}
		}

		$sitemapPageBreadcrumbs = $cmsSitemapPagesTable->getSitemapPageBreadcrumbs($sitemapPage['parent_id']);
		
		$this->view->systemMessages = $systemMessages;
		$this->view->sitemapPageBreadcrumbs = $sitemapPageBreadcrumbs;
		$this->view->form = $form;
		$this->view->sitemapPage = $sitemapPage;
	}
	
	public function disableAction() {
		
		$request = $this->getRequest();
		
		if (!$request->isPost() || $request->getPost('task') != 'disable') {
			// request is not post
			// or task is not delete
			//redirect to index page
			
			//redirect to same or another page
			$redirector = $this->getHelper('Redirector');
			$redirector->setExit(true)
				->gotoRoute(array(
					'controller' => 'admin_sitemap',
					'action' => 'index'
					), 'default', true);
		}
		
		$flashMessenger = $this->getHelper('FlashMessenger');
		
		try {
			
			// read $_POST['id']
			$id = (int) $request->getPost('id');

			if ($id <= 0) {
				
				throw new Application_Model_Exception_InvalidInput('Invalid sitemap page id: ' . $id);
			}

			$cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();

			$sitemapPage = $cmsSitemapPagesTable->getSitemapPageById($id);

			if (empty($sitemapPage)) {
				throw new Application_Model_Exception_InvalidInput('No sitemap page is found with id: ' . $id);
			}

			$cmsSitemapPagesTable->disableSitemapPage($id);

			$flashMessenger->addMessage('Sitemap page ' . $sitemapPage['short_title'] . ' has been disabled', 'success');

			//redirect to same or another page
			$redirector = $this->getHelper('Redirector');
			$redirector->setExit(true)
				->gotoRoute(array(
					'controller' => 'admin_sitemap',
					'action' => 'index',
					'id' => $sitemapPage['parent_id']
					), 'default', true);

		} catch (Application_Model_Exception_InvalidInput $ex) {
			
			$flashMessenger->addMessage($ex->getMessage(), 'errors');
			
			//redirect to same or another page
			$redirector = $this->getHelper('Redirector');
			$redirector->setExit(true)
				->gotoRoute(array(
					'controller' => 'admin_sitemap',
					'action' => 'index'
					), 'default', true);
		}
		
	}
	
	public function enableAction() {
		
		$request = $this->getRequest();
		
		if (!$request->isPost() || $request->getPost('task') != 'enable') {
			// request is not post
			// or task is not delete
			//redirect to index page
			
			//redirect to same or another page
			$redirector = $this->getHelper('Redirector');
			$redirector->setExit(true)
				->gotoRoute(array(
					'controller' => 'admin_sitemap',
					'action' => 'index'
					), 'default', true);
		}
		
		$flashMessenger = $this->getHelper('FlashMessenger');
		
		try {
			
			// read $_POST['id']
			$id = (int) $request->getPost('id');

			if ($id <= 0) {
				
				throw new Application_Model_Exception_InvalidInput('Invalid sitemap page id: ' . $id);
			}

			$cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();

			$sitemapPage = $cmsSitemapPagesTable->getSitemapPageById($id);

			if (empty($sitemapPage)) {
				throw new Application_Model_Exception_InvalidInput('No sitemap page is found with id: ' . $id);
			}

			$cmsSitemapPagesTable->enableSitemapPage($id);

			$flashMessenger->addMessage('SitemapPage ' . $sitemapPage['short_title'] . ' has been enabled', 'success');

			//redirect to same or another page
			$redirector = $this->getHelper('Redirector');
			$redirector->setExit(true)
				->gotoRoute(array(
					'controller' => 'admin_sitemap',
					'action' => 'index',
					'id' => $sitemapPage['parent_id']
					), 'default', true);

		} catch (Application_Model_Exception_InvalidInput $ex) {
			
			$flashMessenger->addMessage($ex->getMessage(), 'errors');
			
			//redirect to same or another page
			$redirector = $this->getHelper('Redirector');
			$redirector->setExit(true)
				->gotoRoute(array(
					'controller' => 'admin_sitemap',
					'action' => 'index'
					), 'default', true);
		}
		
	}
	
	public function updateorderAction() {
		
		$request = $this->getRequest();
		
		if (!$request->isPost() || $request->getPost('task') != 'saveOrder') {
			// request is not post
			// or task is not saveOrder
			//redirect to index page
			
			//redirect to same or another page
			$redirector = $this->getHelper('Redirector');
			$redirector->setExit(true)
				->gotoRoute(array(
					'controller' => 'admin_sitemap',
					'action' => 'index'
					), 'default', true);
		}
		
		$flashMessenger = $this->getHelper('FlashMessenger');
		
		$parentId = (int) $request->getParam('parent_id', 0);
		
		try {
			
			$sortedIds = $request->getPost('sorted_ids');
			
			if (empty($sortedIds)) {
				throw new Application_Model_Exception_InvalidInput('Sorted ids are not sent');
			}
			
			$sortedIds = trim($sortedIds, ' ,');
			
			if (!preg_match('/^[0-9]+(,[0-9]+)*$/', $sortedIds)) {
				throw new Application_Model_Exception_InvalidInput('Invalid sorted ids: ' . $sortedIds);
			}
			
			$sortedIds = explode(',', $sortedIds);
			
			$cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();
			
			$cmsSitemapPagesTable->updateOrderOfSitemapPages($sortedIds);
			
			$flashMessenger->addMessage('Order is successfully saved', 'success');
			
			//redirect to same or another page
			$redirector = $this->getHelper('Redirector');
			$redirector->setExit(true)
				->gotoRoute(array(
					'controller' => 'admin_sitemap',
					'action' => 'index',
					'id' => $parentId
					), 'default', true);
			
		} catch (Application_Model_Exception_InvalidInput $ex) {
			
			$flashMessenger->addMessage($ex->getMessage(), 'errors');
			
			//redirect to same or another page
			$redirector = $this->getHelper('Redirector');
			$redirector->setExit(true)
				->gotoRoute(array(
					'controller' => 'admin_sitemap',
					'action' => 'index',
					'id' => $parentId
					), 'default', true);
		}
	}
	
	
	public function deleteAction() {
		$request = $this->getRequest();
		
		if (!$request->isPost() || $request->getPost('task') != 'delete') {
			// request is not post
			// or task is not delete
			//redirect to index page
			
			//redirect to same or another page
			$redirector = $this->getHelper('Redirector');
			$redirector->setExit(true)
				->gotoRoute(array(
					'controller' => 'admin_sitemap',
					'action' => 'index'
					), 'default', true);
		}
		
		$flashMessenger = $this->getHelper('FlashMessenger');
		
		try {
			
			// read $_POST['id']
			$id = (int) $request->getPost('id');

			if ($id <= 0) {
				
				throw new Application_Model_Exception_InvalidInput('Invalid sitemapPage id: ' . $id);
			}

			$cmsSitemapPagesTable = new Application_Model_DbTable_CmsSitemapPages();

			$sitemapPage = $cmsSitemapPagesTable->getSitemapPageById($id);

			if (empty($sitemapPage)) {
				throw new Application_Model_Exception_InvalidInput('No sitemap page is found with id: ' . $id);
			}

			$cmsSitemapPagesTable->deleteSitemapPage($id);

			$flashMessenger->addMessage('Sitemap page ' . ' ' . $sitemapPage['short_title'] . ' has been deleted', 'success');

			//redirect to same or another page
			$redirector = $this->getHelper('Redirector');
			$redirector->setExit(true)
				->gotoRoute(array(
					'controller' => 'admin_sitemap',
					'action' => 'index',
					'id' => $sitemapPage['parent_id']
					), 'default', true);

		} catch (Application_Model_Exception_InvalidInput $ex) {
			
			$flashMessenger->addMessage($ex->getMessage(), 'errors');
			
			//redirect to same or another page
			$redirector = $this->getHelper('Redirector');
			$redirector->setExit(true)
				->gotoRoute(array(
					'controller' => 'admin_sitemap',
					'action' => 'index'
					), 'default', true);
		}
	}
}