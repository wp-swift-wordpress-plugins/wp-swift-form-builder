<?php
/*
 * @function  set_form_data
 * Set the form data
 *
 * @param 		$form_inputs		string or array
 * @param 		$post_id 			the post id
 * @param 		$form_inputs		string or array
 */
// public function set_form_data($form_inputs="form_inputs", $post_id, $args=false, $attributes= false, $option=false)


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

if (isset($args["action"]) && $args["action"]!='') {
	$this->action = 'action="'.$args["action"].'"';
}
if (isset($args["check_from_data_for_errors"])) {
    $this->check_from_data_for_errors = $args["check_from_data_for_errors"];
}
if (isset($args["form_class"])) {
    $this->form_class = $args["form_class"];
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
                    case "password":
                        $form_data['form-'.$id] = array("passed"=>false, "clean"=>"", "value"=>"", "section"=>1, "required"=>$required, "type"=>$type,  "placeholder"=>$placeholder, "label"=>$label, "help"=>$help);
                        break;
                    case "textarea":
                        $form_data['form-'.$id] = array("passed"=>false, "clean"=>"", "value"=>"", "section"=>1, "required"=>$required, "type"=>$type,  "placeholder"=>$placeholder, "label"=>$label, "help"=>$help);
                        break; 
                    case "checkbox":
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