<?php
/*
 * Licence Validation Class
 *
 * This class will 
 *	(1) validate the lic and set the option to true
 *	(2) check for new versions of software
 *	(3) download the new update
 *
 */
class tpg_lic_validation {
	
	private	$api_url="http://api.tpginc.net/tpg-api-listener.php";
	
	private $opts=array();
	private $paths=array();
	private $module_data= array( 
				'updt_sys'=>'',
				"module"=>'',
				);
	
	private $bnt_func="";
	private $btn_text="";		//description on button
	private $btn_desc_b4="";	//text above button
	private $btn_desc_af="";	//text after button
	private $btn_hidden="";		//hidden text <input type=hidden name=cmd value=_s-xclick>
	private $data=array();
	
	public $plugin_data=array();
	public $plugin_ext_data=array();
	
	
	/**
	 * Constrtor for lic validation
	 *
	 * @param	array	$gp_opts	options array
 	 * @param	array	$gp_paths	paths array
 	 * @param	array	$module		module data array
	 */
	
	function __construct($_opts,$_paths,$_module) {
		$this->opts=$_opts;
		$this->paths=$_paths;
		$this->module_data=$_module;
		$this->ro = tpg_gp_factory::create_resp_obj();
		
	}
	
	/**
     * set variables in class
     * 
	 * desc
	 *
     * @param 	string	variable name
	 * @param   string  value 
	 * @return	bool 
     */
	public function set_var($_var,$_val) {
		// var must exist; dynamic var not allowed
		if (array_key_exists($_var, get_class_vars(__CLASS__))) {
			$this->$_var = $_val;
			return true;
		} else {
			return false;
		}
	}

		
	/**
     * Validate license
     * 
	 * desc
	 *
     * @param 	void
	 * @return	array	response from request 
     */
	function validate_lic(){
		date_default_timezone_set('UTC');
		$this->data['hash']=time();
		$this->data['module']=$this->module_data['module'];
		$this->data['func']='validate';
		$this->data['lic-key']=$this->opts['lic-key'];
		$this->data['lic-email']=$this->opts['lic-email'];

    	$_resp = $this->curl_json_req($this->data);
		
		return $_resp;
	}
	
	/**
     * Update source from store
     * 
	 * this routine will update the source
	 *
     * @param 	array	an array of parms
	 *                   dest_path,tmp_path,dl_url,module name, skip_list array
	 * @return	object   response 
     */
	function update_source(array $_p){
		
		//instanitate updater only when needed to avoid conflict with wp core upgrader function
		if ($this->module_data['updt-sys'] == 'wp') {
			$this->wpu = tpg_gp_factory::create_wp_upgrader();
		}

		
		$_tmpflnm = download_url($_p['dl_url']);
		
		if (is_wp_error($_tmpflnm)) {
			$_keys=array_keys($_tmpflnm->errors) ;
			//print_r($_keys);
			//show_message('download failed '.$_tmpflnm->errors[$_keys[0]][0]);
			$_msg=__('Either the connection failed or you have reached your donwload limit. Load download link directly in browser for addtion information.','tpg-get-posts').' <br>'.__('Download link:','tpg-get-posts').$_p['dl_url'];
			//show_message($_msg);
			$this->ro->add_errtxt($_tmpflnm->get_error_code(),$_msg);
			$this->ro->add_errmsg($_tmpflnm->get_error_code(),$_tmpflnm->get_error_message());
			$this->ro->error();
			return $this->ro;
		}

		//if download was success
		if ($this->ro->is_success()) {
			$_resp=$this->wpu->fs_connect();
			if (is_wp_error($_resp)) {
				$this->ro->add_errmsg($_resp->get_error_code(),$_resp->get_error_message());
				$this->ro->error();
			} else {
				$this->ro->add_msg(__("opened fs connection successful",'tpg-get-posts'));
			}
		}
		global $wp_filesystem;

		//if file system connect success
		if ($this->ro->is_success()) {
			// Clean up working directory before unpack new data
			if ( $wp_filesystem->is_dir($_p['tmp_path']) ) {
				$wp_filesystem->delete($_p['tmp_path'], true);
			}
			$_resp = unzip_file($_tmpflnm, $_p['tmp_path']);
			if (is_wp_error($_resp)) {
				$this->ro->add_errmsg($_resp->get_error_code(),$_resp->get_error_message());
				$this->ro->error();
			} else {
				$this->ro->add_msg(sprintf(__('unzip %s to %s successful','tpg-get-posts'),$_tmpflnm,$_p['tmp_path']));
			}
		}
		
		//if unzip was success
		if ($this->ro->is_success()) {
			$_from=$_p['upg_ext_path'];
			$_to=$_p['dest_path'];
			if (array_key_exists('skip_list',$_p)) {
				$_skip = $_p['skip_list'];
			} else {
				$_skip=array();
			}
			$_resp = copy_dir($_from,$_to,$_skip);
			if (is_wp_error($_resp)) {
				$this->ro->add_errmsg($_resp->get_error_code(),$_resp->get_error_message());
				$this->ro->error();
			} else {
				$this->ro->add_msg(sprintf(__('copy from %s to %s successful','tpg-get-posts'),$_from,$_to));
			}
		}

		$this->ro->add_msg("updated source");
		unlink($_tmpflnm);
		// Clean up working directory
		if ( $wp_filesystem->is_dir($_p['tmp_path']) ) {
			$wp_filesystem->delete($_p['tmp_path'], true);
		}
		return $this->ro;
	}
	
	/**
     * get the version from the repository
     * 
	 * Get the version of the premium plugin from the tpg repository
	 *
     * @param 	void
	 * @return	mixed	false or array if sucessful 
     */
	function get_version(){
		date_default_timezone_set('UTC');
		$this->data['hash']=time();
		$this->data['module']=$this->module_data['module'];
		$this->data['func']='get-version';
		$this->data['lic-key']=$this->opts['lic-key'];
		$this->data['lic-email']=$this->opts['lic-email'];

    	$_resp = $this->curl_json_req($this->data);

		$this->ro->reset();
		if ($_resp->success) {
			$this->ro->add_msg(__('store version successful','tpg-get-posts'));
			$this->ro->add_data('version',$_resp->metadata->version);
		} else {
			$this->ro->success=false;
			$this->ro->add_errmsg(__('get-version-err','tpg-get-posts'),$_resp->errors[0]);
			$this->ro->add_data('version','0.0.0');
		}
		return $this->ro;
	}
	
	/**
     * get update link
     * 
	 * get the link to download the software
	 *
     * @param 	void
	 * @return	void 
     */
	function get_update_link(){
		date_default_timezone_set('UTC');
		$this->data['hash']=time();
		$this->data['module']=$this->module_data['module'];
		$this->data['func']='get-update-link';
		
		$this->data['order']=$this->opts['lic-key'];
		$this->data['email']=$this->opts['lic-email'];
		
    	$_resp = $this->curl_json_req( $this->data);
    	return $_resp;

	}
	
	/**
     * curl_json_req
     * 
	 * send a request to the api 
	 * 
     * @param 	void
	 * @return	void 
     */
	 function curl_json_req($data,$url=''){
		
		if ($url=='') {
			$url=$this->api_url; 
		}
		 
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json' ));
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(array("parms" => $data)));
		curl_setopt($ch, CURLOPT_POST, true); 
		$_res = curl_exec($ch);
		
		//res false error, res true chk for json fmt
		if ($_res){
			$_r = json_decode($_res);
			//if (is_null($_r)) {$_r = $_res;}
		} else {
			//get curl error
			$_r = tpg_gp_factory::create_resp_obj();
			$_r->success = false;
			$_r->add_errmsg(curl_errno($ch),curl_error($ch));
		} 

		curl_close($ch);
		return $_r;

	}

	 
	/**
     * gen_update_button
	 * 
	 * create update button
	 *
     * @param 	void
	 * @return	void 
     */
	function gen_update_button(){
		$this->bnt_func="update";
		$this->btn_text=__("update",'tpg-get-posts');     
		$this->btn_desc_b4="";	
		$this->btn_desc_af="";	
		$this->btn_hidden="";		//hidden text <input type=hidden name=cmd value=_s-xclick>
		$_button = gen_button();
		return $_button;
	}
	
	/**
     * generate lic validate button
	 * 
	 * desc
	 *
     * @param 	void
	 * @return	string    button code
     */
	function gen_validate_button(){
		$this->bnt_func='validate';
		$this->btn_text=__('validate','tpg-get-posts');     
		$this->btn_desc_b4="";	
		$this->btn_desc_af="";	
		$this->btn_hidden="";		//hidden text <input type=hidden name=cmd value=_s-xclick>
		$_button = gen_button();
		return $_button;
	}

	/**
     * generate button
	 * 
	 * desc
	 *
     * @param 	void
	 * @return	string    button code
     */
	function gen_button(){
		$button_code  = '<div id="'.$this->btn_func.'-button-wrapper">';
		if ($this->desc != '') {
			$button_code .= '<div id="'.$this->btn_func.'-desc-b4">'.$this->btn_desc_b4.'</div>';
		}
		$button_code .= '<div id="'.$this->btn_func.'-button"><form action="'.$this->api_url.'" method=post>';
		$button_code .= $this->btn_hidden;
		if ($this->btn_text != '') {
			$button_code .= '<input type=hidden name="funct_desc" value="'.$this->btn_func.'">';
		}
		if ($this->desc_af != '') {
			$button_code .= '<div id="'.$this->btn_func.'-desc-after">'.$this->btn_desc_af.'</div>';
		}
		$button_code .= '</form></div>	</div>';
		return $button_code;
	}
	
	/**
	 * Returns current plugin version and extension data.
	 * 
	 * @return string Plugin version
	 */
	public function get_plugin_info($_ext='') {
		if ( ! function_exists( 'get_plugin_data' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$plugin_name=$this->paths['name'].'.php';
		$this->plugin_data = get_plugin_data( $this->paths['dir'].$plugin_name );
		if ($_ext != '' && file_exists($this->paths['ext'].$_ext)) {	
			$this->plugin_ext_data = get_plugin_data( $this->paths['ext'].$_ext );
		} else {	
			$this->plugin_ext_data = array('Version'=>'0.0.0','Description'=>__('Ext file not found','tpg-get-posts'));
		}

		return; 
	}
	
	
	/**
     * normalize version
	 * 
	 * Normalize the version so alpha 2.0.1 and 2.01.0 will compare correctly.  
	 * convert alph ver xx.xx.xx to numeric x.xxxx
	 *
     * @param 	string	version
	 * @return	float	version numeric in x.xxxx
     */
	function normalize_ver($_v) {
	    //convert alph ver xx.xx.xx to x.xxxx
    	$va = array_map('intval',explode('.',$_v));
		return $va[0]+$va[1]*.01+$va[2]*.0001;
	}


}//end class
?>
