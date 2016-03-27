(function( $ ) {
	'use strict';

	function hookExtendForms() {
		$( '.draftsforfriends .extend' ).click(function(event) {
			event.preventDefault();
			$( this ).hide();
			$( this ).next( 'form' ).show();
		});

		$( '.draftsforfriends .cancel' ).click(function(event) {
			event.preventDefault();
			$( this ).parent().prev( '.extend' ).show();
			$( this ).parent().hide();
		});
	}

	$( document ).ready(hookExtendForms);

})( jQuery );
