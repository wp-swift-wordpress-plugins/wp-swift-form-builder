<?php

/*
 *
 */

$i=0;

foreach ($this->form_inputs as $id => $input):
    // $tabIndex++;
    // $this->tab_index++;

    $i++;
// $section_open=false;
    /*if($i!=$input['section']): ?>
        <div class="row">
            <div class="small-12 large-4 columns"></div>
            <div class="small-12 large-8 columns">
                <h4><?php echo $form_headers[$i]; ?></h4>
            </div>
        </div>
        <?php
        $i=$input['section'];
    endif;*/

    switch ($input['type']) {
        case "section": 
            if ($this->Section_Layout_Addon) {
                $this->Section_Layout_Addon->section_open($input['section_header'], $input['section_content']);
            }
            else {
                $this->section_open($input['section_header'], $input['section_content']);
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
        case "username": // Wordpress username
        case "password":
            $this->bld_form_input($id, $input);
            break;
        case "hidden":
            $this->bld_form_hidden_input($id, $input);
            break;
        case "textarea":
            $this->bld_form_textarea($id, $input);
            break; 
        case "radio":
            $this->build_form_radio($id, $input);
        case "checkbox":
            $this->build_form_checkbox($id, $input);
            break; 
        case "select":
            $this->bldFormSelect($id, $input, '');
            break;
        // case "select2":
        //     $this->bldFormSelect2($id, $input, $form_pristine, $form_num_error_found, $tabIndex);
        // case "multi_select":
        //     $this->bldFormSelect2($id, $input, $form_pristine, $form_num_error_found, $tabIndex);
            // $this->bldFormSelect($id, $input, $form_pristine, $form_num_error_found, $tabIndex, 'multiple');   
            // break; 
        // case "file":
        //     $this->bldFormFileUpload($id, $input, $form_pristine, $form_num_error_found, $tabIndex);
        //     break; 
        // case "date_range":
        //     bldFormDateRange($id, $input, $form_pristine, $form_num_error_found, $tabIndex, $section_id);
        //     break;    
        // case "password_combo":
        //     bldFormPasswordCombo($id, $input, $form_pristine, $form_num_error_found, $tabIndex, $section_id);
            // break;                                                               
    }           
endforeach;