<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once( 'simple_html_dom.php' );

//$icp = new ilawCategoryParser( );
//
//$links = $icp->initCategoryParser( 'opinion/between-the-lines/', 2 );
//
//print_r($links);

class ilawCategoryParser{

    private $_category_link = null;
    private $_last_parsed_date = null;
    private $_pages_to_get = null;
    private $_links = array();
    private $_is_the_end = false;
    private $_counter;
    private $_domain = 'http://www.illawarramercury.com.au/';
    private $_new_parsing_date;


    public function __construct( $last_parsed = 0, $pages = 10 ){
        $this->_pages_to_get = $pages;
        $this->_last_parsed_date = strtotime($last_parsed);
        $this->_counter = 0;
        $this->_new_parsing_date = time();
    }

    public function initCategoryParser( $link ){
        $this->_category_link = $link;
        $html = new simple_html_dom();
        $link = $this->_domain.$link;
        $html->load_file( $link );
        $this->_counter++;

        if( $this->_endOfLink( $html ) || $this->_counter > $this->_pages_to_get ){
            return array(
                'last_link'     =>  $this->_category_link,
                'links'         =>  $this->_links,
                'parsing_data'  =>  $this->_new_parsing_date
            );
        } else {
            $this->_parseHtml( $html );
        }

        return array(
            'last_link'     =>  $this->_category_link,
            'links'         =>  $this->_links,
            'parsing_data'  =>  $this->_new_parsing_date
        );

    }

    private function _parseHtml( $html ){
        if( is_null( $html ) ) return;

        $this->_getArticleLinks( $html );
        $this->_followNextPage( $html );
    }

    private function _endOfLink( $html ){
        if( is_null( $html ) ) return true;

        return $this->_is_the_end;
    }

    private function _getArticleLinks( $html ){
        $articles_wrapper = $html->find('section[class=article-listings-default]', 0);

        if( is_null( $articles_wrapper ) ) return;

        $articles = $articles_wrapper->find('article');

        foreach( $articles as $article ){
            $article_date = $article->find('time',0)->getAttribute('datetime');
            if( strtotime($article_date) < $this->_last_parsed_date ) return;
            $this->_links[] = $article->find('a',0)->getAttribute('href');
        }

    }

    private function _followNextPage( $html ){

        $pagination = $html->find('ul[class=pager]', 0);

        if( $this->_counter > 1 ){
            $this->_is_the_end = true;
            return;
        }

        if( is_null( $pagination ) ){
            $this->_is_the_end = true;
            return;
        }
        $next_link_holder = $pagination->find( 'li[class=next]',0 );
        if( is_null( $next_link_holder ) ){
            $this->_is_the_end = true;
            return;
        }
        $next_link = $next_link_holder->find('a',0)->getAttribute('href');
        if( is_null( $next_link ) ){
            $this->_is_the_end = true;
            return;
        }
        $this->initCategoryParser( $next_link );

    }
}

?>