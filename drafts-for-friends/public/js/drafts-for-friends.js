(function( $ ) {
	'use strict';

	var SECONDS_PER_MINUTE = 60;
	var SECONDS_PER_HOUR = 60 * SECONDS_PER_MINUTE;
	var SECONDS_PER_DAY = 24 * SECONDS_PER_HOUR;
	var HOURS_PER_DAY = 24;

	function replaceTemplate( template, values ) {
		var res = template;

		if ( res ) {
			Object.keys( values ).forEach(function( k ) {
				if ( values[k] ) {
					var regexp = new RegExp('\{' + k + '\}', 'g');
					res = res.replace( regexp, values[k] );
				}
			});
		}

		return res;
	}

	function _n( key, domain, num, values ) {
		var l10n = window[domain];
		var localizedMessage;

		if ( l10n ) {
			var quantifier = (1 === num) ? 'singular' : 'plural';
			var template = l10n[key][quantifier];

			localizedMessage = replaceTemplate( template, values );
		}

		return localizedMessage;
	}

	function __( key, domain, values ) {
		var l10n = window[domain];
		var localizedMessage;

		if ( l10n ) {
			var template = l10n[key];

			localizedMessage = replaceTemplate( template, values );
		}

		return localizedMessage;
	}

	function formatTime( time ) {
		var parts = [];
		var secs  = time % 60;
		var mins  = Math.floor( time / SECONDS_PER_MINUTE ) % SECONDS_PER_MINUTE;
		var hours = Math.floor( time / SECONDS_PER_HOUR ) % HOURS_PER_DAY;
		var days  = Math.floor( time / SECONDS_PER_DAY );

		if ( secs > 0 ) {
			parts.push( _n( 'seconds', 'draftsforfriends_l10n', secs, {num: secs} ) );
		}
		if ( mins > 0 ) {
			parts.unshift( _n( 'minutes', 'draftsforfriends_l10n', mins, {num: mins} ) );
		}
		if ( hours > 0 ) {
			parts.unshift( _n( 'hours', 'draftsforfriends_l10n', hours, {num: hours} ) );
		}
		if ( days > 0 ) {
			parts.unshift( _n( 'days', 'draftsforfriends_l10n', days, {num: days} ) );
		}
		if ( parts.length > 1 ) {
			// only keep 2 most significant parts, i.e. 2 days and 3 hours, or 4 hours 5 minutes.
			return __( 'time', 'draftsforfriends_l10n', {first: parts[0], second: parts[1]} );
		}

		return parts[0];
	}

	function markInvalid( element ) {
		element.addClass( 'invalid ');
		element.change(function() {
			if ( '' !== element.val().trim() ) {
				element.removeClass( 'invalid' );
			}
		});
	}

	function checkNotEmpty( element ) {
		if ( '' === element.val() ) {
			markInvalid( element );
			return false;
		}
		return true;
	}

	function checkIsNumeric( element ) {
		if ( isNaN( parseInt( element.val() ) ) ) {
			markInvalid( element );
			return false;
		}
		return true;
	}

	function init() {
		$( '.draftsforfriends .extend' ).click(function( event ) {
			event.preventDefault();
			$( this ).hide();
			$( this ).next( 'form' ).show();
		});

		$( '.draftsforfriends .cancel' ).click(function( event ) {
			event.preventDefault();
			$( this ).parent().prev( '.extend' ).show();
			$( this ).parent().hide();
		});

		$( '.draftsforfriends .timer' ).each(function() {
			var label = $( this );
			var time = label.data('expire');

			function updateTime( elem, time ) {
				if ( time > 0 ) {
					var timeout = 1000;
					var period = 1;

					if ( time > SECONDS_PER_HOUR ) {
						timeout *= SECONDS_PER_MINUTE;
						period *= SECONDS_PER_MINUTE;
					}

					setTimeout(function() {
						elem.text( formatTime( time ) );
						updateTime( elem, time - period );
					}, timeout);
				} else {
					elem.text( 'Expired' );
					elem.addClass( 'expired' )
				}
			}

			updateTime( label, time );
		});

		$( '#draftsforfriends-share' ).submit(function( event ) {
			var shareForm = $( this );
			var postSelect = shareForm.find( "select[name='post_id']" );
			var timeTextField = shareForm.find( "input[name='expires']" );

			return checkNotEmpty( postSelect ) &&
				checkNotEmpty( timeTextField ) &&
				checkIsNumeric( timeTextField );
		});

		$( '.draftsforfriends .actions form' ).each(function() {
			var form = $( this );
			var timeTextField = form.find( "input[name='expires']" );

			form.submit(function( event ) {
				return checkNotEmpty( timeTextField ) && checkIsNumeric( timeTextField );
			});
		});
	}

	$( document ).ready(init);

})( jQuery );
