<?php
//ver 1.2

if( !class_exists('MGS_Forms') ){
	class MGS_Forms{
		private static $instance;
		public static $dominio;
		public static $flag_saved;
		public static $flag_mailed;
		public static $flag_payoption;
		public static $flag_mailed_ok;
		public static $flag_post;
		public static $config_plg_send_mail_raw_subject;
		public static $config_plg_send_mail_ok_subject;
		public static $compatible;
		public static $content_array;
		public static $__POST;
		public static $debug;
		
		public $pp_url_sandbox	= 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		public $pp_url			= 'https://www.paypal.com/cgi-bin/webscr';
		public $pp_mode			= 'normal'; /*	sandbox | normal	*/

		public static function get_instance(){
			if( null === self::$instance ){
				self::$instance = new MGS_Forms();
			}
			return self::$instance;
		}
		
		public function __construct(){
			if( !class_exists('FusionBuilder') ){
				add_action( 'admin_notices', array($this, 'mgs_forms_error_no_fusionbuilder') );
			}
			load_theme_textdomain('mgs-forms', MGS_FORMS_PLUGIN_DIR.'/languages');
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts_admin' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			//add_action( 'add_meta_boxes', array($this, 'fusion_mgs_form_meta_box') );
			add_shortcode( 'fusion_mgs_forms', array( $this, 'fusion_mgs_forms' ) );
			add_shortcode( 'fusion_mgs_form_elemento', array( $this, 'fusion_mgs_form_elemento' ) );
			add_shortcode( 'fusion_mgs_form_elemento_raw', array( $this, 'fusion_mgs_form_elemento_raw' ) );
						
			
			/*	dominio sitio	*/
			$hostname = strtoupper(str_replace('www.', '', $_SERVER['SERVER_NAME']));
			self::$dominio = strtolower(htmlspecialchars(strip_tags(str_replace(array('/'), '', ($hostname)))));
			
			/*	banderas basicas	*/
			self::$flag_post = false;
			self::$flag_saved = false;
			self::$flag_mailed = false;
			self::$flag_mailed_ok = false;
			self::$flag_payoption = false;
			
			/*	compatibilidad con otras PLGs	*/
			self::$compatible = array(
				'wpml'		=> ( function_exists('icl_object_id') ) ? true : false,
				'akismet'	=> ( class_exists('Akismet') ) ? true : false,
				'mgs_otros'	=> false,
			);
			
			/* a futuro carga configuracion dinamicamente	*/
			self::$config_plg_send_mail_raw_subject = __('Envio de información', 'mgs-forms');
			self::$config_plg_send_mail_ok_subject = __('Gracias por completar el formulario', 'mgs-forms');
			
			self::$content_array = array();
			self::$debug = array();
		}
		
		public static function activation(){
			if( !class_exists('FusionBuilder') ){
				add_action( 'admin_notices', array($this, 'mgs_forms_error_no_fusionbuilder') );
			}else{
				global $wpdb;
				global $table_name_mgs_forms;
				global $sql_tabla;
				$version_instalada = get_option( 'MGS_FORMS_VERSION', 0 );
				require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
				dbDelta( $sql_tabla );
				if( MGS_FORMS_VERSION > $version_instalada ){
					update_option( 'MGS_FORMS_VERSION', MGS_FORMS_VERSION, false );
				}
			}
		}
		
		public function enqueue_scripts(){
			wp_enqueue_style( 'mgs-forms-css', MGS_FORMS_PLUGIN_DIR_URL.'css/estilos.css' );
			wp_enqueue_script( 'mgs_forms-validator', MGS_FORMS_PLUGIN_DIR_URL.'js/jquery.validate.min.js', array('jquery'), '1.15.0' );
			wp_enqueue_script( 'mgs_forms-validator-add-methods', MGS_FORMS_PLUGIN_DIR_URL.'js/additional-methods.min.js', '', '1.15.0' );
			wp_enqueue_script( 'mgs_forms-validator-tooltips', MGS_FORMS_PLUGIN_DIR_URL.'js/jquery-validate.bootstrap-tooltip.min.js', '', '0.10.2' );
			
			wp_enqueue_style( 'mgs-forms-bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css' );
			wp_enqueue_style( 'mgs-forms-bootstrap-theme', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css' );
			wp_enqueue_script( 'mgs_forms-bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js', '', '3.3.7' );
			
			wp_enqueue_style( 'mgs-forms-bootstrap-datepicker-css', MGS_FORMS_PLUGIN_DIR_URL.'css/bootstrap-datepicker.standalone.min.css');
			wp_enqueue_script( 'mgs-forms-bootstrap-datepicker-js', MGS_FORMS_PLUGIN_DIR_URL.'js/bootstrap-datepicker.min.js', '', '1.7.0' );
			wp_enqueue_script( 'mgs-forms-bootstrap-datepicker-js-es', MGS_FORMS_PLUGIN_DIR_URL.'js/bootstrap-datepicker.es.min.js', '', '1.7.0' );
			wp_enqueue_script( 'mgs-forms-bootstrap-datepicker-js-ca', MGS_FORMS_PLUGIN_DIR_URL.'js/bootstrap-datepicker.ca.min.js', '', '1.7.0' );
		}
		
		public function enqueue_scripts_admin(){
			wp_enqueue_style( 'mgs-forms-css-admin', MGS_FORMS_PLUGIN_DIR_URL.'css/admin.css' );
		}
		
		public function fusion_mgs_forms($atts, $content){
			$unique_class = 'mgs-forms-' . rand();
			$html = '';
			
			//$html .= '<pre>'.print_r($_GET, true).'</pre>';
			
			if( $this->verifica_post() && $this->verifica_nonce() && $this->verifica_recaptcha() ){
				self::$__POST = $this->zanitice_post($_POST);
				
				if( self::$__POST['pago_confirmado']=='pendiente' && self::$__POST['optionpay_metodo']=='paypal' ){
					$paypal = array(
						'business'		=> self::$__POST['optionpay_email'],
						'item_name'		=> self::$__POST['pago_por'],
						'currency_code'	=> self::$__POST['optionpay_moneda'],
						'return'		=> add_query_arg(self::$__POST, get_permalink(get_the_id())).'&status=ok',
						'cancel_return'	=> add_query_arg(self::$__POST, get_permalink(get_the_id())).'&status=cancel',
					);
					$paypal['cmd'] = '_xclick';
					$paypal['amount'] = self::$__POST['pago'];
					return $this->MakePayPalForm($paypal, self::$__POST);
					die();
				}elseif( self::$__POST['mgs-forms-acc']=='save' ){
					unset(self::$__POST['status']);
					unset(self::$__POST['optionpay_email']);
					unset(self::$__POST['optionpay_url_return_ok']);
					unset(self::$__POST['optionpay_url_return_error']);
					unset(self::$__POST['optionpay_moneda']);
					unset(self::$__POST['optionpay_paypal_sandbox']);
					if( $_POST['mgs_forms_chk_file']=='yes' && isset($_FILES) ){
						self::$__POST['UPLOADS'] = $this->upload_post_file($_FILES);
					}
					
					$pre_id = $this->save_in_db(self::$__POST, get_the_id());
					
					//$html .= $pre_id.'<pre>'.print_r($_POST, true).'</pre>';
					
					if( $atts['send_mail_raw']=='yes' && self::$flag_saved ){
						$content_raw = str_replace('fusion_mgs_form_elemento', 'fusion_mgs_form_elemento_raw', $content);
						do_shortcode($content_raw);
						$this->send_mail_raw($atts, self::$__POST, $pre_id);
					}
					
					if( $atts['send_mail_ok']=='yes' && self::$flag_saved ){
						$this->send_mail_ok($atts, self::$__POST, $pre_id);
					}
					
					$html .= $this->avisos($atts, $pre_id, $org_get);
				}
			}elseif( $_GET['mgs-forms-acc']=='save' && $_GET['pago_confirmado']=='pendiente' && $_GET['status']=='ok'){
				self::$__POST = $this->zanitice_post($_GET);
				self::$__POST['pago_confirmado'] = 'Confirmado';
				$org_get = self::$__POST;
				unset(self::$__POST['status']);
				unset(self::$__POST['optionpay_email']);
				unset(self::$__POST['optionpay_url_return_ok']);
				unset(self::$__POST['optionpay_url_return_error']);
				unset(self::$__POST['optionpay_moneda']);
				unset(self::$__POST['optionpay_paypal_sandbox']);
				
				$pre_id = $this->save_in_db(self::$__POST, get_the_id());
					
				if( $atts['send_mail_raw']=='yes' && self::$flag_saved ){
					$content_raw = str_replace('fusion_mgs_form_elemento', 'fusion_mgs_form_elemento_raw', $content);
					do_shortcode($content_raw);
					$this->send_mail_raw($atts, self::$__POST, $pre_id);
				}
				
				if( $atts['send_mail_ok']=='yes' && self::$flag_saved ){
					$this->send_mail_ok($atts, self::$__POST, $pre_id);
				}
				
				$atts['redirect_url_ok'] = $org_get['optionpay_url_return_ok'];
				$atts['redirect_url_bad'] = $org_get['optionpay_url_return_error'];
				$html .= $this->avisos($atts, $pre_id, $org_get);
			}elseif( $_GET['mgs-forms-acc']=='save' && $_GET['pago_confirmado']=='pendiente' && $_GET['status']=='cancel'){
				self::$__POST = $this->zanitice_post($_GET);
				self::$__POST['pago_confirmado'] = 'Confirmado';
				$org_get = self::$__POST;
				unset(self::$__POST['status']);
				unset(self::$__POST['optionpay_email']);
				unset(self::$__POST['optionpay_url_return_ok']);
				unset(self::$__POST['optionpay_url_return_error']);
				unset(self::$__POST['optionpay_moneda']);
				unset(self::$__POST['optionpay_paypal_sandbox']);
				$atts['redirect_url_ok'] = $org_get['optionpay_url_return_ok'];
				$atts['redirect_url_bad'] = $org_get['optionpay_url_return_error'];
				$html .= $this->avisos($atts, $pre_id, $org_get);
			}
			
			if( MGS_FORMS_DEBUG ) $html .= '<pre>'.print_r($atts, true).'</pre>';

			
			$html .= '<form id="'.$atts['name'].'" class="mgs-forms '.$unique_class.'" method="post" enctype="multipart/form-data">';
			$html .= do_shortcode($content);
			$html .= $this->form_hiddens($unique_class);
			if( $atts['validator']=='yes' ){
				$html .= $this->build_jq_validate($atts);
			}			
			$html .= $this->form_submit_button($atts);
			$html .= '</form>';
			
			/*
			$html .= 'content_array:<pre>'.print_r(self::$content_array, true).'</pre>';
			$html .= 'verifica_recaptcha:<pre>'.print_r($this->verifica_recaptcha(), true).'</pre>';
			$html .= '__POST:<pre>'.print_r(self::$__POST, true).'</pre>';
			$html .= 'build_raw_message:<pre>'.print_r($this->build_raw_message(self::$__POST, '3333'), true).'</pre>';
			*/
			return $html;
		}
		
		private function MakePayPalForm($attr, $post){
			if( $post['optionpay_paypal_sandbox']=='yes' ){
				$u = $this->pp_url_sandbox;
			}else{
				$u = $this->pp_url;
			}
			
			$rt = '
				<form action="'.$u.'" method="post" target="_top" id="autopaypalform">
					<input type="hidden" name="lc" value="AL">
					<input type="hidden" name="no_note" value="1">
					<input type="hidden" name="no_shipping" value="1">
					<input type="hidden" name="rm" value="1">
					<input type="hidden" name="bn" value="PP-DonationsBF:Logo-ESF02.png:NonHosted">
					<input type="image" src="" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
					<img alt="" border="0" src="https://www.paypalobjects.com/es_XC/i/scr/pixel.gif" width="1" height="1">
			';
			foreach($attr as $k=>$v){
				$rt .= '<input type="hidden" name="'.$k.'" value="'.$v.'">';
			}
			$rt .= '
				</form>
				<script>
					jQuery(document).ready(function(e){
						jQuery("#autopaypalform").submit();
					});
				</script>
			';
			return $rt;
		}
		
		public function fusion_mgs_form_elemento_raw($atts, $content){
			if( isset(self::$__POST[$atts['id']]) ){
				if( is_array(self::$__POST[$atts['id']]) ){
					self::$content_array[$atts['id']] = array(
						'label'		=> $content,
						'value'		=> implode(', ', self::$__POST[$atts['id']])
					);
				}else{
					self::$content_array[$atts['id']] = array(
						'label'		=> $content,
						'value'		=> self::$__POST[$atts['id']]
					);
				}
			}elseif( isset($_FILES[$atts['id']]) ){
				self::$content_array[$atts['id']] = array(
					'label'		=> $content,
					'value'		=> self::$__POST['UPLOADS'][$atts['id']]['uploaded']
				);
			}
			return '';
		}
		
		public function fusion_mgs_form_elemento($atts, $content){
			if( $atts['class_ancho']=='' ) $atts['class_ancho'] = '1_1';
			$html = '<div class="mgs-forms-warper-input mgs-forms-warper-input-'.$atts['id'].' col-'.$atts['class_ancho'].'">';
			switch( $atts['tipo'] ){
				case 'text':
				case 'email':
					$html .= $this->_text($atts, $content);
					break;
				case 'number':
					$html .= $this->_number($atts, $content);
					break;
				case 'date':
					$html .= $this->_date($atts, $content);
					break;
				case 'textarea':
					$html .= $this->_textarea($atts, $content);
					break;
				case 'select':
					$html .= $this->_select($atts, $content);
					break;
				case 'checkbox':
					$html .= $this->_checkbox($atts, $content);
					break;
				case 'checkboxs':
					$html .= $this->_checkboxs($atts, $content);
					break;
				case 'radios':
					$html .= $this->_radios($atts, $content);
					break;
				case 'label':
					$html .= $this->_label($atts, $content);
					break;
				case 'code':
					$html .= $this->_code($atts, $content);
					break;
				case 'upload':
					$html .= $this->_upload($atts, $content);
					break;
				case 'recaptcha':
					$html .= $this->_recaptcha($atts, $content);
					break;
				case 'optionpay':
					$html .= $this->_optionpay($atts, $content);
					break;
			}
            $html .= '</div>';
			return $html;
		}
		
		public function mgs_forms_error_no_fusionbuilder(){
			if( !class_exists('FusionBuilder') ){
				if ( isset($_GET['action']) ) unset($_GET['action']);
				deactivate_plugins( plugin_basename( __FILE__ ) );
				?>
				<div class="error notice mgs-tables-error">
					<h3 style="color:#dc3232">MGS-Forms addon para Fusion Builder</h3>
					<p>MGS-Forms addon para Fusion Builder requiere de Fusion Builder 1.0 o mayor esta funcionar correctamente.</p>
					<p><a class="button button-primary button-medium" href="<?php echo admin_url('admin.php?page=avada-plugins')?>">Ir a la instalación de Avada</a> <a class="button button-primary button-medium" href="https://theme-fusion.com/" target="_blank">Adquirir Avada</a></p>
				</div>
				<?php
				return false;
			}
		}
		
		/*****************************************************************************************************/
		
		private function _optionpay($atts, $content){
			global $wp;
			$current_url = home_url( add_query_arg(array(), $wp->request));
			if( $atts['optionpay_url_return_ok']=='' ){
				$atts['optionpay_url_return_ok'] = home_url( add_query_arg(array('status'=>'ok'), $wp->request));
			}
			if( $atts['optionpay_url_return_error']=='' ){
				$atts['optionpay_url_return_error'] = home_url( add_query_arg(array('status'=>'error'), $wp->request));
			}
			$html .= '
				<div class="mgs-optionpay-items">
			';
			if( $atts['optionpay_paypal']=='yes' ){
				$html .= '
					<input type="hidden" name="optionpay_email" value="'.$atts['optionpay_email'].'" />
					<input type="hidden" name="optionpay_url_return_ok" value="'.$atts['optionpay_url_return_ok'].'" />
					<input type="hidden" name="optionpay_url_return_error" value="'.$atts['optionpay_url_return_error'].'" />
					<input type="hidden" name="optionpay_moneda" value="'.$atts['optionpay_moneda'].'" />
					<input type="hidden" name="optionpay_paypal_sandbox" value="'.$atts['optionpay_paypal_sandbox'].'" />
				';
			}
			$html .= '
					<input type="hidden" name="pago_confirmado" id="pago_confirmado" value="pendiente" />
					<input type="hidden" name="'.$atts['id'].'[]" value=" "/>
					<input type="hidden" name="'.$atts['id'].'_por" value=""/>
					<label class="mgs-forms-label form-control-label mgs-optionpay-title">'.$atts['optionpay_items_label'].'</label>
					<div class="mgs-radios">
			';
			if( self::$flag_post ){
				$value = $_POST[$atts['id']];
			}else{
				$value = '';
			}
			$values = trim($atts['optionpay_items']);
			$values_array = explode("\n", $values);
			$values_array = array_filter($values_array, 'trim');
			foreach($values_array as $v){
				$t_array = explode('::', $v);
				$t_array = array_filter($t_array, 'trim');
				if( $value==$t_array[1] ){ $s = 'checked';}else{ $s = '';}
				$html .= '
						<div class="mgs-radios-element">
							<label class="mgs-forms-rad-replace-fa">
								<input type="radio" class="mgs-forms-control form-control paymontoradio" name="'.$atts['id'].'[]" value="'.number_format($t_array[1], 2).'" data-placement="left" data-text="'.$t_array[0].'" '.$s.'/>
								<i class="far fa-circle fa-2x"></i><i class="far fa-check-circle fa-2x"></i> <span class="label">'.$t_array[0].'</span>
							</label>
							<div class="clear"></div>
						</div>
				';
			}
			$html .= '
					</div>
					<div class="clear"></div>
				</div>
				<div class="mgs-optionpay-opciones">
					<input type="hidden" name="optionpay_metodo[]" value=" " />
					<label class="mgs-forms-label form-control-label mgs-optionpay-title">Seleccione una forma de pago</label>
					<div class="mgs-radios">
			';
			
			if( $atts['optionpay_paypal']=='yes' && $atts['optionpay_ctacte']=='yes' ){
				$c_paypal = 'checked="checked"';
				$c_ctacte = '';
			}elseif( $atts['optionpay_paypal']=='yes' && $atts['optionpay_ctacte']!='yes' ){
				$c_paypal = 'checked="checked"';
				$c_ctacte = '';
			}elseif( $atts['optionpay_paypal']!='yes' && $atts['optionpay_ctacte']=='yes' ){
				$c_paypal = '';
				$c_ctacte = 'checked="checked"';
			}
			
			if( $atts['optionpay_paypal']=='yes' ){
				$html .= '
						<div class="mgs-radios-element pay-options">
							<label class="mgs-forms-rad-replace-fa">
								<input type="radio" class="mgs-forms-control form-control payoptionradio" name="optionpay_metodo[]" value="paypal" data-placement="left" data-text="'.$atts['optionpay_paypal_label'].'" '.$c_paypal.'/>
								<i class="far fa-circle fa-2x"></i><i class="far fa-check-circle fa-2x"></i> <span class="label">'.$atts['optionpay_paypal_label'].'</span>
							</label>
							<div class="clear"></div>
						</div>
				';
			}
			if( $atts['optionpay_ctacte']=='yes' ){
				$html .= '
						<div class="mgs-radios-element pay-options">
							<label class="mgs-forms-rad-replace-fa">
								<input type="radio" class="mgs-forms-control form-control payoptionradio" name="optionpay_metodo[]" value="ctacte" data-placement="left" data-text="'.$atts['optionpay_ctacte_label'].'" '.$c_ctacte.'/>
								<i class="far fa-circle fa-2x"></i><i class="far fa-check-circle fa-2x"></i> <span class="label">'.$atts['optionpay_ctacte_label'].'</span>
							</label>
							<div class="clear"></div>
						</div>
				';
			}
			$html .= '
					</div>
					<div id="paymetoddebug" class="pay-alert-content-wrapper" style="display:none">Selecciono pagar <span class="js_monto"></span><span class="js_moneda">'.$atts['optionpay_moneda'].'</span> mediante: <span class="js_metodo"></span> por <span class="js_item"></span></div>
					
					<div class="pay-option-ctacte-info pay-alert-content-wrapper" style="display:none">'.$atts['optionpay_ctacte_info'].'</div>
				</div>
				<script>
					var text_info = jQuery(".pay-option-ctacte-info").html();
					console.log(text_info);
					
					jQuery(".payoptionradio, .paymontoradio").on("change", function(){
						MGS_FORM_CalcMontos();
					});
					
					function MGS_FORM_CalcMontos(){
						var m = jQuery(".payoptionradio:checked").val();
						var l = jQuery(".payoptionradio:checked").data("text");
						var monto = jQuery(".paymontoradio:checked").val();
						var desc = jQuery(".paymontoradio:checked").data("text");
						
						var nice_monto = "<span class=\"js_monto\">" + monto + "'.$atts['optionpay_moneda'].'</span>";
						var nice_desc = "<span class=\"js_desc\">" + desc + "</span>";
						
						jQuery("input[name='.$atts['id'].'_por]").val(desc);
						
						if( m && l && monto ){
							console.log(m, l, monto);
							jQuery("#paymetoddebug .js_monto").html(monto);
							jQuery("#paymetoddebug .js_metodo").html(l);
							jQuery("#paymetoddebug .js_item").html(desc);
							jQuery("#paymetoddebug").fadeIn();
						}else{
							jQuery("#paymetoddebug").fadeOut();
						}
						
						if( m=="ctacte" && monto && desc ){
							jQuery("#paymetoddebug").fadeOut();
							text_info = text_info.replace(/\{TOTAL\}/, nice_monto);
							text_info = text_info.replace(/\{ITEM\}/, nice_desc);
							jQuery(".pay-option-ctacte-info").html(text_info).fadeIn();
							jQuery(".pay-option-ctacte-info").fadeIn();
							
						}else{
							jQuery(".pay-option-ctacte-info").html("").fadeOut();
						}
							
					}
				</script>
				<div class="clear"></div>
			';
			
			
			return $html;
		}
		
		private function _recaptcha($atts, $content){
			$html = '
				<script src="https://www.google.com/recaptcha/api.js" async defer></script>
				<div class="g-recaptcha" data-sitekey="'.get_option('mgs_forms_reCAPTCHA_public_key').'" data-theme="'.$atts['recaptcha_theme'].'" data-size="'.$atts['recaptcha_size'].'" data-callback="recaptchaCallback"></div>
				<input id="hidden-grecaptcha" name="hidden-grecaptcha" type="text" style="opacity: 0; position: absolute; top: 0; left: 0; height: 1px; width: 1px;" required="required"/>
				<input type="hidden" id="has-grecaptcha" name="has-grecaptcha" value="1">
				<script>
					function recaptchaCallback() {
						var response = grecaptcha.getResponse(),
						$button = jQuery(".button-register");
						jQuery("#hidden-grecaptcha").val(response);
					}
				</script>
			';
			return $html;
		}
		
		private function _text($atts, $content){
			$html = '';
			if( self::$flag_post ){
				$value = $_POST[$atts['id']];
			}else{
				$value = $atts['value'];
			}
			if( $atts['obligatorio']=='yes' ){
				$label = $content.' <span class="required">*</span>';
				$placeholder = $content.' *';
			}else{
				$label = $content;
				$placeholder = $content;
			}
			if( $atts['show_label']=='yes' ){
				$html .= '<label for="'.$atts['id'].'" class="mgs-forms-label form-control-label">'.$label.'</label>';
			}
			$html .= '<input type="'.$atts['tipo'].'" class="mgs-forms-control form-control" name="'.$atts['id'].'" id="'.$atts['id'].'" placeholder="'.$placeholder.'" value="'.$value.'" data-placement="bottom"';
			if( $atts['obligatorio']=='yes' ) $html .= ' required="required"';
			if( $atts['solo_lectura']=='yes' ) $html .= ' readonly';
			if( $atts['len_limit_flag']=='yes' ) $html .= ' minlength="'.$atts['len_limit_min'].'" maxlength="'.$atts['len_limit_max'].'"';
			$html .= '/>';
			return $html;
		}
		
		private function _number($atts, $content){
			$html = '';
			if( self::$flag_post ){
				$value = $_POST[$atts['id']];
			}else{
				$value = $atts['value'];
			}
			if( $atts['obligatorio']=='yes' ){
				$label = $content.' <span class="required">*</span>';
				$placeholder = $content.' *';
			}else{
				$label = $content;
				$placeholder = $content;
			}
			if( $atts['show_label']=='yes' ){
				$html .= '<label for="'.$atts['id'].'" class="mgs-forms-label form-control-label">'.$label.'</label>';
			}
			$html .= '<input type="number" class="mgs-forms-control form-control" name="'.$atts['id'].'" id="'.$atts['id'].'" placeholder="'.$placeholder.'" value="'.$value.'" data-placement="bottom"';
			if( $atts['number_max_limit_flag']=='yes' && $atts['number_max_limit']!='' ) $html .= ' max="'.$atts['number_max_limit'].'"';
			if( $atts['number_min_limit_flag']=='yes' && $atts['number_min_limit']!='' ) $html .= ' min="'.$atts['number_min_limit'].'"';
			if( $atts['obligatorio']=='yes' ) $html .= ' required="required"';
			if( $atts['solo_lectura']=='yes' ) $html .= ' readonly';
			$html .= '/>';
			return $html;
		}
		
		private function _textarea($atts, $content){
			$html = '';
			if( self::$flag_post ){
				$value = $_POST[$atts['id']];
			}else{
				$value = $atts['value'];
			}
			if( $atts['obligatorio']=='yes' ){
				$label = $content.' <span class="required">*</span>';
				$placeholder = $content.' *';
			}else{
				$label = $content;
				$placeholder = $content;
			}
			if( $atts['show_label']=='yes' ){
				$html .= '<label for="'.$atts['id'].'" class="mgs-forms-label form-control-label">'.$label.'</label>';
			}
			$html .= '<textarea class="mgs-forms-control form-control" name="'.$atts['id'].'" id="'.$atts['id'].'" placeholder="'.$placeholder.'" rows="'.$atts['textarea_rows'].'"';
			if( $atts['obligatorio']=='yes' ) $html .= ' required="required"';
			if( $atts['solo_lectura']=='yes' ) $html .= ' readonly';
			$html .= '>'.$value.'</textarea>';
			return $html;
		}
		
		private function _select($atts, $content){
			$html = '';
			if( self::$flag_post ){
				$value = $_POST[$atts['id']];
			}else{
				$value = $atts['value'];
			}
			if( $atts['obligatorio']=='yes' ){
				$label = $content.' <span class="required">*</span>';
				$placeholder = $content.' *';
			}else{
				$label = $content;
				$placeholder = $content;
			}
			if( $atts['show_label']=='yes' ){
				$html .= '<label for="'.$atts['id'].'" class="mgs-forms-label form-control-label">'.$label.'</label>';
			}
			$html .= '<select class="mgs-forms-control form-control" name="'.$atts['id'].'" id="'.$atts['id'].'"';
			if( $atts['obligatorio']=='yes' ) $html .= ' required="required"';
			if( $atts['solo_lectura']=='yes' ) $html .= ' readonly';
			$html .= '>';
			$values = trim($atts['select_values']);
			$values_array = explode("\n", $values);
			$values_array = array_filter($values_array, 'trim');
			foreach($values_array as $v){
				$t_array = explode('::', $v);
				$t_array = array_filter($t_array, 'trim');
				if( isset($t_array[1]) ){
					if( $value==$t_array[0] ){ $s = 'selected';}else{ $s = '';}
					$html .= '<option value="'.$t_array[0].'" '.$s.'>'.$t_array[1].'</option>';
				}else{
					if( $value==$t_array[0] ){ $s = 'selected';}else{ $s = '';}
					$html .= '<option value="'.$t_array[0].'" '.$s.'>'.$t_array[0].'</option>';
				}
			}
			$html .= '</select>';
			return $html;
		}
		
		private function _checkbox($atts, $content){
			$html = '';
			$html .= '<input type="hidden" name="'.$atts['id'].'" id="'.$atts['id'].'" value=" "/>';
			if( self::$flag_post ){
				$status = ($_POST[$atts['id']]==$atts['checkbox_value'])?'checked':'';
			}else{
				$status = ($atts['checkbox_status']=='yes')?'checked':'';
			}
			
			if( $atts['obligatorio']=='yes' ){
				$label = self::doMarkdownLinks($atts['checkbox_label']).' <span class="required">*</span>';
			}else{
				$label = self::doMarkdownLinks($atts['checkbox_label']);
			}
			$html .= '<label for="'.$atts['id'].'" class="mgs-forms-label-hidden">'.$content.'</label>';
			$html .= '<label class="';
			$html .= ($atts['checkbox_replace_fa']=='yes')?' mgs-forms-chk-replace-fa':'';
			$html .= '">';
			$html .= '<input type="'.$atts['tipo'].'" class="mgs-forms-control form-control" name="'.$atts['id'].'" id="'.$atts['id'].'" value="'.$atts['checkbox_value'].'" data-placement="bottom" '.$status;
			if( $atts['obligatorio']=='yes' ) $html .= ' required="required"';
			$html .= '/>';
			if( $atts['checkbox_replace_fa']=='yes' ){
				$html .= '<i class="far fa-square fa-2x"></i><i class="far fa-check-square fa-2x"></i> <span class="label">'.$label.'</span>';
			}else{
				$html .= $label;
			}
			$html .= '</label><div class="clear"></div>';
			return $html;
		}
		
		private function _checkboxs($atts, $content){
			$html = '';
			$html .= '<input type="hidden" name="'.$atts['id'].'[]" value=" "/>';
			if( $atts['obligatorio']=='yes' ){
				$label = $content.' <span class="required">*</span>';
			}else{
				$label = $content;
			}
			if( $atts['show_label']=='yes' ){
				$html .= '<label class="mgs-forms-label form-control-label">'.$label.'</label><div class="clear"></div>';
			}
			$html .= '<div class="mgs-checkboxs">';
			$values = trim($atts['checkboxs_values']);
			$values_array = explode("\n", $values);
			$values_array = array_filter($values_array, 'trim');
			foreach($values_array as $v){
				if( strpos($v, '::')===false ){
					$l_c = strip_tags($v);
					$v_c = strip_tags($v);
				}else{
					$t_array = explode('::', $v);
					$t_array = array_filter($t_array, 'trim');
					$l_c = strip_tags($t_array[0]);
					$v_c = strip_tags($t_array[1]);
				}
				$html .= '<div class="mgs-checkboxs-element">';
				$html .= '<label class="';
				$html .= ($atts['checkboxs_replace_fa']=='yes')?' mgs-forms-chk-replace-fa':'';
				$html .= '">';
				$html .= '<input type="checkbox" class="mgs-forms-control form-control" name="'.$atts['id'].'[]" value="'.$v_c.'" data-placement="left"';
				if( $atts['obligatorio']=='yes' ) $html .= ' required="required"';
				$html .= ' />';
				if( $atts['checkboxs_replace_fa']=='yes' ){
					$html .= '<i class="far fa-square fa-2x"></i><i class="far fa-check-square fa-2x"></i> <span class="label">'.$l_c.'</span>';
				}else{
					$html .= $l_c;
				}
				$html .= '</label><div class="clear"></div></div>';
			}
			$html .= '</div><div class="clear"></div>';
			return $html;
		}
		
		private function _radios($atts, $content){
			$html = '';
			$html .= '<input type="hidden" name="'.$atts['id'].'[]" value=" "/>';
			if( $atts['obligatorio']=='yes' ){
				$label = $content.' <span class="required">*</span>';
			}else{
				$label = $content;
			}
			if( $atts['show_label']=='yes' ){
				$html .= '<label class="mgs-forms-label form-control-label">'.$label.'</label><div class="clear"></div>';
			}
			$html .= '<div class="mgs-radios">';
			$values = trim($atts['radios_values']);
			$values_array = explode("\n", $values);
			$values_array = array_filter($values_array, 'trim');
			foreach($values_array as $v){
				if( strpos($v, '::')===false ){
					$l_c = strip_tags($v);
					$v_c = strip_tags($v);
				}else{
					$t_array = explode('::', $v);
					$t_array = array_filter($t_array, 'trim');
					$l_c = strip_tags($t_array[0]);
					$v_c = strip_tags($t_array[1]);
				}
				$html .= '<div class="mgs-radios-element">';
				$html .= '<label class="';
				$html .= ($atts['radios_replace_fa']=='yes')?' mgs-forms-rad-replace-fa':'';
				$html .= '">';
				$html .= '<input type="radio" class="mgs-forms-control form-control" name="'.$atts['id'].'[]" value="'.$v_c.'" data-placement="left"';
				if( $atts['obligatorio']=='yes' ) $html .= ' required="required"';
				$html .= ' />';
				if( $atts['radios_replace_fa']=='yes' ){
					$html .= '<i class="far fa-circle fa-2x"></i><i class="far fa-check-circle fa-2x"></i> <span class="label">'.$l_c.'</span>';
				}else{
					$html .= $l_c;
				}
				$html .= '</label><div class="clear"></div></div>';
			}
			$html .= '</div><div class="clear"></div>';
			return $html;
		}
		
		private function _label($atts, $content){
			$html = '';
			$html .= '<label for="'.$atts['id'].'" class="mgs-forms-label form-control-label">'.$content.'</label>';
			return $html;
		}
		
		private function _code($atts, $content){
			if( base64_encode(base64_decode($atts['code']))===$atts['code']){
				$atts['code'] = html_entity_decode(base64_decode($atts['code']), ENT_QUOTES);
			}
			$html = '';
			if( $atts['code_type']=='JS' ){
				$html .= '<script id="'.$atts['id'].'">';
				$html .= $atts['code'];
				$html .= '</script>';
			}
			return $html;
		}
		
		/*public function mgs_forms_test_save_content($atts, $content){
			if( $atts['tipo']=='code' ){
				if( base64_encode(base64_decode($atts['code']))===$atts['code']){
					$atts['code'] = base64_decode($atts['code']);
				}
			}
		}*/
		
		private function _date($atts, $content){
			$html = '';
			if( self::$flag_post ){
				$value = $_POST[$atts['id']];
			}else{
				$value = $atts['value'];
			}
			if( $atts['obligatorio']=='yes' ){
				$label = $content.' <span class="required">*</span>';
				$placeholder = $content.' *';
			}else{
				$label = $content;
				$placeholder = $content;
			}
			if( $atts['show_label']=='yes' ){
				$html .= '<label for="'.$atts['id'].'" class="mgs-forms-label form-control-label">'.$label.'</label>';
			}
			$html .= '<input type="text" class="mgs-forms-control form-control mgs-datepicker" name="'.$atts['id'].'" id="'.$atts['id'].'" placeholder="'.$placeholder.'" value="'.$value.'" data-placement="bottom"';
			if( $atts['obligatorio']=='yes' ) $html .= ' required="required"';
			if( $atts['solo_lectura']=='yes' ) $html .= ' readonly';
			$html .= '/>';
			$html .= '
				<script>
					jQuery(".mgs-datepicker").datepicker({
						format		: "'.$atts['fecha_format'].'",
						language	: "'.$atts['fecha_lang'].'",
						orientation	: "bottom auto",
						autoclose	: true
					});
					console.log("running datapicker");
				</script>
			';
			return $html;
		}
		
		private function _upload($atts, $content){
			$html = '';
			
			if( self::$flag_post ){
				$value = $_POST[$atts['id']];
			}else{
				$value = $atts['value'];
			}
			if( $atts['obligatorio']=='yes' ){
				$label = $content.' <span class="required">*</span>';
				$placeholder = $content.' *';
			}else{
				$label = $content;
				$placeholder = $content;
			}
			if( $atts['show_label']=='yes' ){
				$html .= '<label for="'.$atts['id'].'" class="mgs-forms-label form-control-label">'.$label.'</label>';
			}
			/*$html .= '<input type="file" class="mgs-forms-control form-control" name="'.$atts['id'].'" id="'.$atts['id'].'" placeholder="'.$placeholder.'" value="'.$value.'" data-placement="bottom"';
			if( $atts['obligatorio']=='yes' ) $html .= ' required="required"';
			if( $atts['solo_lectura']=='yes' ) $html .= ' readonly';
			$html .= '/>';*/
			
			
			$html .= '
				<div class="input-group">
					<label class="input-group-btn">
						<span class="btn btn-default">
							'.$placeholder.' <input type="file" name="'.$atts['id'].'" id="'.$atts['id'].'" style="display:none;"
			';
			if( $atts['obligatorio']=='yes' ) $html .= ' required="required"';
			$html .= '/>
						</span>
					</label>
					<input type="text" class="mgs-forms-control form-control" value="'.$value.'" readonly>
				</div>
				<script>
					//https://www.abeautifulsite.net/whipping-file-inputs-into-shape-with-bootstrap-3
					jQuery(function() {
						jQuery(document).on("change", ":file", function() {
							var input = jQuery(this);
							
							var ext = input.val().split(".").pop().toLowerCase();
							if(jQuery.inArray(ext, ["gif","png","jpg","jpeg"]) == -1) {
								alert("Solo se permiten imagenes");
							}else{
								var numFiles = input.get(0).files ? input.get(0).files.length : 1;
								var label = input.val().split("\\\").pop();
								input.trigger("fileselect", [numFiles, label]);
							}
						});
						jQuery(document).ready( function() {
							jQuery(":file").on("fileselect", function(event, numFiles, label) {
								var input = jQuery(this).parents(".input-group").find(":text"),
								log = numFiles > 1 ? numFiles + " files selected" : label;
								if( input.length ) {
									input.val(log);
								} else {
									if( log ) alert(log);
								}

							});
						});
					});
				</script>
			';
			
			$html .= '<input type="hidden" name="mgs_forms_chk_file" id="mgs_forms_chk_file" value="yes">';
			return $html;
		}
		
		//Crea links utilizando el formato Markdown para ser utilizados por ejemplo en las politicas de privacidad.
		private function doMarkdownLinks($s){
			return preg_replace_callback('/\{(.*?)\}\((.*?)\)/', function ($matches){
				return '<a href="' . $matches[2] . '">' . $matches[1] . '</a>';
			}, htmlspecialchars($s));
		}
		
		private function verifica_recaptcha(){
				//if( $_POST['mgs-forms-acc']=='save' && $_POST["g-recaptcha-response"] ){
				if( isset($_POST["has-grecaptcha"]) ){
					if( $_POST["g-recaptcha-response"] ){
						$secret = get_option('mgs_forms_reCAPTCHA_private_key');
						$response = NULL;
						$reCaptcha = new ReCaptcha($secret);
						$response = $reCaptcha->verifyResponse(
							$_SERVER["REMOTE_ADDR"],
							$_POST["g-recaptcha-response"]
						);
						if( $response->success ){
							return true;
						}else{
							return false;
						}
					}
				}else{
					//form sin recaptcha
					return true;
				}
		}
		
		private function verifica_post(){
			if( $_POST['mgs-forms-acc']=='save' && $_POST['website']=='' ){
				self::$flag_post = true;
				if( MGS_FORMS_DEBUG ) self::$debug['verifica_post'] = 'true';
				return true;
			}else{
				self::$flag_post = false;
				if( MGS_FORMS_DEBUG ) self::$debug['verifica_post'] = 'false';
				return false;
			}
		}
		
		private function verifica_nonce(){
			/*if( !wp_verify_nonce($_POST['nonce'], $_POST['unique_class']) ){
				wp_die(__('Error al procesar el formulario, intente más tarde. [nonce]', 'mgs-forms'));
			}else{
				if( MGS_FORMS_DEBUG ) self::$debug['verifica_nonce'] = 'true';
				return true;
			}*/
			return true;
		}
		
		private function zanitice_post($post){
			// si el valor de un elemento de POST esta vacio, coloco un espacio en su lugar, para almacenar en la bbdd
			// ese espacio y evitar el corrimiento de columnas al exportar.
			foreach( $post as $_K=>$_P){
				if( $_P=='' ) $post[$_K] = ' ';
				if( is_array($_P) ){
					$tt = array_filter($_P, function($value){return $value!=='';});
					$tt = array_filter($tt, function($value){return $value!==' ';});
					rsort($tt);
					if( count($tt)==1 ) $tt = $tt[0];
					$post[$_K] = $tt;
				}
				
			}
			if( MGS_FORMS_DEBUG ) self::$debug['zanitice_post'] = 'completed';
			
			unset($post['g-recaptcha-response']);
			unset($post['hidden-grecaptcha']);
			
			return $post;
		}
		
		private function upload_post_file($file){
			foreach( $file as $_K=>$_F){
				$rand_seed = wp_rand(111111111, 999999999);
				$base_name = 'mgs-'.$rand_seed.'-'.basename($file[$_K]['name']);
				$target_path = MGS_FORMS_UPLOAD_DIR . $base_name;
				$target_url = MGS_FORMS_UPLOAD_URL . $base_name;
				if( move_uploaded_file($file[$_K]['tmp_name'], $target_path) ){
					$file[$_K]['uploaded'] = $target_url;
				}
			}
			if( MGS_FORMS_DEBUG ) self::$debug['upload_post_file'] = 'completed';
			return $file;
		}
		
		private function save_in_db($post, $page_id){
			//elimina el campo website, este se utiliza para tratar de interseptar spams
			unset($post['website']);
			$fields = serialize($post);
			global $wpdb;
			global $table_name_mgs_forms;
			//$table_name_mgs_forms = $wpdb->prefix . 'MGS_Forms_submits';
			$wpdb->insert(
				$table_name_mgs_forms,
				array(
					'post_id'		=> $page_id,
					'fecha'			=> date('Y-m-d'),
					'nonce'			=> $post['mgs-forms-ID-gen'],
					'fields'		=> $fields,
					'refferer'		=> $post['referrer'],
					'agent'			=> $post['agent']
				)
			);
			$_id = $wpdb->insert_id;
			if( $wpdb->last_error === '' ){
				self::$flag_saved = true;
				if( MGS_FORMS_DEBUG ) self::$debug['save_in_db'] = 'completed';
				return $_id;
			}else{
				self::$flag_saved = false;
				if( MGS_FORMS_DEBUG ) self::$debug['save_in_db'] = 'faild';
				return false;
			}
		}
		
		/*	funciones para el armado del mail en formato raw, se envia al admin u otra persona del sitio	*/
		private function build_raw_from($atts){
			if( $atts['send_mail_raw_from']=='' ){
				return 'no-reply@'.self::$dominio;
			}else{
				return $atts['send_mail_raw_from'];
			}
		}

		private function build_raw_from_name($atts){
			if( $atts['send_mail_raw_from_name']=='' ){
				return get_bloginfo('name');
			}else{
				return $atts['send_mail_raw_from_name'];
			}
		}
		
		private function build_raw_subject($atts){
			if( $atts['send_mail_raw_subject']=='' ){
				return self::$config_plg_send_mail_raw_subject;
			}else{
				return $atts['send_mail_raw_subject'];
			}
		}
		
		private function build_raw_message($post, $id){
			$raw_info = $post;
			$raw_text = '';
			//$raw_info['date'] = date('Y-m-d');
			$raw_info['date'] = date('d/m/Y');
			$raw_info['db_ID'] = $id;
			
			$array_exclude= array('mgs_forms_chk_file', 'nonce', 'unique_class', 'referrer', 'agent', 'mgs-forms-ID-gen', 'mgs-forms-acc', 'status', 'optionpay_email', 'optionpay_url_return_ok', 'optionpay_url_return_error', 'optionpay_moneda', 'optionpay_paypal_sandbox');
			
			foreach($raw_info as $k=>$v){
				if( isset(self::$content_array[$k]) ){	//es un campo del form
					$raw_text .= '<strong>'.self::$content_array[$k]['label'].':</strong> '.self::$content_array[$k]['value'].'<br>';
				}elseif( $k=='UPLOADS' ){	//es un archivo
					foreach($v as $fk=>$fv){
						if( isset(self::$content_array[$fk]) ){
							$raw_text .= '<strong>'.self::$content_array[$fk]['label'].':</strong> '.self::$content_array[$fk]['value'].'<br>';
						}
					}
				}else{	//informacion adicional
					if( !in_array($k, $array_exclude) ){
						$raw_text .= '<strong>'.$k.':</strong> '.$v.'<br>';
					}
				}
			}
			return $raw_text;
			//return $raw_text;
			//return '<pre style="white-space:pre-wrap;white-space:-moz-pre-wrap;white-space:-pre-wrap;white-space:-o-pre-wrap;word-wrap:break-word;">'.print_r(self::$content_array, true).'</pre>';
		}
		
		private function send_mail_raw($atts, $post, $id){
			$headers = 'MIME-Version: 1.0'."\r\n";
			$headers .= 'Content-type: text/html; charset=utf8'."\r\n";
			$headers .= 'From: '.$this->build_raw_from_name($atts).' <'.$this->build_raw_from($atts).'>'."\r\n";
			if( $atts['send_mail_raw_responder_a']=='yes' ){
				$headers .= 'Reply-To: '.$post['correo'].' <'.$post['correo'].'>\r\n';
			}
			if( wp_mail($atts['send_mail_raw_to'], $this->build_raw_subject($atts), $this->build_raw_message($post, $id), $headers) ){
				self::$flag_mailed = true;
				if( MGS_FORMS_DEBUG ) self::$debug['send_mail_raw'] = 'completed';
				return true;
			}else{
				self::$flag_mailed = false;
				if( MGS_FORMS_DEBUG ) self::$debug['send_mail_raw'] = 'faild';
				return false;
			}
		}
		
		/*	funciones para el armado del mail que se envia a quien completa el form, este es en texto plano	*/
		private function build_ok_from_name($atts){
			if( $atts['send_mail_ok_remitente_name']=='' ){
				return get_bloginfo('name');
			}else{
				return $atts['send_mail_ok_remitente_name'];
			}
		}
		
		private function build_ok_from($atts){
			if( $atts['send_mail_ok_remitente']=='' ){
				return 'no-reply@'.self::$dominio;
			}else{
				return $atts['send_mail_ok_remitente'];
			}
		}
		
		private function build_ok_subject($atts){
			if( $atts['send_mail_ok_subject']=='' ){
				return self::$config_plg_send_mail_ok_subject;
			}else{
				return $atts['send_mail_ok_subject'];
			}
		}
		
		private function build_ok_message($post, $id, $atts){
			$texto_mail = $atts['send_email_ok_plantilla'];
			foreach($post as $k=>$v){
				$texto_mail = str_replace('{'.strtoupper($k).'}', $v, $texto_mail);
			}
			
			$_fusion_options = get_option('fusion_options');
			$texto_mail = '<img src="'.esc_url_raw($_fusion_options['logo']['url']).'" alt="'.bloginfo('name').'"><br /><br />' . $texto_mail;
			
			return $texto_mail;
		}
		
		private function send_mail_ok($atts, $post, $id){
			$headers = 'MIME-Version: 1.0'."\r\n";
			$headers .= 'Content-type: text/html; charset=utf8'."\r\n";
			$headers .= 'From: '.$this->build_ok_from_name($atts).' <'.$this->build_ok_from($atts).'>'."\r\n";
			if( wp_mail($post['correo'], $this->build_ok_subject($atts), $this->build_ok_message($post, $id, $atts), $headers) ){
				self::$flag_mailed_ok = true;
				if( MGS_FORMS_DEBUG ) self::$debug['send_mail_ok'] = 'completed';
				return true;
			}else{
				self::$flag_mailed_ok = false;
				if( MGS_FORMS_DEBUG ) self::$debug['send_mail_ok'] = 'faild';
				return false;
			}
		}
		
		/*	redireccionamientos	*/
		private function redirecciona_ok($atts, $id, $post){
			if( $atts['redirect_url_ok']!="" && $atts['redirect']=='yes' ){
				$page_id = url_to_postid($atts['redirect_url_ok']);
				$url =  add_query_arg(
					array(
						'pre_id'	=> $id,
						'nonce'		=> $post['mgs-forms-ID-gen']
					),
					get_permalink($page_id)
				);
				echo '<script>window.location = "'.$url.'";</script>';
				die();
			}else{
				return $this->msj_alert_success($atts);
			}
		}
		
		private function redirecciona_bad($atts){
			if( $atts['redirect_url_bad']!='' && $atts['redirect']=='yes' ){
				$page_id = url_to_postid($atts['redirect_url_bad']);
				echo '<script>window.location = "'.get_permalink($page_id).'";</script>';
				die();
			}else{
				return $this->msj_alert_error($atts);
			}
		}
		
		/*	avisos sin redireccionar	*/
		private function msj_alert_success($atts){
			return do_shortcode('[fusion_alert type="success" border_size="1px" box_shadow="yes"]'.$atts['msj_ok'].'[/fusion_alert]');
		}
		
		private function msj_alert_error($atts){
			return do_shortcode('[fusion_alert type="error" border_size="1px" box_shadow="yes"]'.$atts['msj_bad'].'[/fusion_alert]');
		}
		
		private function avisos($atts, $pre_id, $post){
			if( self::$flag_saved ){
				return $this->redirecciona_ok($atts, $pre_id, $post);
			}else{
				return $this->redirecciona_bad($atts);
			}
		}
		
		private function form_hiddens($unique_class){
			$semilla = rand(11111,99999);
			$out = '';
			$out .= '<input type="text" name="website" id="website" style="display:none !Important"/>';
			$out .= '<input type="hidden" name="nonce" value="'.wp_create_nonce($unique_class).'" />';
			$out .= '<input type="hidden" name="unique_class" value="'.$unique_class.'" />';
			$out .= '<input type="hidden" name="referrer" value="'.$_SERVER['HTTP_REFERER'].'"/>';
			$out .= '<input type="hidden" name="agent" value="'.$_SERVER['HTTP_USER_AGENT'].'"/>';
			$out .= '<input type="hidden" name="mgs-forms-ID-gen" value="'.wp_create_nonce($_SERVER['HTTP_USER_AGENT'].$unique_class).'"/>';
			$out .= '<input type="hidden" name="mgs-forms-acc" value="save">';
			/*	verifico PLGs activos y con compativilidad	*/
			/*	WPML	*/
			if( self::$compatible['wpml'] ){
				$out .= '<input type="hidden" name="ICL_lang" value="'.ICL_LANGUAGE_CODE.'">';
			}
			return $out;
		}
		
		private function form_submit_button($atts){
			$out = '<div class="fusion-clearfix"></div>';
			$out .= '<div class="mgs-forms-warper-input">';
			if( $atts['boton_theme_colors']=='no' ){
				$out .= '
				<style type="text/css" scoped="scoped">
					/*	texto normal		*/
					.fusion-button.button-'.$atts['name'].' .fusion-button-text,
					.fusion-button.button-'.$atts['name'].' i {color:'.$atts['boton_color_texto_normal'].';}
					.fusion-button.button-'.$atts['name'].' {border-width:0px;border-color:'.$atts['boton_color_texto_normal'].';}
					.fusion-button.button-'.$atts['name'].' .fusion-button-icon-divider{border-color:'.$atts['boton_color_texto_normal'].';}
					
					/*	texto hover			*/
					.fusion-button.button-'.$atts['name'].':hover .fusion-button-text,
					.fusion-button.button-'.$atts['name'].':hover i,
					.fusion-button.button-'.$atts['name'].':focus .fusion-button-text,
					.fusion-button.button-'.$atts['name'].':focus i,
					.fusion-button.button-'.$atts['name'].':active .fusion-button-text,
					.fusion-button.button-'.$atts['name'].':active{color:'.$atts['boton_color_texto_hover'].';}
					.fusion-button.button-'.$atts['name'].':hover,
					.fusion-button.button-'.$atts['name'].':focus,
					.fusion-button.button-'.$atts['name'].':active{border-width:0px;border-color:'.$atts['boton_color_texto_hover'].';}
					.fusion-button.button-'.$atts['name'].':hover .fusion-button-icon-divider,
					.fusion-button.button-'.$atts['name'].':hover .fusion-button-icon-divider,
					.fusion-button.button-'.$atts['name'].':active .fusion-button-icon-divider{border-color:'.$atts['boton_color_texto_hover'].';}
					
					/*	fondo normal		*/
					.fusion-button.button-'.$atts['name'].'{background:'.$atts['boton_color_fondo_normal'].';}
					
					/*	fondo hover			*/
					.fusion-button.button-'.$atts['name'].':hover,
					.button-'.$atts['name'].':focus,.fusion-button.button-'.$atts['name'].':active{background:'.$atts['boton_color_fondo_hover'].';}
					
					/*.fusion-button.button-'.$atts['name'].'{width:100%;}*/
				</style>
				';
			}
			
			$out .= '<button type="submit" class="fusion-button button-flat button-square button-medium button-default button-'.$atts['name'].'">';
			if( $atts['icon_boton']!='' ){
				if( $atts['icon_divider_boton']=='yes' ){
					$icono = '<i class="'.$atts['icon_boton'].' mgs-pay-icon"></i>';
					$divisor = '<span class="fusion-button-icon-divider button-icon-divider-'.$atts['icon_position_boton'].'">'.$icono.'</span>';
					if( $atts['icon_position_boton']=='left' ){
						$out .= $divisor.'<span class="fusion-button-text fusion-button-text-'.$atts['icon_position_boton'].'">'.$atts['text_boton'].'</span>';
					}else{
						$out .= '<span class="fusion-button-text fusion-button-text-'.$atts['icon_position_boton'].'">'.$atts['text_boton'].'</span>'.$divisor;
					}
				}else{
					$icono = '<i class="'.$atts['icon_boton'].' button-icon-'.$atts['icon_position_boton'].' mgs-pay-icon"></i>';
					if( $atts['icon_position_boton']=='left' ){
						$out .= $icono.'<span class="fusion-button-text">'.$atts['text_boton'].'</span>';
					}else{
						$out .= '<span class="fusion-button-text">'.$atts['text_boton'].'</span>'.$icono;
					}
				}
			}else{
				$out .= '<span class="fusion-button-text">'.$atts['text_boton'].'</span>';
			}			
			$out .= '</button></div>';
			return $out;
		}
		
		private function build_jq_validate($atts){
			$out = '';
			$out .= '<style>.mgs-forms-warper-input .tooltip{color:'.$atts['tooltip_text_color'].'}.mgs-forms-warper-input .tooltip .tooltip-arrow{border-bottom-color:'.$atts['tooltip_color'].'}.mgs-forms-warper-input .tooltip-inner{background-color:'.$atts['tooltip_color'].'}</style>';
			$out .= '
				<script>
					jQuery.extend(jQuery.validator.messages,{required:"'.$atts['jq_val_msj_required'].'",remote:"'.$atts['jq_val_msj_required'].'",email:"'.$atts['jq_val_msj_email'].'",url:"Por favor, escribe una URL válida.",date:"Por favor, escribe una fecha válida.",dateISO:"Por favor, escribe una fecha (ISO) válida.",number:"Por favor, escribe un número válido.",digits:"Por favor, escribe sólo dígitos.",creditcard:"Por favor, escribe un número de tarjeta válido.",equalTo:"Por favor, escribe el mismo valor de nuevo.",extension:"Por favor, escribe un valor con una extensión aceptada.",maxlength:jQuery.validator.format("Por favor, no escribas más de {0} caracteres."),minlength:jQuery.validator.format("'.$atts['jq_val_msj_len_limit_min'].'"),rangelength:jQuery.validator.format("Por favor, escribe un valor entre {0} y {1} caracteres."),range:jQuery.validator.format("Por favor, escribe un valor entre {0} y {1}."),max:jQuery.validator.format("Por favor, escribe un valor menor o igual a {0}."),min:jQuery.validator.format("Por favor, escribe un valor mayor o igual a {0}."),nifES:"Por favor, escribe un NIF válido.",nieES:"Por favor, escribe un NIE válido.",cifES:"Por favor, escribe un CIF válido."});
					
					var validator = jQuery("#'.$atts['name'].'").validate();
				</script>
			';
			return $out;
		}
		
		
		
		
		private function fusion_mgs_form_meta_box($post_type, $post){
			add_meta_box( 'fusion_mgs_form-meta-box-registros', 'My First Meta Box', array($this, 'fusion_mgs_form_meta_box_render'), 'page', 'normal', 'high' );
		}
		
		private function fusion_mgs_form_meta_box_render(){
			echo 'hola mundo';
		}
	}
}