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
    $this->form_settings["form_data"][$key]['value'] = $value;
    // $this->form_settings["form_data"][$key]['value'] = $value;
    if($this->form_settings["form_data"][$key]['required'] && $this->form_settings["form_data"][$key]['value']=='') {
        $this->increase_error_count();
        return;
    }
    else if(!$this->form_settings["form_data"][$key]['required'] && $this->form_settings["form_data"][$key]['value']=='') {
        $this->form_settings["form_data"][$key]['clean'] = $this->form_settings["form_data"][$key]['value'];
        $this->form_settings["form_data"][$key]['passed'] = true;
        return;
    }

    if(!is_array($this->form_settings["form_data"][$key]['value'])) {
        // echo "value <pre>"; var_dump($this->form_settings["form_data"][$key]['value']); echo "</pre>";
        $this->form_settings["form_data"][$key]['value'] = trim($this->form_settings["form_data"][$key]['value']);
        // $this->form_settings["form_data"][$key]['value'] = stripslashes($this->form_settings["form_data"][$key]['value']);
        // $this->form_settings["form_data"][$key]['value'] = htmlspecialchars($this->form_settings["form_data"][$key]['value']);       
    }
    // elseif(is_array($this->form_settings["form_data"][$key]['value'])) {
    //     echo "is_array<pre>"; var_dump($this->form_settings["form_data"][$key]['value']); echo "</pre>";
    // }
    // echo "type:<pre>"; var_dump($this->form_settings["form_data"][$key]['type']); echo "</pre><br>";
    switch ($this->form_settings["form_data"][$key]['type']) {
        case "text":
        case "textarea":
            $this->form_settings["form_data"][$key]['clean'] = sanitize_text_field( $this->form_settings["form_data"][$key]['value'] );
            break;
        case "username":
            $username_strlen = strlen ( $this->form_settings["form_data"][$key]['value']  );
            if ($username_strlen<4 || $username_strlen>30) {
                $this->increase_error_count();
                return $this->form_settings["form_data"][$key];
            }
            $this->form_settings["form_data"][$key]['clean'] = sanitize_user( $this->form_settings["form_data"][$key]['value'], $strict=true ); 
            break;
        case "email":
            if ( !is_email( $this->form_settings["form_data"][$key]['value'] ) ) { 
                $this->increase_error_count();
                return $this->form_settings["form_data"][$key]; 
            }
            else {
                $this->form_settings["form_data"][$key]['clean'] = sanitize_email( $this->form_settings["form_data"][$key]['value'] );  
            }
            break;
        case "url":
            if (filter_var($this->form_settings["form_data"][$key]['value'], FILTER_VALIDATE_URL) === false) {
                $this->increase_error_count();
                return $this->form_settings["form_data"][$key];
            }
            else {
                $this->form_settings["form_data"][$key]['clean'] = $this->form_settings["form_data"][$key]['value'];
            }
            break;
        case "select2":
        case "select":
            $this->form_settings["form_data"][$key]['selected_option'] = $value;
            break;
        case "file":     
            break; 
        case "hidden":
                if (isset($this->form_settings["form_data"][$key]['nonce'])) {
                    $retrieved_nonce = $value;
                    if (!wp_verify_nonce($retrieved_nonce, 'search_nonce' ) ) {
                        $this->increase_error_count();
                        die( 'Failed security check' );
                        return;  
                    }
                }
                if (isset($this->form_settings["form_data"][$key]['expected'])) {
                    echo "<pre>";var_dump($this->form_settings["form_data"][$key]['expected']);echo "</pre>";
                    if ($this->form_settings["form_data"][$key]['expected'] != $value ) {
                        $this->increase_error_count();
                        return;  
                    }
                }             
            break; 
        case "password":
                // echo "<pre>"; var_dump($value); echo "</pre>";  
                break; 
        case "checkbox":
                if(!is_array($this->form_settings["form_data"][$key]['value'])) {
                    $this->form_settings["form_data"][$key]['selected_option'] = trim($this->form_settings["form_data"][$key]['value']);
                }
                // elseif(is_array($this->form_settings["form_data"][$key]['value'])) {
                //     echo "is_array<pre>"; var_dump($this->form_settings["form_data"][$key]['value']); echo "</pre>";
                // }
                break;                          
        // default: echo "<pre>"; var_dump($value); echo "</pre>";
    }
    // esc_attr() - Escaping for HTML attributes. Encodes the <, >, &, ” and ‘ (less than, greater than, ampersand, double quote and single quote) characters. Will never double encode entities.
    // $this->form_settings["form_data"][$key]['clean'] =  esc_attr($this->form_settings["form_data"][$key]['value']);
    $this->form_settings["form_data"][$key]['passed'] = true;
    // return $data;
    return;
// }