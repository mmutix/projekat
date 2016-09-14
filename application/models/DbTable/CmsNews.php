<?php
class Application_Model_DbTable_CmsNews extends Zend_Db_Table_Abstract {
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    protected $_name = 'cms_news';
    /**
     * @param iny $id
     * return null|array Associative array with keys as cms_news table columns or NULL if not found
     */
    public function getNewsById($id) {
        $select = $this->select();
        $select->where('id = ?', $id);
        $row = $this->fetchRow($select);
        if ($row instanceof Zend_Db_Table_Row) {
            return $row->toArray();
        } else {
            //row is not found
            return NULL;
        }
    }    /**
     * 
     * @param array $news Associative array with keys at column names and values as column new values
     * @return int The ID of new news (autoincrement)
     */
    public function insertNews($news) {
        //fetch order number of new new        
        $id = $this->insert($news);
        return $id;
    }
    /**
     * @param type  int $id
     * @param array $news Associative array with keys at column names and values as column news values
     */
    public function updateNews($id, $news) {
        if (isset($news['id'])) {
            //forbid changing of news id
            unset($news['id']);
        }
        $this->update($news, 'id = ' . $id);
    }

    /**
     * 
     * @param int $id ID of new to delete
     */
    public function deleteNews($id) {
        $this->delete('id = ' . $id);
    }
    /**
     * 
     * @param int $id ID of new to disable
     */
    public function disableNews($id) {
        $this->update(array(
            'status' => self::STATUS_DISABLED
                ), 'id = ' . $id);
    }
    /**
     * 
     * @param int $id ID of new to enable
     */
    public function enableNews($id) {
        $this->update(array(
            'status' => self::STATUS_ENABLED
                ), 'id = ' . $id);
    }
    
    public function updateOrderOfNews($sortedIds) {
        foreach ($sortedIds as $orderNumber => $id) {
            $this->update(array(
                'order_number' => $orderNumber + 1 // +1 because order_number starts from 1, not from 0 
                    ), 'id = ' . $id);
        }
    }
    /**
     * Array $parameters is keeping search parameters.
     * Array $parameters must be in following format:
     *      array(
     *       'filters'=>array1(
     *          'status'=>1,
     *          'id' =>array(3,8,11)
     *              ) 
     *       'orders'=>array(
     *          'username'=>ASC,//key is column, if value is ASC then order by asc
     *          'first_name' =>DESC,//key is column, if value is DESC then order by desc 
     *          ),
     *        'limit'=>50, //limit result set to 50 rows
     *        'page' =>3 //start from page 3. If no limit is set, page is ignored            
     *      )
     * @param array $parameters Asociative array with keys filters, orders, limit and page
     */
    public function search(array $parameters = array()) {
        $select = $this->select();
        if (isset($parameters['filters'])) {
            $filters = $parameters['filters'];
            $this->processFilters($filters, $select);
        }
        if (isset($parameters['orders'])) {
            $orders = $parameters['orders'];
            foreach ($orders as $field => $orderDirection) {
                switch ($field) {
                    case 'id':
                    case 'title':
                    case 'description':
                    case 'status':
                    case 'order_number':
                        if ($orderDirection === 'DESC') {
                            $select->order($field . ' DESC');
                        } else {
                            $select->order($field);
                        }
                        break;
                }
            }
        }
        if (isset($parameters['limit'])) {
            if (isset($parameters['page'])) {
                //page is set do limit by page
                $select->limitPage($parameters['page'], $parameters['limit']);
            } else {
                //page is not set, just do regular limit
                $select->limit($parameters['limit']);
            }
        }
        return $this->fetchAll($select)->toArray();
    }
    /**
     * 
     * @param array $filters See function search $parameters['filters']
     * return int Count of rows that match $filters
     */
    public function count(array $filters = array()) {
        $select = $this->select();
        $this->processFilters($filters, $select);
        $select->reset('columns');
        $select->from($this->_name, 'COUNT(*) as total');
        $row = $this->fetchRow($select);
        return $row['total'];
    }
    /**
     * Fill $select object with WHERE conditions
     * @param array $filters
     * @param Zend_Db_Select $select
     */
    protected function processFilters(array $filters, Zend_Db_Select $select) {
        //$select object will be modified outside this function
        //object are always passed by reference
        foreach ($filters as $field => $value) {
            switch ($field) {
                case 'id':
                case 'title':
                case 'description':
                case 'status':
                case 'order_number':
                    if (is_array($value)) {
                        $select->where($field . ' IN (?)', $value);
                    } else {
                        $select->where($field . ' =?', $value);
                    }
                    break;
                case 'title_search':
                    $select->where('title LIKE ?', '%' . $value . '%');
                    break;
                case 'description_search':
                    $select->where('description LIKE ?', '%' . $value . '%');
                    break;
                case 'id_exclude':
                    if (is_array($value)) {
                        $select->where('id NOT IN (?)', $value);
                    } else {
                        $select->where('id !=?', $value);
                    }
                    break;
            }
        }
    }
}