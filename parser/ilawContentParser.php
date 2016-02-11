<?php
ini_set('max_execution_time', 300);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once( 'ilawCategoryParser.php' );
include_once( 'ilawParser.php' );

class ilawContentParser{

    private $_link_to_parse = null;
    private $_no_pages = null;
    private $_template = null;

    public function __construct( $link, $no_pages = 1, $template='default_category' ){

        $this->_link_to_parse   = $link;
        $this->_no_pages        = $no_pages;
        $this->_template        = $template;
    }

    public function parse(){

        switch( $this->_template ){
            case "default_category":
                return $this->_getCategoryArticles();
                break;
        }

    }

    private function _getCategoryArticles(){
        $response = array( 'error' => false, 'content' => array(), 'n'=>2 );
        $ilawCategoryParser = new ilawCategoryParser( 0, $this->_no_pages );

        $links = $ilawCategoryParser->initCategoryParser( $this->_link_to_parse );

        if( empty( $links['links'] ) ){
            return $response;
        }

        //if we have links, for each link we must get the content.
        foreach( $links['links'] as $link ){
            $rsp = $this->_getArticleContent( $link );
            foreach(  $rsp as $id=>$value){
                $response['content'][$id] = $value;
            }

        }


        return $response;
    }

    private function _getArticleContent( $link ){
        $ilawParser = new ilawParser();

        return $ilawParser->getArticleFromUrl( $link );

    }

}

//$content = new ilawContentParser( 'news/local-news/' );
//$content->parse();

