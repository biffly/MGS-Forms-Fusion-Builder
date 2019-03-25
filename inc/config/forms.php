<?php
global $mgs_forms_options;
global $mgs_forms_elements_options;
$mgs_forms_options = array(
	'name'          				=> 'MGS Form',
	'multi'         				=> 'multi_element_parent',
	'shortcode'     				=> 'fusion_mgs_forms',
	'element_child' 				=> 'fusion_mgs_form_elemento',
	'icon'          				=> 'fa fa-code-fork',
	'preview'       				=> MGS_FORMS_PLUGIN_DIR . 'js/preview/preview.php',
	'preview_id'					=> 'fusion-builder-block-module-mgs-forms-addon-preview-template',
	'params'        => array(
		/*	Default element content NO borrar	*/
		array(
			'type'        => 'tinymce',
			'heading'     => __( 'Content', 'fusion-builder' ),
			'description' => __( 'Enter some content for this quote.', 'fusion-builder' ),
			'param_name'  => 'element_content',
			'value'       => '[fusion_mgs_form_elemento]Formulario[/fusion_mgs_form_elemento]',
		),
		/*	ID	*/
		array(
			'type'        	=> 'textfield',
			'heading'     	=> 'Nombre',
			'description' 	=> __( 'Nombre del formulario', 'mgs-forms' ),
			'param_name'  	=> 'name',
			'value'       	=> '',
			'placeholder' 	=> true,
		),
					
		/*	Boton	*/
		array(
			'type'        	=> 'textfield',
			'heading'     	=> __( 'Etiqueta boton', 'mgs-forms' ),
			'description' 	=> __( 'Texto que aparece en el boton de enviar del formulario.', 'mgs-forms' ),
			'param_name'  	=> 'text_boton',
			'value'       	=> '',
			'default'		=> __('Enviar', 'mgs-forms'),
			'group'			=> __('Boton', 'mgs-forms')
		),
		array(
			'type'        	=> 'iconpicker',
			'heading'     	=> __( 'Icono', 'mgs-form' ),
			'param_name'  	=> 'icon_boton',
			'value'       	=> '',
			'description' 	=> __( 'Haga click para seleccionar un icono, click nuevamente para deseleccionarlo', 'mgs-forms' ),
			'group'			=> __('Boton', 'mgs-forms')
		),
		array(
			'type'        	=> 'radio_button_set',
			'heading'     	=> __( 'Posición del icono', 'fusion-builder' ),
			'description' 	=> esc_attr__( 'Seleccione la posición del icono en el boton.', 'mgs-froms' ),
			'param_name'  	=> 'icon_position_boton',
			'value'       	=> array(
				'left'	=> __( 'Izquierda', 'mgs-forms' ),
				'right'	=> __( 'Derecha', 'mgs-forms' ),
			),
			'default'     	=> 'left',
			'dependency'  	=> array(
				array(
					'element'	=> 'icon_boton',
					'value'    	=> '',
					'operator' 	=> '!=',
				),
			),
			'group'			=> __('Boton', 'mgs-forms')
		),
		array(
			'type'        	=> 'radio_button_set',
			'heading'     	=> __( 'Divisor del icono', 'mgs-forms' ),
			'description' 	=> __( 'Seleccione para mostrar un divisor entre el icono y el texto.', 'mgs-forms' ),
			'param_name'  	=> 'icon_divider_boton',
			'default'     	=> 'no',
			'dependency'  	=> array(
				array(
					'element'	=> 'icon_boton',
					'value'    	=> '',
					'operator' 	=> '!=',
				),
			),
			'value'			=> array(
				'yes'	=> __( 'Si', 'mgs-forms' ),
				'no'	=> __( 'No', 'mgs-forms' ),
			),
			'group'			=> __('Boton', 'mgs-forms')
		),
		array(
			'type'        	=> 'radio_button_set',
			'heading'     	=> __( 'Utilizar colores del tema', 'mgs-forms' ),
			'description' 	=> __( 'Determina si el boton utiliza los colores configurados como default en el tema o unos personalizados.', 'mgs-forms' ),
			'param_name'  	=> 'boton_theme_colors',
			'default'     	=> 'yes',
			'value'			=> array(
				'yes'	=> __( 'Por defecto', 'mgs-forms' ),
				'no'	=> __( 'Personalizados', 'mgs-forms' ),
			),
			'group'			=> __('Boton', 'mgs-forms')
		),
		array(
			'type'			=> 'colorpicker',
			'heading'		=> __( 'Color del boton', 'mgs-forms' ),
			'description'   => __( 'Color del fondo del boton para su estado normal.', 'mgs-forms' ),
			'param_name'    => 'boton_color_fondo_normal',
			'value'         => '',
			'default'		=> '#a0ce4e',
			'dependency'  	=> array(
				array(
					'element'	=> 'boton_theme_colors',
					'value'		=> 'no',
					'operator'	=> '==',
				),
			),
			'group'			=> __('Boton', 'mgs-forms')
		),
		array(
			'type'			=> 'colorpicker',
			'heading'		=> __( 'Color del boton hover', 'mgs-forms' ),
			'description'   => __( 'Color del fondo del boton para el hover.', 'mgs-forms' ),
			'param_name'    => 'boton_color_fondo_hover',
			'value'         => '',
			'default'		=> '#96c346',
			'dependency'  	=> array(
				array(
					'element'	=> 'boton_theme_colors',
					'value'		=> 'no',
					'operator'	=> '==',
				),
			),
			'group'			=> __('Boton', 'mgs-forms')
		),
		array(
			'type'			=> 'colorpicker',
			'heading'		=> __( 'Color del texto', 'mgs-forms' ),
			'description'   => __( 'Color del texto del boton para su estado normal.', 'mgs-forms' ),
			'param_name'    => 'boton_color_texto_normal',
			'value'         => '',
			'default'		=> '#ffffff',
			'dependency'  	=> array(
				array(
					'element'	=> 'boton_theme_colors',
					'value'		=> 'no',
					'operator'	=> '==',
				),
			),
			'group'			=> __('Boton', 'mgs-forms')
		),
		array(
			'type'			=> 'colorpicker',
			'heading'		=> __( 'Color del texto hover', 'mgs-forms' ),
			'description'   => __( 'Color del texto del boton para el hover.', 'mgs-forms' ),
			'param_name'    => 'boton_color_texto_hover',
			'value'         => '',
			'default'		=> '#ffffff',
			'dependency'  	=> array(
				array(
					'element'	=> 'boton_theme_colors',
					'value'		=> 'no',
					'operator'	=> '==',
				),
			),
			'group'			=> __('Boton', 'mgs-forms')
		),
		
		/*	Mostrar pag de OK	*/
		array(
			'type'        	=> 'radio_button_set',
			'heading'     	=> __( 'Redirección', 'mgs-forms' ),
			'description' 	=> __( 'Redirigir al completarse el envio?', 'mgs-forms' ),
			'param_name'  	=> 'redirect',
			'default'     	=> 'no',
			'value'       	=> array(
				'yes'	=> __( 'Si', 'mgs-forms' ),
				'no'	=> __( 'No', 'mgs-forms' ),
			),
			'group'			=> __('Redirección', 'mgs-forms')
		),
		array(
			'type'        	=> 'textfield',
			'heading'     	=> __( 'Destino OK', 'mgs-forms' ),
			'description' 	=> __( 'URL a donde se redirige al completar el envio.', 'mgs-forms' ),
			'param_name'  	=> 'redirect_url_ok',
			'value'       	=> '',
			'dependency'  	=> array(
				array(
					'element'  	=> 'redirect',
					'value'    	=> 'yes',
					'operator' 	=> '==',
				),
			),
			'group'			=> __('Redirección', 'mgs-forms')
		),
		array(
			'type'        	=> 'textfield',
			'heading'     	=> __( 'Destino Error', 'mgs-forms' ),
			'description' 	=> __( 'URL a donde se redirige cuando ocurre un error al completar el formulario. Dejar en Blanco para utilizar el mensaje de error sin redirigir.', 'mgs-forms' ),
			'param_name'  	=> 'redirect_url_bad',
			'value'       	=> '',
			'dependency'  	=> array(
				array(
					'element'  	=> 'redirect',
					'value'    	=> 'yes',
					'operator' 	=> '==',
				),
			),
			'group'			=> __('Redirección', 'mgs-forms')
		),
		array(
			'type'			=> 'textarea',
			'heading'     	=> __( 'Mensaje Error', 'mgs-forms' ),
			'description' 	=> __( 'Texto que se mostrara si ocurre un error al enviar el formulario. Se mostrara dentro de un elemento `Alert` de Fusion Builder', 'mgs-forms' ),
			'param_name'  	=> 'msj_bad',
			'default'      	=> 'Ocurrió un error al enviar el formulario. Intente más tarde.',
			'value'			=> '',
			'group'			=> __('Redirección', 'mgs-forms')
		),
		array(
			'type'			=> 'textarea',
			'heading'     	=> __( 'Mensaje OK', 'mgs-forms' ),
			'description' 	=> __( 'Texto que se mostrara al completar exitosamente el formulario.  Se mostrara dentro de un elemento `Alert` de Fusion Builder', 'mgs-forms' ),
			'param_name'  	=> 'msj_ok',
			'default'      	=> 'Completo con éxito el formulario.',
			'value'			=> '',
			'dependency'  	=> array(
				array(
					'element'  	=> 'redirect',
					'value'    	=> 'yes',
					'operator' 	=> '!=',
				),
			),
			'group'			=> __('Redirección', 'mgs-forms')
		),
		
		/*	Envia email RAW	*/
		array(
			'type'        => 'radio_button_set',
			'heading'     => __( 'Enviar admin', 'mgs-forms' ),
			'description' => __( 'Enviar datos guardados en formato RAW?', 'mgs-forms' ),
			'param_name'  => 'send_mail_raw',
			'default'     => 'no',
			'value'       => array(
				'yes'	=> __( 'Si', 'mgs-forms' ),
				'no'	=> __( 'No', 'mgs-forms' ),
			),
			'group'			=> __('Email', 'mgs-forms')
		),
		array(
			'type'        => 'radio_button_set',
			'heading'     => __( 'Responder a?', 'mgs-forms' ),
			'description' => sprintf(__('Agrega una cabecera al correo que fuerza la respuesta del mismo a quien lleno en formulario. Esto solo funciona si en el formulario hay un campo del tipo "<strong>%s</strong>" y el mismo tiene como ID: "<strong>%s</strong>"', 'mgs-forms'), __('Correo electrónico', 'mgs-forms'), 'correo'),
			'param_name'  => 'send_mail_raw_responder_a',
			'default'     => 'no',
			'dependency'  => array(
				array(
					'element'  => 'send_mail_raw',
					'value'    => 'yes',
					'operator' => '==',
				),
			),
			'value'       => array(
				'yes'	=> __( 'Si', 'mgs-forms' ),
				'no'	=> __( 'No', 'mgs-forms' ),
			),
			'group'			=> __('Email', 'mgs-forms')
		),
		array(
			'type'        => 'textfield',
			'heading'     => __( 'Destinatario', 'mgs-forms' ),
			'description' => __( 'Correo del destinatario de los datos en formato RAW', 'mgs-forms' ),
			'param_name'  => 'send_mail_raw_to',
			'value'       => '',
			'dependency'  => array(
				array(
					'element'  => 'send_mail_raw',
					'value'    => 'yes',
					'operator' => '==',
				),
			),
			'group'			=> __('Email', 'mgs-forms')
		),
		array(
			'type'        => 'textfield',
			'heading'     => __( 'Remitante', 'mgs-forms' ),
			'description' => __( 'Direccion de correo desde la que se enviara la información. Por defecto <i>no-reply@'.MGS_Forms::$dominio.'</i>', 'mgs-forms' ),
			'param_name'  => 'send_mail_raw_from',
			'value'       => 'no-reply@'.MGS_Forms::$dominio,
			'dependency'  => array(
				array(
					'element'  => 'send_mail_raw',
					'value'    => 'yes',
					'operator' => '==',
				),
			),
			'group'			=> __('Email', 'mgs-forms')
		),
		array(
			'type'        => 'textfield',
			'heading'     => __( 'Nombre del remitante', 'mgs-forms' ),
			'description' => __( 'Nombre que aparecera asiociado a la cuanta de correo desde donde se enviara la información. Por defecto <i>'.get_bloginfo('name').'</i>', 'mgs-forms' ),
			'param_name'  => 'send_mail_raw_from_name',
			'value'       => get_bloginfo('name'),
			'dependency'  => array(
				array(
					'element'  => 'send_mail_raw',
					'value'    => 'yes',
					'operator' => '==',
				),
			),
			'group'			=> __('Email', 'mgs-forms')
		),
		array(
			'type'        => 'textfield',
			'heading'     => __( 'Asunto', 'mgs-forms' ),
			'description' => __( 'Coloque un asunto para el correo. Por defecto se ulizara: <i>Envio de información</i>', 'mgs-forms' ),
			'param_name'  => 'send_mail_raw_subject',
			'value'       => MGS_Forms::$config_plg_send_mail_raw_subject,
			'dependency'  => array(
				array(
					'element'  => 'send_mail_raw',
					'value'    => 'yes',
					'operator' => '==',
				),
			),
			'group'			=> __('Email', 'mgs-forms')
		),
				
		/*	E-Mail OK	*/
		array(
			'type'        => 'radio_button_set',
			'heading'     => __('Email OK', 'mgs-forms' ),
			'description' => sprintf(__('Enviar email de OK a quien complete el formulario? Debera tener creado un campo tipo "<strong>%s</strong>" y debera tener por ID: "<strong>%s</strong>" en forma obligatoria para que esta funcionalidad se ejecute correctamente.', 'mgs-forms'), __('Correo electrónico', 'mgs-forms'), 'correo'),
			'param_name'  => 'send_mail_ok',
			'default'     => 'no',
			'value'       => array(
				'yes'	=> __( 'Si', 'mgs-forms' ),
				'no'	=> __( 'No', 'mgs-forms' ),
			),
			'group'			=> __('Email OK', 'mgs-forms')
		),
		array(
			'type'        => 'textfield',
			'heading'     => __( 'Remitente', 'mgs-forms' ),
			'description' => __( 'Correo desde el cual se envia en email de agracecimiento por completar el formulario.', 'mgs-forms' ),
			'param_name'  => 'send_mail_ok_remitente',
			'value'       => '',
			'dependency'  => array(
				array(
					'element'  => 'send_mail_ok',
					'value'    => 'yes',
					'operator' => '==',
				),
			),
			'group'			=> __('Email OK', 'mgs-forms')
		),
		array(
			'type'        => 'textfield',
			'heading'     => __( 'Nombre remitente', 'mgs-forms' ),
			'description' => __( 'Nombre asociado a la cuenta de correo desde el cual se envia en email de agracecimiento por completar el formulario.', 'mgs-forms' ),
			'param_name'  => 'send_mail_ok_remitente_name',
			'value'       => '',
			'dependency'  => array(
				array(
					'element'  => 'send_mail_ok',
					'value'    => 'yes',
					'operator' => '==',
				),
			),
			'group'			=> __('Email OK', 'mgs-forms')
		),
		array(
			'type'        => 'textfield',
			'heading'     => __( 'Asunto', 'mgs-forms' ),
			'description' => __( 'Asunto del email de agradecimiento por completar el formulario..', 'mgs-forms' ),
			'param_name'  => 'send_mail_ok_subject',
			'value'       => '',
			'dependency'  => array(
				array(
					'element'  => 'send_mail_ok',
					'value'    => 'yes',
					'operator' => '==',
				),
			),
			'group'			=> __('Email OK', 'mgs-forms')
		),
		array(
			'type'        	=> 'textarea',
			'heading'     	=> __('Contenido', 'mgs-forms'),
			'description' 	=> __('Aqui debera colocar el contenido o cuerpo del correo. Puede utilizar los <strong>ID\'s</strong> de los elementos del formulario para colocar información dinamica dentro del mismo. Sigiendo la sigiente regla: "{ID} el nombre del ID debera ser colocado en mayusculas siempre, sin importar si en el elemento esta definido en minusculas.". Colocando el <strong>ID</strong> entre corchetes se reemplazara por el valor enviado para ese <strong>ID</strong>.', 'mgs-forms'),
			'param_name'  	=> 'send_email_ok_plantilla',
			'dependency'  	=> array(
				array(
					'element'  	=> 'send_mail_ok',
					'value'    	=> 'yes',
					'operator'	=> '==',
				),
			),
			'group'			=> __('Email OK', 'mgs-forms'),
			'escape_html'					=> true,
		),
		
		/*	jquery Validator	*/
		array(
			'type'        	=> 'radio_button_set',
			'heading'     	=> __( 'Validación', 'mgs-forms' ),
			'description' 	=> __( 'Activar jQuery validator?', 'mgs-forms' ),
			'param_name'  	=> 'validator',
			'default'     	=> 'yes',
			'value'			=> array(
				'yes'	=> __( 'Si', 'mgs-forms' ),
				'no'	=> __( 'No', 'mgs-forms' ),
			),
			'group'			=> __('Validar', 'mgs-forms')
		),
		array(
			'type'			=> 'colorpicker',
			'heading'		=> __( 'Color tooltip', 'mgs-forms' ),
			'description'   => __( 'Color del tooltip de error.', 'mgs-forms' ),
			'param_name'    => 'tooltip_color',
			'value'         => '',
			'default'     	=> '#ff2b2b',
			'dependency'  	=> array(
				array(
					'element'	=> 'validator',
					'value'		=> 'yes',
					'operator'	=> '==',
				),
			),
			'group'			=> __('Validar', 'mgs-forms')
		),
		array(
			'type'			=> 'colorpicker',
			'heading'		=> __( 'Texto tooltip', 'mgs-forms' ),
			'description'   => __( 'Color del texto del tooltip de error.', 'mgs-forms' ),
			'param_name'    => 'tooltip_text_color',
			'value'         => '',
			'default'		=> '#ffffff',
			'dependency'  	=> array(
				array(
					'element'	=> 'validator',
					'value'		=> 'yes',
					'operator'	=> '==',
				),
			),
			'group'			=> __('Validar', 'mgs-forms')
		),
		array(
			'type'        	=> 'textfield',
			'heading'     	=> __( 'Campo obligatorio', 'mgs-forms' ),
			'description'	=> __( 'Campo obligatorio', 'mgs-forms' ),
			'param_name'  	=> 'jq_val_msj_required',
			'value'       	=> 'Este campo es obligatorio.',
			'dependency'  	=> array(
				array(
					'element'	=> 'validator',
					'value'		=> 'yes',
					'operator'	=> '==',
				),
			),
			'group'			=> __('Validar', 'mgs-forms')
		),
		array(
			'type'			=> 'textfield',
			'heading'		=> __( 'Correo no valido', 'mgs-forms' ),
			'description'	=> __( 'Correo no valido', 'mgs-forms' ),
			'param_name'	=> 'jq_val_msj_email',
			'value'			=> 'Por favor, escribe una dirección de correo válida.',
			'dependency'	=> array(
				array(
					'element'	=> 'validator',
					'value'		=> 'yes',
					'operator'	=> '==',
				),
			),
			'group'			=> __('Validar', 'mgs-forms')
		),
	),
);

$mgs_forms_elements_options = array(
	'name'              => __( 'Elemento', 'mgs-forms' ),
	'shortcode'         => 'fusion_mgs_form_elemento',
	'hide_from_builder' => true,
	'allow_generator'   => true,
	'on_save'			=> 'mgs_forms_test_save_content',
	'admin_enqueue_js'  => MGS_FORMS_PLUGIN_DIR_URL.'js/shortcodes/filter.js',
	'params'            => array(
		/*	ID	*/
		array(
			'type'        => 'textfield',
			'heading'     => 'ID',
			'description' => __( 'Nombre del elemento', 'mgs-forms' ),
			'param_name'  => 'id',
			'value'       => '',
			'placeholder' => true,
		),
		
		/*	type	*/
		array(
			'type'			=> 'select',
			'heading'		=> __('Tipo', 'mgs-forms'),
			'description'	=> __('Tipo de elemento a mostrar.', 'mgs-forms'),
			'param_name'	=> 'tipo',
			'value'			=> array(
				'text'			=> __('Texto', 'mgs-forms'),
				'textarea'		=> __('Area de texto', 'mgs-forms'),
				'email'			=> __('Correo electrónico', 'mgs-forms'),
				'number'		=> __('Número', 'mgs-forms'),
				'date'			=> __('Fecha', 'mgs-forms'),
				'select'		=> __('Select box', 'mgs-forms'),
				'checkbox'		=> __('Casilla de verificación unica', 'mgs-forms'),
				'checkboxs'		=> __('Casillas de verificación', 'mgs-forms'),
				'radios'		=> __('Casillas de opción', 'mgs-forms'),
				'label'			=> __('Label simple', 'mgs-forms'),
				'code'			=> __('Codigo', 'mgs-forms'),
				'upload'		=> __('Subida de archivo', 'mgs-forms'),
				'recaptcha'		=> __('Google reCAPTCHA', 'mgs-forms'),
			),
			'default'		=> 'text'
		),
		
		/*	fecha	*/
		array(
			'type'			=> 'select',
			'heading'		=> __('Formato', 'mgs-forms'),
			'description'	=> __('Formato de la fecha.', 'mgs-forms'),
			'param_name'	=> 'fecha_format',
			'value'			=> array(
				'dd/mm/yyyy'	=> 'dd/mm/yyyy',
				'mm/dd/yyyy'	=> 'mm/dd/yyyy',
			),
			'default'		=> 'dd/mm/yyyy',
			'dependency'  => array(
				array(
					'element'  => 'tipo',
					'value'    => 'date',
					'operator' => '==',
				),
			),
		),
		array(
			'type'			=> 'select',
			'heading'		=> __('Idioma', 'mgs-forms'),
			'description'	=> __('Idioma en que se mostraran las fechas.', 'mgs-forms'),
			'param_name'	=> 'fecha_lang',
			'value'			=> array(
				__('Español', 'mgs-forms')		=> 'es',
				__('Catalan', 'mgs-forms')		=> 'ca',
				__('Ingles', 'mgs-forms')		=> 'en',
			),
			'default'		=> 'es',
			'dependency'  => array(
				array(
					'element'  => 'tipo',
					'value'    => 'date',
					'operator' => '==',
				),
			),
		),
		
		/*	JS Code	*/
		/*
		array(
			'type'				=> 'code',
			'heading'          	=> __( 'Codigo', 'mgs-form' ),
			'description'      	=> __( 'Este no es un elemento visible en el formulario, puede ser utilizado para agregar codigo javascript o CSS, debera omitir las etiquetas &lt;script&gt; o &lt;style&gt;', 'mgs-form' ),
			'param_name'       	=> 'code',
			'value'       		=> '',
			'dependency'  => array(
				array(
					'element'  => 'tipo',
					'value'    => 'code',
					'operator' => '==',
				),
			),
		),
		array(
			'type'        => 'radio_button_set',
			'heading'     => __( 'Tipo', 'mgs-forms' ),
			'description' => __( 'Debe especificar que tipo de contenido esta introduciendo.', 'mgs-forms' ),
			'param_name'  => 'code_type',
			'default'     => 'JS',
			'value'       => array(
				'JS'	=> __( 'JS', 'mgs-forms' ),
				'CSS'	=> __( 'CSS', 'mgs-forms' ),
				'HTML'	=> __( 'HTML', 'mgs-forms' ),
			),
			'dependency'  => array(
				array(
					'element'  => 'tipo',
					'value'    => 'code',
					'operator' => '==',
				),
			)
		),*/
		
		/*	radios	*/
		array(
			'type'        => 'textarea',
			'heading'     => __( 'Elementos', 'mgs-forms' ),
			'description' => __( 'Debera colocar un elemento por linea. Primero etiqueta o label y luego el valor separados por <strong>::</strong>. Ej: Etiqueta :: valor. Si se omite el valor se utilizara la etiqueta para el mismo', 'mgs-forms' ),
			'param_name'  => 'radios_values',
			'value'       => '',
			'dependency'  => array(
				array(
					'element'  => 'tipo',
					'value'    => 'radios',
					'operator' => '==',
				),
			)
		),
		array(
			'type'        => 'radio_button_set',
			'heading'		=> __('Estilo', 'mgs-forms'),
			'description' => __('Utilizar fontawesome para reemplazar los radio buttons?.', 'mgs-forms'),
			'param_name'  => 'radios_replace_fa',
			'default'     => 'no',
			'value'       => array(
				'yes'	=> __( 'Si', 'mgs-forms' ),
				'no'	=> __( 'No', 'mgs-forms' ),
			),
			'dependency'  => array(
				array(
					'element'  => 'tipo',
					'value'    => 'radios',
					'operator' => '==',
				),
			)
		),
		
		/*	checkboxs	*/
		array(
			'type'        => 'textarea',
			'heading'     => __( 'Elementos', 'mgs-forms' ),
			'description' => __( 'Debera colocar un elemento por linea. Primero etiqueta o label y luego el valor separados por <strong>::</strong>. Ej: Etiqueta :: valor. Si se omite el valor se utilizara la etiqueta para el mismo', 'mgs-forms' ),
			'param_name'  => 'checkboxs_values',
			'value'       => '',
			'dependency'  => array(
				array(
					'element'  => 'tipo',
					'value'    => 'checkboxs',
					'operator' => '==',
				),
			)
		),
		array(
			'type'        => 'radio_button_set',
			'heading'		=> __('Estilo', 'mgs-forms'),
			'description' => __('Utilizar fontawesome para reemplazar los checkboxs?.', 'mgs-forms'),
			'param_name'  => 'checkboxs_replace_fa',
			'default'     => 'no',
			'value'       => array(
				'yes'	=> __( 'Si', 'mgs-forms' ),
				'no'	=> __( 'No', 'mgs-forms' ),
			),
			'dependency'  => array(
				array(
					'element'  => 'tipo',
					'value'    => 'checkboxs',
					'operator' => '==',
				),
			)
		),
		
		/*	checkbox unico	*/
		array(
			'type'        	=> 'textfield',
			'heading'     	=> __( 'Texto', 'mgs-forms' ),
			'description' 	=> __( 'Texto de la casilla de verificación. Puede incluir un link respetando esta estructura: {texto link}(URL link). Ej: He leído y acepto el {aviso legal}(url-aviso-legal)', 'mgs-forms' ),
			'param_name'  	=> 'checkbox_label',
			'value'       	=> '',
			'placeholder'	=> true,
			'escape_html'	=> true,
			'dependency'  	=> array(
				array(
					'element'  => 'tipo',
					'value'    => 'checkbox',
					'operator' => '==',
				),
			)
		),
		array(
			'type'        => 'textfield',
			'heading'     => __( 'Valor', 'mgs-forms' ),
			'description' => __( 'Valor enviado si la casilla de verificación esta marcada.', 'mgs-forms' ),
			'param_name'  => 'checkbox_value',
			'value'       => '',
			'placeholder' => true,
			'dependency'  => array(
				array(
					'element'  => 'tipo',
					'value'    => 'checkbox',
					'operator' => '==',
				),
			)
		),
		array(
			'type'        => 'radio_button_set',
			'heading'		=> __('Marcado', 'mgs-forms'),
			'description' => __('Determina si la casilla de verificación aparece parcada o no por defecto.', 'mgs-forms'),
			'param_name'  => 'checkbox_status',
			'default'     => 'no',
			'value'       => array(
				'yes'	=> __( 'Si', 'mgs-forms' ),
				'no'	=> __( 'No', 'mgs-forms' ),
			),
			'dependency'  => array(
				array(
					'element'  => 'tipo',
					'value'    => 'checkbox',
					'operator' => '==',
				),
			)
		),
		array(
			'type'        => 'radio_button_set',
			'heading'		=> __('Estilo', 'mgs-forms'),
			'description' => __('Utilizar fontawesome para reemplazar el checkbox?.', 'mgs-forms'),
			'param_name'  => 'checkbox_replace_fa',
			'default'     => 'no',
			'value'       => array(
				'yes'	=> __( 'Si', 'mgs-forms' ),
				'no'	=> __( 'No', 'mgs-forms' ),
			),
			'dependency'  => array(
				array(
					'element'  => 'tipo',
					'value'    => 'checkbox',
					'operator' => '==',
				),
			)
		),
		
		/*	select	*/
		array(
			'type'        => 'textarea',
			'heading'     => __( 'Valores', 'mgs-forms' ),
			'description' => __( 'Valores para el select. Uno por linea.', 'mgs-forms' ),
			'param_name'  => 'select_values',
			'value'       => 'id :: valor',
			'placeholder' => true,
			'dependency'  => array(
				array(
					'element'  => 'tipo',
					'value'    => 'select',
					'operator' => '==',
				),
			)
		),
		
		/*	textarea	*/
		array(
			'type'				=> 'range',
			'heading'          	=> __( 'Alto', 'mgs-form' ),
			'description'      	=> __( 'Cantidad de columnas.', 'mgs-form' ),
			'param_name'       	=> 'textarea_rows',
			'min'         		=> '1',
			'max'         		=> '15',
			'step'        		=> '1',
			'value'       		=> '8',
			'dependency'  => array(
				array(
					'element'  => 'tipo',
					'value'    => 'textarea',
					'operator' => '==',
				),
			),
		),
		
		/*	numero	*/
		array(
			'type'        	=> 'radio_button_set',
			'heading'		=> __('Limitar máximo', 'mgs-forms'),
			'description'	=> __('Activa la opción de limitar el valor máximo.', 'mgs-forms'),
			'param_name'  	=> 'number_max_limit_flag',
			'default'     	=> 'no',
			'value'       	=> array(
				'yes'	=> __( 'Si', 'mgs-forms' ),
				'no'	=> __( 'No', 'mgs-forms' ),
			),
			'dependency'  	=> array(
				array(
					'element'  => 'tipo',
					'value'    => 'number',
					'operator' => '==',
				),
			)
		),
		array(
			'type'        => 'textfield',
			'heading'     => __( 'Máximo', 'mgs-forms' ),
			'description' => __( 'Valor máximo que acepta esta casilla.', 'mgs-forms' ),
			'param_name'  => 'number_max_limit',
			'value'       => '',
			'placeholder' => true,
			'dependency'  => array(
				array(
					'element'  => 'number_max_limit_flag',
					'value'    => 'yes',
					'operator' => '==',
				),
			)
		),
		array(
			'type'        	=> 'radio_button_set',
			'heading'		=> __('Limitar mínimo', 'mgs-forms'),
			'description'	=> __('Activa la opción de limitar el valor mínimo.', 'mgs-forms'),
			'param_name'  	=> 'number_min_limit_flag',
			'default'     	=> 'no',
			'value'       	=> array(
				'yes'	=> __( 'Si', 'mgs-forms' ),
				'no'	=> __( 'No', 'mgs-forms' ),
			),
			'dependency'  	=> array(
				array(
					'element'  => 'tipo',
					'value'    => 'number',
					'operator' => '==',
				),
			)
		),
		array(
			'type'        => 'textfield',
			'heading'     => __( 'Mínimo', 'mgs-forms' ),
			'description' => __( 'Valor mínimo que acepta esta casilla.', 'mgs-forms' ),
			'param_name'  => 'number_min_limit',
			'value'       => '',
			'placeholder' => true,
			'dependency'  => array(
				array(
					'element'  => 'number_min_limit_flag',
					'value'    => 'yes',
					'operator' => '==',
				),
			)
		),
		
		/*	recaptcha	*/
		array(
			'type'        	=> 'radio_button_set',
			'heading'		=> __('Tamaño', 'mgs-forms'),
			'description'	=> __('Tamaño del widget', 'mgs-forms'),
			'param_name'  	=> 'recaptcha_size',
			'default'     	=> 'normal',
			'value'       	=> array(
				'normal'	=> __( 'Normal', 'mgs-forms' ),
				'compact'	=> __( 'Compacto', 'mgs-forms' ),
			),
			'dependency'  	=> array(
				array(
					'element'  => 'tipo',
					'value'    => 'recaptcha',
					'operator' => '==',
				),
			)
		),
		array(
			'type'        	=> 'radio_button_set',
			'heading'		=> __('Colores', 'mgs-forms'),
			'description'	=> __('Esquema de colores del widget', 'mgs-forms'),
			'param_name'  	=> 'recaptcha_theme',
			'default'     	=> 'light',
			'value'       	=> array(
				'light'	=> __( 'Claro', 'mgs-forms' ),
				'dark'	=> __( 'Oscuro', 'mgs-forms' ),
			),
			'dependency'  	=> array(
				array(
					'element'  => 'tipo',
					'value'    => 'recaptcha',
					'operator' => '==',
				),
			)
		),
		
		array(
			'type'        	=> 'radio_button_set',
			'heading'		=> __('Limitar largo', 'mgs-forms'),
			'description'	=> __('Activa la opción de limitar la cantidad de caracteres del campo.', 'mgs-forms'),
			'param_name'  	=> 'len_limit_flag',
			'default'     	=> 'no',
			'value'       	=> array(
				'yes'	=> __( 'Si', 'mgs-forms' ),
				'no'	=> __( 'No', 'mgs-forms' ),
			),
			'dependency'  => array(
				array(
					'element'  => 'tipo',
					'value'    => 'text',
					'operator' => '==',
				),
			)
		),
		array(
			'type'				=> 'range',
			'heading'          	=> __( 'Mínimos', 'mgs-form' ),
			'description'      	=> __( 'Cantidad de mínima de caracteres.', 'mgs-form' ),
			'param_name'       	=> 'len_limit_min',
			'min'         		=> '0',
			'max'         		=> '255',
			'step'        		=> '1',
			'value'       		=> '0',
			'dependency'  => array(
				array(
					'element'  => 'len_limit_flag',
					'value'    => 'yes',
					'operator' => '==',
				),
			),
		),
		array(
			'type'				=> 'range',
			'heading'          	=> __( 'Máximo', 'mgs-form' ),
			'description'      	=> __( 'Cantidad de máxima de caracteres.', 'mgs-form' ),
			'param_name'       	=> 'len_limit_max',
			'min'         		=> '0',
			'max'         		=> '255',
			'step'        		=> '1',
			'value'       		=> '0',
			'dependency'  => array(
				array(
					'element'  => 'len_limit_flag',
					'value'    => 'yes',
					'operator' => '==',
				),
			),
		),
		
		/*	comunes a todos los campos	*/
		array(
			'type'        => 'textfield',
			'heading'     => __( 'Etiqueta', 'mgs-forms' ),
			'description' => __( 'Label del campo.<ul><li>En los casos como el de las casillas de verificación única no se muestra pero debe estar establecido.</li><li>En le caso del campo Codigo JS se utiliza para identificar el bloque, debe estar establecida.</li></ul>', 'mgs-forms' ),
			'param_name'  => 'element_content',
			'value'       => '',
			'placeholder' => true,
			'dependency'  => array(
				array(
					'element'  => 'tipo',
					'value'    => 'recaptcha',
					'operator' => '!=',
				),
			)
		),
		array(
			'type'        => 'radio_button_set',
			'heading'		=> __('Label', 'mgs-forms'),
			'description' => __( 'Mostrar label?', 'mgs-forms' ),
			'param_name'  => 'show_label',
			'default'     => 'no',
			'value'       => array(
				'yes'	=> __( 'Si', 'mgs-forms' ),
				'no'	=> __( 'No', 'mgs-forms' ),
			),
			'dependency'  => array(
				array(
					'element'  => 'tipo',
					'value'    => 'checkbox',
					'operator' => '!=',
				),
				array(
					'element'  => 'tipo',
					'value'    => 'label',
					'operator' => '!=',
				),
				array(
					'element'  => 'tipo',
					'value'    => 'code',
					'operator' => '!=',
				),
				array(
					'element'  => 'tipo',
					'value'    => 'recaptcha',
					'operator' => '!=',
				),
			)
		),
		array(
			'type'        => 'radio_button_set',
			'heading'     => __( 'Solo lectura', 'mgs-forms' ),
			'description' => __( 'Este campo es de solo lectura?', 'mgs-forms' ),
			'param_name'  => 'solo_lectura',
			'default'     => 'no',
			'value'       => array(
				'yes'	=> __( 'Si', 'mgs-forms' ),
				'no'	=> __( 'No', 'mgs-forms' ),
			),
			'dependency'  => array(
				array(
					'element'  => 'tipo',
					'value'    => 'checkbox',
					'operator' => '!=',
				),
				array(
					'element'  => 'tipo',
					'value'    => 'label',
					'operator' => '!=',
				),
				array(
					'element'  => 'tipo',
					'value'    => 'code',
					'operator' => '!=',
				),
				array(
					'element'  => 'tipo',
					'value'    => 'recaptcha',
					'operator' => '!=',
				),
			)
		),
		array(
			'type'        => 'radio_button_set',
			'heading'     => __( 'Requerido', 'mgs-forms' ),
			'description' => __( 'Este campo es obligatorio?', 'mgs-forms' ),
			'param_name'  => 'obligatorio',
			'default'     => 'no',
			'value'       => array(
				'yes'	=> __( 'Si', 'mgs-forms' ),
				'no'	=> __( 'No', 'mgs-forms' ),
			),
			'dependency'  => array(
				array(
					'element'  => 'solo_lectura',
					'value'    => 'no',
					'operator' => '==',
				),
				array(
					'element'  => 'tipo',
					'value'    => 'label',
					'operator' => '!=',
				),
				array(
					'element'  => 'tipo',
					'value'    => 'code',
					'operator' => '!=',
				),
				array(
					'element'  => 'tipo',
					'value'    => 'recaptcha',
					'operator' => '!=',
				),
			)
		),
		array(
			'type'			=> 'select',
			'heading'		=> __('Ancho', 'mgs-forms'),
			'description'	=> __('Ancho del elemento, se debera tener en cuanta a la hora de reordenar los mismos.', 'mgs-forms'),
			'param_name'	=> 'class_ancho',
			'value'			=> array(
				'1_1'	=> '1/1',
				'1_2'	=> '1/2',
				'1_3'	=> '1/3',
			),
			'default'		=> '1_1',
			'dependency'  => array(
				array(
					'element'  => 'tipo',
					'value'    => 'textarea',
					'operator' => '!=',
				),
				array(
					'element'  => 'tipo',
					'value'    => 'code',
					'operator' => '!=',
				),
				array(
					'element'  => 'tipo',
					'value'    => 'recaptcha',
					'operator' => '!=',
				),
			)
		),
	)
);

function map_mgs_forms_addon_with_fb(){
	global $mgs_forms_options;
	global $mgs_forms_elements_options;
	fusion_builder_map($mgs_forms_options);
	fusion_builder_map($mgs_forms_elements_options);
}