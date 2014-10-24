<?php

/**
 * Plugin: Gravity Forms PDF Extended
 * File: pdf-settings.php
 * 
 * The controller that handles the Gravity Forms Settings page in Wordpress
 */

class GFPDF_Settings
{		
		
	static $model;
	
	public function __construct()
	{
		/*
		 * We'll initialise our model which will do compatibility checks and store in
		 * the $gfpdfe_data data class.
		 */
		 include PDF_PLUGIN_DIR . 'model/settings.php';			 
		 self::$model = new GFPDF_Settings_Model();			
	}
	
	/* 
	 * Check if we're on the settings page 
	 */ 
	public static function settings_page() {			 		
		if(class_exists('RGForms') && RGForms::get("page") == "gf_settings") {		 										
			/* 
			 * Tell Gravity Forms to initiate our settings page
			 * Using the following Class/Model
			 */ 
			 RGForms::add_settings_page('PDF', array(self::$model, 'gfpdf_settings_page'));
				 
		}			
	}
	
	/*
	 * Use to function to determine whether the user is requesting to initialise the plugin
	 */
	protected function run_setting_routing()
	{
		global $gfpdfe_data;
		
		/* 
		 * Check if we need to redeploy default PDF templates/styles to the theme folder 
		 */
		if( rgpost("gfpdf_deploy") && 
		( wp_verify_nonce(PDF_Common::post('gfpdf_deploy_nonce'),'gfpdf_deploy_nonce_action') || wp_verify_nonce(PDF_Common::get('_wpnonce'),'pdf-extended-filesystem') ) ) 
		{		
			/*
			 * Check if the user wants to upgrade the system or only initialise the fonts
			 */	
			if(PDF_Common::post('font-initialise'))
			{
				/*
				 * We only want to reinitialise the font files and configuration
				 */	
				 if(GFPDF_InstallUpdater::initialise_fonts() === false)
				 {
					 return true;
				 }
			}
			else if(rgpost('upgrade'))
			{
				/* 
				 * Deploy new template styles 
				 * If we get false returned Wordpress is trying to get 
				 * access details to update files so don't display anything.
				 */
				if(self::deploy() === 'false')
				{
					return true;
				}
			}
		}
		
		/*
		 * Check if we need to sync the theme folders because a user changes theme
		 * Sniff the _wpnonce values to determine this.
		 */	
		 if(isset($_GET['_wpnonce']))
		 {
			 /*
			  * Check if we want to copy the theme files
			  */
			 if(is_dir($gfpdfe_data->old_template_location) && wp_verify_nonce(PDF_Common::get('_wpnonce'), 'gfpdfe_migrate') )
			 {	

				 if(GFPDF_InstallUpdater::run_template_migration() === 'false')
				 {
					return true; 
				 }
			 }
		 }		
	}
	
	/*
	 * Deploy the latest template files
	 */
	private function deploy()
	{
		global $gfpdfe_data;

		/*
		 * Initialise all multisites if a super admin is logged in and the software hasn't been installed before
		 */		
		if(is_multisite() && is_super_admin() && ($gfpdfe_data->fresh_install === true) )
		{
			$results = GFPDF_InstallUpdater::run_multisite_deployment(array('GFPDF_InstallUpdater', 'do_deploy'));

			if($results === true)
			{
				add_action($gfpdfe_data->notice_type, array('GFPDF_Notices', 'gf_pdf_network_deploy_success'));	
			}
			elseif($results === false)
			{
				add_action($gfpdfe_data->notice_type, array('GFPDF_Notices', 'gf_pdf_auto_deploy_network_failure'));						
			}	
			return $results;				
		}
		else
		{
			/*
			 * Run the updater
			 */
			$results = GFPDF_InstallUpdater::pdf_extended_activate();
			if($results === 'false')
			{
				return $results;	
			}
			add_action($gfpdfe_data->notice_type, array('GFPDF_Notices', 'gf_pdf_deploy_success')); 	
		}		
	}

}
