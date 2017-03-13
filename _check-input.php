<?php
/*
 * Check an individual form input field and sets the array with the findings 
 *
 * @param $key      an array key that matches the form input name (POST key)
 * @param $value    the value of the form input
 */
// public function check_input($key, $value){
// echo "<pre>";var_dump($key);echo "</pre>";
// echo "<pre>";var_dump($value);echo "</pre>";
$this->form_inputs[$key]['value'] = $value;
// $this->form_inputs[$key]['value'] = $value;
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
    // echo "value <pre>"; var_dump($this->form_inputs[$key]['value']); echo "</pre>";
    $this->form_inputs[$key]['value'] = trim($this->form_inputs[$key]['value']);
    // $this->form_inputs[$key]['value'] = stripslashes($this->form_inputs[$key]['value']);
    // $this->form_inputs[$key]['value'] = htmlspecialchars($this->form_inputs[$key]['value']);       
}
// elseif(is_array($this->form_inputs[$key]['value'])) {
//     echo "is_array<pre>"; var_dump($this->form_inputs[$key]['value']); echo "</pre>";
// }
// echo "type:<pre>"; var_dump($this->form_inputs[$key]['type']); echo "</pre><br>";
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
            // echo "<pre>"; var_dump($value); echo "</pre>";  
            break; 
    case "checkbox":
            if(!is_array($this->form_inputs[$key]['value'])) {
                $this->form_inputs[$key]['selected_option'] = trim($this->form_inputs[$key]['value']);
            }
            // elseif(is_array($this->form_inputs[$key]['value'])) {
            //     echo "is_array<pre>"; var_dump($this->form_inputs[$key]['value']); echo "</pre>";
            // }
            break;                          
    // default: echo "<pre>"; var_dump($value); echo "</pre>";
}
// esc_attr() - Escaping for HTML attributes. Encodes the <, >, &, ” and ‘ (less than, greater than, ampersand, double quote and single quote) characters. Will never double encode entities.
// $this->form_inputs[$key]['clean'] =  esc_attr($this->form_inputs[$key]['value']);
$this->form_inputs[$key]['passed'] = true;