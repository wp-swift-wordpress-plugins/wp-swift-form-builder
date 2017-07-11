<?php
/*
Plugin Name:       WP Swift: Form Builder
Description:       Generate Forms with Shortcodes
Version:           1.0.0
Author:            Gary Swift
License:           GPL-2.0+
Text Domain:       wp-swift-form-builder
*/

/*
 * Include the WordPress Admin API interface settings for this plugin.
 * This will declare all menu pages, tabs and inputs etc but it does not
 * handle any business logic related to form functionality.
 */
require_once 'form-builder-wordpress-admin-interface.php';
require_once 'email-templates/wp-swift-email-templates.php';
/*
 * The main plugin class that will handle business logic related to form 
 * functionality.
 */
class WP_Swift_Form_Builder_Plugin {
    public $action='';
    // public $form_settings = null;
    public $form_inputs = array();
    private $post_id = null;
    private $form_id = '';
    private $form_name = '';
    private $submit_button_id = '';
    private $submit_button_name = '';
    private $submit_button_text = '';
    private $css_framework = "zurb_foundation";
    private $show_mail_receipt = false;
    private $form_pristine = true;
    private $enctype = '';
    private $error_count = 0;
    private $tab_index = 1;
    private $extra_error_msgs = array();
    private $extra_msgs = array();
    private $list_form_errors_in_warning_panel = true;
    private $clear_after_submission = true;
    private $Section_Layout_Addon = null;
    private $default_input_keys_to_skip = array('submit-request-form', 'mail-receipt', 'form-file-upload', 'g-recaptcha-response');
    private $form_class ='form-builder';
    private $success_msg = '';
    private $option = '';

    /*
     * Initializes the plugin.
     */
    public function __construct($form_data=false, $form_builder_args=false) { //"option") {
        
        $this->set_form_data($form_data, $form_builder_args);
        add_action( 'wp_enqueue_scripts', array( $this, 'wp_swift_form_builder_css_file') );
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_javascript') );
        /*
         * Inputs
         */
        // add_action( 'admin_menu', array($this, 'wp_swift_form_builder_add_admin_menu'), 20 );
        // add_action( 'admin_init', array($this, 'wp_swift_form_builder_settings_init') );
        if (isset($attributes["section-layout"])) {
            $section_layout_string = $attributes["section-layout"];
            if ( class_exists($section_layout_string) ) {
                $this->Section_Layout_Addon = new $section_layout_string();
            }
        }
    }

    public function get_show_mail_receipt() {
        return $this->show_mail_receipt;
    }
    public function get_form_inputs() {
        return $this->form_inputs;
    }

    public function get_form_inputs_value($key) {
        if (isset($this->form_inputs[$key])) {
            return $this->form_inputs[$key];
        }
        else {
            return false;
        }
    }
    public function validate_form($input_keys_to_skip=array()) {
        $this->default_input_keys_to_skip = array('submit-request-form', 'mail-receipt', 'form-file-upload', 'g-recaptcha-response');
        $this->default_input_keys_to_skip = array_merge($this->default_input_keys_to_skip, $input_keys_to_skip);

        // The form is submitted by a user and so is no longer pristine
        $this->set_form_pristine(false);
        //Loop through the POST and validate. Store the values in $form_data
        foreach ($_POST as $key => $value) {
            // echo "key <pre>"; var_dump($key); echo "</pre>";
            if (!in_array($key, $this->default_input_keys_to_skip)) { //Skip the button,  mail-receipt checkbox, g-recaptcha-response etc
                $check_if_submit = substr($key, 0, 7);
                // Get the substring of the key and make sure it is not a submit button
                if ($check_if_submit!='submit-') {

                    $this->check_input($key, $value);//Validate input    
                }
                
            }
        }
    }

    public function process_form() {
        echo "<div class='callout secondary'>"; 
        echo '<h5>public function process_form()</h5>';
        echo '<p>This the the default form handling for the <code>WP Swift: Form Builer</code> plugin. You will need to write your own function to handle this POST request.</p>';
        echo 'var_dump($_POST)<br><br>';
        echo "<pre>";var_dump($_POST);echo "</pre>";
        echo "</div>";
    }
     /*
     * Get the submit button name 
     * This can be used to check if this POST object was
     */
    public function get_submit_button_name() {
        return $this->submit_button_name;
    }    
    /*
     * Get form_pristine
     */
    public function get_form_pristine() {
        return $this->form_pristine;
    }
    /*
     * Set form_pristine
     */
    public function set_form_pristine($form_pristine) {
        $this->form_pristine = $form_pristine;
    }

    /*
     * Get error_count
     */
    public function get_error_count() {
        return $this->error_count;//form_num_error_found
    }
    /*
     * Increase error_count
     */
    public function increase_error_count() {
        $this->error_count++;
    }

    /*
     * Get extra_error_msgs
     */
    public function get_extra_error_msgs() {
        return $this->extra_error_msgs;
    }
    /*
     * Increase extra_error_msgs
     */
    public function add_extra_error_msgs($msg, $increase_count=true) {
        if ($increase_count) {
           $this->error_count++;
        }
        $this->extra_error_msgs[] = $msg;
    }

    /*
     * Get extra msgs
     */
    public function get_extra_msgs() {
        return $this->extra_msgs;
    }
    /*
     * Add new msg
     */
    public function add_extra_msg($msg) {
        $this->extra_msgs[] = $msg;
    }

    /*
     * Set success msg
     */
    public function set_success_msg($msg) {
        $this->success_msg = $msg;
    }

    /*
     * Set success msg
     */
    public function set_input_error($key, $msg) {
        $this->form_inputs[$key]["help"] = $msg;
        $this->form_inputs[$key]["passed"] = false;
        $this->increase_error_count();
    }

    public function enqueue_javascript () {
        $options = get_option( 'wp_swift_form_builder_settings' );
        // echo "<pre>wp-swift-form-builder</pre>";
        
        if (isset($options['wp_swift_form_builder_checkbox_javascript'])==false) {
           wp_enqueue_script( $handle='wp-swift-form-builder', $src=plugins_url( '/assets/javascript/wp-swift-form-builder.js', __FILE__ ), $deps=null, $ver=null, $in_footer=true );
        }
    }

    /*
     * Add the css file
     */
    function wp_swift_form_builder_css_file() {
        $options = get_option( 'wp_swift_form_builder_settings' );
        // echo "<pre>2 wp-swift-form-builder-style</pre>";
        // echo "<pre>"; var_dump($options); echo "</pre>";
        // echo "<pre>"; var_dump(!isset($options['wp_swift_form_builder_checkbox_css'])); echo "</pre>";
        // echo "<pre>"; var_dump(isset($options['wp_swift_form_builder_checkbox_css'])); echo "</pre>";
        if (isset($options['wp_swift_form_builder_checkbox_css'])==false) {
            wp_enqueue_style('wp-swift-form-builder-style', plugins_url( 'assets/css/wp-swift-form-builder.css', __FILE__ ) );
        }

    }

    /*
     * Set the form data
     */
    // $this->set_form_data($form_data, $form_builder_args);
    // public function set_form_data($form_inputs="form_inputs", $post_id, $args=false, $attributes= false, $option=false) {
    public function set_form_data($form_inputs=array(), $args=false) {
        include('_set-form-data.php');
    }

    /*
     * Build the form
     */
    public function acf_build_form() {
        include('_acf-build-form.php');
    }

    /*
     * Build the form
     */
    public function read_only_form() {
        // include('_acf-build-form.php');
    }

    public function front_end_form_input_loop() {
        include('_front-end-form-input-loop.php');
    }
    
    /*
     * Build the HTML before the form input
     */
    public function before_form_input($id, $data) {
        $data = $this->form_element_open($id, $data);
        $this->form_element_anchor($id);
        $this->form_element_label($id, $data);
        $this->form_element_form_input_open();
        return $data;
    }
    /*
     * Build the HTML after the form input
     */
    public function after_form_input($id, $data) {
        $data = $this->form_element_help($data);
        $this->form_element_form_input_close();
        $this->form_element_close($id, $data);
        return $data;
    }

    public function bld_form_input($id, $data, $section='') {
        $has_error='';
        if(!$this->form_pristine) {
            if($this->clear_after_submission && $this->error_count===0) {
                // No errors found so clear the values
                $data['value']=''; 
            }
        }
        // data_type is the same as $data['type'] unless it is an invalid attributes type such as username
        $data_type = $data['type'];
        if ($data['type']=='username') {
            $data['type']='text';
            $data_type = 'username';
        }
        $data = $this->before_form_input($id, $data);
        if (isset($data['name'])) {
            $name = $data['name'];
        }
        else {
            $name = $id;
        }
        
        if (isset($data['id-index'])) {
            $id .= '-'.$data['id-index'];
        }
        ?><input 
            type="<?php echo $data['type']; ?>" 
            data-type="<?php echo $data_type; ?>" 
            class="<?php echo $this->get_form_input_class() ?>" 
            id="<?php echo $id; ?>" 
            name="<?php echo $name; ?>" 
            tabindex="<?php echo $this->tab_index++; ?>" 
            <?php if ( isset($data["value"])): ?> value="<?php echo $data['value'] ?>" <?php endif ?>
            <?php if ( isset($data["placeholder"])): ?> placeholder="<?php echo $data['placeholder'] ?>" <?php endif ?>
            <?php if ( isset($data["section"])): ?> data-section="<?php echo $data["section"] ?>" <?php endif ?>
            <?php echo $data['required']; ?>   
        ><?php 
        $data = $this->after_form_input($id, $data);
    } 

    public function bld_combo_form_input($id, $data, $section='') {
       
        if (isset($data['order']) && $data['order'] == 0):
            
            $data = $this->form_element_open($id, $data);
            $this->form_element_anchor($id);
            $this->form_element_label($id, array('label'=>$data['parent_label'], 'required'=>$data['required']));
            $this->form_element_form_input_open(); ?>
            <div class="row <?php echo $data['data_type'] ?>">
                <div class="small-6 columns">
                    <div class="input-1"><?php  $this->bld_form_input($id, $data); ?></div>
                </div>
            <?php elseif (isset($data['order']) && $data['order'] == 1): ?>
                <div class="small-6 columns">
                    <div class="input-2"><?php  $this->bld_form_input($id, $data); ?></div>            
                </div>
            </div>
            <?php $this->after_form_input($id, $data); ?>
        <?php endif;
       
    } 

    public function bld_form_hidden_input($id, $data, $tabIndex=0, $section='') {
        // echo "<pre>"; var_dump($data); echo "</pre>";
        // $has_error='';
        // echo "<pre>this->form_pristine: "; var_dump($this->form_pristine); echo "</pre>";
        // echo "<pre>this->clear_after_submission "; var_dump($this->clear_after_submission); echo "</pre>";
        // echo "<pre>this->error_count "; var_dump($this->error_count); echo "</pre>";
        // if(!$this->form_pristine) {
        //     if($this->clear_after_submission && $this->error_count===0) {
        //         // No errors found so clear the values
        //         $data['value']=''; 
        //     }
        // }
        // data_type is the same as $data['type'] unless it is an invalid attributes type such as username
        // $data_type = $data['type'];
        // if ($data['type']=='username') {
        //     $data['type']='text';
        //     $data_type = 'username';
        // }
        // $data = $this->before_form_input($id, $data);
        if (isset($data['data-type'])) {
            $data_type = $data['data-type'];
        }
        else {
            $data_type = $data['type'];
        }
        if (isset($data['name'])) {
            $name = $data['name'];
        }
        else {
            $name = $id;
        }
        
        if (isset($data['id-index'])) {
            $id .= '-'.$data['id-index'];
        }
        ?><input 
            type="hidden" 
            data-type="<?php echo $data_type; ?>" 
            class="hidden" 
            id="<?php echo $id; ?>" 
            name="<?php echo $name; ?>" 
            value="<?php echo $data['value']; ?>"
            <?php if ( isset($data["section"])): ?> data-section="<?php echo $data["section"] ?>" <?php endif ?>
            <?php echo $data['required']; ?>   
        ><?php 
        // $data = $this->after_form_input($id, $data);
    } 
    private function form_element_open($id, $data) {
            $has_error='';

            if(!$this->form_pristine && $data['passed']==false && $data["type"] !== "checkbox") {
                // This input has has error detected so add an error class to the surrounding div
                $has_error = 'has-error';
            }
            // echo "<pre>has_error: "; var_dump($has_error); echo "</pre>";
            // echo "<pre>"; var_dump($data); echo "</pre>";
            if(!$this->form_pristine) {
                if($this->clear_after_submission && $this->error_count===0) {
                    // No errors found so clear the values
                    $data['value']=''; 
                }
            }
            // $has_error = 'has-error';
        ?><!-- @start form element -->
        <div class="row form-group form-builder <?php echo $has_error; ?>" 
        id="<?php echo $id; ?>-form-group"><?php 
        return $data;
    }
    private function form_element_anchor($id) {
        ?><a href="<?php echo $id; ?>-anchor"></a><?php
    }
    private function form_element_label($id, $data) {
        // echo "Required: <pre>".$data['required']."</pre><hr>";
        ?><div class="<?php echo $this->get_form_label_div_class() ?>form-label">
            <?php if ($data['label']!=''): ?>
                <label for="<?php echo $id; ?>" class="control-label <?php echo $data['required']; ?>"><?php echo $data['label']; ?> <span></span></label>
            <?php endif ?>
        </div><?php     
    }
    private function form_element_close() {
        ?></div><!-- @end form element --><?php 
    }
    private function form_element_form_input_open() {
        ?><div class="<?php echo $this->get_form_input_div_class() ?>form-input"><?php /*small-12 medium-9 large-9 columns*/
    }
    private function form_element_form_input_close() {
        ?></div><?php 
    }
    private function form_element_help($data) {
        if ($data['help']) {
             $help = $data['help'];
        }
        else {
            $help = $data['label']. ' is required';
            if ($data['type']=='email' || $data['type']=='url') {
                $help .= ' and must be valid';
            }  
            $data['help'] = $help;
        }   
        if ($data['required']): 
            ?><small class="error"><?php echo $help; ?></small><?php 
        endif;
        if (isset($data['instructions']) && $data['instructions']): 
            ?><small class="instructions"><?php echo $data['instructions']; ?></small><?php 
        endif;
        return $data;
    }
    /*
     * Get the CSS class for div wrapping the label
     */
    private function get_form_label_div_class() {
        $framework = $this->css_framework;
        $options = get_option( 'wp_swift_form_builder_settings' );
        if (isset($options['wp_swift_form_builder_select_css_framework'])) {
            $framework = $options['wp_swift_form_builder_select_css_framework'];
        }

        if ($framework === "zurb_foundation") {
            return $framework." small-12 medium-12 large-12 columns ";
        }
        elseif ($framework === "bootstrap") {
            return "col-xs-12 col-sm-12 col-md-12 col-lg-12 ";
        }    
        else {
            return "";
        }
    }
    /*
     * Get the CSS class for div wrapping the label
     */
    private function get_form_input_div_class() {
        $framework = $this->css_framework;
        $options = get_option( 'wp_swift_form_builder_settings' );
        if (isset($options['wp_swift_form_builder_select_css_framework'])) {
            $framework = $options['wp_swift_form_builder_select_css_framework'];
        }
           
        if ($framework === "zurb_foundation") {
            return " small-12 medium-12 large-12 columns ";
        }
        elseif ($framework === "bootstrap") {
            return "col-xs-12 col-sm-12 col-md-12 col-lg-12 ";
        }     
        else {
            return "";
        }
    }

    /*
     * Get the CSS class for the input
     */
    private function get_form_input_class() {
        return "form-control form-builder-control js-form-builder-control";
    }
    /*
     * Set the CSS framework
     */
    public function set_css_framework($css_framework) {
        $this->css_framework = $css_framework;
    }
    function bld_form_textarea($id, $input) {
        if(!$this->form_pristine) {
            if($this->clear_after_submission && $this->error_count===0) {
                // No errors found so clear the values
                $input['value']=''; 
            }
        }

        $this->before_form_input($id, $input);   
        ?><textarea class="form-control js-form-builder-control" rows="3" id="<?php echo $id; ?>" name="<?php echo $id; ?>" tabindex="<?php echo $this->tab_index++; ?>" placeholder="<?php echo $input['placeholder']; ?>" <?php echo $input['required']; ?>><?php echo $input['value']; ?></textarea><?php
        $this->after_form_input($id, $input);

    }


    function bld_form_select($id, $data, $multiple) {

        if(!$this->form_pristine) {
            if($this->clear_after_submission && $this->error_count===0) {
                // No errors found so clear the selected value
                $data['selected_option']=''; 
            }
        }

        $this->before_form_input($id, $data);   
        // echo "<pre>";var_dump($data['options']);echo "</pre>";
          ?><select class="form-control js-form-control" id="<?php echo $id; ?>" name="<?php echo $id; ?>" tabindex="<?php echo $this->tab_index++; ?>" <?php echo $data['required']; ?> <?php echo $multiple; ?>>
                <?php if(!$multiple): ?>
                    <option value="">Please select an option...</option>
                <?php endif; ?>
                <?php foreach ($data['options'] as $option): ?>
                    <?php 
                        if($option['option_value'] === $data['selected_option']) { 
                            $selected='selected'; 
                        } else { 
                            $selected=''; 
                        }
                    ?>
                    <option value="<?php echo  $option['option_value']; ?>" <?php echo $selected; ?>><?php echo $option['option']; ?></option>
                <?php endforeach; ?>
            </select><?php
        $this->after_form_input($id, $data);
    }
    function build_form_radio($id, $input) {
        if(!$this->form_pristine) {
            if($this->clear_after_submission && $this->error_count===0) {
                // No errors found so clear the selected value
                $input['selected_option']=''; 
            }
        }

        $this->before_form_input($id, $input);
        $count=0;  
        $checked='';
          ?><?php 
            foreach ($input['options'] as $option): $count++;
                if ( ($input['selected_option']=='' && $count==1) || ($input['selected_option']==$option['option_value'])){
                    $checked=' checked';
                }
                ?><input id="<?php echo $id.'-'.$count ?>" name="<?php echo $id ?>-radio" type="radio" tabindex="<?php echo $this->tab_index++; ?>" value="<?php echo $option['option_value'] ?>"<?php echo $checked; ?>>
                <label for="<?php echo $id.'-'.$count ?>"><?php echo $option['option'] ?></label><?php 
            endforeach; ?><?php
        $this->after_form_input($id, $input);
    }
    function build_form_checkbox($id, $data) {
        if(!$this->form_pristine) {
            if($this->clear_after_submission && $this->error_count===0) {
                // No errors found so clear the checked values
                foreach ($data['options'] as $key => $option) {
                    $data['options'][$key]['checked'] = false;
                }
            }
        }

        $data = $this->before_form_input($id, $data);
        $count=0;  
        
        $name_append = '';
        if (count($data['options']) > 1) {
            $name_append = '[]';
        }
     
        foreach ($data['options'] as $option): $count++;
            $checked='';
            // echo "<br><pre>";var_dump($option);echo "</pre>";
            if ( $option['checked'] == true ){
                $checked=' checked';
            }
            if (isset($data['name'])) {
                $name = $data['name'].$name_append;
            }
            else {
                $name = $id.''.$name_append;
                // $name = $id.'-checkbox'.$name_append;
            }
            ?><label for="<?php echo $id.'-'.$count ?>" class="lbl-checkbox"><input id="<?php echo $id.'-'.$count ?>" name="<?php echo $name ?>" type="checkbox" tabindex="<?php echo $this->tab_index++; ?>" value="<?php echo $option['option_value'] ?>"<?php echo $checked; ?>>
            <?php echo $option['option'] ?></label><?php 
        endforeach;
        $data = $this->after_form_input($id, $data);
    }


    function bld_FormSelect2($id, $data, $form_pristine, $form_num_error_found, $tabIndex, $multiple) {
        if(!$form_pristine) {
            if(!$form_num_error_found) {
                // No errors found so clear the selected option
                $data['selected_option']=''; 
            }
        }
        if(!$form_pristine && $data['passed']==false) {
            // This input has has error detected so add an error class to the surrounding div
            $has_error = 'has-error';
        }   
        ?>
        <div class="row form-group form-builder <?php echo $has_error; ?>" id="<?php echo $id; ?>-form-group">
            <div class="small-12 medium-3 large-3 columns form-label">
                <label for="<?php echo $id; ?>" class="control-label <?php echo $data['required']; ?>"><?php echo $data['label']; ?> <span></span></label>
            </div>
            <div class="small-12 medium-9 large-9 columns">
                <select class="form-control js-form-control" id="<?php echo $id; ?>" name="<?php echo $id; ?>" tabindex="<?php echo $this->tab_index++; ?>" <?php echo $data['required']; ?> <?php echo $multiple; ?>>
                    <?php if(!$multiple): ?>
                        <option value="">Please select an option...</option>
                    <?php endif; ?>
                    <?php foreach ($data['options'] as $option): ?>
                        <?php if($option['option_value'] == $data['selected_option']){ $selected='selected'; } else { $selected=''; }?>
                        <option value="<?php echo  $option['option_value']; ?>" <?php echo $selected; ?>><?php echo $option['option']; ?></option>
                    <?php endforeach; ?>
                </select>
                <?php $data['help'] ? $help= $data['help'] : $help = $data['label']. ' is required'; ?>
                <small class="error"><?php echo $help; ?></small>
                <?php if($multiple): ?>
                    <small>Hold down the control/command button to select multiple options</small>
                <?php endif; ?>
            </div>
        </div>
        <?php 
    }
    public function section_open($section_header, $section_content) {
        ?>
        <!-- @start section -->
        <?php if ($section_header): ?>
            <div class="row"><div class="columns"><h4><?php echo $section_header ?></h4></div></div>
        <?php endif ?>
        <?php if ($section_content): ?>
            <div class="row"><div class="columns"><p><?php echo $section_content ?></p></div></div>
        <?php endif;    
    }

    public function section_close() {
        ?><!-- @end section --><?php       
    } 

    public function html_section_open_side_by_side ($section_header, $section_content) {
      ?>
          <div class="row form-section">
           <div class="small-12 medium-6 large-6 columns large-push-6">
               <div class="search-info">
                   <h3 class="search-header-info"><?php echo $section_header ?></h3>
                   <div class="entry-content"><?php echo $section_content ?></div>
               </div>
           </div>
           <div class="small-12 medium-6 large-6 columns large-pull-6">   
      <?php
        return '';
    }

    public function html_section_close_side_by_side () {
        $html = '</div>';
        $html .= '</div>'; 
        return $html;
    }
    // public function get_enctype($form_data) {
    //     $form_file = '';
    //     foreach ($form_data as $key => $value) {
    //         switch ($value["type"]) {           
    //             case "text":
    //             case "url":
    //             case "email":
    //             case "number":
    //             case "textarea":
    //             case "select":
    //             case "multi_select":
    //             case "date_range":
    //                 break;    
    //             case "file":
    //                 $form_file = array();
    //                 $enctype = 'enctype="multipart/form-data"';
    //                 $form_class = 'js-check-form-file';
    //                 $form_file["enctype"] = $enctype;
    //                 $form_file["form_class"] = $form_class;
    //                 break;                          
    //         }
    //     }
    //     return $form_file;
    // } 


    /*
     * Check an individual form input field and sets the array with the findings 
     *
     * @param $key      an array key that matches the form input name (POST key)
     * @param $value    the value of the form input
     *
     * @return null
     */
    public function check_input($key, $value){
        include('_check-input.php');
    }



     
}
// Initialize the plugin
$form_builder_plugin = new WP_Swift_Form_Builder_Plugin();