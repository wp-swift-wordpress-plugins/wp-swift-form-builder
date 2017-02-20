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
    /*
     * Initializes the plugin.
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_javascript') );
    }



 
    public function enqueue_javascript () {
        wp_enqueue_script( $handle='wp-swift-form-builder', $src=plugins_url( '/assets/javascript/wp-swift-form-builder.js', __FILE__ ), $deps=null, $ver=null, $in_footer=true );
    }
    /*
     * Add the css file
     */
    public function lorem() {
        return "Lorem ipsum dolor sit amet, consectetur adipisicing elit. Autem amet temporibus qui optio unde, deleniti ipsum. Nulla amet explicabo veniam repudiandae eveniet iure laborum quam distinctio facilis, ea reiciendis animi!";
    }
    public function add($i, $j) {
        if (is_integer($i) && is_integer($j)) {
            return $i + $j;
            # code...
        }
        else {
            return 'NAN';
        }
    }

public function set_form_data($form_inputs="form_inputs", $post_id, $args=false, $option=false) {
        $this->form_settings = array();
        // $this->post_id = $post_id;
        $this->form_settings["form_pristine"] = true;
        $this->form_settings["form_num_error_found"] = 0;
        $this->form_settings["enctype"] = "";
        $this->form_settings["form_class"] = "";
        $this->form_settings["option"]=$option;
        if (isset($args["show_mail_receipt"])) {
            $this->show_mail_receipt = true;
        }
        if (get_sub_field('form_name', $post_id)) {
             $this->form_settings["form-name"] = sanitize_title_with_dashes( get_sub_field('form_name') );
        }
        elseif(isset($args["form_name"])) {
            $this->form_settings["form-name"] = sanitize_title_with_dashes($args["form_name"]);
        }
        else {
             $this->form_settings["form-name"] = "request-form";
        }
        if (get_sub_field('button_text', $post_id)) {
              $this->form_settings["submit-button-text"] = get_sub_field('button_text');
        }
        elseif(isset($args["button_text"])) {
            $this->form_settings["submit-button-text"] = $args["button_text"];
        }
        else {
              $this->form_settings["submit-button-text"] = "Submit Form";
        }

// $this->form_settings["form-name"] = "request-form";
// $this->form_settings["submit-button-text"] = "Submit Form";
$this->form_settings["submit-button-name"] = "submit-".$this->form_settings["form-name"];


        $this->form_settings["error_class"] = "";
        $this->form_settings["ajax"] = false;
        $form_data = array();
        
if (is_array($form_inputs)) {
    $this->form_settings["form_data"] = $form_inputs;
}
else if (is_string($form_inputs)) {

     // Construct the array that makes the form
    if ( have_rows($form_inputs, $option) ) :

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
    endif; 
}

   


    // return $this->form_settings;   

    
 
}


public function acf_build_form() {
?>
    <form method="post" name="<?php echo $this->form_settings["form-name"]; ?>" id="<?php echo $this->form_settings["form-name"]; ?>" class="<?php echo $this->form_settings["form_class"]; ?>"  novalidate<?php echo $this->form_settings["enctype"]; ?>>
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

    foreach ($form_data as $id => $settings):
        $tabIndex++;
        $i++;
    // $section_open=false;
        /*if($i!=$settings['section']): ?>
            <div class="row">
                <div class="small-12 large-4 columns"></div>
                <div class="small-12 large-8 columns">
                    <h4><?php echo $form_headers[$i]; ?></h4>
                </div>
            </div>
            <?php
            $i=$settings['section'];
        endif;*/

        switch ($settings['type']) {
            case "section": 
                echo $this->html_section_open_side_by_side ( $settings['section_header'], $settings['section_content']);
                break; 
            case "section_close": 
                echo $this->html_section_close_side_by_side ( );
                break;               
            case "text":
            case "url":
            case "email":
            case "number":
            case "username":
                $this->bldFormInput($id, $settings, $form_pristine, $form_num_error_found, $tabIndex);
                break;
            case "textarea":
                $this->bldFormTextarea($id, $settings, $form_pristine, $form_num_error_found, $tabIndex);
                break; 
            case "select":
                $this->bldFormSelect($id, $settings, $form_pristine, $form_num_error_found, $tabIndex, '');
                break;
            case "select2":
                $this->bldFormSelect2($id, $settings, $form_pristine, $form_num_error_found, $tabIndex);
            case "multi_select":
                $this->bldFormSelect2($id, $settings, $form_pristine, $form_num_error_found, $tabIndex);
                // $this->bldFormSelect($id, $settings, $form_pristine, $form_num_error_found, $tabIndex, 'multiple');   
                break; 
            case "file":
                $this->bldFormFileUpload($id, $settings, $form_pristine, $form_num_error_found, $tabIndex);
                break; 
            case "date_range":
                bldFormDateRange($id, $settings, $form_pristine, $form_num_error_found, $tabIndex, $section_id);
                break;    
            case "password_combo":
                $tabIndex = bldFormPasswordCombo($id, $settings, $form_pristine, $form_num_error_found, $tabIndex, $section_id);
                break;                                                               
        }           
    endforeach;
    return $tabIndex;    
}

    public function bldFormInput($id, $data, $form_pristine, $form_num_error_found, $tabIndex=0, $section='') {
        $has_error='';
        if(!$form_pristine) {
            if(!$form_num_error_found) {
                // No errors found so clear the values
                $data['value']=''; 
            }
        }
        if($section) {
            $section = ' data-section="'.$section.'"';
        }
        else {
            $section='';
        }

        $data_type = $data['type'];
        if ($data['type']=='username') {
            $data['type']='text';
            $data_type = 'username';
        }
        $this->form_element_open($id, $data, $form_pristine);
        $this->form_element_anchor($id);
        $this->form_element_label($id, $data);
        $this->form_element_form_input_open();
        ?><input 
            type="<?php echo $data['type']; ?>" 
            data-type="<?php echo $data_type; ?>" 
            class="form-builder-control js-form-builder-control" 
            id="<?php echo $id; ?>" 
            name="<?php echo $id; ?>" 
            value="<?php echo $data['value']; ?>" 
            placeholder="<?php echo $data['placeholder']; ?>" 
            tabindex=<?php echo $tabIndex; ?> 
            <?php echo $data['required']; ?>
            <?php echo $section; ?>
        ><?php 
        $this->form_element_help($data);
        $this->form_element_form_input_close();
        $this->form_element_close($id, $data);
    }  

private function form_element_open($id, $data, $form_pristine) {
        $has_error='';
        if(!$form_pristine) {
            if(!$form_num_error_found) {
                // No errors found so clear the values
                $data['value']=''; 
            }
        }

        if(!$form_pristine && $data['passed']==false) {
            // This input has has error detected so add an error class to the surrounding div
            $has_error = 'has-error';
        }
    ?><!-- @start form element -->
    <div class="row form-group form-builder <?php echo $has_error; ?>" 
    id="<?php echo $id; ?>-form-group"><?php 
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
    $data['help'] ? $help= $data['help'] : $help = $data['label']. ' is required';       
    ?><small class="error"><?php echo $help; ?></small><?php 
}
/*
 * Get the CSS class for div wrapping the label
 */
private function get_form_label_div_class() {
    if ($this->css_framework === "zurb_foundation") {
        return "small-12 medium-3 large-3 columns ";
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
        return "small-12 medium-9 large-9 columns ";
    }
    else {
        return "";
    }
}
/*
 * Set the CSS framework
 */
public function set_css_framework($css_framework) {
    $this->css_framework = $css_framework;
}
function bldFormTextarea($id, $data, $form_pristine, $form_num_error_found, $tabIndex) {
    $has_error='';
    if(!$form_pristine) {
        if(!$form_num_error_found) {
            // No errors found so clear the values
            $data['value']=''; 
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
        <div class="small-12 medium-9 large-9  columns">
            <textarea class="form-control js-form-control" rows="3" id="<?php echo $id; ?>" name="<?php echo $id; ?>" tabindex=<?php echo $tabIndex; ?> placeholder="<?php echo $data['placeholder']; ?>" <?php echo $data['required']; ?>><?php echo $data['value']; ?></textarea>
            <?php $data['help'] ? $help= $data['help'] : $help = $data['label']. ' is required'; ?>
            <small class="error"><?php echo $help; ?></small>
        </div>
    </div>
    <?php 
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

private function process_form($form_settings, $post) {
    $send_email=false;//Debug variable
    $form_settings["form_pristine"]=false;
    
    # Google recaptcha library
    // require_once "recaptchalib.php";
    // # your secret key
    // $secret = "6LcnawkUAAAAALJRqPuRKjMLBDfBcJDJQB0JD31j";
    // # empty response
    // $response = null;
    // # check secret key
    // $reCaptcha = new ReCaptcha($secret);

    // if submitted check response
    // if ($post["g-recaptcha-response"]) {
    //     $response = $reCaptcha->verifyResponse(
    //         $_SERVER["REMOTE_ADDR"],
    //         $post["g-recaptcha-response"]
    //     );
    // }

    // if ($response != null && $response->success) {
        $mail_receipt=false;//auto-reponse flag
        if(isset($post['mail-receipt'])){
            $mail_receipt=true;//Send an auto-response to user
        }
        // include('_email-template.php');
   
        //Loop through the POST and validate. Store the values in $form_data
        foreach ($post as $key => $value) {
            if (($key!='submit-request-form') && ($key!='mail-receipt') && ($key!='form-file-upload') && ($key!='g-recaptcha-response')) { //Skip the button and mail-receipt checkbox
                $form_settings["form_data"][$key] = check_input($form_settings["form_data"][$key], $value);//Validate input    
                // echo '<pre>';var_dump($form_settings["form_data"][$key]); echo '</pre>';
            }
        }

        // Loop through form1_data and increase form1_num_error_found count for each error
        foreach ($form_settings["form_data"] as $key => $value) {
            if(!$form_settings["form_data"][$key]['passed']) {
                //An error has been found in this input so increase the count
                $form_settings["form_num_error_found"]++;
            }
        }

        if($form_settings["form_num_error_found"]) {
            // Error has been found in user input
            $form_settings["response_msg"] = "We're sorry, there has been an error with the form input.<br>Please rectify the ".$form_num_error_found." errors below and resubmit.";
            $form_settings["error_class"] = 'error';
            // echo "<pre>"; var_dump($form_settings); echo "</pre>";
        }
        else {  

            //If a debug email is set in ACF, send the email there instead of the admin email
            get_field('debug_email', $form_settings["option"]) ? $to = get_field('debug_email', $form_settings["option"]) : $to = get_option('admin_email');

            // Set reponse subject for email (ACF)
            get_field('response_subject', $form_settings["option"]) ? $response_subject = get_field('response_subject', $form_settings["option"]).$date : $response_subject = "New Enquiry - ".date("Y-m-d H:i:s"). ' GMT';

            // Set reponse message
            // echo "<pre>".$to."</pre>";
            // echo "<pre>".$response_subject."</pre>";
 // Set reponse message
            get_field('response_message') ? $response_message = get_field('response_message').$date : $response_message = '<p>A website user has made the following enquiry.</p>';

            // Set auto_response_message
            get_field('auto_response_message') ? $auto_response_message = get_field('auto_response_message') : $auto_response_message = 'Thank you very much for your enquiry. A representative will be contacting you shortly.';

            // Set auto_response_message
            get_field('browser_output_headder') ? $browser_output_headder = get_field('browser_output_headder') : $browser_output_headder = 'Hold Tight, We\'ll Get Back To You';


            // Start making the string that will be sent in the email
            $email_string =$response_message;
            //Create string that will hold table of users input
            $table= '<table style="width:100%">';
            $j=0;
            foreach ($form_settings["form_data"] as $key => $value) {
                $required = $value['required'];
                $type = $value['type'];
                $table.= '<tr>';
                $table.= '<th style="width:30%">'.ucwords(str_replace('-', ' ',substr($key, 5))).':</th>';

                if($value['type']=='select') {
                    $table.= '<td>'.ucwords(str_replace('-', ' ',$value['clean'])).'</td>';
                }
                else {
                    $table.= '<td>'.$value['clean'].'</td>';
                }                    
                
                $table.= '</tr>';
                $j++;
            }
            $table.= '</table>';

            // Add the table of values to the string
            $email_string .= $table;

            if( get_field('email', 'option') ) {
                $from_email = get_field('email', 'option');
            }
            else {
                $from_email = get_bloginfo('admin_email');
            }
            $from_email = get_bloginfo('admin_email');
            // $headers = array('From: '.html_entity_decode(get_bloginfo('name')).' <'.$from_email.'>');

            if ($send_email) {
                $status = wp_mail($to, $response_subject.' - '.date("D j M Y, H:i"). ' GMT',  wrap_email($email_string));
            }

            // Construct the reponse to show to the user
            $confirmation_output_wrapper_open = '<div id="contact-thank-you">';                  
            $confirmation_output_wrapper_open .= '<div class="callout primary" data-closable="slide-out-right">';
            $confirmation_output = '<h3>'.$browser_output_headder.'</h3>';
            $confirmation_output .= $auto_response_message;
            $confirmation_output .= '<p>A copy of your enquiry is shown below.</p>';
            $confirmation_output .= $table;
            $confirmation_outputwrapper_close = '<button class="close-button" aria-label="Dismiss alert" type="button" data-close>';
            $confirmation_outputwrapper_close .= '<span aria-hidden="true">&times;</span>';
            $confirmation_outputwrapper_close .= '</button>';
            $confirmation_output_wrapper_close .= '</div></div>';
            if (!$form_settings["ajax"]) {
                $confirmation_output = $confirmation_output_wrapper_open.$confirmation_output. $confirmation_outputwrapper_close ;
            }
            $form_settings["confirmation_output"] = $confirmation_output;

            //If the user has requested it, send an email acknowledgement
            if($mail_receipt) {
                $auto_response_subject='Auto-response (no-reply)';
                if( get_field('auto_response_subject') ) {
                    $auto_response_subject = get_field('auto_response_subject');
                }
                $user_response_msg = $auto_response_message;
                $user_response_msg .= '<p>A copy of your enquiry is shown below.</p>';
                $user_response_msg .= $table;
                if ($send_email) {
                    $status = wp_mail($form_settings["form_data"]['form-email']['clean'], $auto_response_subject, wrap_email($user_response_msg));
                }
                
            }              

        }     
    // } else {
    //     $form_settings["response_msg"] = "We're sorry, please use the recaptcha.<br>".json_encode($response);
    //     $form_settings["error_class"] = 'error';
    // }

    return $form_settings;
}


}
// Initialize the plugin
$form_builder_plugin = new WP_Swift_Form_Builder_Plugin();