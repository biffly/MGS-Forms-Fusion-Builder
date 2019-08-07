<?php
/*
Plugin Name: MGS-Forms para Fusion Builder
Plugin URI: http://www.marceloscenna.com.ar
Description: Creacion de formularios y almacenado en BBDD. Permite crar de forma rapida un formulario y agregarlo utilizando Fusion Builder
Version: 1.9
Author: Marcelo Scenna
Author URI: http://www.marceloscenna.com.ar
Text Domain: mgs-forms
*/

if( !defined('ABSPATH') ){ exit; }
error_reporting(E_ALL & ~E_NOTICE);

require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/biffly/MGS-Forms-Fusion-Builder/',
	__FILE__,
	'MGS-Forms-FusionBuilder/MGS-forms.php'
);

/*
$tmp_upload_dir = wp_upload_dir();

if( get_option('MGS_FORMS_UPLOAD_DIR') ){
	$tmp_upload_dir = get_option('MGS_FORMS_UPLOAD_DIR');
}else{
	$tmp_upload_dir = wp_upload_dir();
	$tmp_upload_dir = $tmp_upload_dir['basedir'].'/mgs_forms_uploads';
	update_option( 'MGS_FORMS_UPLOAD_DIR', $tmp_upload_dir );
}
*/
if( get_option('MGS_FORMS_DEBUG')!='' && get_option('MGS_FORMS_DEBUG')=='true' ){
	$_debug = true;
}else{
	$_debug = false;
}

if( !defined('MGS_FORMS_BASENAME') )			define( 'MGS_FORMS_BASENAME', plugin_basename(__FILE__) );
if( !defined('MGS_FORMS_PLUGIN_DIR') ) 			define( 'MGS_FORMS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
if( !defined('MGS_FORMS_PLUGIN_DIR_URL') )		define( 'MGS_FORMS_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );
if( !defined('MGS_FORMS_VERSION') ) 			define( 'MGS_FORMS_VERSION', '1.9' );
if( !defined('MGS_FORMS_SLUG') )	 			define( 'MGS_FORMS_SLUG', 'MGS-Forms-FusionBuilder/MGS-forms.php' );
if( !defined('MGS_FORMS_PLUGIN_REMOTE_PATH') )	define( 'MGS_FORMS_PLUGIN_REMOTE_PATH', 'http://marceloscenna.com.ar/update.php' );
if( !defined('MGS_FORMS_SPECIAL_SECRET_KEY') ) 	define( 'MGS_FORMS_SPECIAL_SECRET_KEY', '589b4113769878.59794845' );
if( !defined('MGS_FORMS_LICENSE_SERVER_URL') )	define( 'MGS_FORMS_LICENSE_SERVER_URL', 'http://marceloscenna.com.ar' );
if( !defined('MGS_FORMS_ITEM_REFERENCE') )		define( 'MGS_FORMS_ITEM_REFERENCE', 'MGS-FORMS-FB' );
if( !defined('MGS_FORMS_DEBUG') )				define( 'MGS_FORMS_DEBUG', $_debug);


$tmp_upload_dir = wp_upload_dir();
$tmp_upload_url = $tmp_upload_dir['url'].'/mgs_forms_uploads/';
$tmp_upload_dir = $tmp_upload_dir['basedir'].'/mgs_forms_uploads/';
if( !defined('MGS_FORMS_UPLOAD_DIR') )			define( 'MGS_FORMS_UPLOAD_DIR', $tmp_upload_dir );
if( !defined('MGS_FORMS_UPLOAD_URL') )			define( 'MGS_FORMS_UPLOAD_URL', $tmp_upload_url );

if( !file_exists(MGS_FORMS_UPLOAD_DIR) ){
	mkdir(MGS_FORMS_UPLOAD_DIR, 0777, true);
}


global $mgs_forms_options;
global $mgs_forms_elements_options;
global $MGS_UP;


global $wpdb;
global $table_name;
global $sql_tabla;
$table_name = strtolower($wpdb->prefix . 'mgs_forms_submits');
$charset_collate = $wpdb->get_charset_collate(); 
$sql_tabla = "CREATE TABLE $table_name (id int(11) NOT NULL AUTO_INCREMENT, post_id int(11) NULL, fecha date NOT NULL, nonce varchar(255) NULL, fields text NOT NULL, agent text, refferer text, veri_date date DEFAULT NULL, veri_agent text, UNIQUE KEY id (id) ) $charset_collate;";


include(MGS_FORMS_PLUGIN_DIR.'/inc/class/class-main.php');
include(MGS_FORMS_PLUGIN_DIR.'/inc/config/forms.php');
include(MGS_FORMS_PLUGIN_DIR.'/inc/admin/class-main.php');
include(MGS_FORMS_PLUGIN_DIR.'/inc/class/class-update.php');
//include(MGS_FORMS_PLUGIN_DIR.'/inc/class/recaptchalib.php');

register_activation_hook(__FILE__, array('MGS_Tables', 'activation'));
add_action('wp_loaded', 'MGS_Forms_addon_load', 10);
add_action('fusion_builder_before_init', 'map_mgs_forms_addon_with_fb', 3);

if( is_admin() ){
	//add_filter('mce_external_plugins', 'mgs_tables_addon_TinyMCE_registre_javascript');
	//add_action('mce_buttons', 'mgs_tables_addon_TinyMCE_registre_buttons');
	//add_action('wp_print_scripts','mgs_tables_print_scripts');
	//add_editor_style(MGS_TABLES_PLUGIN_DIR_URL.'css/editor.css');
	//add_editor_style(MGS_TABLES_PLUGIN_DIR_URL.'css/font-awesome.min.css');
	add_action( 'admin_print_scripts', 'mgs_forms_admin_scripts');
}






if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'")!=$table_name ){
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta($sql_tabla);
	if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'")!=$table_name ) add_action( 'admin_notices', 'mgs_forms_notice_not_db' );
}



function MGS_Forms_addon_load(){
	global $MGS_UP;	
	MGS_Forms::get_instance();
	$MGS_UP = new MGS_PLG_Update(MGS_FORMS_VERSION, MGS_FORMS_PLUGIN_REMOTE_PATH, MGS_FORMS_SLUG, false, MGS_FORMS_LICENSE_SERVER_URL, MGS_FORMS_SPECIAL_SECRET_KEY);
	if( !$MGS_UP->is_licensed()==true ) add_action( 'admin_notices', 'mgs_forms_notice_not_registred' );
}

function mgs_forms_admin_scripts(){
	wp_enqueue_script('mgs_forms_admin-dataTables_js', MGS_FORMS_PLUGIN_DIR_URL.'js/jquery.dataTables.min.js', array('jquery'));
	
	wp_enqueue_style('mgs_font-datatables_css', MGS_FORMS_PLUGIN_DIR_URL.'css/jquery.dataTables.min.css');
}

function mgs_forms_notice_not_registred(){
	$screen = get_current_screen();
	if( $screen->parent_base!='mgs-forms-registros' ){
	?>
    <div class="error notice mgs-tables-error">
        <h3 style="color:#dc3232">MGS-Forms addon para Fusion Builder (Beta)</h3>
        <p>MGS-Forms para Fusion Builder no se encuantra registrado, debera ingresar su licencia para activarlo.</p>
        <p><a class="button button-primary button-medium" href="http://www.marceloscenna.com.ar/categoria-producto/wordpress/mgs-forms/" target="_blank">No poseo un licencia</a> <a class="button button-primary button-medium" href="<?php echo admin_url('admin.php?page=mgs-forms-registro')?>">Ingresar mi licensia ahora.</a></p>
    </div>
    <?php
	}
}

function mgs_forms_notice_not_db(){
	global $sql_tabla;
	?>
    <div class="error notice mgs-tables-error">
        <h3 style="color:#dc3232">MGS-Forms addon para Fusion Builder (Beta)</h3>
        <p>MGS-Forms para Fusion Builder no pudo crear una tabla nueva en su base de datos.</p>
        <p>Puede utilizar este codigo SQL para crearla manualmente.</p>
        <textarea rows="5" style="width:40%"><?php echo $sql_tabla?></textarea>
    </div>
    <?php
}
