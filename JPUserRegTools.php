<?php
/**
 * @package JPUserRegistrationBlacklist
 * @version 1.3
 */
/*
Plugin Name: JP User Registration Blacklist
Plugin URI: 
Description: Apply comment IP and e-mail address blacklist rules to user registrations.  Puts user's IP in user's website field.  Solve a simple math problem to register.
Author: Justin Parr
Version: 1.3
Author URI: http://justinparrtech.com
*/


// *************** ADD A MATH PROBLEM TO THE USER REG FORM ***************
add_action('register_form','JP_verifyMath_register_form');
function JP_verifyMath_register_form (){
	?>
	<p>
	<label for="mathproblem">Solve: 6+3<br />
	<input type="text" name="mathproblem" id="mathproblem" class="input" value="5" size="25" /></label>
	</p>
	<?php
}

// **************** PREVENT REGISTRATION IF USER FAILS MATH PROBLEM *************
add_filter('registration_errors', 'JP_verifyMath_registration_errors', 10, 3);
function JP_verifyMath_registration_errors ($errors, $sanitized_user_login, $user_email) {
	if ( $_POST['mathproblem']!='9' )
	$errors->add( 'first_name_error', __('You suck at math!  Please try again.','mydomain') );
	return $errors;
}


// **************** PREVENT REGISTRATION IF USER IP IN BLACKLIST *************
add_filter('registration_errors', 'JP_verifyIP_registration_errors', 10, 3);
function JP_verifyIP_registration_errors ($errors, $sanitized_user_login, $user_email) {
	if ( wp_blacklist_check('', $user_email, '', '', $_SERVER['REMOTE_ADDR'], '') )
	$errors->add( 'first_name_error', __('There was a technical problem.  Please try again.','mydomain') );
	return $errors;
}

// **************** SET WEBSITE (URL) TO IP ADDRESS *************
add_action('user_register', 'JP_addIP_user_register');
function JP_addIP_user_register ($user_id) {
	//update_user_meta($user_id, 'url', $_SERVER['REMOTE_ADDR']);
	wp_update_user( array( 'ID' => $user_id, 'user_url' => $_SERVER['REMOTE_ADDR'] ) );
}



?>
