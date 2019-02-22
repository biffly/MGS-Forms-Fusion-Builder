<div class="wrap mgs-admin-warp">
	<?php MGS_Forms_Builder_Admin::header(); ?>
    <form method="post" target="_blank" action="<?php echo MGS_FORMS_PLUGIN_DIR_URL.'csv-out.php'?>">
        <div class="mgs-forms-settings">    
            <div class="mgs-forms-option">
                <div class="mgs-forms-option-title">
                    <h3><?php echo __('Formulario', 'mgs-forms' ); ?></h3>
                    <span class="mgs-forms-option-label"><p><?php echo __('Seleccione un formulario para ver los registros.', 'mgs-forms'); ?></p></span>
                </div>
                <div class="mgs-forms-option-field" style="width:74%;">
                    <?php
                    global $wpdb;
                    global $table_name;
					
					if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'")==$table_name ){					
						$forms_ids = $wpdb->get_results("SELECT post_id FROM ".$table_name." GROUP BY post_id ORDER BY post_id");
						$state = '';
						$placeholder = __('Seleccione formulario', 'mgs-forms');
						if( count($forms_ids)<=0 ){
							$state = 'disabled';
							$placeholder = __('No hay registros en la base de datos', 'mgs-forms');
						}
                    ?>
                        <select id="formulario" name="formulario" class="mgs-select-field" <?php echo $state?> placeholder="<?php echo $placeholder?>">
                            <option value=""><?php echo $placeholder?></option>
                            <?php foreach($forms_ids as $f){?>
                            <option value="<?php echo $f->post_id?>"><?php echo get_the_title($f->post_id)?></option>
                            <?php }?>
                        </select>
                    <?php
					}else{
						echo '<div class="error notice mgs-tables-error"><h3>Debera solucionar el problena de la creación de la tabla para poder utilizar este plugin.</h3></div>';
					}
					?>
                </div>
                <div class="mgs-forms-option-field" style="width:24%">
                	<input type="submit" class="button button-primary mgs-forms-save-settings mgs-forms-export-csv" value="<?php echo __('Descargar CSV')?>" disabled>
                </div>
                <hr />
                <div class="mgs-forms-option-title">
                    <h3><?php echo __('Ocultar campos', 'mgs-forms' ); ?></h3>
                    <span class="mgs-forms-option-label"><p><?php echo __('Seleccione los campos que desea ocultar del lsitado. Esto solo afecta a el listado en pantalla, el CSV tendra toda la información recolectada.', 'mgs-forms'); ?></p></span>
                </div>
                <div class="mgs-forms-option-field" style="width:100%;">
                    <div class="clear"></div>
                    <label class="mgs-forms-chk-replace-fa">
                    	<input type="checkbox" name="hide_fields[]" class="mgs-forms-control form-control" value="referrer" checked/>
                        <i class="fa fa-square-o"></i><i class="fa fa-check-square-o"></i> <span class="label">Referer</span>
                    </label> 
                    <label class="mgs-forms-chk-replace-fa">
                    	<input type="checkbox" name="hide_fields[]" class="mgs-forms-control form-control" value="agent" checked/>
                        <i class="fa fa-square-o"></i><i class="fa fa-check-square-o"></i> <span class="label">Agent</span>
                    </label> 
                    
                </div>
                <div class="clear"></div>
            </div>
        </div>
        <div class="mgs-forms-settings list-regs"></div>
        <div class="clear"></div>
	</form>
    
    <script>
		
		jQuery.fn.dataTable.ext.errMode = function(settings, helpPage, message){ 
			jQuery('.list-regs').prepend('<div class="mgs-table-error"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> Los datos no parecen consistentes, puede deberse a que el formulario fue editado luego de su creación.</div>');
		};
		
		var espera = '<div class="mgs-mgs-espera"><i class="fa fa-exclamation-circle" aria-hidden="true"></i> Seleccione unu formulario para ver los resultados</div>';
		var cargando = '<div class="mgs-mgs-espera"><i class="fa fa-spinner fa-spin fa-2x fa-fw"></i> Cargando resultados</div><table id="mgs-registros" class="table table-striped table-bordered dataTable" width="100%"></table>';
		var tabla ='';
		
		jQuery('.list-regs').html(espera);
		
		jQuery('#formulario').on('change', function(){
			LoadRegistros();
		});
		
		jQuery("input[name='hide_fields[]']").on('change', function(){
			LoadRegistros();
		});
		
		jQuery('body').on('click', 'a.mgs-forms-del-registro', function(e){
			e.preventDefault();
			var row = jQuery(this).parent().parent();
			var d_id = jQuery(this).data('id');
			bootbox.confirm({
				title		: 'Eliminar?',
				message		: 'Realmente desea eliminar este registro? La operación no se puede deshacer.',
				callback	: function(result){
					if( result ){
						jQuery('.mgs-forms-del-registro').fadeOut();
						row.addClass('selected');
						DeleteRegistro(d_id);
					}
				}
			});
		});
		
		function DeleteRegistro(id){
			jQuery.ajax({ 
				data	: {
					action	: 'delete_registros',
					id		: id,
				},
				type	: 'post',
				url		: ajaxurl,
				success	: function(data){
					var resp = jQuery.parseJSON(data);
					if( resp.est=='OK' ){
						tabla.row('.selected').remove().draw(false);
						bootbox.alert("Registro eliminado con éxito.");
					}else{
						bootbox.alert("No se pudo eliminar el registro.");
						jQuery('tr').removeClass('selected');
					}
					jQuery('a.mgs-forms-del-registro').fadeIn();
				}
			});
		}
		
		
		function LoadRegistros(){
			var form = jQuery('#formulario').val();
			var hide_f = [];
			
			jQuery("input[name='hide_fields[]']").each(function(){
				if( jQuery(this).is(':checked') ){
					hide_f.push(jQuery(this).val());
				}
			});
			
			if( form!='' ){
				jQuery('.mgs-forms-export-csv').prop('disabled', false);
				jQuery('.list-regs').html(cargando);
				jQuery.ajax({ 
					data	: {
						action	: 'get_registros',
						form	: form,
						hide	: hide_f
					},
					type	: 'post',
					url		: ajaxurl,
					success	: function(data){
						var resp = jQuery.parseJSON(data);
						if( resp.est=='OK' ){
							var cab = [];			
							jQuery.each(resp.header, function(i, item){	
									cab.push( {title:i} );
							});
							cab[cab.length-1] = {title:'', orderable:false};
							
							tabla = jQuery('#mgs-registros').DataTable({
								data	: resp.data,
								columns	: cab
							});
							
							jQuery('.mgs-mgs-espera').hide().remove();
						}
						
					}
				});
			}else{
				jQuery('.mgs-forms-export-csv').prop('disabled', true);
				jQuery('.list-regs').html(espera);
			}
		}
		
	</script>
    
    <?php MGS_Forms_Builder_Admin::footer(); ?>
</div>