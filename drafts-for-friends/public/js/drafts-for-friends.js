(function( $ ) {
	'use strict';

	$('form.draftsforfriends-extend').hide();
	$('a.draftsforfriends-extend').show();
	$('a.draftsforfriends-extend-cancel').show();
	$('a.draftsforfriends-extend-cancel').css('display', 'inline' );

	window.draftsforfriends = {
		toggle_extend: function(key) {
			$('#draftsforfriends-extend-form-'+key).show();
			$('#draftsforfriends-extend-link-'+key).hide();
			$('#draftsforfriends-extend-form-'+key+' input[name="expires"]').focus();
		},
		cancel_extend: function(key) {
			$('#draftsforfriends-extend-form-'+key).hide();
			$('#draftsforfriends-extend-link-'+key).show();
		}
	};
})( jQuery );
