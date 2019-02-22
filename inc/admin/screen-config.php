<?php


if( isset($_POST['mgs_forms_config_acc']) && $_POST['mgs_forms_config_acc']=='save' ){
	if( $_POST['mgs_forms_debug_mode']!='' && $_POST['mgs_forms_debug_mode']=='yes' ){
		update_option( 'MGS_FORMS_DEBUG', 'true' );
		define( 'MGS_FORMS_DEBUG', true );
	}else{
		update_option( 'MGS_FORMS_DEBUG', 'false' );
		define( 'MGS_FORMS_DEBUG', false );
	}
	update_option( 'mgs_forms_reCAPTCHA_public_key', $_POST['mgs_forms_reCAPTCHA_public_key'] );
	update_option( 'mgs_forms_reCAPTCHA_private_key', $_POST['mgs_forms_reCAPTCHA_private_key'] );
}
?>
<div class="wrap mgs-admin-warp">
	<?php MGS_Forms_Builder_Admin::header(); ?>
    
    <div class="feature-section">
        <div class="mgs-registration-form">
            <form id="mgs_forms_config_form" method="post">
                <div class="mgs-important-notice mgs-registration-form-container">
                    <p class="about-description">Debug</p>
                    <label for="mgs_forms_debug_mode"><input type="checkbox" id="mgs_forms_debug_mode" name="mgs_forms_debug_mode" value="yes" <?php if( MGS_FORMS_DEBUG ) echo 'checked'?>> Activar mode de depuración</label>
                </div>
                <div class="mgs-important-notice mgs-registration-form-container">
                    <p class="about-description">reCAPTCHA</p>
                    <label for="mgs_forms_reCAPTCHA_public_key">Public Site Key</label>
                    <input type="text" id="mgs_forms_reCAPTCHA_public_key" name="mgs_forms_reCAPTCHA_public_key" value="<?php echo get_option('mgs_forms_reCAPTCHA_public_key')?>" />
                    <label for="mgs_forms_reCAPTCHA_private_key">Secret Site Key</label>
                    <input type="text" id="mgs_forms_reCAPTCHA_private_key" name="mgs_forms_reCAPTCHA_private_key" value="<?php echo get_option('mgs_forms_reCAPTCHA_private_key')?>" />
                </div>
                <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary button-large mgs-large-button mgs-register" value="Guardar"></p>
				<input type="hidden" name="mgs_forms_config_acc" id="mgs_forms_config_acc" value="save">
			</form>
        </div>
	</div>
    

    <?php MGS_Forms_Builder_Admin::footer(); ?>
</div>