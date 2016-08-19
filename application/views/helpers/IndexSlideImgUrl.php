<?php

class Zend_View_Helper_IndexSlideImgUrl extends Zend_View_Helper_Abstract
{
	public function indexSlideImgUrl($indexSlide) {
		
		$indexSlideImgFileName = $indexSlide['id'] . '.jpg';
		
		$indexSlideImgFilePath = PUBLIC_PATH . '/uploads/index-slides/' . $indexSlideImgFileName;                    ;
		
		//Helper ima property view koji je Zend_View
		// i preko kojeg pozivamo ostale view helpere
		//na primer $this->view->baseUrl()
		
		
		if (is_file($indexSlideImgFilePath)) {
			
			return $this->view->baseUrl('/uploads/index-slides/' . $indexSlideImgFileName);
			
		} else {
			return '';
		}
	}
}