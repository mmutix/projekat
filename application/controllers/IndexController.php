<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
		$cmsIndexSlidesDbTable = new Application_Model_DbTable_CmsIndexSlides();
		
		$indexSlides = $cmsIndexSlidesDbTable->search(array(
			'filters' => array(
				'status' => Application_Model_DbTable_CmsIndexSlides::STATUS_ENABLED
			),
			'orders' => array(
				'order_number' => 'ASC'
			)
		));
                
                $cmsSitemapPagesDbTable = new Application_Model_DbTable_CmsSitemapPages();
		$servicesSitemapPages = $cmsSitemapPagesDbTable->search(array(
                'filters' => array(
                'status' => Application_Model_DbTable_CmsSitemapPages::STATUS_ENABLED,
                'type' => 'ServicesPage'
			),
                'limit' => 1
		));
                $newsSitemapPages = $cmsSitemapPagesDbTable->search(array(
                    'filters' => array(
                    'status' => Application_Model_DbTable_CmsSitemapPages::STATUS_ENABLED,
                    'type' => 'NewsPage',
                                ),
                    'limit' => 1
                        ));
                $servicesSitemapPage = !empty($servicesSitemapPages) ? $servicesSitemapPages[0] : null;
        
                $cmsServicesDbTable = new Application_Model_DbTable_CmsServices();
                $newsSitemapPage = !empty($newsSitemapPages) ? $newsSitemapPages[0] : null;
                $cmsNewsDbTable = new Application_Model_DbTable_CmsNews();
                $news = $cmsNewsDbTable->search(array(
                    'filters' => array(
                        'status'=>  Application_Model_DbTable_CmsNews::STATUS_ENABLED,
                    ),
                    'orders' => array(//sortiram tabelu po
                        'order_number'=>'ASC'
                    ),
                    'limit' => 9,
                    //'page' => 2
                ));
                
		$services = $cmsServicesDbTable->search(array(
                'filters' => array(
                'status'=>  Application_Model_DbTable_CmsServices::STATUS_ENABLED,
                ),
                'orders' => array(
                'order_number'=>'ASC'
                ),
                'limit' => 4,
                //'page' => 2
                ));
                
		$this->view->indexSlides = $indexSlides;
                $this->view->news = $news;
                $this->view->newsSitemapPage = $newsSitemapPage;
                $this->view->services = $services;
                $this->view->servicesSitemapPage = $servicesSitemapPage;
    }
}

