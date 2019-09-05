<?php
class MGS_Forms_Builder_Admin {
	
	public $meta_title;
	
	public function __construct(){
		add_action('admin_menu', array($this, 'admin_menu'));
		add_action('wp_ajax_get_registros', array($this, 'get_registros_callback'));
		add_action('wp_ajax_delete_registros', array($this, 'delete_registros_callback'));
		add_action('add_meta_boxes', array($this, 'mgs_meta_box_registros'));	
		//add_action('enqueue_scripts', 'mgs_forms_admin_scripts');
		$this->meta_title = '
			<span class="MGS-builder-logo"></span>
			<span class="MGS-builder-title">MGS Forms</span>
			<!--<a href="http://www.marceloscenna.com.ar/producto/mgs-forms-para-fusion-builder-beta/" target="_blank" rel="noopener noreferrer">
				<span class="fusion-builder-help dashicons dashicons-editor-help"></span>
			</a>-->
		';					
				
	}
	
	function admin_menu(){
		$formularios = add_menu_page('MGS Forms', 'MGS Forms', 'manage_options', 'mgs-forms-formularios', array($this, 'Formularios'), 'dashicons-media-spreadsheet', '2.111111');
		$config = add_submenu_page('mgs-forms-formularios', __('Configuración', 'mgs-forms'), __('Configuración', 'mgs-forms'), 'manage_options', 'mgs-forms-config', array($this, 'Config'));
		$registro = add_submenu_page('mgs-forms-formularios', __('Registro', 'mgs-forms'), __('Registro', 'mgs-forms'), 'manage_options', 'mgs-forms-registro', array($this, 'Registro'));
		
		add_action('admin_print_scripts-'.$formularios, array($this, 'mgs_forms_admin_scripts'));
		add_action('admin_print_scripts-'.$config, array($this, 'mgs_forms_admin_scripts'));
		add_action('admin_print_scripts-'.$registro, array($this, 'mgs_forms_admin_scripts'));
	}
	
	public function Formularios(){
		require_once('screen-forms.php');
	}
	
	public function Config(){
		require_once('screen-config.php');
	}
	
	public function Registro(){
		require_once('screen-registro.php');
	}
	
	public function mgs_forms_admin_scripts(){
		wp_enqueue_script('mgs_forms-bootstrap_js', MGS_FORMS_PLUGIN_DIR_URL.'js/bootstrap.min.js', array('jquery'));
		wp_enqueue_script('mgs_forms_admin-bootbox_js', MGS_FORMS_PLUGIN_DIR_URL.'js/bootbox.min.js', array('jquery', 'mgs_forms-bootstrap_js'));
		
		wp_enqueue_style('mgs_font-awesome_admin_css', MGS_FORMS_PLUGIN_DIR_URL.'css/font-awesome.min.css');
		wp_enqueue_style('mgs_font-bootstrap_admin_css', MGS_FORMS_PLUGIN_DIR_URL.'css/bootstrap.min.css');
		//wp_enqueue_style('mgs_forms_admin_css', MGS_FORMS_PLUGIN_DIR_URL.'css/admin.css');		
	}
	
	protected static function mgs_forms_admin_tab($title, $page){
		if( isset($_GET['page']) ){
			$active_page = $_GET['page'];
		}
		if( $active_page==$page ){
			$link = 'javascript:void(0);';
			$active_tab = ' nav-tab-active';
		}else{
			$link = 'admin.php?page='.$page;
			$active_tab = '';
		}
		echo '<a href="'.$link.'" class="nav-tab'.$active_tab.'">'.$title.'</a>';
	}
	
	public static function footer(){
		echo '
			<div class="mgs-thanks">
				<p class="description">'.__('Gracias por elegir MGS Forms para Fusion Builder, nos esforzamos para usted.', 'mgs-forms' ).'<br>Marcelo Scenna &copy; '.date('Y').'</p>
			</div>
		';
	}
	
	public static function header(){
		echo '
			<div class="mgs-admin-header">
				<h1>'.__('MGS Forms para Fusion Builder!', 'mgs-forms').'</h1>
				<div class="about-text">
					<p>'.__('Creación de formularios y almacenado en BBDD. Permite crear de forma rápida un formulario y agregarlo utilizando Fusion Builder.', 'mgs-forms').'</p>
					<p><img alt="GitHub release" src="https://img.shields.io/github/release/biffly/MGS-Forms-Fusion-Builder.svg?style=for-the-badge"/> <img alt="GitHub last commit" src="https://img.shields.io/github/last-commit/biffly/MGS-Forms-Fusion-Builder.svg?style=for-the-badge"/></p>
					<p><img alt="GitHub top language" src="https://img.shields.io/github/languages/top/biffly/MGS-Forms-Fusion-Builder.svg"/> <img alt="GitHub  issues" src="https://img.shields.io/github/issues-raw/biffly/MGS-Forms-Fusion-Builder.svg"/> <img alt="WP ver min" src="https://img.shields.io/badge/wordpress-4.9-blue.svg?logo=wordpress"/> <img alt="WP ver tested" src="https://img.shields.io/badge/wordpress-5.1.1%20tested-green.svg?logo=wordpress"/> <img alt="License" src="https://img.shields.io/badge/license-BSD%202--Clause-blue.svg"/></p>
				</div>
				<div class="mgs-logo"><span class="mgs-version">'.__('Versión', 'mgs-forms').' '.MGS_FORMS_VERSION.'</span></div>
			</div>
			<h2 class="nav-tab-wrapper">
		';
		self::mgs_forms_admin_tab(__('Formularios', 'mgs-forms'), 'mgs-forms-formularios');
		self::mgs_forms_admin_tab(__('Registro', 'mgs-forms'), 'mgs-forms-registro');
		echo '
			</h2>
		';
	}
	
	public function get_registros_callback(){
		if( isset($_POST['form']) && $_POST['form']!='' ){
			global $wpdb;
			global $table_name_mgs_forms;
			$form = $_POST['form'];
			
			$sql = "SELECT * FROM ".$table_name_mgs_forms." WHERE post_id='".$form."' ORDER BY fecha DESC";
			
			$header = array('fecha'	=> '-');
			$data = array();
			$exclude_data = array('unique_class', 'nonce', 'mgs-forms-ID-gen', 'mgs-forms-acc');
			$exclude_data = array_merge($exclude_data, $_POST['hide']);
			
			$resus = $wpdb->get_results($sql);
			foreach( $resus as $resu ){
				$f = unserialize($resu->fields);
				foreach( $f as $k=>$v ){
					if( !in_array($k, $exclude_data) ){
						$header[$k] = '-';
					}
				}
			}
			if( $_POST['screen']!='edit-post' ){
				$header['-'] = '-';
			}
			
			
			$resus = $wpdb->get_results($sql);
			foreach( $resus as $resu ){
				$t = array($resu->fecha);
				$f = unserialize($resu->fields);
				foreach( $f as $k=>$v ){
					if( !in_array($k, $exclude_data) ){
						if( is_array($v) ){
							$t[] = implode(' | ', $v);
						}else{
							$t[] = $v;
						}
					}
				}
				if( $_POST['screen']!='edit-post' ){
					$t[] = '<a href="#" data-id="'.$resu->id.'" class="mgs-forms-del-registro button button-default"><i class="fa fa-trash" aria-hidden="true"></i></a>';
				}
				$data[] = $t;
			}
			
			$resp = array(
				'est'		=> 'OK',
				'header'	=> $header,
				'data'		=> $data
			);
		}else{
			$resp = array('est'	=> 'ERR');
		}
		echo json_encode($resp);
		die();
	}
	
	public function delete_registros_callback(){
		if( isset($_POST['id']) && $_POST['id']!='' ){
			global $wpdb;
			global $table_name_mgs_forms;
			$id = $_POST['id'];
			if( $wpdb->delete($table_name_mgs_forms, array('id'=>$id)) ){
				$resp = array('est'	=> 'OK');
			}else{
				$resp = array('est'	=> 'ERR');
			}
		}else{
			$resp = array('est'	=> 'ERR');
		}
		echo json_encode($resp);
		die();
	}
	
	public function mgs_meta_box_registros($postType){
		add_meta_box('mgs-meta-box-registros', $this->meta_title, array($this, 'mgs_meta_box_registros_render'), $postType, 'normal', 'default');
		add_filter('postbox_classes_'.$postType.'_mgs-meta-box-registros', array($this, 'mgs_meta_box_registros_classes'));
	}
	
	public function mgs_meta_box_registros_classes($classes=array()){
		$classes[] = 'mgs-meta-box';
	    return $classes;
	}
	
	public function mgs_meta_box_registros_render(){
		global $post;
		global $wpdb;
		global $table_name_mgs_forms;
		
		if( $wpdb->get_var("SHOW TABLES LIKE '$table_name_mgs_forms'")==$table_name_mgs_forms ){					
			$forms_ids = $wpdb->get_results("SELECT post_id FROM ".$table_name_mgs_forms." WHERE post_id=".$post->ID." GROUP BY post_id ORDER BY post_id");
			if( $forms_ids ){
				echo '
					<label class="mgs-forms-chk-replace-fa" style="display:none;">
						<input type="checkbox" name="hide_fields[]" class="mgs-forms-control form-control mgs-hide-admin" value="referrer" checked/>
						<i class="fa fa-square-o"></i><i class="fa fa-check-square-o"></i> <span class="label">Referer</span>
					</label> 
					<label class="mgs-forms-chk-replace-fa" style="display:none;">
						<input type="checkbox" name="hide_fields[]" class="mgs-forms-control form-control mgs-hide-admin" value="agent" checked/>
						<i class="fa fa-square-o"></i><i class="fa fa-check-square-o"></i> <span class="label">Agent</span>
					</label>
					<h4>Recuerde que para eliminar algun registro debera hacerlo desde <a href="admin.php?page=mgs-forms-formularios">aquí</a></h4> 
					<table id="mgs-registros-admin" class="table table-striped table-bordered dataTable" width="100%"></table>
					<script>
						LoadRegistros();
						
						function LoadRegistros(){
							var form = '.$post->ID.';
							var hide_f = [];
					
							jQuery("input.mgs-hide-admin").each(function(){
								if( jQuery(this).is(":checked") ){
									hide_f.push(jQuery(this).val());
								}
							});
							jQuery.ajax({ 
								data	: {
									action	: "get_registros",
									form	: form,
									hide	: hide_f,
									screen	: "edit-post"
								},
								type	: "post",
								url		: ajaxurl,
								success	: function(data){
									var resp = jQuery.parseJSON(data);
									if( resp.est=="OK" ){
										var cab = [];			
										jQuery.each(resp.header, function(i, item){	
												cab.push( {title:i} );
										});
										cab[cab.length-1] = {title:"", orderable:false};
										
										tabla = jQuery("#mgs-registros-admin").DataTable({
											data	: resp.data,
											columns	: cab
										});
									}
								}
							});
						}
					</script>
				';
			}
		}
	}
}
new MGS_Forms_Builder_Admin();