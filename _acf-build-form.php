<?php
/*
 * acf_build_form()
 */
if ($this->error_count>0): ?>
    <div class="callout alert">
        <h3>Errors Found</h3>
        <p>We're sorry, there has been an error with the form input. Please rectify the <?php echo $this->error_count ?> errors below and resubmit.</p>
        <ul><?php 
            if ($this->check_from_data_for_errors) {
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
endif;

if (count($this->extra_msgs ) > 0 ): ?>
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
<form method="post" <?php echo $this->action; ?> name="<?php echo $this->form_settings["form-name"]; ?>" id="<?php echo $this->form_settings["form-name"]; ?>" class="<?php echo $framework.' '; echo $this->form_class; ?>"  novalidate<?php echo $this->form_settings["enctype"]; ?>>
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