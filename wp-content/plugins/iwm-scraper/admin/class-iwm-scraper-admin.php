<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       www.cristidev.ro
 * @since      1.0.0
 *
 * @package    Iwm_Scraper
 * @subpackage Iwm_Scraper/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Iwm_Scraper
 * @subpackage Iwm_Scraper/admin
 * @author     Cristi <cristidev@gmail.com>
 */
class Iwm_Scraper_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	private $_categories;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Iwm_Scraper_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Iwm_Scraper_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/iwm-scraper-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Iwm_Scraper_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Iwm_Scraper_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/iwm-scraper-admin.js', array( 'jquery' ), $this->version, false );

		wp_localize_script(
			$this->plugin_name, // this needs to match the name of our enqueued script
			'categories',      // the name of the object
			array('ajaxurl' => admin_url('admin-ajax.php')) // the property/value
		);

	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */

	public function add_plugin_admin_menu() {

		/*
         * Add a settings page for this plugin to the Settings menu.
         *
         * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
         *
         *        Administration Menus: http://codex.wordpress.org/Administration_Menus
         *
         */
		add_options_page( 'IWM Scraper Functions Setup', 'IWM Scraper', 'manage_options', $this->plugin_name, array($this, 'display_plugin_setup_page')
		);
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */

	public function add_action_links( $links ) {
		/*
        *  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
        */
		$settings_link = array(
			'<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_name ) . '">' . __('Settings', $this->plugin_name) . '</a>',
		);
		return array_merge(  $settings_link, $links );

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */

	public function display_plugin_setup_page() {
		include_once( 'partials/iwm-scraper-admin-display.php' );
	}

	public function options_update() {
		register_setting($this->plugin_name, $this->plugin_name, array($this, 'validate'));

	}

	public function iwm_import_categories(){
		$response = array( 'error'=> false, 'message' => 'Unknown error' );

		$iwm_categories_parser = new Iwm_Categories_Parser( 'http://www.illawarramercury.com.au/sitemap-sections.xml' );

		$categories = $iwm_categories_parser->parseCategories();
		$response['categories'] = $categories['subcategories'];

		wp_send_json( $response );
	}

	public function iwm_save_categories(){
		$response = array( 'error'=> true, 'message' => 'Unknown error' );
		if( empty( $_POST['ctgs'] ) ){
			$response = array( 'error'=> true, 'message' => 'No categories to save' );
			wp_send_json( $response );
			exit;
		}
		$categories = json_decode(str_replace("\\", "",$_POST['ctgs']), true);
		ksort($categories);

		$this->_categories = $categories;

		$unsuccesful = array();
		$saved_categories = array();

		foreach( $this->_categories as $slug => $category ){
			$parent_id = 0;
			if( !empty( $category['parent'] ) ){
				$parent_id = $this->_iwm_parent_id( $this->_categories[$category['parent_identifier']], $category['type'] );
				if( $parent_id < 1 ){
					$unsuccesful[] = $slug;
					continue;
				} else {
					$saved_categories[] = $category['parent_identifier'];
				}
				$category_id = $this->_iwm_save_item( $category, $parent_id, $category['type'] );
				if( $category_id > 0 ){
					$saved_categories[] = $slug;
				} else{
					$unsuccesful[] = $slug;
				}

			} else {
				$category_id = $this->_iwm_save_item( $category, 0, $category['type'] );
				if( $category_id > 0 )
					$saved_categories[] = $slug;
				else{
					$unsuccesful[] = $slug;
				}
			}

		}
		$response['error'] = false;
		$response['success_saved'] = $saved_categories;
		$response['failed_saved'] = $unsuccesful;

		wp_send_json( $response );
	}

	private function _iwm_save_item( $category, $parent = 0, $type ){
		switch( strtolower($type) ){
			case "category":
				return $this->_iwm_save_category( $category, $parent );
				break;
			case "page":
				return $this->_iwm_save_page( $category, $parent );
				break;
		}
	}

	private function _iwm_save_page( $item, $parent = 0 ){
		$addon_slug = 1;
		while( $this->_iwm_page_exists( $item['slug'], 'category' ) ){
			if( !empty( $item['parent'] ) ){
				$item['slug'] = $item['parent'].'_'.$item['slug'];
			} else {
				$item['slug'] .= '_'.$addon_slug;
			}

			$this->_categories[$item['orig_url']]['slug'] = $item['slug'];
			$addon_slug++;
		}
		$iwm_page = array(
			'post_title'    => wp_strip_all_tags( $item['name'] ),
			'post_name'		=>	$item['slug'],
			'post_content'  => ' ',
			'post_status'   => 'publish',
			'post_type' 	=> 'page',
			'post_parent'	=>	$parent
		);

		$page_id = wp_insert_post( $iwm_page );

		if( !$page_id ) return 0;

		update_post_meta($page_id, "orig_url", $item['orig_url']);

		return $page_id;
	}

	private function _iwm_save_category( $category, $parent = 0 ){
		$addon_slug = 1;
		while( $this->_iwm_slug_exists( $category['slug'], 'category' ) ){
			if( !empty( $category['parent'] ) ){
				$category['slug'] = $category['parent'].'_'.$category['slug'];
			} else {
				$category['slug'] .= '_'.$addon_slug;
			}

			$this->_categories[$category['orig_url']]['slug'] = $category['slug'];
			$addon_slug++;
		}
		$category_id = wp_insert_category(
			array(
				'cat_name' 			=> $category['name'],
				'category_nicename'	=>	$category['slug'],
				'category_parent'	=>	$parent
				)
		);

		if( !$category_id ) return false;

		$term_meta = array();
		$term_meta['orig_url'] = $category['orig_url'];
		update_option( "taxonomy_$category_id", $term_meta );

		return true;
	}

	private function _iwm_slug_exists( $slug, $taxonomy ){
		$parent_term = term_exists( $slug, $taxonomy );
		if( !empty( $parent_term['term_id'] ) ) return true;
		return false;
	}

	private function _iwm_page_exists( $slug, $taxonomy ){
		$page = get_posts(
			array(
				'name'      => $slug,
				'post_type' => 'page'
			)
		);

		if( empty( $page[0] ) ) return false;

		return true;
	}

	private function _iwm_parent_id( $item, $type ){
		switch( strtolower( $type ) ){
			case "category":
				return $this->_iwm_category_id( $item );
				break;
			case "page":
				return $this->_iwm_page_id( $item );
				break;
		}
	}

	private function _iwm_page_id( $item ){
		$page = get_posts(
			array(
				'name'      => $item['slug'],
				'post_type' => 'page'
			)
		);

		if( empty( $page[0] ) ) return 0;
		return $page[0]->ID;
	}

	private function _iwm_category_id( $category ){
		$parent_term = term_exists( $category['slug'], 'category' );
		if( !empty( $parent_term['term_id'] ) ) return $parent_term['term_id'];
		if( !empty( $category['parent'] ) ) return 0;

		return $this->_iwm_save_category( $category );

	}

	public function validate($input) {
		$valid = array();
		if( empty( $input['sitemap_categories'] ) ){
			add_settings_error(
				'sitemap_categories',                     // Setting title
				'sitemap_categories_texterror',            // Error ID
				'Please enter a valid URL',     // Error message
				'error'                         // Type of message
			);
		}
		$valid['sitemap_categories'] = esc_url($input['sitemap_categories']);
		return $valid;
	}


}