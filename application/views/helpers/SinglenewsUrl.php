<?php
class Zend_View_Helper_SinglenewsUrl extends Zend_View_Helper_Abstract
{
    protected $urlSlugFilter;
	
	protected function getUrlSlugFilter() {
		
		/*** Lazy Loading ***/
		
		if (!$this->urlSlugFilter) {
			$this->urlSlugFilter = new Application_Model_Filter_UrlSlug();
		}
		
		return $this->urlSlugFilter;
	}
    public function singlenewsUrl($singleNews) {
        $urlSlugFilter = $this->getUrlSlugFilter();
        return $this->view->url(array(
            'id' => $singleNews['id'],
            'singlenews_slug' => $urlSlugFilter->filter ($singleNews['title']) 
            
        ), 'singlenews-route', true);
        
    }
}