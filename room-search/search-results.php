<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}	
	
// Retreat specific resource for total number of weekly availabel beds
define('RETREAT_RESOURCES', array(1599));
// Rooms with 3 spaces available
define('LIMITED_ROOMS', array(1622));	
	
	
// Returns Rooms In Calendar Format To Show Availability
function SearchAjax() {		
	$endDate = new DateTime('+6 months');
	$search_query = array(
		'retreat'  	=> $_POST['retreat'],
		'qty'		=> $_POST['guests'],
		'weeks'		=> $_POST['weeks'],
		'arr_date' 	=> new DateTime(),
		'dep_date' 	=> $endDate,
	);
	
	$dateTimeStart = $search_query['arr_date'];
	$dateTimeEnd   = $search_query['dep_date'];
	$duration	   = $search_query['weeks'];		

	$bookings = WC_Bookings_Controller::get_bookings_in_date_range( $dateTimeStart->getTimestamp(), $dateTimeEnd->getTimestamp(), '', false );
	
	$date_array   = array();
	$noDate_array = array();
	$temp		  = array();
	$temp_master  = array();
	$exclude 	  = array();
	$available_resources = array();

	foreach ($bookings as $booking) {
		if ($booking->has_resources()) {
					
			$resources = $booking->product->get_resources();
			
			foreach ($resources as $resource) {
				$resource_id = $resource->ID;	
				$available_bookings = $booking->product->get_available_bookings($booking->start, $booking->end, $resource_id, $search_query['qty']);

				array_push( $temp, date('d-m-Y', $booking->start), date('d-m-Y', $booking->end) );
				array_push( $temp, $resource_id, $available_bookings );
				array_push( $temp_master, $temp );
				$temp = array();
				
	            if ($available_bookings < $search_query['qty']) {
		            array_push($exclude, $booking->product_id);
	            }				
			}
		}	
	}
	
	foreach ($temp_master as $tm) {
		$tmed = date_create($tm[1]);
		date_sub($tmed, date_interval_create_from_date_string("6 days"));
		array_push( $date_array, $tm[0], date_format($tmed, "d-m-Y") );
		if (in_array($tm[2], RETREAT_RESOURCES) && $tm[3] < $search_query['qty'] && $tm[3] != '') {
			array_push( $noDate_array, $tm[0], date_format($tmed, "d-m-Y") );
		}
	}

	$noDate_array = array_unique($noDate_array, SORT_REGULAR);
	$date_array   = array_unique($date_array, SORT_REGULAR);
	$exclude 	  = array_unique($exclude, SORT_REGULAR);
				
	echo '<div class="calwrap"><button class="calarrow disabled" id="prev"></button>';
	echo '<div class="errorblock"></div><div class="resultscalendar" data-weeks="'.$duration.'" data-dates="';
	$i = 0;
	foreach($date_array as $result) {
		echo  $i < count($date_array) -1 ? $result . ',' : $result;
    	$i++;
    }
	echo '" data-nodates="';
	$n = 0;
	foreach($noDate_array as $noResult) {
		echo  $n < count($noDate_array) -1 ? $noResult . ',' : $noResult;
    	$n++;
    }
	echo '" data-arrd="" data-chkd="" data-depd=""></div>';
	echo '<button class="calarrow" id="next"></button></div>';
	// echo '<div class="steptitle greyed formtitle" id="step3"><div class="stepnumber" >3</div><h2 class="pratabase" style="text-align: center;">Select Your Room</h2></div>';
	echo '<form class="roomsearchrun" method="POST" data-url="'.get_admin_url().'admin-ajax.php">';
	echo '<input type="hidden" id="room_search_rooms" data-excid="';
	$e = 0;
	foreach($exclude as $excluded) {
		echo  $e < count($excluded) -1 ? $excluded . ',' : $excluded;
    	$e++;
    }	
	echo '"/>';
	echo '<input type="button" class="inactive" id="room_search_btn" name="room_search_btn" value="Next"/>';
	echo '</form>';
	
	die();
}
add_action( 'wp_ajax_SearchAjax', 'SearchAjax' );
add_action( 'wp_ajax_nopriv_SearchAjax', 'SearchAjax' );






// Returns Rooms In A List Categoriesed by Retreat
function RoomSearchAjax() {	
	$search_query = array(
		'retreat'  	=> $_POST['retreat'],
		'arr_date' 	=> $_POST['arr_date'],
		'chk_date' 	=> $_POST['chk_date'],
		'dep_date' 	=> $_POST['dep_date'],
		'bed_type'  => $_POST['bed_type'],
		'qty'		=> $_POST['guests'],
		'weeks'		=> $_POST['weeks'],
	);	
	$args = array(
		'post_type' 		=> 'product',
		'post_status' 		=> 'publish',
		'offset'        	=> 0,
		'posts_per_page' 	=> 500,
	);
			    
	$products = get_posts( $args );	
	$bookings = WC_Bookings_Controller::get_bookings_in_date_range( strtotime($search_query['chk_date']), strtotime($search_query['dep_date']), '', false );
	$available_rooms = true;	
	$exclude = array();
	$primaryRooms = array();
	$categoryList = array();
	$secondaryRooms = array();
	
	?>
	<div class="productscontainer">
	<?php
		if ($bookings) {
			foreach ($bookings as $booking) {
				$persons = $booking->product->get_min_persons();
				if ($booking->has_resources()) {
							
					$resources = $booking->product->get_resources();
					
					foreach ($resources as $resource) {
						$resource_id = $resource->ID;	
						$available_bookings = $booking->product->get_available_bookings($booking->start, $booking->end, $resource_id, $search_query['qty']);

			            if ($available_bookings < $search_query['qty'] || $available_bookings < $persons) {
				            array_push($exclude, $booking->product_id);
			            }				
					}
					
				}	
			}		
		} 
		
		$resultsCount = 0;	
					       
		foreach ( $products as $product ) {		
			$product_id = $product->ID;
			$product = new WC_Product( $product_id );  
			$categories = $product->get_category_ids();		
			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $product->post->ID ), 'single-post-thumbnail' );
			$min_persons = get_post_meta( $product_id, '_wc_booking_min_persons_group', false );
			$max_persons = get_post_meta( $product_id, '_wc_booking_max_persons_group', false );
		
			foreach ($categories as $cat) {
				$cat_id = (int) $cat;
				$cat_name = get_term_by( 'id', $cat_id, 'product_cat' );
				
				$roomInfo = array(
					'ID' 			=> $product->ID,
					'post_id' 		=> $product->post->ID,
					'image' 		=> $image,
					'gallery'		=> $product->get_gallery_attachment_ids(),
					'name'			=> $product->get_name(),
					'shortdesc' 	=> $product->get_short_description(),
					'desc'			=> $product->get_description(),
					'price' 		=> $product->get_price_html( $price = '' ),
					'min_persons' 	=> $min_persons[0],
					'max_persons' 	=> $max_persons[0],
					'cat' 		  	=> $categories,
					'amenities'	  	=> get_post_meta( $product_id, '_custom_room', false ),
				);
				
				if( $cat_name && !in_array($product_id, $exclude)) {
				    if (strtolower( $cat_name->name ) == strtolower( $search_query['retreat'] ) ) { 
				    	if ( $search_query['qty'] == 1 ){
					    	$roomInfo;
					    	array_push($primaryRooms, $roomInfo);
							$resultsCount++;
						} else {
							if (has_term( 'Entire Room', 'product_cat', $product->post->ID ) && $search_query['qty'] == 2 ) {
						    	$roomInfo;
						    	array_push($primaryRooms, $roomInfo);
								$resultsCount++;
							} else if (has_term( 'Entire Room', 'product_cat', $product->post->ID ) && $search_query['qty'] == 3 ) {
								if (!in_array($product->post->ID, LIMITED_ROOMS) ) {
									$roomInfo;
							    	array_push($primaryRooms, $roomInfo);
									$resultsCount++;
								}
							}
						}						   		
					} 		
					array_push($categoryList, strtolower($cat_name->name) );
				}
			}
			
			if ( in_array('room', $categoryList) && !in_array(strtolower( $search_query['retreat'] ), $categoryList)) {
				if ( $search_query['qty'] == 1 ){
					$roomInfo;
					array_push($secondaryRooms, $roomInfo);							
				} else {
					if (has_term( 'Entire Room', 'product_cat', $product->post->ID ) && $search_query['qty'] == 2 ) {	
						$roomInfo;
						array_push($secondaryRooms, $roomInfo);
					} else if (has_term( 'Entire Room', 'product_cat', $product->post->ID ) && $search_query['qty'] == 3 ) {
						if (!in_array($product->post->ID, LIMITED_ROOMS) ) {
							$roomInfo;
							array_push($secondaryRooms, $roomInfo);										
						}
					}
				}
			}
			$categoryList = array();			
		}
		
		if ($resultsCount == 0) {
			$available_rooms = false;
		} ?>



		<?php 
		if (!empty($primaryRooms)) { 	
		?>
			<div class="roomscont primaryrooms">
				<div class="roomtitlecont">
					<button>
						<h3>Your Room Options</h3>
						<span>
							<i class="icon-arrow-down iconhidden"></i>
						</span>
					</button>
				</div>

				<?php if ($available_rooms) { ?>
					<div class="roomwrap">
						<?php
						foreach ($primaryRooms as $room) {
							result($room, $search_query); 
						} 
						?>
					</div>
				<?php } else { ?>
					<div class="roomwrap">
						<div>Sorry, no rooms are available for your selected dates</div>
					</div>
				<?php } ?>				
			</div>
		
		<?php } 
			
		if ($secondaryRooms != '') { 
			if (!empty($primaryRooms)) {	
			?>
				<div class="roomscont secondaryrooms roomshidden">
					<div class="roomtitlecont">
						<button>
							<h3>Alternative Retreat Options</h3>
							<span>
								<i class="icon-arrow-down"></i>
							</span>
						</button>
					</div>	
			<?php } else { ?>	
					<div class="roomscont primaryrooms">
						<div class="roomtitlecont">
							<button>
								<h3>Your Room Options</h3>
								</button>
						</div>		
			<?php } 
				sort($secondaryRooms);
			?>		
					<div class="roomwrap">
						<?php
						$secondaryRooms = array_unique($secondaryRooms, SORT_REGULAR);
						foreach ($secondaryRooms as $room) {
							result($room, $search_query);
						} 
						?>
					</div>
				</div>
		<?php 
		} 
		?>
							
					
		<?php if (empty($primaryRooms) && empty($secondaryRooms)) { ?>
			<div class="roomwrap">
				<div>Sorry, no rooms are available for your selected dates</div>
			</div>
		<?php } ?>

	</div>
	
	<?php	
	die();

}
add_action( 'wp_ajax_RoomSearchAjax', 'RoomSearchAjax' );
add_action( 'wp_ajax_nopriv_RoomSearchAjax', 'RoomSearchAjax' );
?>





<?php
// Result List Item
function result($room, $search_query) {
	$room_cats = array();
	$room_retreat = array();
	$bed_config = array();
	
	$room_category_ids = $room['cat'];
	foreach ($room_category_ids as $room_cat_id) {
		$cat_id = (int) $room_cat_id;
		$cat_name = get_term_by( 'id', $cat_id, 'product_cat' );
		if ($cat_name->slug == 'entire-room' || $cat_name->slug == 'shared-room') {
			array_push($room_cats, $cat_name->slug);
		}
		if ($cat_name->slug == 'juicy-oasis') {
			array_push($room_retreat, 'Juicy Oasis');
		} else if ($cat_name->slug == 'juicy-mountain') {
			array_push($room_retreat, 'Juicy Mountain');
		} 
		if ($cat_name->slug == 'single' || $cat_name->slug == 'twin' || $cat_name->slug == 'double') {
			array_push($bed_config, $cat_name->slug);
		}
	}
	
	$product_id = $room['post_id'];
	$prices = get_post_meta($product_id , '_wc_booking_pricing', true);
	$std_price = get_post_meta($product_id , '_wc_booking_cost', true);
	$persons = $search_query['qty'];
	$duration = (int)$search_query['weeks'] / 7;
	    
	if ($persons == 1) {   
		$finalprice = $std_price * $duration;
	} else { 	
		foreach ($prices as $price) {
			$multiamount = $price['from'];
			$multiprice = $price['base_cost'];
				    	
			if ($persons == $multiamount) {
				$finalprice = ($multiprice * $persons) * $duration;
			}
		}
	}		
?>
	<div class="productresult" data-cat="<?php _e($room_cats[0]); ?>"> 
		<div class="results">   
			<div class="resultimg" style="background-image: url(<?php echo $room['image'][0]; ?>)" data-id="<?php echo $room['post_id']; ?>"></div>
			<div class="resultinfo" data-bedconfig="<?php echo strtolower(implode(",", $bed_config)); ?>">
				<div class="resultname"><?php echo $room_retreat[0] . ' - ' . $room['name']; ?></div>
				
				<div class="amenitiescont">
					<?php 
						foreach ($room['amenities'] as $amenities) { 
							foreach ($amenities as $amenity) {
							?>
								<div class="amenityitem">
									<div class="amenityicon <?php _e(strtolower(preg_replace('/\s+/', '', $amenity))); ?>"></div>
									<div class="amenitytip"><?php _e(str_replace('_', '&nbsp;', $amenity)); ?></div>
								</div>
							<?php
							}
						}
					?>
				</div>
				
				<div class="resultdesc"><?php echo $room['shortdesc']; ?></div>
				<?php if (has_term( 'Shared Room', 'product_cat', $room['post_id'] )) { ?>
					<div class="resultprice"><strong>From: <?php echo get_woocommerce_currency_symbol("GBP") . $finalprice; ?> pp (Share Space)</strong></div>
				<?php } else if (has_term( 'Entire Room', 'product_cat', $room['post_id'] )) { ?>
					<div class="resultprice"><strong>From: <?php echo get_woocommerce_currency_symbol("GBP") . $finalprice; ?> pp (Private Room)</strong></div>
				<?php } ?>	
				
				<?php modalPopup($room, $search_query, $room_retreat, $finalprice); ?>	
				
				<button class="roommoreinfo">More Info</button>	
				
						<?php if (!current_user_can('administrator')){ ?>
							<!-- // Returning Guest Discount -->
							<div class="resultscal">
							<form class="cart" method="post" enctype="multipart/form-data" action="/basket">
						<?php } else { ?>
							<!-- // Returning Guest Discount -->
							<div class="resultscal resultscaladmin">
							<form class="cartadmin" method="post" enctype="multipart/form-data" action="/basket">
						<?php } ?>
						<input type="hidden" name="add-to-cart" value="<?php _e($room['post_id']); ?>" class="wc-booking-product-id" />
						<input type="hidden" name="wc_bookings_field_duration" value="<?php _e($search_query['weeks']); ?>">
						<input type="hidden" name="wc_bookings_field_persons" value="<?php _e($room['max_persons']); ?>">
						<?php if (has_term( 'Shared Room', 'product_cat', $room['post_id'] )) { ?>
							<input type="hidden" name="wc_bookings_field_ref_persons" value="<?php _e($search_query['qty']); ?>">
						<?php } else if (has_term( 'Entire Room', 'product_cat', $room['post_id'] )) { ?>
						<input type="hidden" name="wc_bookings_field_ref_persons" value="<?php _e($search_query['qty']); ?>">
							<?php } ?>
						<input type="hidden" name="wc_bookings_field_bed_type" class="bed_type">
						<input type="hidden" name="wc_bookings_field_start_date_day" value="<?php _e(date('d',strtotime($search_query['arr_date']))); ?>">
						<input type="hidden" name="wc_bookings_field_start_date_month" value="<?php _e(date('m',strtotime($search_query['arr_date']))); ?>">
						<input type="hidden" name="wc_bookings_field_start_date_year" value="<?php _e(date('Y',strtotime($search_query['arr_date']))); ?>">
						<?php if (current_user_can('administrator')){ ?>
							<input type="submit" class="wc-bookings-booking-form-button single_add_to_cart_button button alt" value="BOOK NOW">
							<!-- // Returning Guest Discount -->
							<div>
								<input type="hidden" name="wc_bookings_field_jo_discount" id="jodiscount_<?php _e($room['post_id']); ?>" value="0">
								<input type="hidden" name="wc_bookings_field_jm_discount" id="jmdiscount_<?php _e($room['post_id']); ?>" value="0">
								<?php
								if ($room_retreat[0] == 'Juicy Oasis'){ ?>
									<input type="checkbox" name="jo_discount_check" class="discountcheck" id="jodiscountcheck" value="0" onchange="document.getElementById('jodiscount_<?php _e($room['post_id']); ?>').value = this.checked ? 1 : 0">RG
								<?php 
								}
								if ($room_retreat[0] == 'Juicy Mountain'){ ?>
									<input type="checkbox" name="jm_discount_check" class="discountcheck" id="jmdiscountcheck" value="0" onchange="document.getElementById('jmdiscount_<?php _e($room['post_id']); ?>').value = this.checked ? 1 : 0">RG
								<?php
								}
								?>
								<input type="number" name="wc_bookings_field_num_discount" class="discountnumber" id="numdiscount" value="1">Guest Count
							</div>
							<!-- // Returning Guest Discount End -->
						<?php } else { ?>
							<input type="submit" class="test-button wc-bookings-booking-form-button single_add_to_cart_button button alt" value="BOOK NOW">
						<?php } ?>
					</form>
					<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>
				</div>
			</div>
		</div>
	</div>	
<?php
}



// Pop Up Content Window
function modalPopup($room, $search_query, $room_retreat, $finalprice) {
?>
	<div class="modalroom hidden">
		<div class="modaloverlay"></div>
			<div class="modalwindow">
			<div class="modalclose">✖</div><!-- &#10006; -->
				<div class="roomimage">
					<div class="resultimg" style="background-image: url(<?php echo $room['image'][0]; ?>)" data-id="<?php echo $room['post_id']; ?>"></div>
				
					<div class="roomimages">
						<?php  
							echo '<div class="galleryimage" style="background-image: url('.$room['image'][0].')" data-image="'.$room['image'][0].'"></div>';   
							foreach( $room['gallery'] as $attachment_id ) {
								$image_link = wp_get_attachment_url( $attachment_id );
								echo '<div class="galleryimage" style="background-image: url('.$image_link.')" data-image="'.$image_link.'"></div>';  
		    				} 
		    			?>
					</div>
				</div>
				
				<div class="resultinfo">
					<div class="resultname"><?php echo $room_retreat[0] . ' - ' . $room['name']; ?></div>
			
					<div class="amenitiescont">
						<?php 
							foreach ($room['amenities'] as $amenities) { 
								foreach ($amenities as $amenity) {
								?>
									<div class="amenityitem">
										<div class="amenityicon <?php _e(strtolower(preg_replace('/\s+/', '', $amenity))); ?>"></div>
										<div class="amenitytip"><?php _e(str_replace('_', '&nbsp;', $amenity)); ?></div>
									</div>
								<?php
								}
							}
						?>
					</div>					
					
					<div class="resultdesc"><?php echo $room['desc']; ?></div>
					<?php if (has_term( 'Shared Room', 'product_cat', $room['post_id'] )) { ?>
						<div class="resultprice"><strong>From: <?php echo get_woocommerce_currency_symbol("GBP") . $finalprice; ?> pp (Share Space)</strong></div>
					<?php } else if (has_term( 'Entire Room', 'product_cat', $room['post_id'] )) { ?>
						<div class="resultprice"><strong>From: <?php echo get_woocommerce_currency_symbol("GBP") . $finalprice; ?> pp (Private Room)</strong></div>
					<?php } ?>		
				
					<div class="resultscal">
							<?php if (!current_user_can('administrator')){ ?>
								<form class="cart" method="post" enctype="multipart/form-data" action="/basket">
							<?php } else { ?>
								<form class="cartadmin" method="post" enctype="multipart/form-data" action="/basket">
							<?php } ?>
							<input type="hidden" name="add-to-cart" value="<?php _e($room['post_id']); ?>" class="wc-booking-product-id" />
							<input type="hidden" name="wc_bookings_field_duration" value="<?php _e($search_query['weeks']); ?>">
							<input type="hidden" name="wc_bookings_field_persons" value="<?php _e($room['max_persons']); ?>">
							<?php if (has_term( 'Shared Room', 'product_cat', $room['post_id'] )) { ?>
								<input type="hidden" name="wc_bookings_field_ref_persons" value="<?php _e($search_query['qty']); ?>">
							<?php } else if (has_term( 'Entire Room', 'product_cat', $room['post_id'] )) { ?>
								<input type="hidden" name="wc_bookings_field_ref_persons" value="<?php _e($search_query['qty']); ?>">
							<?php } ?>							
							<input type="hidden" name="wc_bookings_field_bed_type" class="bed_type">
							<input type="hidden" name="wc_bookings_field_start_date_day" value="<?php _e(date('d',strtotime($search_query['arr_date']))); ?>">
							<input type="hidden" name="wc_bookings_field_start_date_month" value="<?php _e(date('m',strtotime($search_query['arr_date']))); ?>">
							<input type="hidden" name="wc_bookings_field_start_date_year" value="<?php _e(date('Y',strtotime($search_query['arr_date']))); ?>">
							<?php if (current_user_can('administrator')){ ?>
							<input type="submit" class="wc-bookings-booking-form-button single_add_to_cart_button button alt" value="BOOK NOW">
							<?php } else { ?>
							<input type="submit" class="test-button wc-bookings-booking-form-button single_add_to_cart_button button alt" value="BOOK NOW">
							<?php } ?>
						</form>
						<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>
					</div>
					
				</div>
	
			</div>

	</div>
<?php
}


?>