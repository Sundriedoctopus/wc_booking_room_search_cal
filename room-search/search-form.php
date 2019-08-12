<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// manager Function - Used by shortcode 
add_action( 'wp_ajax_RoomSearch', 'RoomSearch' );	
function RoomSearch () { ?>

	<div class="roomsearchformcont">
		<div class="roomsearchwrap"> 
			<form class="roomsearchform" method="POST" data-url="<?php echo get_admin_url().'admin-ajax.php'?>">
				<select id="retreatsel" name="retreat" placeholder="Retreat">
					<option value="any">Any Retreat</option>
					<?php
						$retreatNum = (int) filter_var(esc_attr( get_option('retreat_amount_select') ), FILTER_SANITIZE_NUMBER_INT);
						$retreatStep = 0;
						while ($retreatStep < $retreatNum){ 
							$retreatName = esc_attr( get_option('retreat_'.$retreatStep.'_name') );
							?>
							<option value="<?php echo $retreatName; ?>"><?php echo $retreatName; ?></option>
							<?php
							$retreatStep++;
						}
					?>
				</select>
				
				<select id="guestssel" name="guests" placeholder="Guests">
					<option value="1">1 Guest</option>
					<option value="2">2 Guests</option>
					<option value="3">3 Guests</option>
				</select>
				
				<select id="weeksel" name="weeks" placeholder="Weeks">
					<option value="1">1 Week</option>
					<option value="2">2 Weeks</option>
				</select>
				
				<input type="hidden" id="arr_date" name="arr_date" placeholder="Arrival"/>
				<input type="hidden" id="dep_date" name="dep_date" placeholder="Departure"/>
				
				<input type="submit" value="Next" name="search" class="roomsubmit"/>
			</form>
		</div>
	</div>
	
	<p style="margin-top: 10px; font-size: 12px !important; text-align: center;">More than 3-Guests? Give our team a call and we can help find the perfect accommodation for you.</p>
	
	<div id="resultscontainer">
		<div class="resultsheader">
			<div class="steptitle greyed" id="step2"><div class="stepnumber">2</div><h2 class="pratabase" style="text-align: center;">Select Your Dates</h2></div>
		</div>
		<div class="calendar">
			<div class="resultscalendarkey">
				<div class="keycont">
					<div class="keysegment"> 
						<div class="key" id="greenkey"></div>
						<div class="keytext">AVAILABLE</div>
					</div>
					<div class="keysegment">
						<div class="key" id="orangekey"></div>
						<div class="keytext">LIMITED</div>
					</div>
					<div class="keysegment">
						<div class="key" id="redkey"></div> 
						<div class="keytext">SOLD OUT</div>
					</div>
				</div>
			</div>	
			<div class="resultsmain">
				<div class="loaderimage">
					<div class="graphic"></div>
				</div>
				<div id="resultswrap">
					<div class="calwrap">
						<div class="errorblock"></div>
						<div class="resultscalendar initialload"></div>
					</div>
				</div> 
			</div>
		</div>
		<div class="falsebtn"><input type="button" class="inactive" name="room_search_btn" value="Next"/></div>
	</div>
	
	<div class="steptitle greyed" id="step3">
		<div class="stepnumber" >3</div>
		<h2 class="pratabase" style="text-align: center;">Select Your Room</h2>
	</div>

	<div class="roomfilterform inactive">
		<form class="roomfilterform">
			<select class="roomselect shown" id="roomselect1" name="roomselect" placeholder="Room Configuration">
				<option value="0" selected="selected" disabled="disabled">Select a Layout</option>
				<option value="1">Private</option>
				<option value="2">Shared</option>
			</select>
					
			<select class="roomselect" id="roomselect2" name="roomselect" placeholder="Room Configuration">
				<option value="0" selected="selected" disabled="disabled">Select a Layout</option>
				<option value="1">Double bed</option>
				<option value="2">2x Single Beds</option>
			</select>
					
			<select class="roomselect" id="roomselect3" name="roomselect" placeholder="Room Configuration">
				<option value="0" selected="selected" disabled="disabled">Select a Layout</option>
				<option value="1">3x Single Beds</option>
			</select>
			
			<select class="roomselectbed" id="roomselect4" name="roomselectbed" placeholder="Bed Configuration">
				<option value="0" selected="selected" disabled="disabled">Select a Configuration</option>
				<option value="none">No Preference</option>
				<option value="single">1 x Single Bed</option>
				<option value="twin">2 x Single Beds</option>
				<option value="double">Double Bed</option>
			</select>				
		
			<button class="roomfiltersubmit">Show Rooms</button>
		</form>			
	</div>

	<div class="roomlist"></div>

<?php		
}
add_shortcode('RoomSearch', 'RoomSearch');
?>