<?php

class Zend_View_Helper_PhotoUrl extends Zend_View_Helper_Abstract
{
	public function photoUrl($photo) {
		
		$photoFileName = $photo['id'] . '.jpg';
		
		$photoFilePath = PUBLIC_PATH . '/uploads/photo-galleries/photos/' . $photoFileName;                    ;
		
		//Helper ima property view koji je Zend_View
		// i preko kojeg pozivamo ostale view helpere
		//na primer $this->view->baseUrl()
		
		
		if (is_file($photoFilePath)) {
			
			return $this->view->baseUrl('/uploads/photo-galleries/photos/' . $photoFileName);
			
		} else {
			return '';
		}
	}
}