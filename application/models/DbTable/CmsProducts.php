<?php
class Application_Model_DbTable_CmsProducts extends Zend_Db_Table_Abstract {
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    protected $_name = 'cms_products';
    /**
     * @param iny $id
     * return null|array Associative array with keys as cms_products table columns or NULL if not found
     */
    public function getProductById($id) {
        $select = $this->select();
        $select->where('id = ?', $id);
        $row = $this->fetchRow($select); //find vraca niz objekata tj vise redova, ne samo jedan
        if ($row instanceof Zend_Db_Table_Row) {
            return $row->toArray();
        } else {
            //row is not found
            return NULL;
        }
    }
    /**
     * @param type  int $id
     * @param array $product Associative array with keys at column names and values as column new values
     */
    public function updateProduct($id, $product) {
        if (isset($product['id'])) {
            //forbid changing of user id
            unset($product['id']);
        }
        $this->update($product, 'id = ' . $id);
    }
    /**
     * 
     * @param array $product Associative array with keys at column names and values as column new values
     * @return int The ID of new product (autoincrement)
     */
    public function insertProduct($product) {
       //fetch order number of new product
        
        
        $select = $this->select();
        
        //Sort rows by order_number Descending and fetch one row from the top
        $select->order('order_number DESC');
        
        $this->fetchRow($select);
        
        $productWithBiggestOrderNumber = $this->fetchRow($select);
        
        if($productWithBiggestOrderNumber instanceof Zend_Db_Table_Row){
            
            $product['order_number'] = $productWithBiggestOrderNumber['order_number'] + 1;
        }else {
            // table was empty, we are inserting first product
            $product['order_number'] = 1;
        }
        
        
        
        $id = $this->insert($product);
        
        return $id;
    }
    /**
     * 
     * @param int $id ID of product to delete
     */
    public function deleteProduct($id){
        
        $productPhotoFilePath = PUBLIC_PATH . '/img/' . $id . '.jpg';
        //preneti u products!!!!
        if(is_file($productPhotoFilePath)){
            //delete product photo file
            unlink($productPhotoFilePath);
        }
        
        //product who is going to be deleted
        $product = $this->getProductById($id);
        
        $this->update(array(
            'order_number' => new Zend_Db_Expr('order_number -1')  
        ), 
            'order_number > ' . $product['order_number']);
        
        $this->delete('id = ' . $id);
    }
    /**
     * 
     * @param int $id ID of product to disable
     */
    public function disableProduct($id){
        $this->update(array(
            'stock_status' =>  self::STATUS_DISABLED
        ),'id = ' . $id);
    }
    /**
     * 
     * @param int $id ID of product to enable
     */
    public function enableProduct($id){
        $this->update(array(
            'stock_status' =>  self::STATUS_ENABLED
        ),'id = ' . $id);
    }
    public function updateOrderOfProducts($sortedIds){
        foreach($sortedIds as $orderNumber => $id){
            $this->update(array(
            'order_number' =>  $orderNumber + 1 // +1 because order_number starts from 1, not from 0 
        ),'id = ' . $id);
        }
    }
//    public function enabledProducts($products) {
//       
//        $enabledProducts = 0; 
//        foreach ($products as $product) {
//            
//            
//            if ($product['status'] == self::STATUS_ENABLED) {
//                $enabledProducts += 1;
//            }
//        
//        }return $enabledProducts;
//    }
//    public function allProducts($products) {
//        $allProducts =0;
//        
//        foreach ($products as $product){
//            $allProducts += 1;
//        }
//        
//        return $allProducts ;
//    }
    
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
        public function search(array $parameters=array()){
            $select = $this->select();
            
            if(isset($parameters['filters'])){
                $filters = $parameters['filters'];
                $this->processFilters($filters, $select);
                
                
            }
            if(isset($parameters['orders'])){
                $orders = $parameters['orders'];
                foreach ($orders as $field => $orderDirection){
                    switch($field){
                    case 'id':    
                    case 'stock_status':
                    case 'model':
                    case 'type':
                    case 'description':
                    case 'order_number':
                    case 'price':
                    case 'quantity':
                    case 'part_status':
                        
                        if($orderDirection === 'DESC'){
                            $select->order($field . ' DESC');
                        }else{
                            $select->order($field);
                        }
                        break;
                    }
                }
            }
            if(isset($parameters['limit'])){
                if(isset($parameters['page'])){
                    //page is set do limit by page
                    $select->limitPage($parameters['page'], $parameters['limit']);
                }else{
                    //page is not set, just do regular limit
                    $select->limit($parameters['limit']); 
                }
            }
            //debug da vidimo koji se querie izvrsava
            //die($select->assemble());
            
            return $this->fetchAll($select)->toArray();
        }
        /**
         * 
         * @param array $filters See function search $parameters['filters']
         * return int Count of rows that match $filters
         */
    public function count (array $filters = array()) {
        $select = $this->select();
        
        $this->processFilters($filters, $select);
        
        $select->reset('columns');
        
        $select->from($this->_name ,'COUNT(*) as total');
        
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
        
        foreach ($filters as $field => $value){
                   switch ($field){
                    case 'id':    
                    case 'stock_status':
                    case 'model':
                    case 'type':
                    case 'description':
                    case 'order_number':
                    case 'price':
                    case 'quantity':
                    case 'part_status':
                        
                        if(is_array($value)){
                            $select->where($field . ' IN (?)', $value);
                        }else{$select->where($field . ' =?', $value);
                            
                        }
                        break;
                    case 'model_search':
                        $select->where('model LIKE ?', '%' . $value . '%' );
                         break;
                    case 'type_search':
                        $select->where('type LIKE ?', '%' . $value . '%' );
                         break;
                    case 'description_search':
                        $select->where('description LIKE ?', '%' . $value . '%' );
                         break;
                    case 'part_status_search':
                        $select->where('part_status LIKE ?', '%' . $value . '%' );
                         break;
                    case 'id_exclude':
                        if(is_array($value)){
                            $select->where('id NOT IN (?)', $value);
                        }else{
                            $select->where('id !=?', $value);
                        }
                        
                        break;    
                } 
                }
    }
}

