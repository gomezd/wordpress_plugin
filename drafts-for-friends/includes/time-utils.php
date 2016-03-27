<?php

function calculate_time( $time, $unit ) {
	$mults = array(
		's' => 1,
		'm' => 60,
		'h' => 3600,
		'd' => 24*3600
	);
	$multiply = $mults[$unit] ? $mults[$unit] : 60;

	return $time * $multiply;
}

function format_interval( $time ) {
	$now = new DateTime();
	$exp = new DateTime();
	$exp->setTimestamp( $time );

	if ( $exp < $now ) {
		return __( 'Expired' );
	}

	$diff = $now->diff( $exp );
	$format = array();

	if ( $diff->h !== 0 ) {
		$format[] = sprintf( _n( '%d hour', '%d hours', $diff->h, 'draftsforfriends' ), $diff->h );
	}
	if ( $diff->i !== 0 ) {
		$format[] = sprintf( _n( '%d minute', '%d minutes', $diff->i, 'draftsforfriends' ), $diff->i );
	}
	if ( ! count( $format ) ) {
		return __( 'Less than a minute' );
	} else {
		$format[] = sprintf( __( '%d seconds', 'draftsforfriends' ), $diff->s );
	}

	if ( count( $format ) > 1 ) {
		/* translators: expiration time e.g. 3 hours and 27 minutes or 5 minutes and 10 seconds */
		return sprintf( __( '%s and %s', 'draftsforfriends' ), $format[0], $format[1] );
	}

	return $format[0];
}

?>
