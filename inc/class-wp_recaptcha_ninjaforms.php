<?php

/**
 *	Class to manage NinjaForms Support
 */
class WP_reCaptcha_NinjaForms {
	/**
	 *	Holding the singleton instance
	 */
	private static $_instance = null;

	/**
	 *	@return WP_reCaptcha
	 */
	public static function instance(){
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();
		return self::$_instance;
	}

	/**
	 *	Prevent from creating more instances
	 */
	private function __clone() { }

	/**
	 *	Prevent from creating more than one instance
	 */
	private function __construct() {
		add_action('init', array(&$this,'register_field_recaptcha'));
		add_action('wp_footer',array(&$this,'recaptcha_script'),9999);
		add_filter('ninja_forms_field',array(&$this,'recaptcha_field_data'),10,2);
	}
	function register_field_recaptcha(){
		$args = array(
			'name' => __( 'reCAPTCHA', 'wp-recaptcha-integration' ),
			'edit_function' => '',
			'display_function' => array( &$this , 'field_recaptcha_display' ),
			'group' => 'standard_fields',
			'edit_label' => true,
			'edit_label_pos' => true,
			'edit_req' => false,
			'edit_custom_class' => false,
			'edit_help' => true,
			'edit_meta' => false,
			'sidebar' => 'template_fields',
			'display_label' => true,
			'edit_conditional' => false,
			'conditional' => array(
				'value' => array(
					'type' => 'text',
				),
			),
			'pre_process' => array( &$this , 'field_recaptcha_pre_process' ),
			'process_field' => false,
			'limit' => 1,
			'edit_options' => array(
			),
			'req' => true,
		);

		ninja_forms_register_field('_recaptcha', $args);
	}
	
	function recaptcha_field_data( $data, $field_id ) {
		$field_row = ninja_forms_get_field_by_id($field_id);
		if ( $field_row['type'] == '_recaptcha' ) 
			$data['show_field'] = WP_reCaptcha::instance()->is_required();
		return $data;
	}

	function recaptcha_script($id) {
		/*
		refresh captcha after form submission.
		*/
		$flavor = WP_reCaptcha::instance()->get_option( 'recaptcha_flavor' );
		switch ( $flavor ) {
			case 'recaptcha':
				$html = '<script type="text/javascript"> 
		// reload recaptcha after failed ajax form submit
		jQuery(document).on("submitResponse.default", function(e, response){
			Recaptcha.reload();
		});
	</script>';
				break;
			case 'grecaptcha':
				$html = '<script type="text/javascript"> 
		// reload recaptcha after failed ajax form submit
		(function($){
		$(document).on("submitResponse.default", function(e, response){
			if ( grecaptcha ) {
				var wid = $(\'#ninja_forms_form_\'+response.form_id).find(\'.g-recaptcha\').data(\'widget-id\');
				grecaptcha.reset(wid);
			}
		});
		})(jQuery);
	</script>';
				break;
		}
		WP_reCaptcha::instance()->begin_inject(false,', Ninja form integration');
		echo $html;
		WP_reCaptcha::instance()->end_inject();
	}

	function field_recaptcha_display($field_id, $data){
		if ( WP_reCaptcha::instance()->is_required() )
			WP_reCaptcha::instance()->print_recaptcha_html();
		else 
			echo apply_filters( 'wp_recaptcha_disabled_html' ,'');
	}

	function field_recaptcha_pre_process( $field_id, $user_value ){
		global $ninja_forms_processing;
		$recaptcha_error = __("<strong>Error:</strong> the Captcha didn’t verify.",'wp-recaptcha-integration');

		$field_row = ninja_forms_get_field_by_id($field_id);
		$field_data = $field_row['data'];
		$form_row = ninja_forms_get_form_by_field_id($field_id);
		$form_id = $form_row['id'];


		$require_recaptcha = WP_reCaptcha::instance()->is_required();
	
		if ( $ninja_forms_processing->get_action() != 'save' && $ninja_forms_processing->get_action() != 'mp_save' && $require_recaptcha && ! WP_reCaptcha::instance()->recaptcha_check() ){
			$ninja_forms_processing->add_error('recaptcha-general', $recaptcha_error, 'general');
			$ninja_forms_processing->add_error('recaptcha-'.$field_id, $recaptcha_error, $field_id);				
		}
	}
}
