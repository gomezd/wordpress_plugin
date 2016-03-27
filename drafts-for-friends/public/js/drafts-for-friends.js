(function( $ ) {
	'use strict';

	function formatTime( time ) {
		var parts = [];
		var secs  = time % 60;
		var mins  = Math.floor( time / 60 ) % 60;
		var hours = Math.floor( time / (60 * 60) ) % 24;
		var days  = Math.floor( time / (60 * 60 * 24) );

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
			parts.splice( parts.length - 1, 0, 'and');
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
					setTimeout(function() {
						elem.text( formatTime( time ) );
						updateTime( elem, --time );
					}, 1000);
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
