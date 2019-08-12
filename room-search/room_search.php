<?php
/*
	Plugin Name: Room Search
	Plugin URI: http://sundriedoctopus.com/
	Description: Room Search - Houses the Room Booking Search Facility
	Author: Marcus A - Sun-dried Octopus
	Version: 1.0.0
	Author URI: http://sundriedoctopus.com/
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


// Various Includes
add_action( 'wp_enqueue_scripts', 'ajax_enqueue_scripts' );
function ajax_enqueue_scripts() {
	wp_enqueue_style( 'room_search_css', plugins_url( '/css/room-search.css', __FILE__ ) );
	wp_enqueue_script( 'room_search_js', plugins_url( '/js/room-search.js', __FILE__ ), array('jquery'), '1.0', true );
	wp_enqueue_script( 'room_admin_ajax_js', plugins_url( '/js/room-admin-ajax.js', __FILE__ ), array('jquery'), '1.0', true );
	wp_enqueue_script( 'jquery-ui-datepicker' );
}

add_action( 'wp_enqueue_scripts', 'loadjQueryUi' );
function loadjQueryUi() {
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-ui-datepicker');
}

include('room-list.php');
include('search-form.php');
include('search-results.php');
include('holding-calendar.php');
include('admin_cal_summary.php');


// Sets Prices For Entire Rooms By Persons Multiplier
add_action( 'woocommerce_before_calculate_totals', 'add_custom_price_room' );
function add_custom_price_room( $cart_object ) {
	
    foreach ( WC()->cart->get_cart() as $key => $product ) {
	    $product_id = $product['product_id'];
	    
        if ( has_term( 'entire-room', 'product_cat', $product_id ) ) { 
	    	$prices = get_post_meta($product_id , '_wc_booking_pricing', true);
	    	$std_price = get_post_meta($product_id , '_wc_booking_cost', true);
	    	
	    	$persons = $product['booking']['_ref_persons'];
	    	$duration = (int)$product['booking']['duration'];
	    	  
	    	if ($persons == 1) {   
				$product['data']->set_price($std_price * $duration);
		    } else { 	
		    	foreach ($prices as $price) {
			    	$multiamount = $price['from'];
			    	$multiprice = $price['base_cost'];
				    
				    if ($persons == $multiamount) {
				    	$product['data']->set_price(($multiprice * $persons) * $duration);
			    	}
		    	}
	    	}
	    	
	    	
	    	
	    	// RETURNING GUEST
	    	$curprice = $product['data']->get_price();
	    	$numberOfGuests = $product['booking']['_num_discount'];
	    	$discount = 0;
	    	
	    	
	    	if ( has_term( 'juicy-oasis', 'product_cat', $product_id ) ) {
		    	// JUICY OASIS RETURNING GUEST
		    	if (!isset($product['booking']['_jo_discount']) && $duration > 1) {
			    	$discount = ($curprice / 100) * (5 * $numberOfGuests);
		    	}
		    	if (isset($product['booking']['_jo_discount']) && $duration == 1) {
			    	$discount = ($curprice / 100) * (5 * $numberOfGuests);
		    	}
		    	if (isset($product['booking']['_jo_discount']) && $duration > 1) {
		    		$fivepercent = ($curprice / 100) * 5;
		    		$discount = (($fivepercent + $fivepercent) * $duration) * $numberOfGuests;
		    	}
		    }	
		    
		    if ( has_term( 'juicy-mountain', 'product_cat', $product_id ) ) {	
		    	
		    	// JUICY MOUNTAIN RETURNING GUEST
		    	if (isset($product['booking']['_jm_discount']) && $duration > 1){
					$discount = ($curprice / 100) * ((10 * $duration) * $numberOfGuests);	
				}	   
				if (!isset($product['booking']['_jm_discount']) && $duration > 1){
					$singleWeek = $curprice / $duration;
					$discountWeeks = $singleWeek * ($duration - 1);
					$discount = ($singleWeek / 100) * ((10 * $duration) * $numberOfGuests);	
				} 	
	    	}
	    	
	    	$product['data']->set_price($curprice - $discount);
	    	
	    	if (WC()->cart->deposit_info['deposit_enabled']) {
		    	$deposit_amount = get_option('wc_deposits_checkout_mode_deposit_amount');
		    	if ($deposit_amount > 0) {
			    	$curprice = $product['data']->get_price();
			    	$finalprice = ($curprice / 100) * $deposit_amount;
			    	// $product['data']->set_price($finalprice);
			    	
			    	$deposit = WC()->cart->deposit_info['deposit_amount'];
		            $second_payment = WC()->cart->deposit_info['second_payment'];
		            $original_total = WC()->cart->total;
		            // $deposit_breakdown = WC()->cart->deposit_info['deposit_breakdown'];
						
					// $cart_object->cart_contents_total = $finalprice;
					// $cart_object->set_total( $finalprice );
					
					/*
		            $order->set_total($deposit);
		            $order->add_meta_data('_wc_deposits_order_has_deposit', 'yes', true);
		            $order->add_meta_data('_wc_deposits_deposit_paid', 'no', true);
		            $order->add_meta_data('_wc_deposits_deposit_amount', $deposit, true);
		            $order->add_meta_data('_wc_deposits_second_payment', $second_payment, true);
		            $order->add_meta_data('_wc_deposits_original_total', $original_total, true);
			    	*/
		    	}
			}
	    		    
        }
    }   
}	






/*

add_filter( 'woocommerce_calculated_total', 'custom_calculated_total', 60, 2 );
function custom_calculated_total( $total, $cart ){
	global $woocommerce;
	$cart_total = $app= (float) preg_replace( '/[^0-9\.]/', '', $woocommerce->cart->get_cart_total()  );
	$deposit_amount = get_option('wc_deposits_checkout_mode_deposit_amount');

                if (WC()->cart->discount_cart > 0) {
                    $deposit_amount = (($cart_total - WC()->cart->discount_cart) / 100) * $deposit_amount;
                } else {
                    $deposit_amount = ($cart_total / 100) * $deposit_amount;
                }
	    	
	   echo $cart_total . ' - ' . $deposit_amount; 
    return round( $total - $deposit_amount, $cart->dp );
}

*/






// Store custom field label and value in cart item data
add_filter( 'woocommerce_add_cart_item_data', 'save_my_custom_checkout_field', 10, 2 );
function save_my_custom_checkout_field( $cart_item_data, $product_id ) {
	
	if ( !empty($product['booking']['_ref_persons']) ) { 
		$persons = $product['booking']['_ref_persons'];
		$cart_item_data['booking']['_ref_persons'] = $persons;
	}
	
	if ( !empty($product['booking']['_bed_type']) ) { 
		$persons = $product['booking']['_bed_type'];
		$cart_item_data['booking']['_bed_type'] = $persons;
	}
	
	// Returning Guest Discount
	if ( !empty($product['booking']['_jo_discount']) ) { 
		$return = $product['booking']['_jo_discount'];
		$cart_item_data['booking']['_jo_discount'] = $return;
	}
	if ( !empty($product['booking']['_jm_discount']) ) { 
		$return = $product['booking']['_jm_discount'];
		$cart_item_data['booking']['_jm_discount'] = $return;
	}
	if ( !empty($product['booking']['_num_discount']) ) { 
		$return = $product['booking']['_num_discount'];
		$cart_item_data['booking']['_num_discount'] = $return;
	}
	
	return $cart_item_data;	
}


// Save item custom fields label and value as order item meta data
add_action('woocommerce_add_order_item_meta','save_in_order_item_meta', 10, 3 );
function save_in_order_item_meta( $item_id, $values, $cart_item_key ) {
    if( isset( $values['booking']['_ref_persons'] ) ) {
        wc_add_order_item_meta($item_id, __('_ref_Persons', 'woocommerce-bookings'), $values['booking']['_ref_persons']);
        wc_add_order_item_meta($item_id, __('_bed_Type', 'woocommerce-bookings'), $values['booking']['_bed_type']);
        
        // Returning Guest Discount
        wc_add_order_item_meta($item_id, __('_jo_discount', 'woocommerce-bookings'), $values['booking']['_jo_discount']);
        wc_add_order_item_meta($item_id, __('_jm_discount', 'woocommerce-bookings'), $values['booking']['_jm_discount']);
        wc_add_order_item_meta($item_id, __('_num_discount', 'woocommerce-bookings'), $values['booking']['_num_discount']);
    }
}


// Change Order Notes Placeholder Text - WooCommerce
add_filter( 'woocommerce_checkout_fields', 'additional_woocommerce_checkout_fields' );
function additional_woocommerce_checkout_fields( $fields ) {
	$fields['order']['order_comments']['placeholder'] = 'Anything else you think we would need to know, e.g. allergies, disabilities, arrival time etc.';
	return $fields;
} 


// Empty cart first: new item will replace previous
add_filter( 'woocommerce_add_to_cart_validation', 'empty_woocommerce_checkout', 99, 2 );
function empty_woocommerce_checkout( $passed, $added_product_id ) {
	// wc_empty_cart();
	return $passed;
}


// Add Cart Checks & 'Back To Booking' Button
add_action( 'woocommerce_cart_contents', 'roomCheck', 10, 3);
function roomCheck($cart_object) {
	$roomCount = 0;
	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
		if ( has_term( 'room', 'product_cat', $product_id ) ) { 
			$roomCount++;
		}
	}
    
    if($roomCount > 1) {
	    $roomCountScript = '<script>jQuery(window).load(function(){';
		$roomCountScript .= 'alert("You have added '.$roomCount.' rooms to your booking, are you sure this is what you need?");';
	    $roomCountScript .= '});</script>';
	    echo $roomCountScript;
	    
	    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
			if ( has_term( 'transfers', 'product_cat', $product_id ) ) { 
				WC()->cart->remove_cart_item($cart_item_key);
			}
		}
    } 
    
    if ($roomCount >= 1) {
		echo '<div class="backtobookingbtn"><a href="/retreat-booking"><i class="icon-arrow-left"></i>Back to Room Booking</a></div>';  
	}
    
}






// NEW ADMIN NOTES
add_action( 'woocommerce_after_order_notes', 'admin_notes_field' );
function admin_notes_field( $checkout ) {
	if (current_user_can('administrator')) {
	    echo '<div id="admin_notes_field"><h2>' . __('Admin Notes') . '</h2>';
	
	    woocommerce_form_field( 'admin_notes', array(
	        'type'          => 'textarea',
	        'class'         => array('admin_notes_field form-row-wide'),
	        'label'         => __('Admin Notes'),
	        'placeholder'   => __('Any notes for this order? Customers will NOT see this.'),
	        ), $checkout->get_value( 'admin_notes' ));
	
	    echo '</div>';
	}
}

add_action( 'woocommerce_checkout_update_order_meta', 'admin_notes_field_update_order_meta' );
function admin_notes_field_update_order_meta( $order_id ) {
    if ( ! empty( $_POST['admin_notes'] ) ) {
        update_post_meta( $order_id, 'Admin Notes', sanitize_text_field( $_POST['admin_notes'] ) );
    }
}

add_action( 'woocommerce_admin_order_data_after_order_details', 'admin_notes_field_display_admin_order_meta' );
function admin_notes_field_display_admin_order_meta( $order ){  ?>
    <div class="admin_note_data_column">
        <h4><?php _e( 'Admin Notes', 'woocommerce' ); ?><a href="#" class="edit_address"><?php _e( 'Edit', 'woocommerce' ); ?></a></h4> 
	
		        <div class="adminnotes">
		        	<?php echo '<p><strong>'.__('Note').':</strong> ' . get_post_meta( $order->id, 'Admin Notes', true ) . '</p>'; ?>
		        </div>
		        <div class="edit_address">
			        <p>This will override the previous notes, If you want to keep previous notes, leave the pre-filled info in and adjust or add to it. Separate Notes will a comma, semicolon or bar ( , ; | )</p>
		            <?php woocommerce_wp_textarea_input( array( 'id' => 'admin_notes', 'label' => __( 'Admin Note' ), 'wrapper_class' => 'admin_notes_field', 'value' => get_post_meta( $order->id, 'Admin Notes', true ) ) ); ?>
		        </div>	

    </div>
<?php }

add_action( 'woocommerce_process_shop_order_meta', 'admin_notes_save_extra_details', 45, 2 );
function admin_notes_save_extra_details( $post_id, $post ){
	update_post_meta( $post_id, 'Admin Notes', sanitize_text_field( $_POST['admin_notes'] ) );
}





// New Multi Checkbox field for woocommerce backend
// 'https://stackoverflow.com/questions/50799927/multi-checkbox-fields-in-woocommerce-backend'

function woocommerce_wp_multi_checkbox( $field ) {
    global $thepostid, $post;

    $field['value'] = get_post_meta( $thepostid, $field['id'], true );

    $thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
    $field['class']         = isset( $field['class'] ) ? $field['class'] : 'select short';
    $field['style']         = isset( $field['style'] ) ? $field['style'] : '';
    $field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
    $field['value']         = isset( $field['value'] ) ? $field['value'] : array();
    $field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
    $field['desc_tip']      = isset( $field['desc_tip'] ) ? $field['desc_tip'] : false;

    echo '<fieldset class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '">
    <legend>' . wp_kses_post( $field['label'] ) . '</legend>';

    if ( ! empty( $field['description'] ) && false !== $field['desc_tip'] ) {
        echo wc_help_tip( $field['description'] );
    }

    echo '<ul class="wc-radios">';

    foreach ( $field['options'] as $key => $value ) {

        echo '<li><label><input
                name="' . esc_attr( $field['name'] ) . '"
                value="' . esc_attr( $key ) . '"
                type="checkbox"
                class="' . esc_attr( $field['class'] ) . '"
                style="' . esc_attr( $field['style'] ) . '"
                ' . ( in_array( $key, $field['value'] ) ? 'checked="checked"' : '' ) . ' /> ' . esc_html( $value ) . '</label>
        </li>';
    }
    echo '</ul>';

    if ( ! empty( $field['description'] ) && false === $field['desc_tip'] ) {
        echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
    }

    echo '</fieldset>';
}


// Add custom multi-checkbox field for product general option settings
add_action( 'woocommerce_product_options_general_product_data', 'add_custom_settings_fields', 20 );
function add_custom_settings_fields() {
    global $post;

    echo '<div class="options_group hide_if_variable"">'; // Hidding in variable products

    woocommerce_wp_multi_checkbox( array(
        'id'    	=> '_custom_room',
        'name'  	=> '_custom_room[]',
        'label' 	=> __('Room Amenities', 'woocommerce'),
        'options' 	=> array(
			'12_sq_m'													=> __( '12 sq m', 'woocommerce' ),
			'30_sq_m'													=> __( '30 sq m', 'woocommerce' ),
			'32_sq_m'													=> __( '32 sq m', 'woocommerce' ),
			'34_sq_m'													=> __( '34 sq m', 'woocommerce' ),
			'38_sq_m'													=> __( '38 sq m', 'woocommerce' ),
			'52_sq_m'													=> __( '52 sq m', 'woocommerce' ),
            'Under_Floor_Heating'   									=> __( 'Under Floor Heating', 'woocommerce' ),
            'Fan_Cooling_System'   										=> __( 'Fan Cooling System', 'woocommerce' ),
            'Air_Conditioning'   										=> __( 'Air Conditioning', 'woocommerce' ),
            'Wardrobe_And_Storage'   									=> __( 'Wardrobe and Storage', 'woocommerce' ),
            'Use_Of_Spa_Facilites'										=> __( 'Use of Spa Facilites', 'woocommerce' ),
            'Ensuite'													=> __( 'Ensuite', 'woocommerce' ),
            'Ensuite_With_Shower'   									=> __( 'Ensuite with Shower', 'woocommerce' ),
            'Bath'   													=> __( 'Bath', 'woocommerce' ),
            'Shower'   													=> __( 'Shower', 'woocommerce' ),
            'Double_Shower'   											=> __( 'Double Shower', 'woocommerce' ),
            'Hot_Tub'													=> __( 'Hot Tub', 'woocommerce' ),
            'Balcony'													=> __( 'Balcony', 'woocommerce' ),
            'Balconette'   												=> __( 'Balconette', 'woocommerce' ),
            'Toiletries'   												=> __( 'Toiletries', 'woocommerce' ),
            'Robe'   													=> __( 'Robe', 'woocommerce' ),
            'Towels'   													=> __( 'Towels', 'woocommerce' ),
            'Pool_Towel'												=> __( 'Pool Towel', 'woocommerce' ),
            'Hairdryer'													=> __( 'Hairdryer', 'woocommerce' ),      
            'TV'   														=> __( 'TV', 'woocommerce' ),
            'Free_WiFi'   												=> __( 'Free WiFi', 'woocommerce' ),
            'Blackout_Curtains'   										=> __( 'Blackout Curtains', 'woocommerce' ),
            'Safe'														=> __( 'Safe', 'woocommerce' ), 
        )
    ) );

    echo '</div>';
    echo '<style> ._custom_room_field ul { width: auto !important; } ._custom_room_field li { padding-bottom: 2px !important; }</style>';
}


// Save custom multi-checkbox fields to database when submitted in Backend (for all other product types)
add_action( 'woocommerce_process_product_meta', 'save_product_options_custom_fields', 30, 1 );
function save_product_options_custom_fields( $post_id ){
    if( isset( $_POST['_custom_room'] ) ){
        $post_data = $_POST['_custom_room'];
        // Data sanitization
        $sanitize_data = array();
        if( is_array($post_data) && sizeof($post_data) > 0 ){
            foreach( $post_data as $value ){
                $sanitize_data[] = esc_attr( $value );
            }
        }
        update_post_meta( $post_id, '_custom_room', $sanitize_data );
    }
}

























// ADD ADDITION GUEST CUSTOM FIELDS TO CHECKOUT =============================================
add_action( 'woocommerce_before_order_notes', 'room_guests_custom_checkout_field' );
function room_guests_custom_checkout_field( $checkout ) {
	
	foreach ( WC()->cart->get_cart() as $key => $product ) {
	    $product_id = $product['product_id'];
	    
        if ( has_term( 'entire-room', 'product_cat', $product_id ) || has_term( 'shared-room', 'product_cat', $product_id ) ) { 
	    	
	    	$persons = $product['booking']['_ref_persons'];
			?>
			<div class="additional_guests"><h4 class="additional_guests_title">Additional Guest(s) Details</h4>
			<?php		
			
			$i = 1;
			while ($i <= ($persons-1)) {
				?>
				<div class="additional_guests"><h5>Additional Guest <?php echo $i; ?></h5>
				<?php
					
				woocommerce_form_field( 'room_additional_guest_'.$i.'_name', array(
				    'type'          => 'text',
			        'class'         => array('room_additional_guest_'.$i.' form-row-wide', 'addroomguestnamefield'),
			        'label'         => __('Additional Guest '.$i.' Full Name'),
			        'placeholder'   => __('e.g. John Peterson'), 
			    ), $checkout->get_value('room_additional_guest_'.$i.'_name' ));
					
				woocommerce_form_field('room_additional_guest_'.$i.'_email', array(
			        'type'          => 'text',
			        'class'         => array('room_additional_guest_'.$i.' form-row-wide', 'addroomguestemailfield'),
			        'label'         => __('Additional Guest '.$i.' Email'),
			        'placeholder'   => __('e.g. john@gmail.com'),
			    ), $checkout->get_value('room_additional_guest_'.$i.'_email' ));
			        
			    woocommerce_form_field('room_additional_guest_'.$i.'_phone', array(
			        'type'          => 'tel',	
			        'class'         => array('room_additional_guest_'.$i.' form-row-wide', 'addroomguestphonefield'),
			        'label'         => __('Additional Guest '.$i.' Phone'),
			        'placeholder'   => __('e.g. +44 1234567890'),
			        'pattern'		=> __('[0-9]{3}-[0-9]{3}-[0-9]{4}'),
			    ), $checkout->get_value('room_additional_guest_'.$i.'_phone' ));
			           
				?>
				</div>
				<?php
			    $i++;
			}
			?>
			</div>
			<?php
		}
	}
}	

// UPDATE ORDER META WITH CUSTOM FIELD VALUE  ===============================================
add_action( 'woocommerce_checkout_update_order_meta', 'room_guests_checkout_field_update_order_meta' );
function room_guests_checkout_field_update_order_meta( $order_id ) {
	$order = wc_get_order($order_id);
	foreach ($order->get_items() as $item_id => $item_obj) {
		$product_id = $item_obj['product_id'];	
		$product_qty = wc_get_order_item_meta( $item_id, '_ref_Persons', true );
		$i = 1;
		while ($i < $product_qty)	{		
			update_post_meta( $order_id, 'room_additional_guest_'.$i.'_name', sanitize_text_field( $_POST['room_additional_guest_'.$i.'_name'] ) );
			update_post_meta( $order_id, 'room_additional_guest_'.$i.'_email', sanitize_text_field( $_POST['room_additional_guest_'.$i.'_email'] ) );
			update_post_meta( $order_id, 'room_additional_guest_'.$i.'_phone', sanitize_text_field( $_POST['room_additional_guest_'.$i.'_phone'] ) );
			$i++;
		} 
	}
}

// DISPLAYS CUSTOM ORDER META IN ORDER PAGE =================================================
add_action( 'woocommerce_admin_order_data_after_order_details', 'room_guest_display_order_data_in_admin' );
function room_guest_display_order_data_in_admin( $order ){  ?>
    <div class="room_additional_guest_data_column">
        <h4><?php _e( 'Additional Guest(s) Information', 'woocommerce' ); ?><a href="#" class="edit_address"><?php _e( 'Edit', 'woocommerce' ); ?></a></h4> 
        <?php
		foreach ($order->get_items() as $item_id => $item_obj) {
			$product_id = $item_obj['product_id'];
			$product_qty = wc_get_order_item_meta( $item_id, '_ref_Persons', true );
			$i = 1;
			while ($i < $product_qty)	{		
				?>
				
		        <div class="address">
		        	<?php echo '<p><strong>' . __( 'Name' ) . ':</strong>' . get_post_meta( $order->id, 'room_additional_guest_'.$i.'_name', true ) . '</p>'; ?>
		        	<?php echo '<p><strong>' . __( 'Email' ) . ':</strong>' . get_post_meta( $order->id, 'room_additional_guest_'.$i.'_email', true ) . '</p>'; ?>
		        	<?php echo '<p><strong>' . __( 'Phone' ) . ':</strong>' . get_post_meta( $order->id, 'room_additional_guest_'.$i.'_phone', true ) . '</p>'; ?>
		        </div>
		        <div class="edit_address">
		            <?php woocommerce_wp_text_input( array( 'id' => 'room_additional_guest_'.$i.'_name', 'label' => __( 'Name' ), 'wrapper_class' => '_billing_company_field' ) ); ?>
		            <?php woocommerce_wp_text_input( array( 'id' => 'room_additional_guest_'.$i.'_email', 'label' => __( 'Email' ), 'wrapper_class' => '_billing_company_field' ) ); ?>
		            <?php woocommerce_wp_text_input( array( 'id' => 'room_additional_guest_'.$i.'_phone', 'label' => __( 'Phone' ), 'wrapper_class' => '_billing_company_field' ) ); ?>
		        </div>	

				</div>
				<?php
				$i++;
			}
		}
		
		?>
    </div>
<?php }

// UPDATES CUSTOM ORDER META IN ORDER PAGE ==================================================
add_action( 'woocommerce_process_shop_order_meta', 'room_guest_save_extra_details', 45, 2 );
function room_guest_save_extra_details( $post_id, $post ){
	$order = wc_get_order( $post->ID );
	foreach ($order->get_items() as $item_id => $item_obj) {
		$product_id = $item_obj['product_id'];
		$product_qty = wc_get_order_item_meta( $item_id, '_ref_Persons', true );
		$i = 1;
		while ($i < $product_qty)	{	
			update_post_meta( $post_id, 'room_additional_guest_'.$i.'_name', sanitize_text_field( $_POST['room_additional_guest_'.$i.'_name'] ) );
			update_post_meta( $post_id, 'room_additional_guest_'.$i.'_email', sanitize_text_field( $_POST['room_additional_guest_'.$i.'_email'] ) );
			update_post_meta( $post_id, 'room_additional_guest_'.$i.'_phone', sanitize_text_field( $_POST['room_additional_guest_'.$i.'_phone'] ) );
			$i++;
		}
	}
}

// UPDATES CUSTOM ORDER META IN EMAILS ======================================================
add_filter('woocommerce_email_order_meta_fields', 'room_guest_email_order_meta_fields', 10, 3 );
function room_guest_email_order_meta_fields( $fields, $sent_to_admin, $order ) {
	foreach ($order->get_items() as $item_id => $item_obj) {
		$product_id = $item_obj['product_id'];
		$product_qty = wc_get_order_item_meta( $item_id, '_ref_Persons', true );
		$i = 1;
		while ($i < $product_qty)	{		
			?>
			<div class="additional_guests"><h5>Additional Guest <?php echo $i; ?></h5>
			<?php
				
			$fields['Additional Guest ' . $i] = array(
               	'label' => __( 'Additional Guest ' . $i . ' Name' ),
				'value' => get_post_meta( $order->id, 'room_additional_guest_'.$i.'_name', true ),
			);
			$fields['Additional Guest ' . $i . ' Email'] = array(
               	'label' => __( 'Additional Guest ' . $i . ' Email' ),
				'value' => get_post_meta( $order->id, 'room_additional_guest_'.$i.'_email', true ),
			);
			$fields['Additional Guest ' . $i . ' Phone'] = array(
               	'label' => __( 'Additional Guest ' . $i . ' Phone' ),
				'value' => get_post_meta( $order->id, 'room_additional_guest_'.$i.'_phone', true ),
			);
			$i++;
		}
	}    
	return $fields;
}

?>
