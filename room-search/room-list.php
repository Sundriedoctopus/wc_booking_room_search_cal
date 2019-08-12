<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}	
	
// List Of Rooms - Shortcode Callable Using Category ID ... Example [RoomList category_id="10"]
function RoomList($atts) { 
	
	$args = array(
		'post_type' 		=> 'product',
		'post_status' 		=> 'publish',
		'offset'        	=> 0,
		'posts_per_page' 	=> 500,
	);
			    
	$products = get_posts( $args );	

	foreach ( $products as $product ) {	
		$product_id = $product->ID;
		$product = new WC_Product( $product_id );  
		$categories = $product->get_category_ids();					
							
		foreach ($categories as $cat) {
			
			$cat_id = (int) $cat;
			
			if ($cat_id == $atts['category_id'] && in_array(36, $categories)) {
				
				$image = wp_get_attachment_image_src( get_post_thumbnail_id( $product->post->ID ), 'single-post-thumbnail' );
				
				?>	
			
				<div class="roomcont">
					<div class="roomimg" id="tentsimg" style="background-image: url(<?php _e($image[0]); ?>)"></div>
					<div class="roominfowrap">
						<div class="roominfo">
							<div class="roomtitle"><?php _e($product->get_name()); ?></div>
							<div class="roomdesc">
								<?php _e($product->get_description()); ?>
							</div>
						</div>
					</div>
				</div>
				
				<?php
			}
		}
	}
		
}
add_shortcode('RoomList', 'RoomList');