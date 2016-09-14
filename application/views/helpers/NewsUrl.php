
<?php

class Zend_View_Helper_NewsUrl extends Zend_View_Helper_Abstract {

    public function newsUrl($news) {

        return $this->view->url(array(
                    'id' => $news['id'],
                    'news_slug' => $news['title']
                        ), 'news-route', true);
    }

}
