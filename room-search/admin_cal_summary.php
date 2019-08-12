<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}	
?>

<?php
function AdminDates($atts) {	

	if (current_user_can('administrator')) {
		
		$startDate = new DateTime();
		$endDate = new DateTime('+6 months');
			
		$bookings = WC_Bookings_Controller::get_bookings_in_date_range( $startDate->getTimestamp(), $endDate->getTimestamp(), '', false );
		
		$args = array(
			'post_type' 		=> 'product',
			'post_status' 		=> 'publish',
			'offset'        	=> 0,
			'posts_per_page' 	=> 500,
		);
				    
		$products = get_posts( $args );	
		$productNames = array();

		$sundays = array();

		while ($startDate <= $endDate) {
		    if ($startDate->format('w') == 6) {
		        $saturdays[] = $startDate->format('Y-m-d');
		    }
		
		    $startDate->modify('+1 day');
		}
		?>
		<div class="adminsummary">
			
			<table>
				
				<thead>
					<td class="cornerblock"></td>
					<?php	
					foreach ($saturdays as $saturday){	
						?>
						<td class="dateweek">
							<strong><?php echo date('d/m/Y', strtotime($saturday)); ?> </strong>
						</td>
						<?php
					}
					?>
				</thead>
				
				<tbody>
		
					<?php	
				
					foreach ( $products as $product ) {	
						$product_id = $product->ID;
						$product = new WC_Product( $product_id );  
						$categories = $product->get_category_ids();				
																
						if (in_array(36, $categories)) {
							
							$atts_retreat = $atts['retreat'];
							$filter_cat = 36;
							if ($atts_retreat == 'oasis') {
								$filter_cat = 19;
							} else if ($atts_retreat == 'mountain') {
								$filter_cat = 24;
							} else {
								$filter_cat = 36;
							}
							
							echo $filter_cat . '<br/>';
							
							if (in_array($filter_cat, $categories)) {
							
								array_push($productNames, $product->get_name());
								?>
									<tr class="roomrow">
										<td class="roomtype">
											<strong><?php echo $product->get_name(); ?></strong>
										</td>
										
										<?php
										foreach ($saturdays as $saturday){
													
											$availability = array();	
												
											foreach ($bookings as $booking) {
				
												if ($booking->product_id == $product_id) {
														
													if ($booking->start == strtotime($saturday)) {	
														if ($booking->has_resources()) {
																$resources = $booking->product->get_resources();
																
															foreach ($resources as $resource) {
																$resource_id = $resource->ID;
																																														
																$available_bookings = $booking->product->get_available_bookings($booking->start, $booking->end, $resource_id, 1);
																
																array_push($availability, $available_bookings);
															}
														}
													}
												}
											}
											
											if (!empty($availability)){
												if (!is_numeric($availability)) {	
													$availability = 0;
												}
												$min_available = min($availability);
												
												if ($min_available == 0) { 
												?>
													<td class="redkey">Sold Out</td>
												<?php
												} else if ($min_available > 0) {
												?>
													<td class="orangekey">Limited<br/><?php echo $min_available;?> Left</td>
												<?php
												}
											} else {
												?>
												<td class="greenkey">Available</td>
												<?php
											}
											
											?>
										</td>
									<?php
									}
									?>
										
								</tr>
							<?php
							}
						}
					}
					?>
				</tbody>
			</table>
		</div>
		
		<div class="adminsummary">
			<form>
				<input type="submit" value="Clear All" class="clearbtn"/>
				<select id="filterdate">
					<option selected>No Date</option>
					<?php	
						foreach ($saturdays as $saturday){	
							?>
							<option><?php echo date('d/m/Y', strtotime($saturday)); ?></option>
							<?php
						}
					?>
				</select>
				<select id="filterroom">
					<option selected>No Room</option>
					<?php	
						foreach ($productNames as $productName){	
							?>
							<option><?php _e($productName); ?></option>
							<?php
						}
					?>
				</select>
				<input type="submit" value="Filter" class="filterbtn"/>
			</form>
		</div>
		
		
		
		<?php	
	}
}

add_shortcode('AdminDates', 'AdminDates');
?>






<?php
/*
 * ADDS MINI CART
 */
function AdminMiniCart() {	
	if (!is_cart()){
		if (current_user_can('administrator')) { 
			?>
			<div class="wcmini"><?php woocommerce_mini_cart(); ?></div>
			<?php
		}
	}
}
add_action('wp_head', 'AdminMiniCart');


/*
 * ADDS MINI CART TO HEADER
 */
add_filter( 'woocommerce_add_to_cart_fragments', 'header_add_to_cart_fragment', 30, 1 );
function header_add_to_cart_fragment( $fragments ) {
    global $woocommerce;

    ob_start();
	if (!is_cart()){
	    ?>
	    <a class="cart-customlocation" href="<?php echo esc_url(wc_get_cart_url()); ?>" title="<?php _e('View your shopping cart', 'woothemes'); ?>">
		    <?php echo sprintf(_n('%d item', '%d items', $woocommerce->cart->cart_contents_count, 'woothemes'), $woocommerce->cart->cart_contents_count);?> - <?php echo $woocommerce->cart->get_cart_total(); ?>
		   </a>
	    <?php
	    $fragments['a.cart-customlocation'] = ob_get_clean();
	}
    return $fragments;
}


/*
 * ADDS MINI CART REMOVE AJAX
 */
function remove_item_from_cart() {
	if (!is_cart()){
	    $cart_item_key = $_POST['cart_item_key'];
	    
	    if($cart_item_key){
	       WC()->cart->remove_cart_item($cart_item_key);
	       return true;
	    } 
	    return false;
	}
}
add_action('wp_ajax_remove_item_from_cart', 'remove_item_from_cart');
add_action('wp_ajax_nopriv_remove_item_from_cart', 'remove_item_from_cart');
?>
