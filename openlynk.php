<?php
/* Plugin Name: Openlynk URL Plug-in
Plugin URI: http://openlynk.org
Description: A plugin to allow parameters to be passed in the URL and recognized by WordPress
Author: Renaud Boisjoly
Version: 1.0
*/

/*
Add Openlynk callbackType query parameter so we can intercept them
*/

add_filter('query_vars', 'parameter_queryvars' );
function parameter_queryvars( $qvars )
{
	$qvars[] = 'callbackType';
	return $qvars;
}

/*
Add Openlynk function to display login form in picker (optionally) and return user to picker page
Any value passed to the function will force the Logout link to be displayed. Useful for debugging
*/

function inline_login_form($logout) {

	if ( is_user_logged_in()) {
		$url=wp_logout_url(). '&amp;redirect_to='.get_permalink();
		if ($logout!='') {
			echo('<div class="logout"><a href="'.$url.'">Log-out</a></div>');
		}
		return '';
	}

	return wp_login_form();
}
?>