<?php
/*
 * Include the WordPress Admin API interface settings for this plugin.
 * This will declare all menu pages, tabs and inputs etc but it does not
 * handle any business logic related to form functionality.
 */
class WP_Swift_Form_Builder_Admin_Interface {

    /*
     * Initializes the plugin.
     */
    public function __construct() {
        /*
         * Inputs
         */
        add_action( 'admin_menu', array($this, 'wp_swift_form_builder_add_admin_menu'), 20 );
        add_action( 'admin_init', array($this, 'wp_swift_form_builder_settings_init') );
    }	

	/*
	 *
	 */
	public function wp_swift_form_builder_add_admin_menu(  ) { 

	    $show_form_builder = class_exists('WP_Swift_Admin_Menu');
	    if (!$show_form_builder) {
	        $options_page = add_options_page( 
	            'Form Builder Configuration',
	            'Form Builder',
	            'manage_options',
	            'wp-swift-form-builder-settings-menu',
	            array($this, 'wp_swift_form_builder_options_page') 
	        );  
	    }

	}

	/******************************************************************************
	 *
	 * Render the top level menu page tabs. All other items will be rendered under this
	 *
	 ******************************************************************************/
	// public function wp_swift_admin_menu_options_page_render(  ) { 
	// 	include "_tabs.php";
	// }

	/*
	 *
	 */
	public function wp_swift_form_builder_settings_init(  ) { 

	    register_setting( 'form-builder', 'wp_swift_form_builder_settings' );

	    add_settings_section(
	        'wp_swift_form_builder_plugin_page_section', 
	        __( 'Set your preferences for the Form Builder here', 'wp-swift-form-builder' ), 
	        array($this, 'wp_swift_form_builder_settings_section_callback'), 
	        'form-builder'
	    );

	    // add_settings_field( 
	    //     'wp_swift_form_builder_text_field_0', 
	    //     __( 'Settings field description', 'wp-swift-form-builder' ), 
	    //     array($this, 'wp_swift_form_builder_text_field_0_render'), 
	    //     'form-builder', 
	    //     'wp_swift_form_builder_plugin_page_section' 
	    // );

	    add_settings_field( 
			'wp_swift_form_builder_checkbox_javascript', 
			__( 'Disable JavaScript', 'wp-swift-form-builder' ), 
			array($this, 'wp_swift_form_builder_checkbox_javascript_render'),  
			'form-builder', 
			'wp_swift_form_builder_plugin_page_section' 
	    );

	     add_settings_field( 
	     'wp_swift_form_builder_checkbox_css', 
	     __( 'Disable CSS', 'wp-swift-form-builder' ), 
	     array($this, 'wp_swift_form_builder_checkbox_css_render'),  
	     'form-builder', 
	     'wp_swift_form_builder_plugin_page_section' 
	    );

	    // add_settings_field( 
	    //  'wp_swift_form_builder_radio_field_2', 
	    //  __( 'Settings field description', 'wp-swift-form-builder' ), 
	    //  array($this, 'wp_swift_form_builder_radio_field_2_render'),  
	    //  'form-builder', 
	    //  'wp_swift_form_builder_plugin_page_section' 
	    // );

	    add_settings_field( 
	     'wp_swift_form_builder_select_css_framework', 
	     __( 'CSS Framework', 'wp-swift-form-builder' ), 
	     array($this, 'wp_swift_form_builder_select_css_framework_render'),  
	     'form-builder', 
	     'wp_swift_form_builder_plugin_page_section' 
	    );


	    add_settings_field( 
	        'wp_swift_form_builder_email_template_primary_color', 
	        __( 'Email Template', 'wp-swift-form-builder' ), 
	        array($this, 'wp_swift_form_builder_email_template_primary_color_render'), 
	        'form-builder', 
	        'wp_swift_form_builder_plugin_page_section' 
	    );

	    add_settings_field( 
	        'wp_swift_form_builder_email_template_secondary_color', 
	        __( '', 'wp-swift-form-builder' ), 
	        array($this, 'wp_swift_form_builder_email_template_secondary_color_render'), 
	        'form-builder', 
	        'wp_swift_form_builder_plugin_page_section' 
	    );

	    add_settings_field( 
			'wp_swift_form_builder_checkbox_debug_mode', 
			__( 'Debug Mode', 'wp-swift-form-builder' ), 
			array($this, 'wp_swift_form_builder_checkbox_debug_mode_render'),  
			'form-builder', 
			'wp_swift_form_builder_plugin_page_section' 
	    );
	}


	/*
	 *
	 */
	public function wp_swift_form_builder_email_template_primary_color_render(  ) { 
	    $options = get_option( 'wp_swift_form_builder_settings' );
	    ?>
	    <input type="text" name="wp_swift_form_builder_settings[wp_swift_form_builder_email_template_primary_color]" placeholder="#525050" value="<?php
	    	if (isset($options['wp_swift_form_builder_email_template_primary_color'])) {
	     		echo $options['wp_swift_form_builder_email_template_primary_color'];
	     	}
	     	else {
	     		echo "#525050";
	     	} ?>"> <small>Primary Colour</small>
	    <?php

	}

	/*
	 *
	 */
	public function wp_swift_form_builder_email_template_secondary_color_render(  ) { 

	    $options = get_option( 'wp_swift_form_builder_settings' );
	    ?>
	    <input type="text" name="wp_swift_form_builder_settings[wp_swift_form_builder_email_template_secondary_color]" value="<?php
	    	if (isset($options['wp_swift_form_builder_email_template_secondary_color'])) {
	     	echo $options['wp_swift_form_builder_email_template_secondary_color'];
	     	} ?>"> <small>Secondary Colour</small>
	    <?php

	}	

	/*
	 *
	 */
	public function wp_swift_form_builder_text_field_0_render(  ) { 

	    $options = get_option( 'wp_swift_form_builder_settings' );
	    ?>
	    <input type='text' name='wp_swift_form_builder_settings[wp_swift_form_builder_text_field_0]' value='<?php echo $options['wp_swift_form_builder_text_field_0']; ?>'>
	    <?php

	}
	/*
	 *
	 */
	public function wp_swift_form_builder_checkbox_javascript_render(  ) { 

	    $options = get_option( 'wp_swift_form_builder_settings' );
	    ?>
	    <input type='checkbox' name='wp_swift_form_builder_settings[wp_swift_form_builder_checkbox_javascript]' <?php 
	    // checked( $options['wp_swift_form_builder_checkbox_javascript'], 1 ); 
	    if (isset($options['wp_swift_form_builder_checkbox_javascript'])) {
	         checked( $options['wp_swift_form_builder_checkbox_javascript'], 1 );
	     } 
	    ?> value='1'>
	    <small>You can disable JavaScript here if you prefer to user your own or even not at all.</small>
	    <?php

	}

	/*
	 *
	 */
	public function wp_swift_form_builder_checkbox_css_render(  ) { 

	    $options = get_option( 'wp_swift_form_builder_settings' );
	    ?>
	    <input type='checkbox' name='wp_swift_form_builder_settings[wp_swift_form_builder_checkbox_css]' <?php //checked( $options['wp_swift_form_builder_checkbox_css'], 1 );
	        if (isset($options['wp_swift_form_builder_checkbox_css'])) {
	            checked( $options['wp_swift_form_builder_checkbox_css'], 1 );
	        }  ?> value='1'>
	    <small>Same goes for CSS</small>
	    <?php

	}

	/*
	 *
	 */
	public function wp_swift_form_builder_radio_field_2_render(  ) { 

	    $options = get_option( 'wp_swift_form_builder_settings' );
	    ?>
	    <input type='radio' name='wp_swift_form_builder_settings[wp_swift_form_builder_radio_field_2]' <?php //checked( $options['wp_swift_form_builder_radio_field_2'], 1 );
	    if (isset($options['wp_swift_form_builder_radio_field_2'])) {
	         checked( $options['wp_swift_form_builder_radio_field_2'], 1 );
	     } ?> value='1'>
	    <?php

	}

	/*
	 *
	 */
	public function wp_swift_form_builder_select_css_framework_render(  ) { 

	    $options = get_option( 'wp_swift_form_builder_settings' );
	    ?>
	    <select name='wp_swift_form_builder_settings[wp_swift_form_builder_select_css_framework]'>
	        <option value='zurb_foundation' <?php selected( $options['wp_swift_form_builder_select_css_framework'], 'zurb_foundation' ); ?>>Zurb Foundation</option>
	        <option value='bootstrap' <?php selected( $options['wp_swift_form_builder_select_css_framework'], 'bootstrap' ); ?>>Bootstrap</option>
	        <option value='custom' <?php selected( $options['wp_swift_form_builder_select_css_framework'], 'custom' ); ?>>None</option>
	    </select>

	<?php

	}

		/*
	 *
	 */
	public function wp_swift_form_builder_checkbox_debug_mode_render(  ) { 

	    $options = get_option( 'wp_swift_form_builder_settings' );
	    echo "<pre>";var_dump($options);echo "</pre>";
	    ?>
	    <input type="checkbox" name="wp_swift_form_builder_settings[wp_swift_form_builder_checkbox_debug_mode]" value="1" <?php 
	    	if (isset($options['wp_swift_form_builder_checkbox_debug_mode'])) {
	         	checked( $options['wp_swift_form_builder_checkbox_debug_mode'], 1 );
	     	} 
	    ?>>
	    <small><b>Do not use on live sites!</b></small><br>
	    <small>You can set this to debug mode if you are a developer. This will skip default behaviour such as sending emails.</small>
	    
	    <?php

	}

	/*
	 *
	 */
	public function wp_swift_form_builder_settings_section_callback(  ) { 

	    echo __( 'Form Builder global settings', 'wp-swift-form-builder' );

	}

	/*
	 *
	 */
	public function wp_swift_form_builder_options_page(  ) { 
	    $show_form_builder = class_exists('WP_Swift_Admin_Menu');
	    if (!$show_form_builder): ?>
	        <div id="form-builder-wrap" class="wrap">
	        <h2>WP Swift: Form Builder</h2>

	        <form action='options.php' method='post'>
	            
	            <?php
	            settings_fields( 'form-builder' );
	            do_settings_sections( 'form-builder' );
	            submit_button();
	            ?>

	        </form>
	        </div>
	    <?php 
	    endif;
	}
}
// Initialize the class
$form_builder_admin_interface = new WP_Swift_Form_Builder_Admin_Interface();