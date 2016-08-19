<?php

class Application_Model_Filter_UrlSlug implements Zend_Filter_Interface
{
	public function filter($value) {
		
		$value = preg_replace('/[^\p{L}\p{N}]/u', '-', $value);
		$value = preg_replace('/(\s+)/', '-', $value);
		$value = preg_replace('/(\-+)/', '-', $value);
		$value = trim($value, '-');
		
		return $value;
	}

}