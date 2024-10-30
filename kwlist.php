<?php
/**
* Plugin Name: KWL WooCommerce Wishlist
* Description: Wishlist plugin for WooCommerce.
* Version: 1.0
* Author: Aleksandar Krstic
* Author URI: http://www.krstic.in.rs/
**/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Admin CSS

function kwl_admin_style() {
        wp_register_style( 'kwl_admin_css', plugin_dir_url( __FILE__ ) . 'css/admin-style.css', false, '1.0.0' );
        wp_enqueue_style( 'kwl_admin_css' );
}
add_action( 'admin_enqueue_scripts', 'kwl_admin_style' );

// Front-end CSS and JS

add_action( 'wp_enqueue_scripts', 'kwl_enqueue_styles' );
function kwl_enqueue_styles() {

	wp_enqueue_script( 'jquery-ui-dialog' );
	
	wp_register_script( 'custom-kwl', plugin_dir_url( __FILE__ ) . 'js/custom.js', array('jquery')  );
	wp_enqueue_script( 'custom-kwl' );
	wp_localize_script( 'custom-kwl', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );

	wp_register_style( 'custom', plugin_dir_url( __FILE__ ) . 'css/style.css'  );
	wp_enqueue_style( 'custom' );

	wp_register_style( 'jquery-ui', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.css'  );
	wp_enqueue_style( 'jquery-ui' );
	
}

// Add admin menu item
add_action('admin_menu', 'kwl_menu_item');
 
function kwl_menu_item()
{
    add_menu_page('Simple WooCommerce Wishlist', 'Woo Wishlist', 'administrator', 'kwl_page', 'kwli_settings', 'dashicons-list-view');

    //call register settings function
    add_action( 'admin_init', 'register_kwli_settings' );

}


function register_kwli_settings() {
	//register plugin settings
	register_setting( 'kwli-settings-group', 'kwli_add_in_list' );
	register_setting( 'kwli-settings-group', 'kwli_add_in_list_class' );
	register_setting( 'kwli-settings-group', 'kwli_add_in_single' );
	register_setting( 'kwli-settings-group', 'kwli_add_in_single_class' );
}


function kwli_settings() {
?>
<div class="wrap">
<h1>Simple WooCommerce Wishlist</h1>

<form method="post" action="options.php">
    <?php 
    settings_fields( 'kwli-settings-group' ); 
 	do_settings_sections( 'kwli-settings-group' ); 
 	if ( get_option('kwli_add_in_list') ) {
 		$kwli_add_in_list_val =  sanitize_option( 'kwli_add_in_list', get_option('kwli_add_in_list') );
 	}else{
 		add_option( 'kwli_add_in_list', 'Yes', '', 'yes' );
 	}

 	if ( get_option('kwli_add_in_single') ) {
 		$kwli_add_in_single = sanitize_option( 'kwli_add_in_single', get_option('kwli_add_in_single') );
 	}else{
 		add_option( 'kwli_add_in_single', 'Yes', '', 'yes' );
 	}

    ?>
    <div class="kwl-table">
        <h2>"Add To Wishlist" Button in Product List/Grid</h2>
        <h3>Enable</h3>
    	<div>
    		Yes <input type="radio" class="kwl-radio" name="kwli_add_in_list" value="Yes" <?php checked('Yes', $kwli_add_in_list_val); ?> /> 
        	No <input type="radio" class="kwl-radio" name="kwli_add_in_list" value="No" <?php checked('No', $kwli_add_in_list_val); ?> />
        </div>  
        <h3>Style</h3>
        <div>
        	Custom Class Name <input type="text" class="kwl-text" name="kwli_add_in_list_class" value="<?php echo sanitize_option( 'kwli_add_in_list_class', get_option('kwli_add_in_list_class') ); ?>" />
        </div>  
        <h2>"Add To Wishlist" Button in Single Product Page</h2>
        <h3>Enable</h3>
    	<div>
    		Yes <input type="radio" class="kwl-radio" name="kwli_add_in_single" value="Yes" <?php checked('Yes', $kwli_add_in_single); ?> /> 
        	No <input type="radio" class="kwl-radio" name="kwli_add_in_single" value="No" <?php checked('No', $kwli_add_in_single); ?> />
        </div> 
        <h3>Style</h3>
        <div>
        	Custom Class Name <input type="text" class="kwl-text" name="kwli_add_in_single_class" value="<?php echo sanitize_option( 'kwli_add_in_single_class', get_option('kwli_add_in_single_class') ); ?>" />
        </div>  
    </div>
    
    <?php submit_button(); ?>

</form>
</div>
<?php }



// Add Link to My Account menu

add_filter ( 'woocommerce_account_menu_items', 'kwl_link', 40 );
function kwl_link( $menu_links ){
 
	$menu_links = array_slice( $menu_links, 0, 5, true ) 
	+ array( 'kwl-list' => 'Wishlist' )
	+ array_slice( $menu_links, 5, NULL, true );
 
	return $menu_links;
 
}


//Register Permalink Endpoint

add_action( 'init', 'kwl_add_endpoint' );
function kwl_add_endpoint() {

	add_rewrite_endpoint( 'kwl-list', EP_PAGES );
 
}

// Content for the Wishlist page in My Account +  creating shortcode

add_action( 'woocommerce_account_kwl-list_endpoint', 'kwl_my_account_endpoint_content' );
function kwl_my_account_endpoint_content() {

	echo '<div id="kwl-list-holder">';
	echo '<h3>My product list</h3>';
	echo '<ul>';
	if ( is_user_logged_in() ) {
		$cur_usr = get_current_user_id();
 		$products = get_user_meta($cur_usr, '_kwl_items', false);
 		foreach ($products as $product_id) {
			$product = wc_get_product( $product_id );
			echo '<li><a href="'.get_permalink( $product_id ).'">' . $product->get_name() . '</a> - <a href="#" data-product_id="'.$product_id.'" class="kwl-remove">Remove</a></li>';
		}
	}else{
		if ( isset( $_COOKIE['kwl_items'] ) ) {
			$products = explode( ", ", wp_kses_post( $_COOKIE['kwl_items'] ) );
		}else{
			$products = [];
		}
 		foreach ($products as $product_id) {
			$product = wc_get_product( $product_id );
			echo '<li><a href="'.get_permalink( $product_id ).'">' . $product->get_name() . '</a></li>';
		}
	}
	echo '</ul>';
	echo '</div>';
 	 	
}
add_shortcode( 'kwlist', 'kwl_my_account_endpoint_content' );


// Add buttons to Product List and Single Product page (if they are enabled in Options page)

if ( get_option('kwli_add_in_list') == 'Yes' ) {
	add_action( 'woocommerce_after_shop_loop_item_title', 'kwl_add_to_wishlist_button' );
}

if ( get_option('kwli_add_in_single') == 'Yes' ) {
	add_action( 'woocommerce_after_single_product_summary', 'kwl_add_to_wishlist_button_single' );
}


function kwl_add_to_wishlist_button(){
	global $product;
	$pid = $product->get_id();

	$custom_class = sanitize_html_class( get_option('kwli_add_in_list_class') );

	if ( is_user_logged_in() ) {
		$cur_usr = get_current_user_id();

		//check if product is already added to user's list
		$existing = get_user_meta($cur_usr, '_kwl_items', false);
		if ( !in_array($pid, $existing) ) {
		 	echo '<a href="#" data-product_id="'.$pid.'" class="kwl-add '.$custom_class.'">Add to List</a>';
		}else{
			echo '<a href="#" class="kwl-add '.$custom_class.'">Added</a>';
		}

	// NON logged user
	}else{
		if ( isset( $_COOKIE['kwl_items'] ) ) {
			$products = explode( ", ", wp_kses_post( $_COOKIE['kwl_items'] ) );
		}else{
			$products = [];
		}

		if ( !in_array($pid, $products) ){
			echo '<a href="#" data-product_id="'.$pid.'" class="kwl-add '.$custom_class.'">Add to List</a>';
		}else{
			echo '<a href="#" class="kwl-add '.$custom_class.'">Added</a>';
		}
		 
	}

	
}

function kwl_add_to_wishlist_button_single(){
	global $product;
	$pid = $product->get_id();

	$custom_class = sanitize_html_class( get_option('kwli_add_in_single_class') );

	if ( is_user_logged_in() ) {
		$cur_usr = get_current_user_id();

		//check if product is already added to user's list
		$existing = get_user_meta($cur_usr, '_kwl_items', false);
		if ( !in_array($pid, $existing) ) {
		 	echo '<a href="#" data-product_id="'.$pid.'" class="kwl-add '.$custom_class.'">Add to List</a>';
		}else{
			echo '<a href="#" class="kwl-add '.$custom_class.'">Added</a>';
		}

	// NON logged user
	}else{
		if (isset( $_COOKIE['kwl_items'] ) ) {
			$products = explode( ", ", wp_kses_post( $_COOKIE['kwl_items'] ) );
		}else{
			$products = [];
		}

		if ( !in_array($pid, $products) ){
			echo '<a href="#" data-product_id="'.$pid.'" class="kwl-add '.$custom_class.'">Add to List</a>';
		}else{
			echo '<a href="#" class="kwl-add '.$custom_class.'">Added</a>';
		}
	}
}


// Add product to list for both logged and NON-logged users

add_action('wp_ajax_kwl_add', 'kwl_add');
add_action('wp_ajax_nopriv_kwl_add', 'kwl_add');

function kwl_add() {

	$pid = intval( sanitize_text_field( $_POST['product_id'] ) );

	// Logged user
	if ( is_user_logged_in() ) {
		$cur_usr = get_current_user_id();

		//check if product is already added to user's list
		$existing = get_user_meta($cur_usr, '_kwl_items', false);
		if ( !in_array($pid, $existing) ) {
		 	add_user_meta($cur_usr, '_kwl_items', $pid);
		}

	// NON logged user
	}else{
		if (isset( $_COOKIE['kwl_items'] ) ) {
			$products = explode( ", ", wp_kses_post( $_COOKIE['kwl_items'] ) );
		}else{
			$products = [];
		}

		if ( !in_array($pid, $products) ){
			array_push($products, $pid);
		}
		 
		 wc_setcookie( 'kwl_items', implode(", ", $products) );	
	}
     exit(); 
}



// Remove product from list

add_action('wp_ajax_kwl_remove', 'kwl_remove');
function kwl_remove() {

	$pid = intval( sanitize_text_field( $_POST['product_id'] ) );

	$cur_usr = get_current_user_id();
	delete_user_meta($cur_usr, '_kwl_items', $pid);    

     exit(); 
}