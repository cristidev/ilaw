<?php

class Iwm_Categories_Parser{

    private $_xml = null;
    private $_domain = 'http://www.illawarramercury.com.au';
    private $_categories = array();
    private $_xml_link;

    public function __construct( $xml_link ){
        $this->_xml_link = $xml_link;
    }

    public function parseCategories(){
        $this->_getXml( $this->_xml_link );

        if( !is_null( $this->_xml ) && $this->_xml!==false ){
            foreach( $this->_xml as $category ){
                $this->_extractCategory( $category );
            }
        }

        return $this->_categories;
    }

    private function _extractCategory( $xml_category ){
        $category = array();

        $category_full_url = $xml_category->loc;
        $category_url = trim(str_replace( $this->_domain, '', $category_full_url ), '/');
        if( empty($category_url) ) return;

        $this->_setValue( $this->_categories, $category_url );
    }

    private function _setValue( &$item,$path ){
        $keys = explode('/',$path);
        $url_so_far = '';
        $lk = $keys[count($keys)-1];
        while ($key = array_shift($keys)) {
            if( empty( $key ) || $key == '%2F' ) continue;
            $url_so_far .= $key.'/';
            try{
                $item = &$item['subcategories'][$key];
                if( empty( $item['details'] ) ){
                    $item['details']['url'] = $url_so_far;
                    $item['details']['name'] = $this->_generateCategoryName( $key );
                }

            } catch(Exception $e){
            }
        }

        return;
    }

    private function _generateCategoryName( $key ){
        return ucwords( str_replace( '-', ' ', $key ) );
    }

    private function _getXml( $path_to_xml ){
        $this->_xml = simplexml_load_file( $path_to_xml );

    }

}//end class

?>