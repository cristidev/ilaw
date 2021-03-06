<?php
ini_set('max_execution_time', 300);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once( 'simple_html_dom.php' );

//$parser = new ilawParser();

class ilawParser{

    public $sitemap_url = 'http://www.illawarramercury.com.au/sitemap-news.xml';

    public $_articles = array();
    private $_url = '';
    private $_url_hash = '';
    private $_article_html = null;
    private $_domain = 'http://www.illawarramercury.com.au/';

    public function __construct(){

//        $this->getArticleFromUrl( 'story/3722593/wollongong-prisoner-transfers-up-450-per-cent-union/?cs=300' );
    }

    public function getArticleFromUrl( $url ){
        $url = $this->_domain . trim($url, '/');;

        $this->_url = $url;
        $this->_url_hash = md5($url);

        $this->_articles[$this->_url_hash] = array( 'url' => $url );

        $article_html = new simple_html_dom();
        $article_html->load_file( $url );

        if( is_null( $article_html ) ) return;

        $this->_article_html = $article_html;

        $this->_getMetaFromArticle();
        $this->_getCategoryFromArticle();
        $this->_getArticle();

        return $this->_articles;

        echo '<pre>';
        print_r($this->_articles);
        echo '</pre>';
    }

    private function _getArticle(  ){
        $article_html = $this->_article_html;

        $article_item = $article_html->find( 'article', 0 );

        if( is_null( $article_item ) ) return;

        $this->_getTitleFromArticle( $article_item );
        $this->_getPublishDateFromArticle( $article_item );

        $article_body = $article_item->find( 'div[class=news-article-body]', 0 );

        if( is_null( $article_body )) return;

        $this->_getSummaryFromArticle( $article_body );
        $this->_getImageFromArticle( $article_body );
        $this->_getBodyFromArticle( $article_body );
    }

    private function _getMetaFromArticle(){
        $this->_articles[$this->_url_hash]['meta'] = array( 'title' => '', 'description'=>'' );

        try{
            $this->_articles[$this->_url_hash]['meta']['title'] =  $this->_article_html->find( 'meta[name=title]', 0 )->getAttribute('content');
            $this->_articles[$this->_url_hash]['meta']['description'] =  $this->_article_html->find( 'meta[name=description]', 0 )->getAttribute('content');
        } catch( Exception $e ){

        }
    }


    private function _getBodyFromArticle( $article_body ){
        $this->_articles[$this->_url_hash]['body'] = '';

        $article_body_text = $article_body->innertext;

        //let's remove the unnecessary data
        try{
            $summary = trim( $article_body->find( 'p[class=summary]', 0 )->outertext );
            $article_body_text = str_replace( $summary, '', $article_body_text );
        } catch( Exception $e){
        }
        try{
            $image = $article_body->find( 'div[class=story-images]', 0 );
            if( !is_null( $image ) ){
                $article_body_text = str_replace( $image->outertext, '', $article_body_text );
            }

        } catch( Exception $e){
        }

        try{
            $image = $article_body->find( 'div[class=carousel-overlay-container]', 0 );
            if( !is_null( $image ) ){
                $article_body_text = str_replace( $image->outertext, '', $article_body_text );
            }

        } catch( Exception $e){
        }


        try{
            $aside_divs = $article_body->find( 'div[class=aside]' );
            foreach( $aside_divs as $aside_div ){
                $article_body_text = str_replace( $aside_div->outertext, '', $article_body_text );
            }

        } catch( Exception $e){
        }

        $this->_articles[$this->_url_hash]['body'] = $article_body_text;
    }

    private function _getImageFromArticle( $article_body ){
        $this->_articles[$this->_url_hash]['image'] = array( 'src'=>'', 'alt'=>'', 'title'=>'' );
        $this->_articles[$this->_url_hash]['slides'] = array();

        //array( 'src'=>'', 'alt'=>'', 'title'=>'' );

        try{
            $img_div = $article_body->find( 'div[class=story-images]', 0 );

            if( !is_null( $img_div ) ) {

                $article_image = $img_div->find('img', 0);

                if( !is_null( $article_image ) ) {

                    $this->_articles[$this->_url_hash]['image']['src'] = $img_div->find('img', 0)->getAttribute('data-src');
                    $this->_articles[$this->_url_hash]['image']['alt'] = $img_div->find('img', 0)->getAttribute('alt');
                    $this->_articles[$this->_url_hash]['image']['title'] = $img_div->find('img', 0)->getAttribute('title');
                }
            }
        } catch( Exception $e ){
            //return;
        }

        //let's try and get the slides, if any
        try{
            $slides_ul = $article_body->find( 'ul[class=slides]', 0 );
            if( !is_null( $slides_ul ) ){
                $slides = $slides_ul->find('li');
                if( !is_null( $slides ) ){
                    foreach( $slides as $li ){
                        $slide_ar = array( 'src'=>'', 'alt'=>'', 'title'=>'' );
                        $img_slide = $li->find( 'img', 0 );

                        if( !is_null( $img_slide ) ){
                            $slide_ar['src'] = $img_slide->getAttribute('data-src');
                            $slide_ar['alt'] = $img_slide->getAttribute('alt');
                            $slide_ar['title'] = $img_slide->getAttribute('title');

                            $this->_articles[$this->_url_hash]['slides'][] = $slide_ar;
                        }
                    }
                }
            }

        } catch( Exception $e ){

        }
    }

    private function _getSummaryFromArticle( $article_body ){
        $this->_articles[$this->_url_hash]['summary'] = '';

        try{
            $this->_articles[$this->_url_hash]['summary'] = trim( $article_body->find( 'p[class=summary]', 0 )->innertext );
        } catch( Exception $e ){
            return;
        }
    }

    private function _getPublishDateFromArticle( $article_item ){
        $this->_articles[$this->_url_hash]['publishdate'] = '';

        try{
            $this->_articles[$this->_url_hash]['publishdate'] = $article_item->find( 'time', 0 )->datetime;
        } catch( Exception $e ){
            return;
        }
    }

    private function _getTitleFromArticle( $article_item ){
        $this->_articles[$this->_url_hash]['title'] = '';

        try{
            $title = $article_item->find( 'h1', 0 )->innertext;
            $commented_a = $article_item->find( 'h1', 0 )->find( 'comment',0 );

            if( !is_null( $commented_a ) ){
                $commented_a = $commented_a->outertext;
                $title = str_replace( $commented_a, '', $title );
            }

            $title = trim( $title );
            $this->_articles[$this->_url_hash]['title'] = $title;
        } catch( Exception $e ){
            return;
        }
    }

    private function _getCategoryFromArticle(){
        $article_html = $this->_article_html;
        $this->_articles[$this->_url_hash]['categories'] = array( 'last'=>array() );

        $breadcrumb = $article_html->find('div[class=breadcrumb]', 0);

        if( is_null($breadcrumb) ) return;

        $lis = $breadcrumb->find('li');

        foreach( $lis as $li ){
            $item = array();
            $a = $li->find('a', 0);

            if( is_null( $a ) ) continue;

            $item['slug'] = $a->href;
            if( empty( $a->first_child()->innertext ) ){
                $item['name'] = $a->innertext;
            } else {
                $item['name'] = $a->first_child()->innertext;
            }

            $categories[] = $item;
        }

        $this->_articles[$this->_url_hash]['categories'] = $categories;

        return;

    }

    private function _getSitemap(){

        //new SimpleXMLElement( $this->sitemap_url )
    }

}

?>