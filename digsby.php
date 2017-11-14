<?php

global $checked_windows_list;
$checked_windows_list = [];

sleep( 5 );

// continue looping forever and ever
while ( true ) {
	reset_checked();

	foreach ( get_active_windows_ids() as $w_id ) {
		// only check newly found windows
		if ( ! isset( $checked_windows_list[ $w_id ] ) ) {
			sticky( $w_id );
		}
		// this window is checked
		$checked_windows_list[ $w_id ] = true;
	}
	unset( $w_id ); // preserve memory

	delete_unchecked();

	sleep( 3 );
}

/**
 * @return array
 */
function get_active_windows_ids() {
	$windows_list = trim( shell_exec( 'wmctrl -l' ) );
	preg_match_all( '/0x[a-zA-Z0-9]*/', $windows_list, $window_ids, PREG_SET_ORDER, 0 );

	$return = [];
	foreach ( $window_ids as $window_id ) {
		$return[] = $window_id[0];
	}

	return $return;
}

/**
 * set all checked windows to false
 * So then can be unset later
 */
function reset_checked() {
	global $checked_windows_list;

	foreach ( $checked_windows_list as $key => $val ) {
		$checked_windows_list[ $key ] = false;
	}
}

/**
 * Unset all unchecked windows (to preserve memory)
 */
function delete_unchecked() {
	global $checked_windows_list;

	foreach ( $checked_windows_list as $key => $val ) {
		if ( ! $val ) {
			unset( $checked_windows_list[ $key ] );
		}
	}
}

/**
 * set windows to sticky
 *
 * @param string $w_id
 */
function sticky( $w_id ) {

	$window_type_grep = trim( shell_exec( "xprop -id {$w_id} | grep '^_NET_WM_WINDOW_TYPE'" ) );
	$window_type      = explode( " = ", trim( $window_type_grep ) );

	// skip these window classes
	if ( in_array( $window_type[1], [ '_NET_WM_WINDOW_TYPE_DOCK', '_NET_WM_WINDOW_TYPE_DESKTOP' ] ) ) {
		return;
	}


	shell_exec( "wmctrl -b add,sticky -ir {$w_id}" );
}