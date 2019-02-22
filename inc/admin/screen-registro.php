<?php
global $MGS_UP;
if( isset($_POST['mgs_forms_license']) ){
	$license_key = $_POST['mgs_forms_license'];
	$val_input = $license_key;
	$MGS_UP->Set_License($license_key);
	if( !$MGS_UP->active() ){
		$err_msg = $MGS_UP->err;
	}
}else{
	$MGS_UP->ValLicence();
}
?>
<div class="wrap mgs-admin-warp">
	<?php MGS_Forms_Builder_Admin::header(); ?>
    
    <div class="feature-section">
        <div class="mgs-important-notice">
            <p class="about-description"><?php echo __('¡Gracias por elegir MGS Forms para Fusion Builder! Su producto debe estar registrado para recibir todas las actualizaciones automáticas.', 'mgs-forms');?></p>
        </div>
        <div class="mgs-important-notice mgs-registration-form-container">
	        <p class="about-description">
            	<?php
                if( $MGS_UP->is_licensed()==true ){
					_e('Felicitaciones, su producto esta registrado.', 'mgs-forms');
				}else{
					echo sprintf(__('Ingrese su %s para completar el registro.', 'mgs-forms'), 'Licencia');
				}
				?>
             </p>    
	        <div class="mgs-registration-form">
    		    <form id="mgs_product_registration" method="post">
					<?php
                    if( $MGS_UP->is_licensed() ){
                        $val_input = $MGS_UP->get_license();
						$estado = 'disabled';
						$type = 'password';
                        echo '<span class="dashicons dashicons-yes mgs-icon-key"></span>';
                    }else{
						$val_input = '';
						$estado = '';
						$type = 'text';
                        echo '<span class="dashicons dashicons-no mgs-icon-key"></span>';
                    }
                    ?>
		        	<input type="<?php echo $type?>" name="mgs_forms_license" id="mgs_forms_license" value="<?php echo $val_input?>" <?php echo $estado?> />
                    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary button-large mgs-large-button mgs-register" value="Enviar" <?php echo $estado?>></p>
		        </form>
        		<p class="error-invalid-token"><?php echo $err_msg?></p>
	        </div>
        </div>
	</div>
    

    <?php MGS_Forms_Builder_Admin::footer(); ?>
</div>