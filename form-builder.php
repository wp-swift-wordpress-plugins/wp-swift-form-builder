<?php
/*
Plugin Name:       WP Swift: Form Builder
Description:       Generate Forms with Shortcodes
Version:           1.0.0
Author:            Gary Swift
License:           GPL-2.0+
Text Domain:       wp-swift-form-builder
*/

class WP_Swift_Form_Builder_Plugin {
    public $form_settings = null;
    private $post_id = null;
    private $css_framework = "zurb_foundation";
    private $show_mail_receipt = false;
    private $form_pristine = true;
    private $error_count = 0;
    private $clear_after_submission = true;
    private $Section_Layout_Addon = null;
    private $default_input_keys_to_skip = array('submit-request-form', 'mail-receipt', 'form-file-upload', 'g-recaptcha-response');
    /*
     * Initializes the plugin.
     */
    public function __construct($attributes=false, $form_data=false, $post_id=false, $form_builder_args=false, $option=false) { //"option") {
        
        // echo "<pre>"; var_dump($options); echo "</pre>";
        $this->set_form_data($form_data,  $post_id, $form_builder_args, $attributes, $option);
        
        


 add_action( 'wp_enqueue_scripts', array( $this, 'wp_swift_form_builder_css_file') );
add_action( 'wp_enqueue_scripts', array($this, 'enqueue_javascript') );
        /*
         * Inputs
         */
        add_action( 'admin_menu', array($this, 'wp_swift_form_builder_add_admin_menu'), 20 );
        add_action( 'admin_init', array($this, 'wp_swift_form_builder_settings_init') );

        // echo "<pre>Vivamus aliquet elit ac nisl. Duis vel nibh at velit scelerisque suscipit. Vestibulum eu odio. Vestibulum dapibus nunc ac augue. Suspendisse enim turpis, dictum sed, iaculis a, condimentum nec, nisi.</pre>";
        if (isset($attributes["section-layout"])) {
            $section_layout_string = $attributes["section-layout"];
            if ( class_exists($section_layout_string) ) {
                $this->Section_Layout_Addon = new $section_layout_string();
            }
        }
    }



    public function validate_form($input_keys_to_skip=array()) {
        $this->default_input_keys_to_skip = array('submit-request-form', 'mail-receipt', 'form-file-upload', 'g-recaptcha-response');
        $this->default_input_keys_to_skip = array_merge($this->default_input_keys_to_skip, $input_keys_to_skip);

        // The form is submitted by a user and so is no longer pristine
        $this->set_form_pristine(false);
        //Loop through the POST and validate. Store the values in $form_data
        foreach ($_POST as $key => $value) {
            if (!in_array($key, $this->default_input_keys_to_skip)) { //Skip the button,  mail-receipt checkbox, g-recaptcha-response etc
                $check_if_submit = substr($key, 0, 7);
                // Get the substring of the key and make sure it is not a submit button
                if ($check_if_submit!='submit-') {

                    $this->check_input($key, $value);//Validate input    
                }
                
            }
        }
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

    public function set_form_data($form_inputs="form_inputs", $post_id, $args=false, $attributes= false, $option=false) {
        $this->form_settings = array();
        // $this->post_id = $post_id;
        $this->form_settings["form_pristine"] = true;
        $this->form_settings["form_num_error_found"] = 0;
        $this->error_count = 0;
        $this->form_settings["enctype"] = "";
        $this->form_settings["form_class"] = "";
        $this->form_settings["option"]=$option;
        if (isset($args["show_mail_receipt"])) {
            $this->show_mail_receipt = true;
        }
        if( function_exists('acf')) {
            if (get_sub_field('form_name', $post_id)) {
                 $this->form_settings["form-name"] = sanitize_title_with_dashes( get_sub_field('form_name') );
            }
            // elseif(isset($args["form_name"])) {
            //     $this->form_settings["form-name"] = sanitize_title_with_dashes($args["form_name"]);
            // }
            // else {
            //      $this->form_settings["form-name"] = "request-form";
            // }
            if (get_sub_field('button_text', $post_id)) {
                  $this->form_settings["submit-button-text"] = get_sub_field('button_text');
            }
            // elseif(isset($args["button_text"])) {
            //     $this->form_settings["submit-button-text"] = $args["button_text"];
            // }
            // else {
            //       $this->form_settings["submit-button-text"] = "Submit Form";
            // }
        }

        if(isset($args["form_name"])) {
            $this->form_settings["form-name"] = sanitize_title_with_dashes($args["form_name"]);
        }
        else {
             $this->form_settings["form-name"] = "request-form";
        }
        if(isset($args["button_text"])) {
            $this->form_settings["submit-button-text"] = $args["button_text"];
        }
        else {
              $this->form_settings["submit-button-text"] = "Submit Form";
        }
        if(isset($args["submit_button_name"])) {
            $this->form_settings["submit-button-name"] = $args["submit_button_name"];
        }
        else {
            $this->form_settings["submit-button-name"] = "submit-".$this->form_settings["form-name"];
        }

        if (isset($args["clear_after_submission"])) {
            // echo "<pre>";var_dump($args["clear_after_submission"]);echo "</pre>";
            $this->clear_after_submission = $args["clear_after_submission"];
            // echo "<pre>";var_dump($this->clear_after_submission);echo "</pre>";
        }
// $this->form_settings["form-name"] = "request-form";
// $this->form_settings["submit-button-text"] = "Submit Form";
// $this->form_settings["submit-button-name"] = "submit-".$this->form_settings["form-name"];


        $this->form_settings["error_class"] = "";
        $this->form_settings["ajax"] = false;
        $form_data = array();
        
if (is_array($form_inputs)) {
    $this->form_settings["form_data"] = $form_inputs;
}
else if (is_string($form_inputs)) {
    if( function_exists('acf')) {
         // Construct the array that makes the form
        if ( have_rows($form_inputs, $option) ) {

            $this->form_settings = array();
            $this->form_settings["form_pristine"] = true;
            $this->form_settings["form_num_error_found"] = 0;
            $this->form_settings["enctype"] = "";
            $this->form_settings["form_class"] = "";
            $this->form_settings["option"]=$option;
            if (get_sub_field('form_name')) {
                 $this->form_settings["form-name"] = sanitize_title_with_dashes( get_sub_field('form_name') );
            }
            else {
                 $this->form_settings["form-name"] = "request-form";
            }
            if (get_sub_field('button_text')) {
                  $this->form_settings["submit-button-text"] = get_sub_field('button_text');
            }
            else {
                  $this->form_settings["submit-button-text"] = "Submit Form";
            }

            $this->form_settings["submit-button-name"] = "submit-".$this->form_settings["form-name"];
            $this->form_settings["error_class"] = "";
            $this->form_settings["ajax"] = false;
            $form_data = array();

            while( have_rows($form_inputs, $option) ) : the_row(); // Loop through the repeater for form inputs

                $name =  get_sub_field('name');
                $id = sanitize_title_with_dashes( get_sub_field('name') );
                $type = get_sub_field('type');
                $label = get_sub_field('label');
                $help = get_sub_field('help');
                $placeholder = get_sub_field('placeholder');
                $required = get_sub_field('required');
                $select_options='';

                // If the user has manually added options with the repeater
                if( get_sub_field('select_options') ) {
                    $select_options = get_sub_field('select_options');

                    if(get_sub_field('select_type') === 'user') {                    
                        for ($i = 0; $i < count($select_options); ++$i) {
                            if($select_options[$i]['option_value']=='') {
                                $select_options[$i]['option_value'] = sanitize_title_with_dashes( $select_options[$i]['option'] );
                            }
                        }   
                    }
                }

                // If the user has elected to select predefined options - only countries available at the moment
                if(get_sub_field('select_type') === 'select') {
                    $countries = getCountries(); // Returns an array of countries
                    $i=0;
                    // Push each entry into $select_options in a usable way
                    foreach ($countries as $key => $value) {
                        ++$i;
                        $select_options[$i]['option_value']  = sanitize_title_with_dashes($key);
                        $select_options[$i]['option'] = $value;//$key;
                    }                     
                }

                if($required) {
                    $required = 'required';
                }
                else {
                    $required = '';
                }
                if(!$label) {
                    $label = $name;
                }

                switch ($type) {
                    case "text":
                    case "url":
                    case "email":
                    case "number":
                        $form_data['form-'.$id] = array("passed"=>false, "clean"=>"", "value"=>"", "section"=>1, "required"=>$required, "type"=>$type,  "placeholder"=>$placeholder, "label"=>$label, "help"=>$help);
                        break;
                    case "textarea":
                        $form_data['form-'.$id] = array("passed"=>false, "clean"=>"", "value"=>"", "section"=>1, "required"=>$required, "type"=>$type,  "placeholder"=>$placeholder, "label"=>$label, "help"=>$help);
                        break; 
                    case "select":
                        $form_data['form-'.$id] = array("passed"=>false, "clean"=>"", "value"=>"", "section"=>1, "required"=>$required, "type"=>$type,  "placeholder"=>$placeholder, "label"=>$label, "options"=>$select_options, "selected_option"=>"", "help"=>$help);
                        break;
                    case "multi_select":
                        $form_data['form-'.$id] = array("passed"=>false, "clean"=>"", "value"=>"", "section"=>1, "required"=>$required, "type"=>$type,  "placeholder"=>$placeholder, "label"=>$label, "options"=>$select_options, "selected_option"=>"", "help"=>$help);
                        break;    
                   case "file":
                        $this->form_settings["enctype"] = ' enctype="multipart/form-data"';
                        $this->form_settings["form_class"] = 'js-check-form-file';
                        $form_data['form-'.$id] = array("passed"=>false, "clean"=>"", "value"=>"", "section"=>1, "required"=>$required, "type"=>$type,  "placeholder"=>$placeholder, "label"=>$label, "accept"=>"pdf", "help"=>$help);
                        break;            
                }           
                    
            endwhile;// End the AFC loop  
            $this->form_settings["form_data"] = $form_data;
        }
    }
}

   


    // return $this->form_settings;   

    
 
}


public function acf_build_form() {
?>
    <?php if ($this->error_count>0): ?>
        <div class="callout alert">
            <h3>Errors Found</h3>
            <p>We're sorry, there has been an error with the form input. Please rectify the <?php echo $this->error_count ?> errors below and resubmit.</p>
            <ul><?php 
            foreach ($this->form_settings["form_data"] as $key => $data) {
                if (!$data["passed"]) {
                    if ($data["help"]): 
                    ?>
                        <li><?php echo $data["help"] ?></li>
                    <?php else: 
                        $help = $data['label'].' is required';
                        if ($data['type']=='email' || $data['type']=='url') {
                            $help .= ' and must be valid';
                        }
                        ?>
                        <li><?php echo $help ?></li>
                    <?php 
                    endif;
                }
             } 
             ?></ul>
        </div>
    <?php endif ?>
    <?php 
        $framework='zurb';
        $options = get_option( 'wp_swift_form_builder_settings' );

        if (isset($options['wp_swift_form_builder_select_css_framework'])) {
            $framework = $options['wp_swift_form_builder_select_css_framework'];
        }

     ?>
    <form method="post" name="<?php echo $this->form_settings["form-name"]; ?>" id="<?php echo $this->form_settings["form-name"]; ?>" class="<?php echo $framework.' '; echo $this->form_settings["form_class"]; ?>"  novalidate<?php echo $this->form_settings["enctype"]; ?>>
        <?php
        $tabIndex = $this->front_end_form_input_loop($this->form_settings["form_data"], $tabIndex=1, $this->form_settings["form_pristine"], $this->form_settings["form_num_error_found"]);// ?>

        <!-- <div id="form-hide-until-focus"> -->
            <?php if ($this->show_mail_receipt): ?>
                <div class="row form-builder">
                    <div class="<?php echo $this->get_form_label_div_class() ?>form-label"></div>
                    <div class="<?php echo $this->get_form_input_div_class() ?>form-input">
                        <div class="checkbox">
                          <input type="checkbox" value="" tabindex=<?php echo $tabIndex; ?> name="mail-receipt" id="mail-receipt"><label for="mail-receipt">Acknowledge me with a mail receipt</label>
                        </div>
                    </div>                  
                </div>                       
            <?php endif ?>      
           <?php if (isset($recpatcha)): ?>
            <!--  <div class="row" id="g-recaptcha-row">
                <div class="columns"><div class="g-recaptcha-wrapper"><div class="g-recaptcha" id="g-recaptcha"></div></div></div>
            </div>   -->
            <!-- <div class="g-recaptcha" data-sitekey="6LelawkUAAAAAHlXmEywVaGXQnhcskUkU3tUnzD7"></div>           -->
        <!-- </div> -->
               
           <?php endif ?>
        <?php $tabIndex++; ?>
        <div class="row form-builder">
            <div class="<?php echo $this->get_form_label_div_class() ?>form-label"></div>
            <div class="<?php echo $this->get_form_input_div_class() ?>form-input">
                <button type="submit" name="<?php echo $this->form_settings["submit-button-name"]; ?>" id="<?php echo $this->form_settings["submit-button-name"]; ?>" class="button large" tabindex=<?php echo $tabIndex; ?>><?php echo $this->form_settings["submit-button-text"]; ?></button>
            </div>
        </div>
    </form> 
<?php   
}

public function front_end_form_input_loop($form_data, $tabIndex=1, $form_pristine=true, $form_num_error_found=0) {
    $i=0;

    foreach ($form_data as $id => $data):
        $tabIndex++;
        $i++;
    // $section_open=false;
        /*if($i!=$data['section']): ?>
            <div class="row">
                <div class="small-12 large-4 columns"></div>
                <div class="small-12 large-8 columns">
                    <h4><?php echo $form_headers[$i]; ?></h4>
                </div>
            </div>
            <?php
            $i=$data['section'];
        endif;*/

        switch ($data['type']) {
            case "section": 
                if ($this->Section_Layout_Addon) {
                    $this->Section_Layout_Addon->section_open($data['section_header'], $data['section_content']);
                }
                else {
                    $this->section_open($data['section_header'], $data['section_content']);
                }
                break; 
            case "section_close":
                if ($this->Section_Layout_Addon) {
                    $this->Section_Layout_Addon->section_close();
                }
                else {
                    $this->section_close();
                } 
                break;               
            case "text":
            case "url":
            case "email":
            case "number":
            case "username":
                $this->bld_form_input($id, $data, $tabIndex);
                break;
            case "hidden":
                $this->bld_form_hidden_input($id, $data);
                break;
            case "textarea":
                $this->bldFormTextarea($id, $data, $form_pristine, $form_num_error_found, $tabIndex);
                break; 
            case "radio":
                $this->build_form_radio($id, $data, $tabIndex);
                break; 
            case "select":
                $this->bldFormSelect($id, $data, $tabIndex, '');
                break;
            case "select2":
                $this->bldFormSelect2($id, $data, $form_pristine, $form_num_error_found, $tabIndex);
            case "multi_select":
                $this->bldFormSelect2($id, $data, $form_pristine, $form_num_error_found, $tabIndex);
                // $this->bldFormSelect($id, $data, $form_pristine, $form_num_error_found, $tabIndex, 'multiple');   
                break; 
            case "file":
                $this->bldFormFileUpload($id, $data, $form_pristine, $form_num_error_found, $tabIndex);
                break; 
            case "date_range":
                bldFormDateRange($id, $data, $form_pristine, $form_num_error_found, $tabIndex, $section_id);
                break;    
            case "password_combo":
                $tabIndex = bldFormPasswordCombo($id, $data, $form_pristine, $form_num_error_found, $tabIndex, $section_id);
                break;                                                               
        }           
    endforeach;
    return $tabIndex;    
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

    public function bld_form_input($id, $data, $tabIndex=0, $section='') {
        // echo "<pre>"; var_dump($data); echo "</pre>";
        $has_error='';
        // echo "<pre>this->form_pristine: "; var_dump($this->form_pristine); echo "</pre>";
        // echo "<pre>this->clear_after_submission "; var_dump($this->clear_after_submission); echo "</pre>";
        // echo "<pre>this->error_count "; var_dump($this->error_count); echo "</pre>";
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
        $name = $id;
        if (isset($data['id-index'])) {
            $id .= '-'.$data['id-index'];
        }
        ?><input 
            type="<?php echo $data['type']; ?>" 
            data-type="<?php echo $data_type; ?>" 
            class="<?php echo $this->get_form_input_class() ?>" 
            id="<?php echo $id; ?>" 
            name="<?php echo $name; ?>" 
            tabindex="<?php echo $tabIndex; ?>" 
            <?php if ( isset($data["value"])): ?> value="<?php echo $data['value'] ?>" <?php endif ?>
            <?php if ( isset($data["placeholder"])): ?> placeholder="<?php echo $data['placeholder'] ?>" <?php endif ?>
            <?php if ( isset($data["section"])): ?> data-section="<?php echo $data["section"] ?>" <?php endif ?>
            <?php echo $data['required']; ?>   
        ><?php 
        $data = $this->after_form_input($id, $data);
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
        $name = $id;
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

        if(!$this->form_pristine && $data['passed']==false) {
            // This input has has error detected so add an error class to the surrounding div
            $has_error = 'has-error';
        }
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
    ?><div class="<?php echo $this->get_form_label_div_class() ?>form-label"><!-- Lorem ipsum dolor sit amet, consectetur adipisicing elit. Suscipit tempore quisquam at aperiam iure in laudantium delectus ipsa molestias dolores. Praesentium dolores cumque quos reiciendis, qui quae expedita cum perspiciatis. -->
        <label for="<?php echo $id; ?>" class="control-label <?php echo $data['required']; ?>"><?php echo $data['label']; ?> <span></span></label>
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
    ?><small class="error"><?php echo $help; ?></small><?php 
    return $data;
}
/*
 * Get the CSS class for div wrapping the label
 */
private function get_form_label_div_class() {
        $framework='zurb';
    $options = get_option( 'wp_swift_form_builder_settings' );
    if (isset($options['wp_swift_form_builder_select_css_framework'])) {
        $framework = $options['wp_swift_form_builder_select_css_framework'];
    }
    // echo "<pre>"; var_dump($array); echo "</pre>";
    if ($this->css_framework === "zurb_foundation") {
        return "small-12 medium-12 large-3 columns ";
    }
    else {
        return "";
    }
}
/*
 * Get the CSS class for div wrapping the label
 */
private function get_form_input_div_class() {
    if ($this->css_framework === "zurb_foundation") {
        return "small-12 medium-12 large-9 columns ";
    }
    else {
        return "";
    }
}

/*
 * Get the CSS class for the input
 */
private function get_form_input_class() {
    return "form-builder-control js-form-builder-control";
}
/*
 * Set the CSS framework
 */
public function set_css_framework($css_framework) {
    $this->css_framework = $css_framework;
}
function bldFormTextarea($id, $data, $form_pristine, $form_num_error_found, $tabIndex) {
    if(!$this->form_pristine) {
        if($this->clear_after_submission && $this->error_count===0) {
            // No errors found so clear the values
            $data['value']=''; 
        }
    }

    $this->before_form_input($id, $data);   
    ?><textarea class="form-control js-form-control" rows="3" id="<?php echo $id; ?>" name="<?php echo $id; ?>" tabindex=<?php echo $tabIndex; ?> placeholder="<?php echo $data['placeholder']; ?>" <?php echo $data['required']; ?>><?php echo $data['value']; ?></textarea><?php
    $this->after_form_input($id, $data);

}


function bldFormSelect($id, $data, $tabIndex, $multiple) {
    if(!$this->form_pristine) {
        if($this->clear_after_submission && $this->error_count===0) {
            // No errors found so clear the selected value
            $data['selected_option']=''; 
        }
    }

    $this->before_form_input($id, $data);   
      ?><select class="form-control js-form-control" id="<?php echo $id; ?>" name="<?php echo $id; ?>" tabindex=<?php echo $tabIndex; ?> <?php echo $data['required']; ?> <?php echo $multiple; ?>>
            <?php if(!$multiple): ?>
                <option value="">Please select an option...</option>
            <?php endif; ?>
            <?php foreach ($data['options'] as $option): ?>
                <?php if($option['option_value'] == $data['selected_option']){ $selected='selected'; } else { $selected=''; }?>
                <option value="<?php echo  $option['option_value']; ?>" <?php echo $selected; ?>><?php echo $option['option']; ?></option>
            <?php endforeach; ?>
        </select><?php
    $this->after_form_input($id, $data);
}
function build_form_radio($id, $data, $tabIndex) {
    // echo "<pre>"; var_dump($id); echo "</pre>";
    // echo "<pre>"; var_dump($data); echo "</pre>";
    if(!$this->form_pristine) {
        if($this->clear_after_submission && $this->error_count===0) {
            // No errors found so clear the selected value
            $data['selected_option']=''; 
        }
    }

    $this->before_form_input($id, $data);
    $count=0;  
    $checked='';
      ?><?php 
        foreach ($data['options'] as $option): $count++;
            if ( ($data['selected_option']=='' && $count==1) || ($data['selected_option']==$option['option_value'])){
                $checked=' checked';
            }
            ?><input id="<?php echo $id.'-'.$count ?>" name="<?php echo $id ?>-radio" type="radio" value="<?php echo $option['option_value'] ?>"<?php echo $checked; ?>>
            <label for="<?php echo $id.'-'.$count ?>"><?php echo $option['option'] ?></label><?php 
        endforeach; ?><?php
    $this->after_form_input($id, $data);
}



function bld_FormSelect2($id, $data, $form_pristine, $form_num_error_found, $tabIndex, $multiple) {
echo "<pre>"; var_dump($data); echo "</pre>";
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
            <select class="form-control js-form-control" id="<?php echo $id; ?>" name="<?php echo $id; ?>" tabindex=<?php echo $tabIndex; ?> <?php echo $data['required']; ?> <?php echo $multiple; ?>>
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
            <h4><?php echo $section_header ?></h4>
        <?php endif ?>
        <?php if ($section_content): ?>
            <p><?php echo $section_content ?></p>
        <?php endif;    
    }

    public function section_close() {
        ?><hr><!-- @end section --><?php       
    } 

    public function html_section_open_side_by_side ($section_header, $section_content) {
        // $html = '<div class="row form-section">'."\n";
        // $html .= '<div class="small-12 medium-6 large-6 columns large-push-6">'."\n";
        //     $html .= '<div class="search-info">'."\n";
        //         $html .= '<h3 class="search-header-info">'.$section_header.'</h3>'."\n";
        //         $html .= '<div class="entry-content">'.$section_content.'</div>'."\n";
        //     $html .= '</div>'."\n";
        // $html .= '</div>'."\n";
        // $html .= '<div class="small-12 medium-6 large-6 columns large-pull-6">  '."\n";  
        // return $html;
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


/*
 * Inputs
 */


/*
 *
 */
public function wp_swift_form_builder_add_admin_menu(  ) { 

    // add_submenu_page( 'tools.php', 'WP Swift: Form Builder', 'WP Swift: Form Builder', 'manage_options', 'wp_swift_form_builder', 'wp_swift_form_builder_options_page' );

    if ( empty ( $GLOBALS['admin_page_hooks']['wp-swift-brightlight-main-menu'] ) ) {
        $options_page = add_options_page( 
            'Form Builder Configuration',
            'Form Builder',
            'manage_options',
            'wp-swift-form-builder-settings-menu',
            array($this, 'wp_swift_form_builder_options_page') 
        );  
    }
    else {
        // Create a sub-menu under the top-level menu
        $options_page = add_submenu_page( 'wp-swift-brightlight-main-menu',
           'Form Builder Configuration', 
           'Form Builder',
           'manage_options', 
           'wp-swift-form-builder-settings-menu',
           array($this, 'wp_swift_form_builder_options_page') );       
    }

}


/*
 *
 */
public function wp_swift_form_builder_settings_init(  ) { 

    register_setting( 'plugin_page', 'wp_swift_form_builder_settings' );

    add_settings_section(
        'wp_swift_form_builder_plugin_page_section', 
        __( 'Set your preferences for the Form Builder here', 'wp-swift-form-builder' ), 
        array($this, 'wp_swift_form_builder_settings_section_callback'), 
        'plugin_page'
    );

    // add_settings_field( 
    //     'wp_swift_form_builder_text_field_0', 
    //     __( 'Settings field description', 'wp-swift-form-builder' ), 
    //     array($this, 'wp_swift_form_builder_text_field_0_render'), 
    //     'plugin_page', 
    //     'wp_swift_form_builder_plugin_page_section' 
    // );

    add_settings_field( 
     'wp_swift_form_builder_checkbox_javascript', 
     __( 'Disable JavaScript', 'wp-swift-form-builder' ), 
     array($this, 'wp_swift_form_builder_checkbox_javascript_render'),  
     'plugin_page', 
     'wp_swift_form_builder_plugin_page_section' 
    );

     add_settings_field( 
     'wp_swift_form_builder_checkbox_css', 
     __( 'Disable CSS', 'wp-swift-form-builder' ), 
     array($this, 'wp_swift_form_builder_checkbox_css_render'),  
     'plugin_page', 
     'wp_swift_form_builder_plugin_page_section' 
    );

    // add_settings_field( 
    //  'wp_swift_form_builder_radio_field_2', 
    //  __( 'Settings field description', 'wp-swift-form-builder' ), 
    //  array($this, 'wp_swift_form_builder_radio_field_2_render'),  
    //  'plugin_page', 
    //  'wp_swift_form_builder_plugin_page_section' 
    // );

    add_settings_field( 
     'wp_swift_form_builder_select_css_framework', 
     __( 'CSS Framework', 'wp-swift-form-builder' ), 
     array($this, 'wp_swift_form_builder_select_css_framework_render'),  
     'plugin_page', 
     'wp_swift_form_builder_plugin_page_section' 
    );



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
    <input type='checkbox' name='wp_swift_form_builder_settings[wp_swift_form_builder_checkbox_javascript]' <?php checked( $options['wp_swift_form_builder_checkbox_javascript'], 1 ); ?> value='1'>
    <small>You can disable JavaScript here if you prefer to user your own or even not at all.</small>
    <?php

}

/*
 *
 */
public function wp_swift_form_builder_checkbox_css_render(  ) { 

    $options = get_option( 'wp_swift_form_builder_settings' );
    ?>
    <input type='checkbox' name='wp_swift_form_builder_settings[wp_swift_form_builder_checkbox_css]' <?php checked( $options['wp_swift_form_builder_checkbox_css'], 1 ); ?> value='1'>
    <small>Same goes for CSS</small>
    <?php

}

/*
 *
 */
public function wp_swift_form_builder_radio_field_2_render(  ) { 

    $options = get_option( 'wp_swift_form_builder_settings' );
    ?>
    <input type='radio' name='wp_swift_form_builder_settings[wp_swift_form_builder_radio_field_2]' <?php checked( $options['wp_swift_form_builder_radio_field_2'], 1 ); ?> value='1'>
    <?php

}


/*
 *
 */
public function wp_swift_form_builder_select_css_framework_render(  ) { 

    $options = get_option( 'wp_swift_form_builder_settings' );
    ?>
    <select name='wp_swift_form_builder_settings[wp_swift_form_builder_select_css_framework]'>
        <option value='zurb' <?php selected( $options['wp_swift_form_builder_select_css_framework'], 'zurb' ); ?>>Zurb Foundation</option>
        <option value='custom' <?php selected( $options['wp_swift_form_builder_select_css_framework'], 'custom' ); ?>>None</option>
    </select>

<?php

}


/*
 *
 */
public function wp_swift_form_builder_settings_section_callback(  ) { 

    echo __( 'This section description', 'wp-swift-form-builder' );

}


/*
 *
 */
public function wp_swift_form_builder_options_page(  ) { 
// if (isset($_POST)) {
// echo "<pre>"; var_dump($_POST); echo "</pre>";
// }
// if ( get_option( 'wp_swift_google_analytics' )) {
//     $wp_swift_google_analytics = get_option( 'wp_swift_google_analytics' );
//     echo "<pre>"; var_dump($wp_swift_google_analytics); echo "</pre>";
// }
// if ( get_option( 'wp_swift_form_builder_settings' )) {
//     $wp_swift_form_builder_settings = get_option( 'wp_swift_form_builder_settings' );
//     echo "<pre>"; var_dump($wp_swift_form_builder_settings); echo "</pre>";
// }
    ?>
    <div id="form-builder-wrap" class="wrap">
    <h2>WP Swift: Form Builder</h2>

    <form action='options.php' method='post'>

        
        <?php
        settings_fields( 'plugin_page' );
        do_settings_sections( 'plugin_page' );
        submit_button();
        ?>

    </form>
    </div>
    <?php

}
}
// Initialize the plugin
$form_builder_plugin = new WP_Swift_Form_Builder_Plugin();