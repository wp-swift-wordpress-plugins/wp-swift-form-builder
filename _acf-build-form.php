<?php
/*
 * acf_build_form()
 */
if ($this->get_error_count()>0): ?>
    <div class="callout warning">
        <h3>Errors Found</h3>
        <p>We're sorry, there has been an error with the form input. Please rectify the <?php echo $this->get_error_count() ?> errors below and resubmit.</p>
        <ul><?php 
            if ($this->list_form_errors_in_warning_panel) {
               foreach ($this->form_inputs as $key => $data) {

                    if (isset($data["passed"]) && !$data["passed"] && $data["type"] != "checkbox") {
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
            }
            if (count($this->extra_error_msgs)) {
            	foreach ($this->extra_error_msgs as $key => $msg) {
            	?>
                    <li><?php echo $msg ?></li>
                <?php 
            	}
            } 
         ?></ul>
    </div>

<?php 
// endif;
// $this->success_msg = "Testing";

elseif($this->get_error_count()===0):
?>
    <?php if ($this->success_msg !== ''): ?>
        <div class="callout warning">
            <h3>Form Saved</h3>
            <?php echo $this->success_msg; ?>
        </div>
    <?php endif ?>
<?php
endif;

if (count($this->extra_msgs ) > 0 && $this->get_error_count()===0): ?>
    <div class="callout warning">
        <h3>Notifications</h3>
        <ul><?php 
            if (count($this->extra_msgs)) {
                foreach ($this->extra_msgs as $key => $msg) {
                ?>
                    <li><?php echo $msg ?></li>
                <?php 
                }
            } 
         ?></ul>
    </div>
<?php 
endif;

$framework='zurb';
$options = get_option( 'wp_swift_form_builder_settings' );

if (isset($options['wp_swift_form_builder_select_css_framework'])) {
    $framework = $options['wp_swift_form_builder_select_css_framework'];
}
?>
<form method="post" <?php echo $this->action; ?> name="<?php echo $this->form_name; ?>" id="<?php echo $this->form_id; ?>" class="<?php echo $framework.' '; echo $this->form_class.' '; echo $this->form_name ?>"  novalidate<?php echo $this->enctype; ?>>
    <?php
        $this->front_end_form_input_loop($this->form_inputs, $this->tab_index, $this->form_pristine, $this->error_count);
    ?>

    <!-- <div id="form-hide-until-focus"> -->
        <?php if ($this->show_mail_receipt): ?>
            <div class="row form-builder">
                <div class="<?php echo $this->get_form_label_div_class() ?>form-label"></div>
                <div class="<?php echo $this->get_form_input_div_class() ?>form-input">
                    <div class="checkbox">
                      <input type="checkbox" value="" tabindex=<?php echo $this->tab_index; ?> name="mail-receipt" id="mail-receipt"><label for="mail-receipt">Acknowledge me with a mail receipt</label>
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

    <div class="row form-builder">
        <div class="<?php echo $this->get_form_label_div_class() ?>form-label"></div>
        <div class="<?php echo $this->get_form_input_div_class() ?>form-input">
            <button type="submit" name="<?php echo $this->submit_button_name; ?>" id="<?php echo $this->submit_button_id; ?>" class="button large" tabindex="<?php echo $this->tab_index++; ?>"><?php echo $this->submit_button_text; ?></button>
        </div>
    </div>
</form>