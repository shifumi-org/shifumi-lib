window.onload = function() {
	var createHiddenField = function( name, value ) {
		var input = document.createElement( 'input' );
		input.type = 'hidden';
		input.name = name;
		input.value = value;
		return input;
	};

	var showExtraPriceTypeFields = function( priceType ) {
		document.getElementById( 'ld_cw_paynow_div' ).style.display = 'none';
		document.getElementById( 'ld_cw_subscribe_div' ).style.display = 'none';

		if ( priceType === 'paynow' ) {
			document.getElementById( 'ld_cw_paynow_div' ).style.display = 'block';
		} else if ( priceType === 'subscribe' ) {
			document.getElementById( 'ld_cw_subscribe_div' ).style.display = 'block';
		}
	};
	var coursePriceSelected = document.querySelector( 'input[name="ld_cw_course_price_type"]:checked' );
	if ( coursePriceSelected ) {
		showExtraPriceTypeFields( coursePriceSelected.value );
	}

	// show extra fields when the user selects a course price type
	var coursePriceTypeRadios = document.getElementsByName( 'ld_cw_course_price_type' );
	if ( coursePriceTypeRadios.length > 0 ) {
		for ( var i = 0; i < coursePriceTypeRadios.length; i++ ) {
			coursePriceTypeRadios[i].addEventListener( 'change', function( event ) {
				showExtraPriceTypeFields( event.target.value );
			} );
		}
	}

	// billing cycle control
	var billingCycle = document.getElementById( 'ld_cw_course_price_billing_interval' );
	var billingCycleNumber = document.getElementById( 'ld_cw_course_price_billing_number' );
	var maxValue = 0;
	if ( billingCycle && billingCycleNumber ) {
		billingCycle.addEventListener( 'change', function( event ) {
			switch ( event.target.value ) {
				case 'D':
					maxValue = ldCourseWizard.valid_recurring_paypal_day_max;
					break;

				case 'W':
					maxValue = ldCourseWizard.valid_recurring_paypal_week_max;
					break;

				case 'M':
					maxValue = ldCourseWizard.valid_recurring_paypal_month_max;
					break;

				case 'Y':
					maxValue = ldCourseWizard.valid_recurring_paypal_year_max;
					break;

				default:
					maxValue = 0;
					break;
			}
			if ( billingCycleNumber.value > maxValue ) {
				billingCycleNumber.value = maxValue;
			}
			billingCycleNumber.setAttribute( 'max', maxValue );
		} );
	}

	// add event listener for the submit button
	var createCourseButton = document.getElementById( 'ld_cw_create_course_btn' );
	if ( createCourseButton ) {
		createCourseButton.addEventListener( 'click', function() {
			var form = document.getElementById( 'ld_cw_create_course_form' );
			var courseType = document.querySelector( 'input[name="ld_cw_course_price_type"]:checked' ).value;
			if ( form ) {
				form.appendChild(
					createHiddenField( 'course_price_type'
						, courseType )
				);
				form.appendChild(
					createHiddenField( 'course_disable_lesson_progression'
						, document.querySelector( 'input[name="ld_cw_course_progression"]:checked' ).value )
				);

				if ( courseType === 'paynow' ) {
					form.appendChild(
						createHiddenField( 'course_price'
							, document.getElementById( 'ld_cw_course_price_type_paynow_price' ).value )
					);
				} else if ( courseType === 'subscribe' ) {
					form.appendChild(
						createHiddenField( 'course_price'
							, document.getElementById( 'ld_cw_course_price_type_subscribe_price' ).value )
					);
					form.appendChild(
						createHiddenField( 'course_price_billing_number'
							, document.getElementById( 'ld_cw_course_price_billing_number' ).value )
					);
					form.appendChild(
						createHiddenField( 'course_price_billing_interval'
							, document.getElementById( 'ld_cw_course_price_billing_interval' ).value )
					);
				}
				form.submit();
			}
		} );
	}
};
