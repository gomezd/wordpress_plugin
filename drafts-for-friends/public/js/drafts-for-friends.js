(function( $ ) {
	'use strict';

	var SECONDS_PER_MINUTE = 60;
	var SECONDS_PER_HOUR = 60 * SECONDS_PER_MINUTE;
	var SECONDS_PER_DAY = 24 * SECONDS_PER_HOUR;
	var HOURS_PER_DAY = 24;

	function formatTime( time ) {
		var parts = [];
		var secs  = time % 60;
		var mins  = Math.floor( time / SECONDS_PER_MINUTE ) % SECONDS_PER_MINUTE;
		var hours = Math.floor( time / SECONDS_PER_HOUR ) % HOURS_PER_DAY;
		var days  = Math.floor( time / SECONDS_PER_DAY );

		if ( secs > 0 ) {
			parts.push( secs + ' seconds' );
		}
		if ( mins > 0 ) {
			parts.unshift( mins + ' minutes' );
		}
		if ( hours > 0 ) {
			parts.unshift( hours + ' hours' );
		}
		if ( days > 0 ) {
			parts.unshift( days + ' days' );
		}
		if ( parts.length > 1 ) {
			// only keep 2 most significant parts, i.e. 2 days and 3 hours, or 4 hours 5 minutes.
			parts = [ parts[0], 'and', parts[1] ];
		}

		return parts.join(' ');
	}

	function hookExtendForms() {
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

		$( '.draftsforfriends .timer' ).each(function () {
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
	}

	$( document ).ready(hookExtendForms);

})( jQuery );
