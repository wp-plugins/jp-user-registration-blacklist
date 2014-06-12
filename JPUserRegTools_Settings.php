<?php

// *** Default field values ***
function JPURB_default_seed()
{
	return(mt_rand(100,999));
}

function JPURB_default_MathReject()
{
	return "You suck at math!";
}

function JPURB_default_ACLReject()
{
	return "There was a technical problem.  Please try again later.";
}

function JPURB_default_MathProblemFieldName()
{
	$math='Ma'.mt_rand(10,99).'th';
	return $math;
}

function JPURB_default_all()
{
	$opt=array(
		'seed'			=>	JPURB_default_seed(),
		'MathReject'		=>	JPURB_default_MathReject(),
		'ACLReject'		=>	JPURB_default_ACLReject(),
		'MathProblemFieldName'	=>	JPURB_default_MathProblemFieldName()
	);

	return $opt;
}

function JPURB_option_name()
{
	return 'JPUserRegTools_options';
}


class JPUserRegToolsSettingsPage
{

	// *** Holds the values to be used in the fields callbacks ***
	private $options;


	// *** Class Constructor ***
	public function __construct()
	{
		add_action( 'admin_menu', array( $this, 'JPUserRegTools_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'JPUserRegTools_page_init' ) );
	}

	// *** Add Options Page ***
	public function JPUserRegTools_add_plugin_page()
	{
		// This page will be under "Plugins"
		add_plugins_page(
			'JP User Registration Blacklist', 			// Page Title
			'JP User Registration Blacklist Settings', 		// Menu Text
			'manage_options', 					// WP role (permissions)
			'JPUserRegTools-Settings', 				// Page Slug
			array( $this, 'JPUserRegTools_create_admin_page' )	// Callback
		);
	}

	// *** Options page callback ***
	public function JPUserRegTools_create_admin_page()
	{
		// *** Set class property ***
		$this->options = get_option( JPURB_option_name(), JPURB_default_all() );

		// *** Default Values if not set ***
		if ( is_null( $this->options["seed"] ) )
			$this->options["seed"]=JPURB_default_seed();
		if ( is_null( $this->options["MathReject"] ) )
			$this->options["MathReject"]=JPURB_default_MathReject();
		if ( is_null( $this->options["ACLReject"] ) )
			$this->options["ACLReject"]=JPURB_default_ACLReject();
		if ( is_null( $this->options["MathProblemFieldName"] ) )
			$this->options["MathProblemFieldName"]=JPURB_default_MathProblemFieldName();

		// *** Correct blank or zero fields ***
		if ( $this->options['seed']==0 )
			$this->options["seed"]=JPURB_default_seed();
		if ( $this->options['MathReject']=='' )
			$this->options['MathReject']=JPURB_default_MathReject();
		if ( $this->options['ACLReject']=='' )
			$this->options["ACLReject"]=JPURB_default_ACLReject();
		if ( $this->options['MathProblemFieldName']=='' )
			$this->options["MathProblemFieldName"]=JPURB_default_MathProblemFieldName();

		// *** FORM ***
		?>
		<div class="wrap">
		<?php screen_icon(); ?>
<?php //		<h2>JP User Registration Blacklist</h2>  ?>
		<?php if( isset($_GET['settings-updated']) ) { ?>
		<div id="message" class="updated">
			<p><strong><?php _e('Settings have been saved.') ?></strong></p>
		</div>
		<?php } ?>
		<form method="post" action="options.php">
		<?php

		// *** PRINT OPTIONS GROUP ***
                settings_fields( 'JPUserRegistrationBlacklist_option_group' );   
                do_settings_sections( 'JPUserRegTools-Settings' );

		// *** SUBMIT ***
                submit_button(); 

		// *** CLOSING HTML ***
		?>
		</form></div>
		<?php
	}

	// *** Add Settings to Page **
	public function JPUserRegTools_page_init()
	{
		register_setting(
			'JPUserRegistrationBlacklist_option_group',		// Option group
			JPURB_option_name(),					// Option name
			array( $this, 'JPUserRegTools_sanitize' )		// Sanitize
		);

		add_settings_section(
			'JPUserRegTools_setting_section_id',			// Section ID
			'JP User Registration Blacklist',			// Title
			array( $this, 'JPUserRegTools_print_section_info' ),	// Callback
			'JPUserRegTools-Settings'				// Page
		);

		// *** FIELDS ***

		add_settings_field(
			'seed',							// ID
			'Seed',							// Title 
			array( $this, 'JPUserRegTools_seed_callback' ),		// Callback
			'JPUserRegTools-Settings',				// Page
			'JPUserRegTools_setting_section_id'			// Section ID
		);      
		add_settings_field(
			'MathReject',						// ID
			'Failed Math Response',					// Title 
			array( $this, 'JPUserRegTools_MathReject_callback' ),	// Callback
			'JPUserRegTools-Settings',				// Page
			'JPUserRegTools_setting_section_id'			// Section ID
		);      
		add_settings_field(
			'ACLReject',						// ID
			'Rejected IP or E-mail',				// Title 
			array( $this, 'JPUserRegTools_ACLReject_callback' ),	// Callback
			'JPUserRegTools-Settings',				// Page
			'JPUserRegTools_setting_section_id'			// Section ID
		);      
		add_settings_field(
			'MathProblemFieldName',					// ID
			'Form field name for math problem',			// Title 
			array( $this, 'JPUserRegTools_MathProblemFieldName_callback' ),	// Callback
			'JPUserRegTools-Settings',				// Page
			'JPUserRegTools_setting_section_id'			// Section ID
		);      
	}


	// *** Sanitize each setting field as needed ***
	public function JPUserRegTools_sanitize( $input )
	{
		$new_input = JPURB_default_all();

		if( isset( $input['seed'] ) )
			$new_input['seed'] = absint( $input['seed'] );

		if( isset( $input['MathReject'] ) )
			$new_input['MathReject'] = sanitize_text_field( $input['MathReject'] );

		if( isset( $input['ACLReject'] ) )
			$new_input['ACLReject'] = sanitize_text_field( $input['ACLReject'] );

		if( isset( $input['MathProblemFieldName'] ) )
			$new_input['MathProblemFieldName'] = 
				sanitize_text_field( $input['MathProblemFieldName'] );

		if ( $new_input['seed']==0 )
			$new_input["seed"]=JPURB_default_seed();
		if ( $new_input['MathReject']=='' )
			$new_input['MathReject']=JPURB_default_MathReject();
		if ( $new_input['ACLReject']=='' )
			$new_input["ACLReject"]=JPURB_default_ACLReject();
		if ( $new_input['MathProblemFieldName']=='' )
			$new_input["MathProblemFieldName"]=JPURB_default_MathProblemFieldName();

		return $new_input;
	}

	// *** Print the Section text ***
	public function JPUserRegTools_print_section_info()
	{
		print 'Customize Settings:';
	}

	// *** Seed Callback ***
	public function JPUserRegTools_seed_callback()
	{
		printf(
			'<input type="text" id="seed" name="%s[seed]" value="%s" />',
			JPURB_option_name(),
			isset( $this->options['seed'] ) ? esc_attr( $this->options['seed']) : ''
		);
		printf( '<BR>Random seed value.  Change this periodically to keep bots from 
			hacking your math problem.' );
	}

	// *** Math Problem Reject Message ***
	public function JPUserRegTools_MathReject_callback()
	{
		printf(
			'<textarea rows=3 cols=40 id="MathReject" 
				name="%s[MathReject]">%s</textarea>',
			JPURB_option_name(),
			isset( $this->options['MathReject'] ) ? esc_attr( $this->options['MathReject']) : ''
		);
		printf( '<BR>Error message, when user fails the math problem.' );
	}

	// *** ACL Reject Message ***
	public function JPUserRegTools_ACLReject_callback()
	{
		printf(
			'<textarea rows=3 cols=40 id="ACLReject"
				name="%s[ACLReject]">%s</textarea>',
			JPURB_option_name(),
			isset( $this->options['ACLReject'] ) ? esc_attr( $this->options['ACLReject']) : ''
		);
		printf( '<BR>Error message, when user\'s IP or e-mail is blocked.  
			Keep this generic, so the criminal won\'t know why they are rejected.' );
	}

	// *** Math Field Callback ***
	public function JPUserRegTools_MathProblemFieldName_callback()
	{
		printf(
			'<input type="text" id="MathProblemFieldName"
				name="%s[MathProblemFieldName]" value="%s" />',
			JPURB_option_name(),
			isset( $this->options['MathProblemFieldName'] ) ? 
				esc_attr( $this->options['MathProblemFieldName']) : ''
		);
		printf( '<BR>This is the registration form\'s field name.  
			Change periodically to keep the criminals guessing.' );
	}


}


// *************** GENERATE SETTINGS LINK ********************
function JPUserRegTools_SettingsLink($links) { 
	$settings_link = '<a href="plugins.php?page=JPUserRegTools-Settings">Settings</a>';
	
	//array_unshift($links, $settings_link); 
	$links[]=$settings_link;
	
	return $links; 
}





?>