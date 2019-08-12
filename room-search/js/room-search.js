// Calendar AJAX Processor =======================================
jQuery(document).on('click', '.roomsubmit', function(e){
	e.preventDefault();
	
	var form = jQuery(this).closest('form');
	var ajaxurl  = form.data('url');
	var startdate = new Date(arr_date);
	var enddate = new Date(dep_date);
	var duration = (new Date(enddate - startdate))/1000/60/60/24;
	var retreat = form.find('#retreatsel').val();
	var weeks = form.find('#weeksel').val();
	var guests = form.find('#guestssel').val();
	
	if (jQuery("#resultscontainer").css('display') == 'block') {
		loading();
	}
	
	jQuery.ajax({
        type: 'POST',
        url: ajaxurl, 
        data: { action: 'SearchAjax', 
	        retreat : retreat, 
	        duration : duration, 
	        weeks : weeks, 
	        guests : guests, 
	    },
		beforeSend: function() {
			jQuery('.roomsearchformcont').addClass('disableblock');
		    jQuery('.loaderimage').fadeIn(100);
		},
		complete: function(){
		    jQuery('.loaderimage').fadeOut(100);
		    jQuery('.roomsearchformcont').removeClass('disableblock');
		    prepCal();
		},
        success: function(result) {
	        jQuery('#resultswrap').html(result);
	        loaded();
        }
    });
});

function loading() {
	jQuery('#resultswrap').fadeTo("slow" , 0.3, function(){	jQuery('.roomlist').html(''); });
	jQuery('.roomlist').fadeTo(300, 0).slideUp(300);
	jQuery('#room_search_btn').addClass('inactive')
}

function loaded() {
	jQuery('.falsebtn').remove();
	setTimeout(function(){
		jQuery('#resultscontainer').slideDown(1500).fadeTo(1000, 1);
		jQuery('#resultswrap').fadeTo(1000, 1);
		jQuery('#step2').removeClass('greyed');
		jQuery('html, body').animate({
            scrollTop: jQuery('#step2').offset().top - 100
        }, 1500);
	}, 500);
	if ( jQuery('.initloader .loaderimage').length ) {
		jQuery('.initloader .loaderimage').remove();
	}
	jQuery(window).trigger('resize');
}
// ===============================================================


// Calendar Builder ==============================================
function prepCal() {
	
	if (!jQuery('.checkclass').length) {
	
	var startDate;
    var endDate;
    var endweekStart;
    var calCorStartDate;
    
    var dates = jQuery('.resultscalendar').data('dates') === undefined ? '' : jQuery('.resultscalendar').data('dates').split(',');
    var noDates = jQuery('.resultscalendar').data('nodates') === undefined ? '' : jQuery('.resultscalendar').data('nodates').split(',');
    var weeks = jQuery('.resultscalendar').data('weeks');
    var curtable;
    
    var selectCurrentWeek = function() {
        window.setTimeout(function () {
           jQuery('.available').each(function(){
				jQuery(this).closest('tr').children('td').addClass('available');
			});
            
			jQuery('.hasbooking').each(function(){
				jQuery(this).closest('tr').children('td').addClass('hasbooking');
			});
			
			jQuery('.noDates').each(function(){
				jQuery(this).closest('tr').children('td').addClass('noDates');
			});
			
			jQuery('.ui-priority-secondary').each(function(){
				jQuery(this).removeClass('ui-priority-secondary');
			});
        }, 1);
    }

	jQuery('.resultscalendar').datepicker( {
		minDate: new Date(),
		maxDate: '+6m',
	    showButtonPanel: false,
	    firstDay: 6,
		numberOfMonths: 3,
        showOtherMonths: false,
        selectOtherMonths: false,
        onSelect: function(dateText, inst) { 
            var date = jQuery(this).datepicker('getDate');
            startDate = new Date(date.getFullYear(), date.getMonth(), (date.getDate() - date.getDay()) + 6);
            endDate = new Date(date.getFullYear(), date.getMonth(), ((date.getDate() - date.getDay()) + 6) + (weeks == 1 ? 6 : (7 * weeks)-1 ));
            endweekStart = new Date(date.getFullYear(), date.getMonth(), ((date.getDate() - date.getDay()) + 6) + (weeks == 1 ? 6 : (7 * weeks)-7 ));
			calCorStartDate = new Date(date.getFullYear(), date.getMonth(), (date.getDate() - date.getDay()) + 8);
                 
            errorHide();           
            selectCurrentWeek();
            if ( jQuery('.roomlist').html() != '' ) {
            	jQuery('.roomlist').fadeTo(300, 0).slideUp(200, function() { jQuery(this).html(''); });
            }
            
			jQuery('.roomfilterform select').val(0);
			jQuery('.roomfilterform').addClass('inactive');
        },
        beforeShowDay: function(date) {
	        var day = date.getDay();
			var dateString = jQuery.datepicker.formatDate('dd-mm-yy', date);
			var startDateString = jQuery.datepicker.formatDate('dd-mm-yy', startDate);
			var lastDateString = jQuery.datepicker.formatDate('dd-mm-yy', endDate);
			var endDateString = jQuery.datepicker.formatDate('dd-mm-yy', endweekStart);
			var cssClass = '';
			
			var calCoreStartDateString = jQuery.datepicker.formatDate('dd-mm-yy', calCorStartDate);
			jQuery('.resultscalendar').attr('data-arrd', startDateString); 
			jQuery('.resultscalendar').attr('data-chkd', calCoreStartDateString);
			jQuery('.resultscalendar').attr('data-depd', lastDateString);	
			
			// SETS CLASSES BEFORE THIS UPCOMING SATURDAY
			var dateBefore = new Date();
			dateBefore.setDate((dateBefore.getDate() - dateBefore.getDay()) + 5);
			if (date < dateBefore) {
				cssClass = 'ui-state-disabled ui-datepicker-unselectable noDates beforeThisWeek';
				return [ false, cssClass ];
			} 
			
			if (startDate != null) {
											
				// IF IS IN SELECTED WEEKS RANGE BUT IS NOT AVAILABLE
				if (date >= endweekStart && date <= endDate && jQuery.inArray(endDateString, noDates) >= 0) { 	
					day == 6 ? cssClass = 'noDates errweeksel' : cssClass = 'ui-state-disabled ui-datepicker-unselectable noDates errweeksel';
		            selectError(endDateString);
		            return [ true, cssClass ];
		            
				// IF IS IN SELECTED WEEKS RANGE
				} else if (date >= startDate && date <= endDate) {
		        	day == 6 ? cssClass = 'ui-datepicker-current-day weekselection' : cssClass = 'ui-datepicker-unselectable weekselection';
		        	
					jQuery('#room_search_btn').removeClass('inactive');
		        	
		            return [ true, cssClass ];
		            
				// IF IS NOT IN SELECTED WEEKS RANGE		
				} else {
					
					if (day == 6) {
						if (jQuery.inArray(dateString, dates) >= 0) {
							cssClass = 'hasbooking';
						} 
						if (jQuery.inArray(dateString, noDates) >= 0) {
							cssClass = 'noDates';
						}
						if (jQuery.inArray(dateString, noDates) == -1 && jQuery.inArray(dateString, dates) == -1) {
							cssClass = 'available';
						}
					} else {
						if (jQuery.inArray(dateString, dates) >= 0) {
							cssClass = 'ui-datepicker-unselectable ui-state-disabled hasbooking';
						}
						if (jQuery.inArray(dateString, noDates) >= 0) {
							cssClass = 'ui-datepicker-unselectable ui-state-disabled noDates';
						} 
						if (jQuery.inArray(dateString, noDates) == -1 && jQuery.inArray(dateString, dates) == -1) {
							cssClass = 'ui-datepicker-unselectable ui-state-disabled available';
						}
					}
					
					return [ true, cssClass ];
				}
			
			// ON CALENDAR INITIAL LOAD	
			} else {
		        if (day == 6) {     
					if (jQuery.inArray(dateString, dates) >= 0) {
						cssClass = 'hasbooking';
					} 
					if (jQuery.inArray(dateString, noDates) >= 0) {
						cssClass = 'noDates';
					} 
					if (jQuery.inArray(dateString, noDates) == -1 && jQuery.inArray(dateString, dates) == -1) {
						cssClass = 'available';
					}
					return [ true, cssClass ];
		        } else {
			        return [ false ];
		        }
			}

        },
        onChangeMonthYear: function(year, month, inst) {
            selectCurrentWeek();
        }
    });
    
    selectCurrentWeek();
    jQuery('.resultscalendar').slideDown(500);
    
    }
   
};

jQuery(document).ready(function(){
    jQuery('.resultscalendar .ui-datepicker-calendar tr').live('mousemove', function() { jQuery(this).find('td a').addClass('hoverdate'); });
    jQuery('.resultscalendar .ui-datepicker-calendar tr').live('mouseleave', function() { jQuery(this).find('td a').removeClass('hoverdate'); });	
});

function selectError(enddate) {
	jQuery('#room_search_btn').addClass('inactive');
	jQuery('.errorblock').text('You cannot select these weeks as the week of ' + enddate + ' is already sold out.').slideDown(500);
}
function errorHide() {
	jQuery('.errorblock').slideUp(500);
}
// ===============================================================


// Calendar Navigation ===========================================
jQuery(document).ready(function() {
	if (!jQuery('.checkclass').length) {
	    jQuery(document).on('click', '#next, #prev', function(e) {
		    jQuery('#next, #prev').removeClass('disabled');
		    
		    jQuery('.ui-datepicker-'+e.target.id).trigger("click");
		    
		    if (jQuery('.ui-datepicker-'+e.target.id).hasClass('ui-state-disabled')) {
			    jQuery(this).addClass('disabled');
		    }
		}); 
	}
});
// ===============================================================


// Calendar Resize ===============================================
jQuery(document).ready(function($) {

	jQuery('.resultscalendar').datepicker({
    	numberOfMonths: 1
	});

	var debounce;
	jQuery(window).resize(function() {
		clearTimeout(debounce);
		if (jQuery(window).width() <= 991 && jQuery(window).width() >= 767) {
			debounce = setTimeout(function() {
				debounceDatepicker(2)
    		}, 250);
    	} else if (jQuery(window).width() < 767) {
			debounce = setTimeout(function() {
				debounceDatepicker(1)
    		}, 250);	    	
    	} else {
			debounce = setTimeout(function() {
				debounceDatepicker(3)
    		}, 250);
    	}   
	}).trigger('resize');

	function debounceDatepicker(no) {
	    jQuery('.resultscalendar').datepicker('option', 'numberOfMonths', no);
	    prepCal();
	}

});
// ===============================================================


// ROOM FIND AJAX ================================================
jQuery(document).on('click', '#room_search_btn', function(e){
	
	e.preventDefault();
	
	var form = jQuery(this).closest('form');
	var ajaxurl  = form.data('url');
	var retreat = jQuery('#retreatsel').val();
	var guests = jQuery('#guestssel').val();
	var weeks = jQuery('#weeksel').val() * 7;
	var bed_type= jQuery('.roomselect.shown option:selected').text();
	var exclude = form.find('#room_search_rooms').attr('data-excid');
	var arr_date = jQuery('.resultscalendar').attr('data-arrd');
	var chk_date = jQuery('.resultscalendar').attr('data-chkd');
	var dep_date = jQuery('.resultscalendar').attr('data-depd');
	
	jQuery.ajax({
        type: 'POST',
        url: ajaxurl, 
        data: { action: 'RoomSearchAjax', 
	        retreat : retreat, 
	        exclude : exclude,
	        arr_date : arr_date,
	        chk_date : chk_date,
	        dep_date : dep_date,
	        bed_type : bed_type,
	        guests : guests,
	        weeks : weeks,
	    },
		beforeSend: function() {
			jQuery('.roomsearchformcont').addClass('disableblock');
		    jQuery('.loaderimage').fadeIn(100);
			jQuery('#resultswrap').fadeTo("slow" , 0.5);
			jQuery('#room_search_btn').addClass('inactive')
		},
		complete: function(){
			jQuery('.roomsearchformcont').removeClass('disableblock');
		    jQuery('.loaderimage').fadeOut(100);
			setTimeout(function(){
				// jQuery('#resultscontainer').slideDown(1500).fadeTo(1000, 1);
				jQuery('#resultswrap').fadeTo(1000, 1);
			}, 500);
			prepCal();
		},
        success: function(result) {
	        jQuery('.roomlist').html(result);
	        // jQuery('.roomlist').slideDown(1000).fadeTo(500, 1);
	        jQuery('.roomfilterform').removeClass('inactive');
			jQuery('html, body').animate({
	            scrollTop: jQuery('.roomfilterform').offset().top - 300
	        }, 1500);	   
	        jQuery('#step3').removeClass('greyed');     
        }
    });
	
});
// ===============================================================


// Room FILTER operations ========================================
jQuery(document).on('click', '.roomfiltersubmit', function(e){
	
	e.preventDefault();
	
	var filter = jQuery('.roomselect.shown').val();
	var guests = jQuery('#guestssel').val();
	var bed_type = jQuery('.roomselectbed').val();
	
	if (filter == null) {
		jQuery('.roomselect.shown').addClass('errorshown');
		alert('Please select a room configuration in the highlighted field.');
		return;
	}	
	
	if (guests == 1 && bed_type == null) {
		jQuery('.roomselectbed').addClass('errorshown');
		alert('Please select a room configuration in the highlighted field.');
		return;
	}	
	
	if (filter != null && bed_type != null) {
		jQuery('.roomselect.shown, .roomselectbed').removeClass('errorshown');
	}
	
	if (filter > 0) {
		
		jQuery('.roomselect.shown').removeClass('errorshown');
			
		jQuery('.roomlist .productresult').each(function(){
			
			if (guests == 1) {
				if (filter == 1) {
					jQuery(this).data('cat') != 'entire-room' ? jQuery(this).hide().addClass('hiddenroom') : jQuery(this).show().removeClass('hiddenroom');
				} else {
					jQuery(this).data('cat') == 'entire-room' ? jQuery(this).hide().addClass('hiddenroom') : jQuery(this).show().removeClass('hiddenroom');
				}
				
				if (bed_type != '' || bed_type != undefined || bed_type != 'none' || bed_type != null) {
					var beds = jQuery(this).find('.resultinfo').data('bedconfig');
					var beds_array = beds.split(',');
					var bed_exists = beds_array.includes(bed_type);		
					bed_exists ? jQuery(this).addClass('hasbedtype').removeClass('nobedtype').show() : jQuery(this).removeClass('hasbedtype').addClass('nobedtype').hide();
				}
				
				if (bed_type == '' || bed_type == undefined || bed_type == 'none' || bed_type == null) {
					jQuery(this).removeClass('hasbedtype').removeClass('nobedtype').show();
				}
			}
			
			if (guests > 1) {
				var beds = jQuery(this).find('.resultinfo').data('bedconfig');
				var beds_array = beds.split(',');
				if (beds_array.includes('double') || beds_array.includes('twin')){
					jQuery(this).addClass('hasbedtype').removeClass('nobedtype').show();
				} else {
					jQuery(this).removeClass('hasbedtype').addClass('nobedtype').hide();
				}
			}
		
			jQuery('#resultscontainer').slideDown(1500).fadeTo(1000, 1);
			jQuery('#resultswrap').fadeTo(1000, 1);
			jQuery('.roomlist').slideDown(1000).fadeTo(500, 1);
			
		});
		
	}
	
	jQuery('.bed_type').each(function(){
		var roomconfig = jQuery('.roomselect.shown option:selected').text();
		jQuery(this).val(roomconfig);
	});
	
	// Adds Error Message Once FIltered
	var totalroomcont = 0;
	var roomCheck = 0;
	jQuery('.roomscont').each(function(){
		jQuery(this).addClass('roomshidden');
		var c = 0;
		var i = 0;
		var f = 0;
		jQuery(this).find('.productresult').each(function(){
			if (!jQuery(this).hasClass('hiddenroom')) {
				i++;
			}
			if (!jQuery(this).hasClass('nobedtype')) {
				f++;
			}
			c++;
		});
		
		if(i == 0 || f == 0) {
			jQuery(this).hide().addClass('roomshidden');
			jQuery(this).first().find('.roomwrap').slideUp(0);
			jQuery(this).first().find('i').removeClass('iconhidden');
			if (jQuery('.norooms').length < 1) {
				jQuery(this).find('.roomwrap').append('<div class="norooms">Sorry, no rooms are available for your selected dates</div>');
			}
		} else {
			jQuery(this).show();
			jQuery('.norooms').remove();
		}
		
		if (i > 0 && f == 0) {
			roomCheck++;
		} else {
			jQuery(this).show();
			jQuery('.norooms').remove();
		}
			
		jQuery('.roomscont').first().removeClass('roomshidden');
		jQuery('.roomscont').first().find('.roomwrap').slideDown(500);
		jQuery('.roomscont').first().find('i').addClass('iconhidden');
		
		totalroomcont++;
	});	
	
	if (totalroomcont == 1) {
		jQuery('.roomscont').slideDown(50);
	}
	
	if (totalroomcont - roomCheck == 0) {
		// jQuery('<div class="norooms">Sorry, no rooms are available for your selected dates</div>').insertBefore('.roomlist');			 
	}
	
	
	jQuery('html, body').animate({
            scrollTop: jQuery('.productscontainer').offset().top - 150
    }, 1500);
	
});

// Changes Room Filter Depending on Guests
jQuery('#guestssel').on('change', function(){
	var refguests = parseInt(jQuery('#guestssel').val());
	jQuery('.roomselect').removeClass('shown').val(0);
	jQuery('#roomselect'+refguests).addClass('shown');
});
// ===============================================================


// General Operations ============================================
jQuery(document).ready(function(){
	//Scroll To Top On Page Load
	jQuery('html, body').animate({
        scrollTop: jQuery('body').offset().top
    }, 1000);
	
	// Show Warning If Clicking Disabled Calendar
	jQuery(document).on('click', '.initialload td a', function(){
		alert('Please Select Your Retreat First');
	});
	
	// Room Divider Accordion
	jQuery(document).on('click', '.roomtitlecont', function(){
		jQuery('.roomtitlecont').not(this).find('i').removeClass('iconhidden').closest('.roomscont').addClass('roomshidden').find('.roomwrap').slideUp(500);
		jQuery(this).find('i').addClass('iconhidden').closest('.roomscont').removeClass('roomshidden').find('.roomwrap').slideDown(500);
	});	
	
	// Show Modal Popup for Room
	jQuery(document).on('click', '.roommoreinfo', function(){
		jQuery(this).closest('.resultinfo').find('.modalroom').removeClass('hidden');
	});
	
	// Change Modal Image On Select/Click
	jQuery(document).on('click', '.galleryimage', function(){
		var image = jQuery(this).data('image');
		jQuery(this).closest('.modalwindow').find('.resultimg').css('cssText', 'background-image: url("'+image+'")');
	});
	
	// Close Modal Window
	jQuery(document).on('click', '.modaloverlay, .modalclose', function(){
		jQuery(this).closest('.resultinfo').find('.modalroom').addClass('hidden');
	});

	// When Changing Inital Form Options, Reset Entire Form
	jQuery('.roomsearchform select').on('change', function(){
		resetSys();
	});
	
	jQuery('.roomselectbed').show();	

});

function resetSys(){
	jQuery('.resultscalendar').datepicker('destroy');
	jQuery('#room_search_btn').addClass('inactive')
	jQuery('.resultscalendar').addClass('initialload');
	jQuery.each(jQuery('.resultscalendar').data(), function (i) {
	    jQuery('.resultscalendar').removeAttr('data-' + i);
	});
	setTimeout(function(){
		jQuery('.resultscalendar').datepicker('refresh');
	}, 500);
	jQuery('#step2, #step3').addClass('greyed');
	jQuery('.roomfilterform').addClass('inactive');
	jQuery('.roomfilterform select').val(0);
	
	jQuery('.roomlist').fadeTo(300, 0).slideUp(300);
	
	jQuery('.resultscalendar').datepicker({
		minDate: new Date(),
		maxDate: '+6m',
	    showButtonPanel: false,
	    firstDay: 6,
		numberOfMonths: 3,
        showOtherMonths: false,
        selectOtherMonths: false,
        beforeShowDay: function(date) {
			return [ false, 'ui-state-disabled ui-datepicker-unselectable' ];        
        }
	});	
	
	if (jQuery('#guestssel').val() == 1) {
		jQuery('.roomselectbed').show();	
	} else {
		jQuery('.roomselectbed').hide();
	}
}
// ===============================================================




































jQuery(document).ready(function(){

	// JUICY OASIS

	var JOdates = dateArrCalc("23-04-2019", "22-05-2019");
	var JOnoDates = dateArrCalc("01-01-2019", "22-04-2019");
	var JOdateadditional = ["01-06-2019"];
	var JOnodateadditional = ["02-06-2019"];
	
	jQuery.each( JOdateadditional, function( i, val ) {
		var fJOdateadditional = val;
		JOdates.push(fJOdateadditional);
	});	
	
	jQuery.each( JOnodateadditional, function( i, val ) {
		var fJOnodateadditional = val;
		JOnoDates.push(fJOnodateadditional);
	});	
	
	
	jQuery('.resultscalendarholdJO').datepicker( {
		minDate: new Date(),
		maxDate: '+6m',
	    showButtonPanel: false,
	    firstDay: 6,
		numberOfMonths: 2,
        showOtherMonths: false,
        selectOtherMonths: false,
		beforeShowDay: function(date) {

			var dateString = jQuery.datepicker.formatDate('dd-mm-yy', date);
						
			if (jQuery.inArray(dateString, JOdates) >= 0) {
				cssClass = 'ui-datepicker-unselectable hasbooking';
			}
			if (jQuery.inArray(dateString, JOnoDates) >= 0) {
				cssClass = 'ui-datepicker-unselectable noDates';
			} 
			if (jQuery.inArray(dateString, JOnoDates) == -1 && jQuery.inArray(dateString, JOdates) == -1) {
				cssClass = 'ui-datepicker-unselectable available';
			}
			
			return [ true, cssClass ];
		}
	});
	
	
	
	// JUICY MOUNTAIN
	
	var JMdates = dateArrCalc("01-04-2019", "22-05-2019");
	var JMnoDates = dateArrCalc("01-01-2019", "31-03-2019");
	
	var JMdateadditional = ["01-06-2019"];
	var JMnodateadditional = ["02-06-2019"];
	
	jQuery.each( JMdateadditional, function( i, val ) {
		var fJMdateadditional = val;
		JMdates.push(fJMdateadditional);
	});	
	
	jQuery.each( JMnodateadditional, function( i, val ) {
		var fJMnodateadditional = val;
		JMnoDates.push(fJMnodateadditional);
	});	
		
	jQuery('.resultscalendarholdJM').datepicker( {
		minDate: new Date(),
		maxDate: '+6m',
	    showButtonPanel: false,
	    firstDay: 6,
		numberOfMonths: 2,
        showOtherMonths: false,
        selectOtherMonths: false,
		beforeShowDay: function(date) {

			var dateString = jQuery.datepicker.formatDate('dd-mm-yy', date);
						
			if (jQuery.inArray(dateString, JMdates) >= 0) {
				cssClass = 'ui-datepicker-unselectable hasbooking';
			}
			if (jQuery.inArray(dateString, JMnoDates) >= 0) {
				cssClass = 'ui-datepicker-unselectable noDates';
			} 
			if (jQuery.inArray(dateString, JMnoDates) == -1 && jQuery.inArray(dateString, JMdates) == -1) {
				cssClass = 'ui-datepicker-unselectable available';
			}
			
			return [ true, cssClass ];
		}
	});	
	
	
	
	function dateArrCalc(startDate, endDate) {
		var fromDate =  new Date(startDate.replace( /(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3"));
		var toDate = new Date(endDate.replace( /(\d{2})-(\d{2})-(\d{4})/, "$2/$1/$3"));
		var currentDate = new Date(fromDate);
		var between = [];
		
		while (currentDate <= toDate) {
		    between.push(new Date(currentDate));
		    currentDate.setDate(currentDate.getDate() + 1);
		}
		var fdates = [];
		jQuery.each( between, function( i, val ) {
			var fdate = jQuery.datepicker.formatDate('dd-mm-yy', val);
			fdates.push(fdate);
		});	
		
		return fdates;
	}
	
	
	
    jQuery(document).on('click', '.next, .prev', function(e) {
	    var parentclass = jQuery(this).closest('#resultscontainer').attr('class');
	    jQuery('.' + parentclass + ' .next, .' + parentclass + ' .prev').removeClass('disabled');
	    jQuery('.' + parentclass + ' .ui-datepicker-'+e.target.id).trigger("click");
	    if (jQuery('.' + parentclass + ' .ui-datepicker-'+e.target.id).hasClass('ui-state-disabled')) {
		    jQuery(this).addClass('disabled');
	    }
	}); 	
	
});