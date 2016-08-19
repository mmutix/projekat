<?php

class PhotogalleriesController extends Zend_Controller_Action
{
	public function indexAction() {
		$request = $this->getRequest();
		
		$sitemapPageId = (int) $request->getParam('sitemap_page_id');
		
		if ($sitemapPageId <= 0) {
			throw new Zend_Controller_Router_Exception('Invalid sitemap page id: ' . $sitemapPageId, 404);
		}
		
		$cmsSitemapPageDbTable = new Application_Model_DbTable_CmsSitemapPages();
		
		$sitemapPage = $cmsSitemapPageDbTable->getSitemapPageById($sitemapPageId);
		
		if (!$sitemapPage) {
			throw new Zend_Controller_Router_Exception('No sitemap page is found for id: ' . $sitemapPageId, 404);
		}
		
		if (
			$sitemapPage['status'] == Application_Model_DbTable_CmsSitemapPages::STATUS_DISABLED
			//check if user is not logged in
			//then preview is not available
			//for disabled pages
			&& !Zend_Auth::getInstance()->hasIdentity()
		) {
			throw new Zend_Controller_Router_Exception('Sitemap page is disabled', 404);
		}
		
		$cmsPhotoGalleriesDbTable = new Application_Model_DbTable_CmsPhotoGalleries();
		$photoGalleries = $cmsPhotoGalleriesDbTable->search(array(
			'filters' => array(
				'status' => Application_Model_DbTable_CmsPhotoGalleries::STATUS_ENABLED
			),
			'orders' => array(
				'order_number' => 'ASC'
			)
		));
		
		$this->view->sitemapPage = $sitemapPage;
		$this->view->photoGalleries = $photoGalleries;
	}
	
	public function galleryAction() {
		$request = $this->getRequest();
		
		
		/******** Get PhotoGalleriesPage from sitemap *******/
		$sitemapPageId = (int) $request->getParam('sitemap_page_id');
		
		if ($sitemapPageId <= 0) {
			throw new Zend_Controller_Router_Exception('Invalid sitemap page id: ' . $sitemapPageId, 404);
		}
		
		$cmsSitemapPageDbTable = new Application_Model_DbTable_CmsSitemapPages();
		
		$sitemapPage = $cmsSitemapPageDbTable->getSitemapPageById($sitemapPageId);
		
		if (!$sitemapPage) {
			throw new Zend_Controller_Router_Exception('No sitemap page is found for id: ' . $sitemapPageId, 404);
		}
		/******** Get PhotoGalleriesPage from sitemap *******/
		
		$id = (int) $request->getParam('id');
		
		if ($id <= 0) {
			throw new Zend_Controller_Router_Exception('Invalid photo gallery id: ' . $id, 404);
		}
		
		$cmsPhotoGalleriesDbTable = new Application_Model_DbTable_CmsPhotoGalleries();
		
		$photoGallery = $cmsPhotoGalleriesDbTable->getPhotoGalleryById($id);
		
		if (!$photoGallery) {
			throw new Zend_Controller_Router_Exception('No photo gallery is found for id: ' . $id, 404);
		}
		
		$cmsPhotosDbTable = new Application_Model_DbTable_CmsPhotos();
		
		$photos = $cmsPhotosDbTable->search(array(
			'filters' => array(
				'photo_gallery_id' => $photoGallery['id'],
				'status' => Application_Model_DbTable_CmsPhotos::STATUS_ENABLED
			),
			'orders' => array(
				'order_number' => 'ASC'
			)
		));
		
		$this->view->sitemapPage = $sitemapPage;
		$this->view->photoGallery = $photoGallery;
		$this->view->photos = $photos;
	}
}

