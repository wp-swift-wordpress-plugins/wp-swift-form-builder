<?php

/*
 *
 */

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
        case "username": // Wordpress username
        case "password":
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
        case "checkbox":
            $this->build_form_checkbox($id, $data, $tabIndex);
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