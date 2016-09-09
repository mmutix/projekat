<?php
class AboutusController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
    }
    public function indexAction()
    {
        $request= $this->getRequest();
        $sitemapPageId= (int) $request->getParam('sitemap_page_id');
        //proveravamo page id
        if($sitemapPageId<=0){
            throw new Zend_Controller_Router_Exception('Invalid Sitemap Page id: ' . $sitemapPageId, 404);
        }
        //komunikacija sa bazom
        $cmsSitemapPageDbTable=new Application_Model_DbTable_CmsSitemapPages();
        
        $sitemapPage=$cmsSitemapPageDbTable->getSitemapPageById($sitemapPageId);
        
        if(!$sitemapPage){
            throw new Zend_Controller_Router_Exception('No Sitemap Page is found for id: ' . $sitemapPageId, 404);
        }
        
        if(
                $sitemapPage['status']==Application_Model_DbTable_CmsSitemapPages::STATUS_DISABLED
                //check if user is not logged in
                //then preview is not available
                //for disabled pages
                && !Zend_Auth::getInstance()->hasIdentity()
        ){
            throw new Zend_Controller_Router_Exception('No Sitemap Page is disabled: ' . $sitemapPageId, 404);
        }
        
        $sitemapPageBreadcrumbs = $cmsSitemapPageDbTable->getSitemapPageBreadcrumbs($sitemapPageId);
        
        $this->view->sitemapPage = $sitemapPage;
        $this->view->breadcrumb = $sitemapPageBreadcrumbs;
    }
}