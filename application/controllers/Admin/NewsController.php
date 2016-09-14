<?php

class Admin_NewsController extends Zend_Controller_Action {

    public function indexAction() {
        $flashMessenger = $this->getHelper('FlashMessenger');
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );
        $cmsNewsDbTable = new Application_Model_DbTable_CmsNews();
        $news = $cmsNewsDbTable->search(array(
            //'filters' => array(
            //'description_search'=> 'ideja'
            //),
            'orders' => array(
                'order_number' => 'ASC'
            ),
                //'limit' => 4,
                //'page' => 2
        ));

        $this->view->news = $news;
        $this->view->systemMessages = $systemMessages;
    }

    public function addAction() {
        $request = $this->getRequest();
        $flashMessenger = $this->getHelper('FlashMessenger');
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );
        $form = new Application_Form_Admin_NewsAdd();
        $form->populate(array(
        ));
        if ($request->isPost() && $request->getPost('task') === 'save') {
            try {
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for new news');
                }
                $formData = $form->getValues();
                unset($formData['news_photo']);
                $cmsNewsTable = new Application_Model_DbTable_CmsNews();
                $newsId = $cmsNewsTable->insertNews($formData);
                if ($form->getElement('news_photo')->isUploaded()) {
                    $fileInfos = $form->getElement('news_photo')->getFileInfo('news_photo');
                    $fileInfo = $fileInfos['news_photo'];
                    try {
                        $newsPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                        $newsPhoto->fit(150, 90);
                        $newsPhoto->save(PUBLIC_PATH . '/uploads/news/' . $newsId . '.jpg');
                    } catch (Exception $ex) {
                        $flashMessenger->addMessage('News has been saved, but error occured during image processing', 'errors');
                        $redirector = $this->getHelper('Redirector');
                        $redirector->setExit(true)
                                ->gotoRoute(array(
                                    'controller' => 'admin_news',
                                    'action' => 'edit',
                                    'id' => $newsId
                                        ), 'default', true);
                    }
                }
                $flashMessenger->addMessage('News has been saved', 'success');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_news',
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
            throw new Zend_Controller_Router_Exception('Invalid news id: ' . $id, 404);
        }
        $cmsNewsTable = new Application_Model_DbTable_CmsNews();
        $news = $cmsNewsTable->getNewsById($id);
        if (empty($news)) {
            throw new Zend_Controller_Router_Exception('No news is found with id ' . $id, 404);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        $systemMessages = array(
            'success' => $flashMessenger->getMessages('success'),
            'errors' => $flashMessenger->getMessages('errors'),
        );
        $form = new Application_Form_Admin_NewsAdd();
        $form->populate($news);
        if ($request->isPost() && $request->getPost('task') === 'update') {
            try {
                if (!$form->isValid($request->getPost())) {
                    throw new Application_Model_Exception_InvalidInput('Invalid data was sent for  news');
                }
                $formData = $form->getValues();
                unset($formData['news_photo']);
                if ($form->getElement('news_photo')->isUploaded()) {
                    $fileInfos = $form->getElement('news_photo')->getFileInfo('news_photo');
                    $fileInfo = $fileInfos['news_photo'];
                    try {
                        $newsPhoto = Intervention\Image\ImageManagerStatic::make($fileInfo['tmp_name']);
                        $newsPhoto->fit(150, 90);
                        $newsPhoto->save(PUBLIC_PATH . '/uploads/news/' . $news['id'] . '.jpg');
                    } catch (Exception $ex) {
                        throw new Application_Model_Exception_InvalidInput('Error occured during image processing');
                    }
                }
                $cmsNewsTable->updateNews($news['id'], $formData);
                $flashMessenger->addMessage('News has been updated', 'success');
                $redirector = $this->getHelper('Redirector');
                $redirector->setExit(true)
                        ->gotoRoute(array(
                            'controller' => 'admin_news',
                            'action' => 'index'
                                ), 'default', true);
            } catch (Application_Model_Exception_InvalidInput $ex) {
                $systemMessages['errors'][] = $ex->getMessage();
            }
        }
        $this->view->systemMessages = $systemMessages;
        $this->view->form = $form;
        $this->view->news = $news;
    }

    public function deleteAction() {
        $request = $this->getRequest();
        if (!$request->isPost() || $request->getPost('task') != 'delete') {
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_news',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        try {
            $id = (int) $request->getPost('id');
            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid new id: ' . $id, 'errors');
            }
            $cmsNewsTable = new Application_Model_DbTable_CmsNews();
            $news = $cmsNewsTable->getNewsById($id);
            if (empty($news)) {
                throw new Application_Model_Exception_InvalidInput('No new is found with id: ' . $id, 'errors');
            }
            $cmsNewsTable->deleteNews($id);
            $flashMessenger->addMessage('News: ' . $news['title'] . 'has been deleted', 'success');
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_news',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_news',
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
                        'controller' => 'admin_news',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        try {
            $id = (int) $request->getPost('id');
            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid new id: ' . $id, 'errors');
            }
            $cmsNewsTable = new Application_Model_DbTable_CmsNews();
            $news = $cmsNewsTable->getNewsById($id);
            if (empty($news)) {
                throw new Application_Model_Exception_InvalidInput('No new is found with id: ' . $id, 'errors');
            }
            $cmsNewsTable->disableNews($id);
            $flashMessenger->addMessage('News: ' . $news['title'] . 'has been disabled', 'success');
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_news',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_news',
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
                        'controller' => 'admin_news',
                        'action' => 'index'
                            ), 'default', true);
        }
        $flashMessenger = $this->getHelper('FlashMessenger');
        try {
            $id = (int) $request->getPost('id');
            if ($id <= 0) {
                throw new Application_Model_Exception_InvalidInput('Invalid new id: ' . $id, 'errors');
            }
            $cmsNewsTable = new Application_Model_DbTable_CmsNews();
            $news = $cmsNewsTable->getNewsById($id);
            if (empty($news)) {
                throw new Application_Model_Exception_InvalidInput('No new is found with id: ' . $id, 'errors');
            }
            $cmsNewsTable->enableNews($id);
            $flashMessenger->addMessage('News: ' . $news['title'] . 'has been enabled', 'success');
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_news',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_news',
                        'action' => 'index'
                            ), 'default', true);
        }
    }

    public function updateorderAction() {
        $request = $this->getRequest();
        if (!$request->isPost() || $request->getPost('task') != 'saveOrder') {
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_news',
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
            $cmsNewsTable = new Application_Model_DbTable_CmsNews();
            $cmsNewsTable->updateOrderOfNews($sortedIds);
            $flashMessenger->addMessage('Order is successfully saved', 'success');
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_news',
                        'action' => 'index'
                            ), 'default', true);
        } catch (Application_Model_Exception_InvalidInput $ex) {
            $flashMessenger->addMessage($ex->getMessage(), 'errors');
            $redirector = $this->getHelper('Redirector');
            $redirector->setExit(true)
                    ->gotoRoute(array(
                        'controller' => 'admin_news',
                        'action' => 'index'
                            ), 'default', true);
        }
    }

    public function dashboardAction() {
        $cmsNewsDbTable = new Application_Model_DbTable_CmsNews();
        $totalNumberOfNews = $cmsNewsDbTable->count();
        $activeNews = $cmsNewsDbTable->count(array(
            'status' => Application_Model_DbTable_CmsNews::STATUS_ENABLED
        ));
        $this->view->totalNumberOfNews = $totalNumberOfNews;
        $this->view->activeNews = $activeNews;
    }

}
