
<?php
class CatalogueController extends Zend_Controller_Action {
    
     public function init()
    {
        /* Initialize action controller here */
    }
    
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
        $cmsProductsDbTable = new Application_Model_DbTable_CmsProducts();
        $products = $cmsProductsDbTable->search(array(
            'filters' => array(
            ),
            'orders' => array(
                'order_number' => 'ASC',
            ),
                //'limit' => 4,
                //'page' => 2
        ));
        $sitemapPageBreadcrumbs = $cmsSitemapPageDbTable->getSitemapPageBreadcrumbs($sitemapPageId);
        $this->view->products =  $products;
        $this->view->sitemapPage = $sitemapPage;
        $this->view->breadcrumb = $sitemapPageBreadcrumbs;
           
    }

    public function productsAction() {
        
        $this->view->dontShowPrice = false;
        
        $request = $this->getRequest();
		$productItemId = (int) $request->getParam('id');
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
				$sitemapPage['status'] == Application_Model_DbTable_CmsSitemapPages::STATUS_DISABLED &&
				!Zend_Auth::getInstance()->hasIdentity()
		) {
			throw new Zend_Controller_Router_Exception('Sitemap page is disabled', 404);
		}
        $cmsProductsDbTable = new Application_Model_DbTable_CmsProducts();
		$productItem = $cmsProductsDbTable->search(array(
			'filters' => array(
				'id' => $productItemId,
				'status' => Application_Model_DbTable_CmsProducts::STATUS_ENABLED
			),
			'orders' => array(
				'order_number' => 'ASC',
			)
		));
		$productItem = $productItem[0];
        $cmsProductsDbTable = new Application_Model_DbTable_CmsProducts();
        $products = $cmsProductsDbTable->search(array(
            'filters' => array(
            ),
            'orders' => array(
                'order_number' => 'ASC',
            ),
                //'limit' => 4,
                //'page' => 2
        ));
        $sitemapPageBreadcrumbs = $cmsSitemapPageDbTable->getSitemapPageBreadcrumbs($sitemapPageId);
        
        $this->view->breadcrumb = $sitemapPageBreadcrumbs;
        $this->view->sitemapPage = $sitemapPage;
        $this->view->productItem = $productItem;
        $this->view->products =  $products;
        
    }
    
    
}
