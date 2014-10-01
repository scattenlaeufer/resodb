<?php
/**
 * Plugin Name: ResoDB
 * Description: Eine Datenbank um Resolutionen und Stellungnahmen zu veröffentlichen
 * Version: 0.1
 * Author: Björn Guth
 * License: CC BY-NC-SA DE 4.0
 */

if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

#define( 'RESODB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

#register_activation_hook( __FILE__, array( 'ResoDB', 'plugin_activation' ) );
#register_deactivation_hook( __FILE__, array( 'ResoDB', 'plugin_deactivation' ) );

#require_once( 'RESODB_PLUGIN_DIR' . 'test.php' );

add_action( 'plugins_loaded', array( 'PageTemplater', 'get_instance' ) );
add_action( 'admin_menu', 'reso_db_menu' );

/*
 * First baby steps in adding a administration menu
 */

function reso_db_menu() {
	add_menu_page( 'ResoDB Admin-Interface', 'ResoDB', 'manage_options', 'reso-db-mm', 'reso_db_main_menu' );
	add_submenu_page( 'reso-db-mm', 'ResoDB alle Resos', 'alle Resos', 'manage_options', 'reso-db-mm', 'reso_db_main_menu' );
	add_submenu_page( 'reso-db-mm', 'ResoDB Untermenü', 'Untermenü', 'manage_options', 'reso-db-sm', 'reso_db_submenu' );
}

function reso_db_main_menu() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	global $wpdb;
	$results = $wpdb->get_results("SELECT id,title,author,date,link FROM wp_reso_db ORDER BY id ASC");
?>
	<div class="wrap">
	<h1>ResoDB</h1>
	<p>Dies ist das Administrationsintraface der Resolutionsdatenbank.</p>
	<p>Im folgenden kannst du alle Resolutionen sehen, die sich im System befinden.</p>
	<table border="0">
		<tr>
			<th>ID</th>
			<th>Title</th>
			<th>Verfasser</th>
			<th>Datum</th>
			<th>Link</th>
		</tr>
<?php
	foreach($results as $r) {
		echo '<tr>';
		echo '<td>'.$r->id.'</td>';
		echo '<td>'.$r->title.'</td>';
		echo '<td>'.$r->author.'</td>';
		echo '<td>'.$r->date.'</td>';
		echo '<td><a href="'.$r->link.'" target="_blank">'.$r->link.'</a></td>';
		echo '<td><input type="button" name="rm_'.$r->id.'" value="Löschen" style="width:80px;" onclick="PHPFunktion()"/></td>';
		echo '</tr>';
	}
?>
	</table>
	</div>
<?php
}

function reso_db_submenu() {
	if ( !current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	echo '<dif class="wrap">';
	echo '<p>ein untermenü</p>';
	echo '</div>';
}


/*
 * A class to add templates 
 */

class PageTemplater {

	/**
	 * A Unique Identifier
	 */
	protected $plugin_slug;

	/**
	 * A reference to an instance of this class.
	 */
	private static $instance;

	/**
	 * The array of templates that this plugin tracks.
	 */
	protected $templates;


	/**
	 * Returns an instance of this class. 
	 */
	public static function get_instance() {
		if( null == self::$instance ) {
			self::$instance = new PageTemplater();
		} 
		return self::$instance;
	}


	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	private function __construct() {
		$this->templates = array();
		// Add a filter to the attributes metabox to inject template into the cache.
		add_filter(
					'page_attributes_dropdown_pages_args',
					array( $this, 'register_project_templates' ) 
				);
		// Add a filter to the save post to inject out template into the page cache
		add_filter(
					'wp_insert_post_data', 
					array( $this, 'register_project_templates' ) 
				);
		// Add a filter to the template include to determine if the page has our 
		// template assigned and return it's path
		add_filter(
					'template_include', 
					array( $this, 'view_project_template') 
				);
		// Add your templates to this array.
		$this->templates = array(
			'reso_db_template.php'     => 'Resolutionsdatenbank',
		);
	} 


	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doens't really exist.
	 *
	 */
	public function register_project_templates( $atts ) {
		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );
		// Retrieve the cache list. 
				// If it doesn't exist, or it's empty prepare an array
				$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		} 
		// New cache, therefore remove the old one
		wp_cache_delete( $cache_key , 'themes');
		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $this->templates );
		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );
		return $atts;
	} 

	/**
	 * Checks if the template is assigned to the page
	 */
	public function view_project_template( $template ) {
		global $post;
		if (!isset($this->templates[get_post_meta( 
					$post->ID, '_wp_page_template', true 
				)] ) ) {
					
			return $template;
						
		} 
		$file = plugin_dir_path(__FILE__). get_post_meta( 
					$post->ID, '_wp_page_template', true 
				);
				
		// Just to be safe, we check if the file exist first
		if( file_exists( $file ) ) {
			return $file;
		} 
		else { echo $file; }
		return $template;
	} 


} 


?>
