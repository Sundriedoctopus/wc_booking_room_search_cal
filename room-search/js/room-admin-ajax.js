jQuery(document).ready(function(){     

	var roomName;
	var bookingStart;
	var bookingEnd;
	var durationWeeks;
	var numGuests;
	var roomConfig;
	
   
	jQuery(document).on('submit', '.cartadmin', function(e) { 
	    e.preventDefault();
	    
	    var form = jQuery(this).closest('form');
	    var formInputs = jQuery(this).closest('.results');
	    
	    var bookingStartDay 	= formInputs.find('input[name=wc_bookings_field_start_date_day]').val();
	    var bookingStartMonth 	= formInputs.find('input[name=wc_bookings_field_start_date_month]').val();
	    var bookingStartYear 	= formInputs.find('input[name=wc_bookings_field_start_date_year]').val();
	    
	    roomName 			= formInputs.find('.resultname').html();
		bookingStart 		= bookingStartDay + "/" + bookingStartMonth + "/" + bookingStartYear;
		durationWeeks 		= formInputs.find('input[name=wc_bookings_field_duration]').val() / 7;
		numGuests 			= formInputs.find('input[name=wc_bookings_field_ref_persons]').val();
		roomConfig 			= formInputs.find('input[name=wc_bookings_field_bed_type]').val(); 
				
		jQuery.ajax({
            url     : '',
            type    : form.attr('method'),
            dataType: 'text',
            data    : form.serialize(),
			success : function(results) {
			    formInputs.append('<div class="ajaxsummarybox"><div class="ajaxsummaryboxarrow"></div>' + roomName  + '<br/>' + bookingStart + '<br/>' + durationWeeks + ' week(s) <br/>' + numGuests + ' person(s) <br/>' + roomConfig + '<br/><div class="basketbtn"><a href="/basket">Basket</a></div></div>');
			        
			    jQuery('.wcmini').load(' .wcmini');  
			     
			    return results;
			},
			fail: function( jqXHR, textStatus, errorThrown ) {
			    console.log( 'Could send, server response: ' + textStatus + ': ' + errorThrown );
			}
		});
        return false;
        	
	});
	
	

	jQuery(document).on('click', '.mini_cart_item .remove', function(e) { 
			e.preventDefault();
			jQuery.ajax({
		        type: 'POST',
		        url: '/wp-admin/admin-ajax.php',
		        data: { action: 'remove_item_from_cart', 'cart_item_key': String(jQuery(this).data('cart-item-key')) },
		        success: function (res) {
		            if (res) {
		                alert('Removed Successfully');
		                jQuery('.wcmini').load(' .wcmini'); 
		            }
		        }
		    });
	});	
	





    jQuery('.adminsummary td').on('mouseover', function() {
	    if (!jQuery(this).hasClass('highlightselect') && !jQuery(this).hasClass('cornerblock')) {
		    if (!jQuery(this).hasClass('dateweek')) {
	        	jQuery(this).closest('tr').addClass('highlight');
	        }
	        if (!jQuery(this).hasClass('roomtype')) {
	        	jQuery(this).closest('table').find('td:nth-child(' + (jQuery(this).index() + 1) + ')').addClass('highlight');
	        }
	    }
    });
    jQuery('.adminsummary td').on('mouseout', function() {
        jQuery(this).closest('tr').removeClass('highlight');
        jQuery(this).closest('table').find('td:nth-child(' + (jQuery(this).index() + 1) + ')').removeClass('highlight');
    });


    jQuery('.adminsummary td').on('click', function() {
	    if (!jQuery(this).hasClass('highlightselect') && !jQuery(this).hasClass('cornerblock')) {
		    if (!jQuery(this).hasClass('dateweek')) {
			    jQuery(this).closest('table').find('tr').removeClass('highlightselect');
	        	jQuery(this).closest('tr').addClass('highlightselect');
	        }
	        if (!jQuery(this).hasClass('roomtype')) {
		        jQuery(this).closest('table').find('td').removeClass('highlightselect'); 
	        	jQuery(this).closest('table').find('td:nth-child(' + (jQuery(this).index() + 1) + ')').addClass('highlightselect');
	        }
	    }
    });
    
    
    jQuery('.clearbtn').on('click', function(e){
	    e.preventDefault();
		jQuery('.adminsummary').find('.highlightselect').removeClass('highlightselect');
	    jQuery('.adminsummary form select').prop('selectedIndex',0);
    });
    jQuery('.dateweek').on('click', function(e){
	    jQuery('.adminsummary #filterdate').prop('selectedIndex',0);
    });
    jQuery('.roomtype').on('click', function(e){
	    jQuery('.adminsummary #filterroom').prop('selectedIndex',0);
    });
    jQuery('.filterbtn').on('click', function(e){
	    e.preventDefault();
	    jQuery('.adminsummary').find('td, tr').removeClass('highlightselect'); 
	    
	    var date = jQuery('#filterdate').val().replace(/\s/g,'');
	    var room = jQuery('#filterroom').val().replace(/\s/g,'');

		jQuery('.adminsummary td').each(function () {
			var text = jQuery(this).text().replace(/\s/g,'');
			if (text != '' && text == date && date != null) {
	        	jQuery(this).closest('table').find('td:nth-child(' + (jQuery(this).index() + 1) + ')').addClass('highlightselect');
			}
			if (text != '' && text == room && room != null) {
	        	jQuery(this).closest('tr').addClass('highlightselect');
			}
		});
    });
   
	
});