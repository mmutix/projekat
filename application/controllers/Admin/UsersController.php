<?php

class Admin_UsersController extends Zend_Controller_Action {

	public function indexAction() {
		$flashMessenger = $this->getHelper('FlashMessenger');

		$systemMessages = array(
			'success' => $flashMessenger->getMessages('success'),
			'errors' => $flashMessenger->getMessages('errors')
		);

		$this->view->users = array();
		$this->view->systemMessages = $systemMessages;
	}

	public function addAction() {
		$request = $this->getRequest();

		$flashMessenger = $this->getHelper('FlashMessenger');

		$systemMessages = array(
			'success' => $flashMessenger->getMessages('success'),
			'errors' => $flashMessenger->getMessages('errors'),
		);


		$form = new Application_Form_Admin_UserAdd();

		//default form data
		$form->populate(array(
		));

		if ($request->isPost() && $request->getPost('task') === 'save') {

			try {

				//check form is valid
				if (!$form->isValid($request->getPost())) {
					throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new user');
				}

				//get form data
				$formData = $form->getValues();

				//Insertujemo novi zapis u tabelu
				$cmsUsersTable = new Application_Model_DbTable_CmsUsers();

				//insert member returns ID of the new member
				$userId = $cmsUsersTable->insertUser($formData);

				// do actual task
				//save to database etc
				//set system message
				$flashMessenger->addMessage('User has been saved', 'success');

				//redirect to same or another page
				$redirector = $this->getHelper('Redirector');
				$redirector->setExit(true)
					->gotoRoute(array(
						'controller' => 'admin_users',
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
			throw new Zend_Controller_Router_Exception('Invalid user id: ' . $id, 404);
		}

		$loggedInUser = Zend_Auth::getInstance()->getIdentity();

		if ($id == $loggedInUser['id']) {
			//redirect user to edit profile page
			$redirector = $this->getHelper('Redirector');
			$redirector->setExit(true)
				->gotoRoute(array(
					'controller' => 'admin_profile',
					'action' => 'edit'
					), 'default', true);
		}

		$cmsUsersTable = new Application_Model_DbTable_CmsUsers();

		$user = $cmsUsersTable->getUserById($id);

		if (empty($user)) {
			throw new Zend_Controller_Router_Exception('No user is found with id: ' . $id, 404);
		}


		$flashMessenger = $this->getHelper('FlashMessenger');

		$systemMessages = array(
			'success' => $flashMessenger->getMessages('success'),
			'errors' => $flashMessenger->getMessages('errors'),
		);


		$form = new Application_Form_Admin_UserEdit($user['id']);

		//default form data
		$form->populate($user);

		if ($request->isPost() && $request->getPost('task') === 'update') {

			try {

				//check form is valid
				if (!$form->isValid($request->getPost())) {
					throw new Application_Model_Exception_InvalidInput('Invalid data was sent for user');
				}

				//get form data
				$formData = $form->getValues();


				$cmsUsersTable->updateUser($user['id'], $formData);

				//set system message
				$flashMessenger->addMessage('User has been updated', 'success');

				//redirect to same or another page
				$redirector = $this->getHelper('Redirector');
				$redirector->setExit(true)
					->gotoRoute(array(
						'controller' => 'admin_users',
						'action' => 'index'
						), 'default', true);
			} catch (Application_Model_Exception_InvalidInput $ex) {
				$systemMessages['errors'][] = $ex->getMessage();
			}
		}

		$this->view->systemMessages = $systemMessages;
		$this->view->form = $form;

		$this->view->user = $user;
	}

	public function deleteAction() {

		$request = $this->getRequest();

		if (!$request->isPost() || $request->getPost('task') != 'delete') {
			// request is not post
			// or task is not delete
			// redirect to index page

			$redirector = $this->getHelper('Redirector');
			$redirector->setExit(true)
				->gotoRoute(array(
					'controller' => 'admin_ussers',
					'action' => 'index'
					), 'default', true);
		}

		$flashMessenger = $this->getHelper('FlashMessenger');

		try {
			// read $_POST['id]
			$id = (int) $request->getPost('id');

			if ($id <= 0) {

				throw new Application_Model_Exception_InvalidInput('Invalid user\'s id: ' . $id);
			}

			$loggedInUser = Zend_Auth::getInstance()->getIdentity();

			if ($id == $loggedInUser['id']) {
				//redirect user to edit profile page
				$redirector = $this->getHelper('Redirector');
				$redirector->setExit(true)
					->gotoRoute(array(
						'controller' => 'admin_profile',
						'action' => 'edit'
						), 'default', true);
			}

			$cmsUsersTable = new Application_Model_DbTable_CmsUsers();

			$user = $cmsUsersTable->getUserById($id);

			if (empty($user)) {

				throw new Application_Model_Exception_InvalidInput('No user is found with id: ' . $id);
			}

			$cmsUsersTable->deleteUser($id);

			if ($request->isXmlHttpRequest()) {
				//request is ajax

				$responseJson = array(
					'status' => 'ok',
					'statusMessage' => 'User ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been deleted'
				);

				//send json as response
				$this->getHelper('Json')->sendJson($responseJson);

			} else {
				// request is not ajax
				$flashMessenger->addMessage('User ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been deleted', 'success');
			
				$redirector = $this->getHelper('Redirector');
				$redirector->setExit(true)
					->gotoRoute(array(
						'controller' => 'admin_users',
						'action' => 'index'
						), 'default', true);
			}
			
		} catch (Application_Model_Exception_InvalidInput $ex) {

			if ($request->isXmlHttpRequest()) {
				//request is ajax

				$responseJson = array(
					'status' => 'error',
					'statusMessage' => $ex->getMessage()
				);

				//send json as response
				$this->getHelper('Json')->sendJson($responseJson);

			} else {
				// request is not ajax
				$flashMessenger->addMessage($ex->getMessage(), 'errors');

				$redirector = $this->getHelper('Redirector');
				$redirector->setExit(true)
					->gotoRoute(array(
						'controller' => 'admin_users',
						'action' => 'index'
						), 'default', true);
			}
		}
	}

	public function disableAction() {

		$request = $this->getRequest();

		if (!$request->isPost() || $request->getPost('task') != 'disable') {
			// request is not post
			// or task is not delete
			// redirect to index page

			$redirector = $this->getHelper('Redirector');
			$redirector->setExit(true)
				->gotoRoute(array(
					'controller' => 'admin_users',
					'action' => 'index'
					), 'default', true);
		}

		$flashMessenger = $this->getHelper('FlashMessenger');

		try {
			// read $_POST['id']
			$id = (int) $request->getPost('id');

			if ($id <= 0) {

				throw new Application_Model_Exception_InvalidInput('Invalid user id: ' . $id);
			}

			$loggedInUser = Zend_Auth::getInstance()->getIdentity();

			if ($id == $loggedInUser['id']) {
				//redirect user to edit profile page
				throw new Application_Model_Exception_InvalidInput('You can not disable your account!');
			}

			$cmsUsersTable = new Application_Model_DbTable_CmsUsers();

			$user = $cmsUsersTable->getUserById($id);

			if (empty($user)) {

				throw new Application_Model_Exception_InvalidInput('No user is found with id: ' . $id);
			}

			$cmsUsersTable->disableUser($id);
			
			
			$request instanceof Zend_Controller_Request_Http;
			
			if ($request->isXmlHttpRequest()) {
				//request is ajax request
				//send response as json
				
				$responseJson = array(
					'status' => 'ok',
					'statusMessage' => 'User ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been disabled'
				);
				
				//send json as response
				$this->getHelper('Json')->sendJson($responseJson);
				
			} else {
				//request is not ajax
				//send message over session (flashMessanger)
				// and do redirect
				
				$flashMessenger->addMessage('User ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been disabled', 'success');

				$redirector = $this->getHelper('Redirector');
				$redirector->setExit(true)
					->gotoRoute(array(
						'controller' => 'admin_users',
						'action' => 'index'
						), 'default', true);
			}

			
		} catch (Application_Model_Exception_InvalidInput $ex) {
			
			if ($request->isXmlHttpRequest()) {
				//request is ajax
				
				$responseJson = array(
					'status' => 'error',
					'statusMessage' => $ex->getMessage()
				);
				
				//send json as response
				$this->getHelper('Json')->sendJson($responseJson);
				
			} else {
				// request is not ajax
				$flashMessenger->addMessage($ex->getMessage(), 'errors');

				$redirector = $this->getHelper('Redirector');
				$redirector->setExit(true)
					->gotoRoute(array(
						'controller' => 'admin_users',
						'action' => 'index'
						), 'default', true);
			}
		}
	}

	public function enableAction() {

		$request = $this->getRequest();

		if (!$request->isPost() || $request->getPost('task') != 'enable') {
			// request is not post
			// or task is not delete
			// redirect to index page

			$redirector = $this->getHelper('Redirector');
			$redirector->setExit(true)
				->gotoRoute(array(
					'controller' => 'admin_users',
					'action' => 'index'
					), 'default', true);
		}

		$flashMessenger = $this->getHelper('FlashMessenger');

		try {
			// read $_POST['id]
			$id = (int) $request->getPost('id');

			if ($id <= 0) {
				throw new Application_Model_Exception_InvalidInput('Invalid user id: ' . $id);
			}

			$loggedInUser = Zend_Auth::getInstance()->getIdentity();

			if ($id == $loggedInUser['id']) {
				//redirect user to edit profile page
				throw new Application_Model_Exception_InvalidInput('You can not enable your account!');
			}

			$cmsUsersTable = new Application_Model_DbTable_CmsUsers();

			$user = $cmsUsersTable->getUserById($id);

			if (empty($user)) {
				throw new Application_Model_Exception_InvalidInput('No user is found with id: ' . $id);
			}

			$cmsUsersTable->enableUser($id);
			
			if ($request->isXmlHttpRequest()) {
				//request is ajax
				
				$responseJson = array(
					'status' => 'ok',
					'statusMessage' => 'User ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been enabled'
				);
				
				//send json as response
				$this->getHelper('Json')->sendJson($responseJson);
				
			} else {
				// request is not ajax
				$flashMessenger->addMessage('User ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been enabled', 'success');

				$redirector = $this->getHelper('Redirector');
				$redirector->setExit(true)
					->gotoRoute(array(
						'controller' => 'admin_users',
						'action' => 'index'
						), 'default', true);
			}
			

			
		} catch (Application_Model_Exception_InvalidInput $ex) {
			if ($request->isXmlHttpRequest()) {
				//request is ajax
				
				$responseJson = array(
					'status' => 'error',
					'statusMessage' => $ex->getMessage()
				);
				
				//send json as response
				$this->getHelper('Json')->sendJson($responseJson);
				
			} else {
				// request is not ajax
				$flashMessenger->addMessage($ex->getMessage(), 'errors');

				$redirector = $this->getHelper('Redirector');
				$redirector->setExit(true)
					->gotoRoute(array(
						'controller' => 'admin_users',
						'action' => 'index'
						), 'default', true);
			}
		}
	}

	public function resetpasswordAction() {

		$request = $this->getRequest();
		
		$flashMessenger = $this->getHelper('FlashMessenger');
		
		$systemMessages = array(
			'success' => $flashMessenger->getMessages('success'),
			'errors' => $flashMessenger->getMessages('errors')
		);

		if ($request->isPost() && $request->getPost('task') === 'resetPassword') {
			try {
				// read $_POST['id]
				$id = (int) $request->getPost('id');
				
				if ($id <= 0) {
					throw new Application_Model_Exception_InvalidInput('Invalid user id: ' . $id);
				}
				$loggedInUser = Zend_Auth::getInstance()->getIdentity();

				if ($id == $loggedInUser['id']) {
					//redirect user to edit profile page
					throw new Application_Model_Exception_InvalidInput('You can not reset password for your account!');
				}

				$cmsUsersTable = new Application_Model_DbTable_CmsUsers();
				
				$user = $cmsUsersTable->getUserById($id);
				
				if (empty($user)) {
					throw new Application_Model_Exception_InvalidInput('No user is found with id: ' . $id);
				}
				
				$cmsUsersTable->changeUserPassword($id, Application_Model_DbTable_CmsUsers::DEFAULT_PASSWORD);
				
				if ($request->isXmlHttpRequest()) {
					//request is ajax

					$responseJson = array(
						'status' => 'ok',
						'statusMessage' => 'Password of user ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been reset'
					);

					//send json as response
					$this->getHelper('Json')->sendJson($responseJson);

				} else {
					// request is not ajax
					$flashMessenger->addMessage('Password of user ' . $user['first_name'] . ' ' . $user['last_name'] . ' has been reset', 'success');
				
					$redirector = $this->getHelper('Redirector');
					$redirector->setExit(true)
						->gotoRoute(array(
							'controller' => 'admin_users',
							'action' => 'index'
							), 'default', true);
				}
				
			} catch (Application_Model_Exception_InvalidInput $ex) {
				
				if ($request->isXmlHttpRequest()) {
					//request is ajax

					$responseJson = array(
						'status' => 'error',
						'statusMessage' => $ex->getMessage()
					);

					//send json as response
					$this->getHelper('Json')->sendJson($responseJson);

				} else {
					// request is not ajax
					$flashMessenger->addMessage($ex->getMessage(), 'errors');

					$redirector = $this->getHelper('Redirector');
					$redirector->setExit(true)
						->gotoRoute(array(
							'controller' => 'admin_users',
							'action' => 'index'
							), 'default', true);
				}
			}
		}
		$this->view->systemMessages = $systemMessages;
		$this->view->form = $form;
	}
	
	public function datatableAction() {
		
		$request = $this->getRequest();
		
		$datatableParameters = $request->getParams();
		
		
		/*

Array
(
    [controller] => admin_users
    [action] => datatable
    [module] => default

    [draw] => 2
    
    [order] => Array
        (
            [0] => Array
                (
                    [column] => 2
                    [dir] => asc
                )

        )

    [start] => 0
    [length] => 5
    [search] => Array
        (
            [value] => 
            [regex] => false
        )
)
		*/
		
		
		$cmsUsersTable = new Application_Model_DbTable_CmsUsers();
		
		$loggedInUser = Zend_Auth::getInstance()->getIdentity();
		
		$filters = array(
			'id_exclude' => $loggedInUser
		);
		$orders = array();
		$limit = 5;
		$page = 1;
		$draw = 1;
		
		$columns = array('status', 'username', 'first_name', 'last_name', 'email', 'actions');
		
		//Process datatable parameters
		
		if (isset($datatableParameters['draw'])) {
			
			$draw = $datatableParameters['draw'];
			
			if (isset($datatableParameters['length'])) {
				
				//limit rows per page
				$limit = $datatableParameters['length'];
				
				if (isset($datatableParameters['start'])) {
					
					$page = floor($datatableParameters['start'] / $datatableParameters['length']) + 1;
				}
			}
			
			if (
				isset($datatableParameters['order'])
				&& is_array($datatableParameters['order'])
				) {
				
				foreach ($datatableParameters['order'] as $datatableOrder) {
					$columnIndex = $datatableOrder['column'];
					$orderDirection = strtoupper($datatableOrder['dir']);
					
					if (isset($columns[$columnIndex])) {
						$orders[$columns[$columnIndex]] = $orderDirection;
					}
				}
			}
			
			if (
				isset($datatableParameters['search'])
				&& is_array($datatableParameters['search'])
				&& isset($datatableParameters['search']['value'])
			) {
				
				$filters['username_search'] = $datatableParameters['search']['value'];
			}
		}
		
		$users = $cmsUsersTable->search(array(
			'filters' => $filters,
			'orders' => $orders,
			'limit' => $limit,
			'page' => $page
		));
		
		$usersFilteredCount = $cmsUsersTable->count($filters);
		$usersTotal = $cmsUsersTable->count();
		
		$this->view->users = $users;
		$this->view->usersFilteredCount = $usersFilteredCount;
		$this->view->usersTotal = $usersTotal;
		$this->view->draw = $draw;
		$this->view->columns = $columns;
	}

}
