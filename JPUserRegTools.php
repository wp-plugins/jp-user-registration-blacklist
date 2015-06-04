<?php
/**
 * @package JPUserRegistrationBlacklist
 * @version 1.7.1
 */
/*
Plugin Name: JP User Registration Blacklist
Plugin URI: https://wordpress.org/plugins/jp-user-registration-blacklist
Description: Apply comment IP and e-mail address blacklist rules to user registrations.  Puts user's IP in user's website field.  Solve a simple math problem to register.
Author: Justin Parr
Version: 1.7.1
Author URI: http://justinparrtech.com
*/

// **************** SETTINGS INCLUDE *************************
require_once basename( __FILE__,'.php').'_Settings.php';


// **************** READ SETTINGS ****************************
$JPURB_Opts=get_option(JPURB_option_name(), JPURB_default_all() );


// **************** SEED FOR MATH PROBLEM ************************
function JP_seed () {
	global $JPURB_Opts;

	return $JPURB_Opts['seed'];
}

// *************** ADD A MATH PROBLEM TO THE USER REG FORM ***************
add_action('register_form','JP_verifyMath_register_form');
function JP_verifyMath_register_form (){
	global $JPURB_Opts;


	$c=$_POST['JPREG'];
	if ($c=="") {
		$a=mt_rand(101,900);
		$b=mt_rand(1,10);
		$c=$a+$b+JP_seed();
	}
	else {
		$b=mt_rand(1,10);
		$a=$c-JP_Seed()-$b;
	}

	$d=$a+$b;
	$f=$JPURB_Opts['MathProblemFieldName'];

	if($_POST[$f]!=$d) {
		?>
		<p>
		<label for="<?php echo("$f"); ?>">Solve: <?php echo("Add $a and $b "); ?><br />
		<input type="text" name="<?php echo("$f"); ?>" id="<?php echo("$f"); ?>" class="input" value="0" size="25" /></label>
		<input type="hidden" name="JPREG" value="<?php echo("$c"); ?>" />
		</p>
		<?php
	}
	else {
		?>
		<input type="hidden" name="<?php echo("$f"); ?>" value="<?php echo("$d"); ?>" />
		<input type="hidden" name="JPREG" value="<?php echo("$c"); ?>" />
		<?php
	}
}

// **************** PREVENT REGISTRATION IF USER FAILS MATH PROBLEM *************
add_filter('registration_errors', 'JP_verifyMath_registration_errors', 10, 3);
function JP_verifyMath_registration_errors ($errors, $sanitized_user_login, $user_email) {
	global $JPURB_Opts;

	$f=$JPURB_Opts['MathProblemFieldName'];
	$m=$JPURB_Opts['MathReject'];

	if ( $_POST[$f]!=($_POST['JPREG']-JP_seed()) )
		$errors->add( 'first_name_error', __($m,'mydomain') );

	return $errors;
}


// *************** ADD EMAIL TOKEN TO REGISTRATION FORM ***************
add_action('register_form','JP_verifyEmail_register_form');
function JP_verifyEmail_register_form (){
	global $JPURB_Opts;

	if($_POST["TTT"]=="") {
		$a=mt_rand(1,9);
		for ($i=1;$i<8;$i++)
			$a=$a.mt_rand(0,9);
	}
	else {
		$f=$JPURB_Opts['MathProblemFieldName'];
		if ( $_POST[$f]==($_POST['JPREG']-JP_seed()) ) {
			$a=$_POST["TTT"];
			?>
			<p>
			<label for="TOKEN">Token (check your e-mail): <br />
			<input type="text" name="TOKEN" id="TOKEN" class="input" value="" size="25" /></label>	
			</p>
			<?php
		}
	}
	?>
	<input type="hidden" name="TTT" value="<?php echo("$a"); ?>" />
	<?php
}


// **************** CHECK TOKEN AND SEND E_MAIL *************
add_filter('registration_errors', 'JP_verifyEmail_registration_errors', 10, 3);
function JP_verifyEmail_registration_errors ($errors, $sanitized_user_login, $user_email) {
	$u=get_bloginfo('url');
	$b=get_bloginfo();
	$h=array("content-type: text/html");
	$s='['.$b.'] User Activation Token';
	$m='Thanks for registering with us!<BR><BR>';
	$m=$m.'Here is your token: <B>'.substr($_POST["TTT"],2,4).'</B><BR><BR>';
	$m=$m.'Enter this in the "Token" field, and click REGISTER.<BR><BR>';
	$m=$m.'If you did NOT sign up for '.$b.', <BR>IMMEDIATELY CHANGE YOUR E-MAIL PASSWORD<BR>';
	$m=$m.'<A HREF="'.$u.'">'.$u.'</A>';

	
	if($errors->get_error_code() == '')
		if(substr($_POST["TTT"],2,4)!=$_POST["TOKEN"]) {
			wp_mail( $user_email, $s, $m, $h );
			$errors->add( 'mail_auth_error', "An e-mail has been sent to $user_email. Check your e-mail for a token code, then enter the token code below. - " );
		};

	return $errors;
}


// **************** PREVENT REGISTRATION IF USER IP IN BLACKLIST *************
add_filter('registration_errors', 'JP_verifyIP_registration_errors', 10, 3);
function JP_verifyIP_registration_errors ($errors, $sanitized_user_login, $user_email) {
	global $JPURB_Opts;

	$m=$JPURB_Opts['ACLReject'];

	if ( wp_blacklist_check('', $user_email, '', '', $_SERVER['REMOTE_ADDR'], '') )
		$errors->add( 'first_name_error', __($m,'mydomain') );

	return $errors;
}

// **************** SET WEBSITE (URL) TO IP ADDRESS *************
add_action('user_register', 'JP_addIP_user_register');
function JP_addIP_user_register ($user_id) {
	//update_user_meta($user_id, 'url', $_SERVER['REMOTE_ADDR']);
	wp_update_user( array( 'ID' => $user_id, 'user_url' => $_SERVER['REMOTE_ADDR'] ) );
}


// *************** ADD SETTINGS LINK TO PLUGINS PAGE **************
$plugin = plugin_basename(__FILE__); 
if ( is_admin() )
	add_filter("plugin_action_links_$plugin", 'JPURB_SettingsLink' );


// ************* INSTANTIATE SETTINGS PAGE ******************
if( is_admin() )
	$my_settings_page = new JPUserRegToolsSettingsPage();


// ************* ACTIVATION HOOK ******************
function JPURB_activate() {
	$JPURB_Opts=get_option(JPURB_option_name(), JPURB_default_all() );

	update_option(JPURB_option_name(),$JPURB_Opts);
}
register_activation_hook( __FILE__, 'JPURB_activate' );

?>