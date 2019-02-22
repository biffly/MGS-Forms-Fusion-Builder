<?php
//https://code.tutsplus.com/tutorials/a-guide-to-the-wordpress-http-api-automatic-plugin-updates--wp-25181

class MGS_PLG_Update{
	static public $current_version;
	static public $update_path;
	static public $plg_slug;
	static public $slug;
	
	static public $lic;
	static public $server;
	static public $api_key;
	static public $wp_option_license = 'MGS-FORMS-LICENSE';
	static public $product_id = '155';
	
	public $err;
	
	function __construct($_current_version, $_update_path, $_plg_slug, $lic=false , $_server='', $_api_key=''){
		if( $this->is_licensed() ){
			list ($t1, $t2) = explode('/', $_plg_slug);
			add_filter('pre_set_site_transient_update_plugins', array(&$this, 'check_update'));
			add_filter('plugins_api', array(&$this, 'check_info'), 10, 3);
		
			self::$current_version = $_current_version;
			self::$update_path = $_update_path;
			self::$plg_slug = $_plg_slug;
			self::$slug = str_replace('.php', '', $t2);
			self::$lic = $this->get_license();
		}
		
		self::$server = $_server;
		self::$api_key = $_api_key;
		
		/*
		if( $_server!="" && $_api_key!="" ){
			if( self::is_licensed() ){
				self::$lic = get_option(self::$wp_option_license);
			}else{
				self::$lic = $lic;
			}
			self::$server = $_server;
			self::$api_key = $_api_key;
		}
		*/
	}
	
	public function check_update($transient){
		if( empty($transient->checked) ){
			return $transient;
		}
		$remote_version = $this->getRemote_version();
		if( version_compare(self::$current_version, $remote_version, '<') ){
			$obj = new stdClass();
			$obj->slug = self::$slug;
			$obj->new_version = $remote_version;
			$obj->url = self::$update_path;
			$obj->package = self::$update_path;
			$transient->response[self::$plg_slug] = $obj;
		}
		//var_dump($transient);
		//var_dump($obj->url);
		return $transient;
	}
	
	public function check_info($false, $action, $arg){
		if( $arg->slug === self::$slug ){
			$information = $this->getRemote_information();
			return $information;
		}
		return false;
	}
	
	public function getRemote_version(){
		$request = wp_remote_post(self::$update_path, array('body' => array('action' => 'version', 'lic'=>self::$lic, 'plg'=>self::$plg_slug)));
		if( !is_wp_error($request) || wp_remote_retrieve_response_code($request)===200 ){
			//var_dump($request['body']);
			$_remote_ver = unserialize($request['body']);
			
			//var_dump($request);
			
			return $_remote_ver->new_version;
		}
		return false;
	}
	
	public function getRemote_information(){
		$request = wp_remote_post(self::$update_path, array('body' => array('action' => 'info')));
		if( !is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200 ){
			return unserialize($request['body']);
		}
		return false;
	}
	
	/*************************************************************************/
	
	public function Set_License($l){
		self::$lic = $l;
	}
	
	public function is_licensed(){
		if( self::$wp_option_license!='' ){
			$lic = get_option(self::$wp_option_license);
			if( !empty($lic) ){
				return true;
			}
		}
		return false;
	}
	
	public function active(){
		$url = self::$server . '/?secret_key=' . self::$api_key . '&slm_action=slm_activate&license_key=' . self::$lic . '&registered_domain=' . get_bloginfo('url') . '&item_reference=' . self::$product_id;
		$response = wp_remote_get($url, array('timeout' => 20, 'sslverify' => false));
		
		//var_dump($response);
		
		if( is_array($response) ){
			$json = $response['body'];
			$json = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', utf8_encode($json));
			$license_data = json_decode($json);
		}
		
		if( $license_data->result=='success' ){
			update_option( self::$wp_option_license, self::$lic );
			return true;
		}else{
			$this->err = $license_data->message;
			return false;
		}
	}
	
	public function get_license(){
		$lic = get_option(self::$wp_option_license);
		return $lic;
	}
	
	public function ValLicence(){
		$api_params = array(
			'slm_action'	=> 'slm_check',
			'secret_key'	=> self::$api_key,
			'license_key'	=> self::get_license(),
		);
		$response = wp_remote_get(add_query_arg($api_params, self::$server), array('timeout' => 20, 'sslverify' => false));
		if( is_array($response) ){
			$json = $response['body'];
			$json = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', utf8_encode($json));
			$license_data = json_decode($json);
			if( $license_data->result!='success' ){
				self::Set_License('');
				update_option( self::$wp_option_license, '' );
			}
		}
	}
}