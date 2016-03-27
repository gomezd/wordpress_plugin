<?php

/**
 * Converts the specified time amount in unit to seconds.
 *
 * @since 0.0.1
 *
 * @package drafts-for-friends
 * @subpackage drafts-for-friends/includes
 *
 * @param int    $time The time amount to convert
 * @param string $unit The unit of the amount.
 *                     One of ['s', 'm', 'h', 'd'] (seconds, minutes, hours, days)
 * @return string Time in seconds
 */
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

/**
 * Formats a time interval from now() to $ime
 * into a human readable form with the 2 most significative values, if exist.
 * e.g.:
 *   $time - now():
 *       62 -> 1 minute 2 seconds
 *       7390 -> 2 hours and 3 minutes  (10 seconds ommited)
 *       273790 -> 3 days and 4 hours (minutes and seconds ommited)
 *
 * @since 0.0.1
 *
 * @package drafts-for-friends
 * @subpackage drafts-for-friends/includes
 *
 * @param  int    $time Timestamp to calculate interval from now()
 * @return string Formatted interval
 */
function format_interval( $time ) {
	$now = new DateTime();
	$exp = new DateTime();
	$exp->setTimestamp( $time );

	if ( $exp < $now ) {
		return __( 'Expired' );
	}

	$diff = $now->diff( $exp );
	$format = array();

	if ( $diff->h > 0 ) {
		$format[] = sprintf( _n( '%d hour', '%d hours', $diff->h, 'draftsforfriends' ), $diff->h );
	}
	if ( $diff->i > 0 ) {
		$format[] = sprintf( _n( '%d minute', '%d minutes', $diff->i, 'draftsforfriends' ), $diff->i );
	}
	if ( $diff->s > 0 ) {
		$format[] = sprintf( _n( '%d second', '%d seconds', $diff->s, 'draftsforfriends' ), $diff->s );
	}

	if ( count( $format ) > 1 ) {
		/* translators: expiration time e.g. 3 days and 5 hours */
		return sprintf( __( '%s and %s', 'draftsforfriends' ), $format[0], $format[1]);
	}

	return $format[0];
}

?>
