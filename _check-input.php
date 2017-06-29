<?php
/*
 * Check an individual form input field and sets the array with the findings 
 *
 * @param $key      an array key that matches the form input name (POST key)
 * @param $value    the value of the form input
 */
// public function check_input($key, $value){

$this->form_inputs[$key]['value'] = $value;

if($this->form_inputs[$key]['required'] && $this->form_inputs[$key]['value']=='') {
    $this->increase_error_count();
    return;
}

else if(!$this->form_inputs[$key]['required'] && $this->form_inputs[$key]['value']=='') {
    $this->form_inputs[$key]['clean'] = $this->form_inputs[$key]['value'];
    $this->form_inputs[$key]['passed'] = true;
    return;
}

if(!is_array($this->form_inputs[$key]['value'])) {
    $this->form_inputs[$key]['value'] = trim($this->form_inputs[$key]['value']);
   
}

switch ($this->form_inputs[$key]['type']) {
    case "text":
    case "textarea":
        $this->form_inputs[$key]['clean'] = sanitize_text_field( $this->form_inputs[$key]['value'] );
        break;
    case "username":
        $username_strlen = strlen ( $this->form_inputs[$key]['value']  );
        if ($username_strlen<4 || $username_strlen>30) {
            $this->increase_error_count();
            return $this->form_inputs[$key];
        }
        $this->form_inputs[$key]['clean'] = sanitize_user( $this->form_inputs[$key]['value'], $strict=true ); 
        break;
    case "email":
        if ( !is_email( $this->form_inputs[$key]['value'] ) ) { 
            $this->increase_error_count();
            return $this->form_inputs[$key]; 
        }
        else {
            $this->form_inputs[$key]['clean'] = sanitize_email( $this->form_inputs[$key]['value'] );  
        }
        break;
    case "number":
        if ( !is_numeric( $this->form_inputs[$key]['value'] ) ) { 
            $this->increase_error_count();
            return $this->form_inputs[$key]; 
        }
        else {
            $this->form_inputs[$key]['clean'] = $this->form_inputs[$key]['value'];  
        }
        break;        
    case "url":
        if (filter_var($this->form_inputs[$key]['value'], FILTER_VALIDATE_URL) === false) {
            $this->increase_error_count();
            return $this->form_inputs[$key];
        }
        else {
            $this->form_inputs[$key]['clean'] = $this->form_inputs[$key]['value'];
        }
        break;
    case "select2":
    case "select":
        $this->form_inputs[$key]['selected_option'] = $value;
        break;
    case "file":     
        break; 
    case "hidden":
            if (isset($this->form_inputs[$key]['nonce'])) {
                $retrieved_nonce = $value;
                if (!wp_verify_nonce($retrieved_nonce, 'search_nonce' ) ) {
                    $this->increase_error_count();
                    die( 'Failed security check' );
                    return;  
                }
            }
            if (isset($this->form_inputs[$key]['expected'])) {
                if ($this->form_inputs[$key]['expected'] != $value ) {
                    $this->increase_error_count();
                    return;  
                }
            }             
        break; 
    case "password":
            break; 
    case "checkbox":
            $options = $this->form_inputs[$key]["options"];
            $clean = '';
            foreach ($options as $option_key => $option) {
                if ( in_array($option["option_value"], $value)) {
                    $options[$option_key]["checked"] = true;
                    $clean .= $option["option"].', ';
                }
            }
            $clean = rtrim( $clean, ', ');
            $this->form_inputs[$key]["options"] = $options;
            $this->form_inputs[$key]['clean'] = $clean;
            break;                          
}
// esc_attr() - Escaping for HTML attributes. Encodes the <, >, &, ” and ‘ (less than, greater than, ampersand, double quote and single quote) characters. Will never double encode entities.
$this->form_inputs[$key]['passed'] = true;