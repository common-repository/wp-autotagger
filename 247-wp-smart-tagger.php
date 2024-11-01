<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              www.247labs.com
 * @since             1.0.0
 * @package           247_Wp_Smart_Tagger
 *
 * @wordpress-plugin
 * Plugin Name:       Autotagger
 * Plugin URI:        http://auto-tagger.thedemo.co/register/
 * Description:       Increase the visibility and searchability of your products within your Woo Commerce store
 * Version:           1.0.0
 * Author:            247Labs
 * Author URI:        https://247labs.co
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       247-wp-smart-tagger
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}




// create custom plugin settings menu
add_action('admin_menu', 'Wp_Smart_Tagger_plugin_create_menu');
add_action( 'admin_init', 'Wp_Smart_Tagger_plugin_has_woocommerce_plugin' );


add_action( 'admin_enqueue_scripts', 'Wp_Smart_Tagger_queue_my_admin_scripts');
function Wp_Smart_Tagger_queue_my_admin_scripts() {
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-dialog');
}
function Wp_Smart_Tagger_plugin_has_woocommerce_plugin() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
        add_action( 'admin_notices', 'Wp_Smart_Tagger_child_plugin_notice' );

        deactivate_plugins( plugin_basename( __FILE__ ) ); 

        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
}



function Wp_Smart_Tagger_child_plugin_notice(){
     ?>
	<div class="error"><p>Sorry, but the  Plugin requires the Woo Commerce plugin to be installed and active.</p></div
	><?php
}



function Wp_Smart_Tagger_plugin_create_menu() {

	//create new top-level menu
//	add_menu_page('Wp Smart Tagger Plugin Settings', 'Smart Tagger', 'administrator', __FILE__, 'Wp_Smart_Tagger_settings_page' , plugins_url('/images/icon.png', __FILE__) );
	add_menu_page( 'Autotagger', 'Autotagger', 'manage_options', 'Wp_Smart_Tagger_settings_page','Wp_Smart_Tagger_settings_page');
    add_submenu_page( 'Wp_Smart_Tagger_settings_page', 'Settings', 'Settings', 'manage_options', 'Wp_Smart_Tagger_settings_page_2', 'Wp_Smart_Tagger_settings_callback' );

	//call register settings function
	add_action( 'admin_init', 'register_Wp_Smart_Tagger_plugin_settings' );
}

function Wp_Smart_Tagger_settings_callback(){
	?>
	<form method="post" action="options.php">
    <?php settings_fields( 'wp-smart_tagger-plugin-settings-group' ); ?>
    <?php do_settings_sections( 'wp-smart_tagger-plugin-settings-group' ); ?>

    <table class="form-table">
		<tr> <h3>Settings </h3>	
		 </tr>
        <tr valign="top">
        <th scope="row">API Key</th>
		<tr>To get the API key, please, sign up <a href="http://auto-tagger.thedemo.co/register/">Here</a></tr>
        <td><input type="text" name="wp-smart_tagger_option_api_key" value="<?php echo sanitize_text_field( get_option('wp-smart_tagger_option_api_key') ); ?>" /></td>
       
	    </tr>
         
      
    </table>
    
    <?php submit_button(); ?>

</form>
<?php
}

function register_Wp_Smart_Tagger_plugin_settings() {
	//register our settings
	register_setting( 'wp-smart_tagger-plugin-settings-group', 'wp-smart_tagger_option_api_key' );
}

function Wp_Smart_Tagger_settings_page() {

?>
<div class="wrap">
<h1>Autotagger</h1>

<?php

    $paged    = (get_query_var('paged')) ? absint(get_query_var('paged')) : 1;
    $args = array(
		'post_type' => 'product',
		'page'                 => $paged,
        'posts_per_page'=>  30 ,
		'paginate'             => true,
    );
	$wp_query = new WP_Query($args);
    ?>


    <div class="clear"></div>



    <ul class="products-list">
		<table class="wp-list-table widefat fixed striped">
		<tr>
		<button class="button button-primary button-large"  id="woo-allproducts">Tag last 10 products</button>
		&nbsp
		<button class="button button-primary button-large"  id="woo-products">Tag Selected products</button>
		

		</tr>
		<tr> <p></p> </tr>
		<tr>
		 <th colspan="1" ></th>
		 <th colspan="1" >ID</th>
		 <th colspan="2" >Image</th>
		 <th>Title</th>
		 <th>Tags</th>
		</tr>
        <?php while ($wp_query->have_posts()) : $wp_query->the_post(); ?>
		<tr>
		 <td colspan="1" >
		 <input type="checkbox" name="product_id" value="<?php echo the_id(); ?>" >
		 <input type="hidden" class="product_image auto-tagger-img-<?php echo the_id(); ?>" value="<?php echo the_post_thumbnail_url() ; ?>">
		 </td>
		 <td colspan="1" ><?php echo the_id(); ?></td>
		 <td class=""colspan="2" ><?php the_post_thumbnail( array(100,100), ['class' =>  'attachment-thumbnail size-thumbnail wp-post-image' ] );  ?></td>
		 <td><?php echo the_title(); ?></td>
		 <td><?php 
	     $terms = [];
		 $terms = get_the_terms( get_the_ID() , 'product_tag' ); 
		 if ( ! empty( $terms )  ){
		   foreach ( $terms as $term ) { echo $term->name . ", "; }
		 }
		 ?></td>
		</tr>

        <?php
        endwhile; // end of the loop. 
		wp_reset_query();
        ?>
		</table>
    </ul>
	
    <div class="clear"></div>
     
	  <!-- Pagination ! -->
	  <nav>
      <?php previous_posts_link('&laquo; Newer',$wp_query->max_num_pages); ?>
      <?php next_posts_link('Older &raquo;',$wp_query->max_num_pages); ?>
    </nav>

    

<script type='text/javascript'>
	jQuery( document ).ready( function( $ ) {

	   jQuery( '#woo-allproducts' ).on( 'click', function() {
		console.log("all tags")
		var count = 0;
		var checkboxValues = [];
		var ids = []
		  jQuery('input[type="checkbox"]').each(function(index, elem) {
            checkboxValues.push($(elem).val());
		   });
		
		   for (let i = 0; i < 9; i++) {

			   		  const element = checkboxValues[i];
			  console.log(element);
			  // get tags for each image
			  jQuery.post( "http://auto-tagger.thedemo.co/api/predict/", { 
				api_key : '<?php echo esc_attr( get_option('wp-smart_tagger_option_api_key') ); ?>',
				url:  jQuery('.auto-tagger-img-'+element).val(),
				security: '<?php echo wp_create_nonce( "bk-ajax-nonce" ); ?>' ,
				provider : "clarafai"
				} 
				) .done(function( data ) {
					var tags = [];
					data.forEach(e => {
							tags.push(e)
				    });	
                
				console.log( "tags results" ,tags );
                // Update Product Tags
				jQuery.ajax({ // We use jQuery instead $ sign, because Wordpress convention.
                 url : ajaxurl, // This addres will redirect the query to the functions.php file, where we coded the function that we need.
                 type : 'POST',
                       data : {
                       action : 'Wp_Smart_Tagger_insert_action', 
                       tags : tags,
                       postid : element
                  },
                  beforeSend: function() {
                  },
                 success : function( response ) {
                 },
                 complete: function(){
					 count ++;
					 console.log("count ",count)
					 if(i == 8 ){
						 console.log("all tagged")
						 window.location.reload(false); 
					 }
                }
               });
				
				
				
			})

		   }
		console.log(checkboxValues)
	   });
	   
	 
	   jQuery( '#woo-products' ).on( 'click', function() {
		var checkboxValues = [];
		
		jQuery('input[type="checkbox"]:checked').each(function(index, elem) {
            checkboxValues.push($(elem).val());
        });
		
          
		  for (let i = 0; i < checkboxValues.length; i++) {
			  
			  const element = checkboxValues[i];
			  var tags = [];
			//  console.log(element);
			//  console.log(images_urls[i])
			  // get tags for each image
			  jQuery.post( "http://auto-tagger.thedemo.co/api/predict/", { 
				api_key : '<?php echo esc_attr( get_option('wp-smart_tagger_option_api_key') ); ?>',
				url: jQuery('.auto-tagger-img-'+element).val(),
				security: '<?php echo wp_create_nonce( "bk-ajax-nonce" ); ?>' ,
				provider : "clarafai"
				} 
				) .done(function( data ) {
					
					data.forEach(e => {
							tags.push(e)
				    });	
                
				console.log( "tags results" ,tags );
				console.log( "tags post id" ,element );
                // Update Product Tags
				jQuery.ajax({ // We use jQuery instead $ sign, because Wordpress convention.
                 url : ajaxurl, // This addres will redirect the query to the functions.php file, where we coded the function that we need.
                 type : 'POST',
                       data : {
                       action : 'Wp_Smart_Tagger_insert_action', 
                       tags : tags,
                       postid : element
                  },
                  beforeSend: function() {
                  },
                 success : function( response ) {
                 },
                 complete: function(){
					 console.log("product ",i)
					 if(i == (checkboxValues.length - 1) ){
						 console.log("all tagged")
						 window.location.reload(false); 
					 }
                }
               });
				
				
				
			})

			
    	}; 
	

    	
	});
});

</script>

<?php
 }

 
 add_action( 'wp_ajax_Wp_Smart_Tagger_insert_action', 'Wp_Smart_Tagger_insert_action' );
 function Wp_Smart_Tagger_insert_action() {
	 global $wpdb;
	 wp_verify_nonce( $_POST['security'], 'bk-ajax-nonce' );
	 $postid = intval( esc_html($_POST['postid'] ));
	 $tags =  Wp_Smart_Tagger_recursive_sanitize_text_field($_POST['tags']) ;
	 wp_set_object_terms($postid , $tags, 'product_tag');
	 wp_die();
 }

 /**
 * Recursive sanitation for an array
 * 
 * @param $array
 *
 * @return mixed
 */
function Wp_Smart_Tagger_recursive_sanitize_text_field($array) {
    foreach ( $array as $key => &$value ) {
        if ( is_array( $value ) ) {
            $value = Wp_Smart_Tagger_recursive_sanitize_text_field($value);
        }
        else {
            $value = sanitize_text_field( $value );
        }
    }

    return $array;
}

?>


