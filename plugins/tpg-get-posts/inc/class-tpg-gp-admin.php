<?php
/*
 *  display the settings page
*/
//class tpg_gp_settings extends tpg_get_posts {
class tpg_gp_admin {
	
	//sec since last update 30x24x60= 43200 sec in 30 day month
	private $update_time=60;
	
	private $pp_btn='';
	private $resp_data=array(
					'dl-url'=>'',
					'dl-link'=>'',
					);
	private $ext_name='class-tpg-gp-process-ext.php';
	
	//versions
	public $v_store = '';
	private $v_store_norm = 0.0;
	private $v_plugin = '';
	private $v_plugin_norm = 0.0;
	private $v_plugin_ext = '';
	private $v_plugin_ext_norm = 0.0;
					
	protected $vl=object;
					
	//variables set by constructor				
	public $gp_opts=array();
	public $gp_paths=array();
	public $module_data=array();
	public $plugin_data=array();
	public $plugin_ext_data=array();
	
	function __construct($opts,$paths,$notice=false) {
		$this->gp_opts=$opts;
		$this->gp_paths=$paths;
		$this->module_data= array( 
				'updt-sys'=>'wp',
				"module"=>'tpg-get-posts',
				);
		
		$this->update_time = $this->gp_opts['updt-sec'];
		$this->vl = tpg_gp_factory::create_lic_validation($this->gp_opts,$this->gp_paths,$this->module_data);
		$this->vl->get_plugin_info($this->ext_name);
		$this->plugin_data = $this->vl->plugin_data;
		$this->plugin_ext_data = $this->vl->plugin_ext_data;
		
		// Register link to the pluging list- if not called from the admin_notice hook
		if (!$notice) {
			add_filter('plugin_action_links', array(&$this, 'tpg_get_posts_settings_link'), 10, 2);
		}
		// Add the admin menu item 
		add_action('admin_menu', array(&$this,'tpg_get_posts_admin'));	


		if ($opts['show-ids']) {
			if ($opts['valid-lic'] && file_exists($paths['dir']."ext/class-tpg-show-ids.php")) {
				$ssid = tpg_gp_factory::create_show_ids($this->gp_opts,$this->gp_paths);
			}
		}
		
		//check for stopping of updates
		if ($opts['freeze']) {
			add_filter('site_transient_update_plugins', array(&$this, 'tpg_gp_freeze'));
		}
	}
	
	/**
	 *	add footer info on admin page 
	 *
	 * @package WordPress
	 * @subpackage tpg_get_posts
	 * @since 1.3
	 *
	 * write the footer information on options page
	 * 
	 * @param	array	$links
	 * @param 	 		$file
	 * @return	array	$links
	 *
	*/ 
	public function tpg_gp_footer() {
		printf('%1$s by %2$s<br />', $this->plugin_data['Title'].'  Version: '.$this->plugin_data['Version'], $this->plugin_data['Author']);
	}

	/*
	 *	add link to plugin doc & settings 
	 * @package WordPress
	 * @subpackage tpg_get_posts
	 * @since 1.3
	 *
	 * add the settings link in the plugin description area
	 * 
	 * @param	array	$links
	 * @param 	 		$file
	 * @return	array	$links
	 */
	 
	function tpg_get_posts_settings_link($links, $file) {
		static $this_plugin;
		if (!$this_plugin) $this_plugin = plugin_basename($this->gp_paths['base']);
		if ($file == $this_plugin){
			$settings_link = '<a href="options-general.php?page=tpg-get-posts-settings">'.__('Settings/Doc', 'tpg-get-posts').'</a>';
			array_unshift($links, $settings_link);
		}
		return $links;
}

	/**
	 *	add admin menu
	 * @package WordPress
	 * @subpackage tpg_get_posts
	 * @since 1.3
	 *
	 * add the TPG GET POSTS menu item to the Setting tab 
	 * 
	 * @param    void
	 * @return   void
	 *
	 */
	function tpg_get_posts_admin () {
		// if we are in administrator environment
		if (function_exists('add_submenu_page')) {
			add_options_page('TPG Get Posts Settings', 
							'TPG Get Posts', 
							'manage_options',
							'tpg-get-posts-settings', 
							array(&$this,'tpg_gp_show_settings')
							);
		}
	}
	
	/*
	 * show the settings page
	 *
	 * @package WordPress
	 * @subpackage tpg_get_posts
	 * @since 2.8
	 *
	 * the html text for the setting page is loaded into the content variable 
	 * and then printed.
	 * the style sheet is enqueued using the wp enqueue process
	 * 
	 * @param    type    $id    post id
	 * @return   string         category ids for selection
	 *
	 */ 
	public function tpg_gp_show_settings() {
		//get css, js
		global $gp;	
		$this->gp_admin_load_inc();
		
		// footer info for settings page
		add_action('in_admin_footer', array($this,'tpg_gp_footer'));
		
		//if options have been set, process them & update array
		if( isset($_POST['gp_opts']) ) {
			$new_opts = $_POST['gp_opts'];

			$_func = $_POST['func'];
			
			switch ($_func) {
				case 'updt_opts':
					$this->update_options($new_opts);
					break;
				case 'val_lic':
					$this->validate_lic();
					break;
				case 'updt_plugin':
					$this->update_plugin_ext();
					break;
			}
			//refresh options
			//$this->gp_opts=tpg_get_posts::get_options();
			$this->gp_opts=$gp->get_options();
		}
		
		ob_start();
		include($this->gp_paths['inc'].'doc-text.php');
		$page_content = ob_get_contents();
		ob_end_clean();
		//$page_content = file_get_contents($this->gp_paths['inc'].'doc-text.php');
		
		//replace tokens in text
		$page_content = str_replace("{settings}",$this->tpg_gp_bld_setting(),$page_content);
		$page_content = str_replace("{icon}",screen_icon(),$page_content);
		if ($this->gp_opts['valid-lic']) {
			$page_content = str_replace("{donate}",'',$page_content);
		} else {
			$page_content = str_replace("{donate}",$this->pp_btn,$page_content);
		}
		
		echo $page_content;
	
	}
	
	function tpg_gp_bld_setting() {
		$form_output = $this->build_form();
		//set action link for form
		$action_link = str_replace( '%7E', '~', $_SERVER['REQUEST_URI'])."#gp-settings"; 
		//replace tokens in text
		$form_output = str_replace("{action-link}",$action_link,$form_output);
		//check for update & show cur ver 
		$this->check_for_update();

		//if update available
		if ($this->v_store_norm > $this->v_plugin_ext_norm) {
			//$ver_txt = $this->plugin_ext_data['Version'].'&nbsp;&nbsp;Newer version exists '.$new_ver;
			$ver_txt = $this->v_plugin_ext.'&nbsp;&nbsp;Newer version exists '.$this->v_store;
			//build update button
			$btn_updt_plugin_txt = __('Update Plugin', 'gp_updt_plugin_opts' ) ;
			$upd_button= '<button type="submit" class="button-primary tpg-settings-btn" name="func" value="updt_plugin" />'.$btn_updt_plugin_txt.'</button><input type="hidden" name="dl-url" value="{download-url}" />';
		} else {
			$ver_txt = $this->v_plugin_ext;
			$upd_button='';
		}
		//message for valid lic
		if ($this->gp_opts['valid-lic']) {
			$valid_txt=__("The license opts have been validated - thank you.",'tpg-get-posts');
		} else {
			$valid_txt='';
		}
		
		//set tokens in form
		$form_output = str_replace("{cur-ver}",$ver_txt,$form_output);
		$form_output = str_replace("{valid-lic-msg}",$valid_txt,$form_output);
		$form_output = str_replace("{update-button}",$upd_button,$form_output);
		//$form_output = str_replace("{download-link}",$this->resp_data['dl-link'],$form_output);
		$form_output = str_replace("{download-url}",$this->resp_data['dl-url'],$form_output);

		return $form_output;	
	}
	
	/*
	 *	update_options
	 *  update the wp plugin options
	 *
	 * @subpackage tpg_get_posts
	 * @since 2.0
	 *
	 * update options
	 * 	
	 * @param    null
	 * @return   null
	 */
	function update_options($new_opts){
		//chk box will not return values for unchecked items
		if (!array_key_exists("show-ids",$new_opts)) {
			$new_opts['show-ids'] = false;
		} else {
			$new_opts['show-ids'] = true;
		}
		
		if (!array_key_exists("keep-opts",$new_opts)) {
			$new_opts['keep-opts'] = false;
		} else {
			$new_opts['keep-opts'] = true;
		}
		
		if (!array_key_exists("active-in-widgets",$new_opts)) {
			$new_opts['active-in-widgets'] = false;
		} else {
			$new_opts['active-in-widgets'] = true;
		}
		
		if (!array_key_exists("freeze",$new_opts)) {
			$new_opts['freeze'] = false;
		} else {
			$new_opts['freeze'] = true;
		}
		
		if (!array_key_exists("active-in-backend",$new_opts)) {
			$new_opts['active-in-backend'] = false;
		} else {
			$new_opts['active-in-backend'] = true;
		}
		
		//apply new values to gp_opts 
		foreach($new_opts as $key => $value) {
			$this->gp_opts[$key] = $value;
		}
		
		//update with new values
		update_option( 'tpg_gp_opts', $this->gp_opts);
		
		echo '<div id="message" class="updated fade"><p><strong>' . __('Settings saved.','tpg-get-posts') . '</strong></p></div>';
	}
	
	/*
	 *	tpg gp freeze
	 *  stop the update of the plugin to freeze it at a level
	 *
	 * @param    object
	 * @return   object
	 */
	function tpg_gp_freeze($value) {
		unset($value->response[ $this->gp_paths['base'] ]);
		return $value;
		}
	
	/*
	 *	validate lic
	 *  validate the lic options and update the options table
	 *
	 * @param    null
	 * @return   null
	 */
	function validate_lic(){
		global $gp;
		$_resp=$this->vl->validate_lic();
		if ($_resp->success) {
			$this->gp_opts['valid-lic']=$_resp->{'valid-lic'};
			//update with new values
			update_option( 'tpg_gp_opts', $this->gp_opts);
			//refresh options
			//$this->gp_opts=tpg_get_posts::get_options();
			$this->gp_opts=$gp->get_options();
			echo '<div id="message" class="updated fade"><p><strong>' . __('The license has been validated.','tpg-get-posts') . '</strong></p></div>';
		}
	}
	
	/*
	 *	update plugin ext
	 *  update the premium plugin
	 *
	 * @param    null
	 * @return   null
	 */
	function update_plugin_ext(){

		$_p['dest_path']=$this->gp_paths['dir'].'ext';
		$_p['tmp_path']=WP_CONTENT_DIR.'/upgrade/'.$this->module_data['module'].'.tmp/';
		$_p['dl_url']=$_POST['dl-url'];
		$_p['module-name']= $this->module_data['module'].'.zip';
		$_p['upg_ext_path']= $_p['tmp_path'].$this->module_data['module'].'/ext';

		$_resp=$this->vl->update_source($_p);
		if ($_resp->success) {
			echo '<div id="message" class="updated fade"><p><strong>' . __('The premium plugin has been updated.','tpg-get-posts') . '</strong></p></div>';
		} else {
			$_keys=array_keys($_resp->err_msgs) ;
			if (array_key_exists($_keys[0],$_resp->err_txt)) {
				$errtxt=$_resp->err_txt[$_keys[0]];
			} else {
				$errtxt='';
			}
			echo '<div id="message" class="updated fade"><p><strong>' . printf(__('The update failed with a %s error.  %s','tpg-get-posts'),$_keys[0],$errtxt) . '</strong></p></div>';
		}
	}					
	
	/*
	 * check_for_update
	 * check for an update of the plugin
	 *
	 * @subpackage tpg_get_posts
	 * @since 2.0
	 *
	 * update options
	 * 	
	 * @param    null
	 * @return   null
	 */
	function check_for_update(){
		$ext_updt_msg = false;
		if ($this->gp_opts['valid-lic']) {
			if (!array_key_exists('last-updt',$this->gp_opts) || 	
				($this->gp_opts['last-updt']+ $this->update_time) < time() ) {
					$_resp=$this->vl->get_version();         //get store version
					if ($_resp->success) {
						$this->v_store_norm = $this->normalize_ver($_resp->data['version']);
						$this->v_store = $_resp->data['version'];
					} else {
						$this->v_store = '0.0.0';
						$this->v_store_norm = $this->normalize_ver('0.0.0');
					}
					$this->v_plugin= $this->plugin_data['Version'];
					$this->v_plugin_norm= $this->normalize_ver($this->plugin_data['Version']);
					$this->v_plugin_ext=$this->plugin_ext_data['Version'];
					$this->v_plugin_ext_norm=$this->normalize_ver($this->plugin_ext_data['Version']);
					
					//update opt time in sec since last update
					$_lstupd = $this->update_time = time();
					$this->gp_opts['last-updt']=$_lstupd;
					update_option( 'tpg_gp_opts', $this->gp_opts);
					
					if (($_resp->success && $this->v_store_norm > $this->v_plugin_ext_norm) || (!file_exists($this->gp_paths['ext'].$this->ext_name)) ){
						$ext_updt_msg = true;
						$_resp=$this->vl->get_update_link();
						if ($_resp->success) {
							$this->resp_data['dl-url']=$_resp->{'dl-url'};
							$this->resp_data['dl-link']='<a href="'.$this->resp_data['dl-url'].'">"'.__("Download new version","tpg-get-posts").'</a>';
						} else {
							$this->resp_data['success']=false;
							foreach ($_resp->errors as $err) {
								$this->resp_data['errors'][]=$err;
							}
						}
					}
			}
		} 

		return $ext_updt_msg;
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
	
	/*
	 *	gp_admin_load_inc
	 *  enque css, js and other items for admin page
	 *
	 * @package WordPress
	 * @subpackage tpg_phplist
	 * @since 0.1
	 *
	 * enque the css, js and other items only when the admin page is called.
	 * 	
	 * @param    null
	 * @return   null
	 */
	function gp_admin_load_inc(){
		//enque css style 

		$tgp_css = "tpg-get-posts-admin.css";
		//check if file exists with path
		if (file_exists($this->gp_paths['css'].$tgp_css)) {
			wp_enqueue_style('tpg_get_posts_admin_css',$this->gp_paths['css_url'].$tgp_css);
		}
		if (file_exists($this->gp_paths['css']."user-get-posts-style.css")) {
			wp_enqueue_style('user_get_posts_css',$this->gp_paths['css_url']."user-get-posts-style.css");
		}
		
		//get jquery tabs code
		wp_enqueue_script('jquery-ui-tabs');
		
		//load admin js code
		if (file_exists($this->gp_paths['js']."tpg-get-posts-admin.js")) {
			wp_enqueue_script('tpg_get-posts_admin_js',$this->gp_paths['js_url']."tpg-get-posts-admin.js");
		}
		
		//generate pp donate button
		$ppb = tpg_gp_factory::create_paypal_button();
		$ask="<p>".__('If this plugin helps you build a website, please consider a small donation of $5 or $10 to continue the support of open source software.  Taking one hour&lsquo;s fee and spreading it across multiple plugins is an investment that generates amazing returns.','tpg-get-posts')."</p><p>".__('Thank you for supporting open source software.','tpg-get-posts')."</p>";
		$ppb->set_var("for_text","wordpress plugin tpg-get-posts");
		$ppb->set_var("desc",$ask);
		$this->pp_btn = $ppb->gen_donate_button();
	}
	
	/*
	 *	build form for options
	 *  
	 * @package WordPress
	 * @subpackage tpg_get_posts
	 * @since 2.0
	 *
	 * @param    null
	 * @return   null
	 */
	function build_form() {
		//array to hold changes
		$gp_opts = array();
		
		//test the check boxes to see if the value should be checked
		$ck_show_ids = ($this->gp_opts['show-ids'])? 'checked=checked' : '';
		$ck_keep_opts = ($this->gp_opts['keep-opts'])? 'checked=checked' : '';
		$ck_widgets_opts = ($this->gp_opts['active-in-widgets'])? 'checked=checked' : '';
		$ck_freeze = ($this->gp_opts['freeze'])? 'checked=checked' : '';
		$ck_backend = ($this->gp_opts['active-in-backend'])? 'checked=checked' : '';
		$btn_updt_opts_txt = __('Update Options', 'tpg-get-posts' ) ;
		$btn_val_lic_txt = __('Validate Lic', 'tpg-get-posts' ) ;

		//hack for translation
		$__ = '__';
		//create output form
		$output = <<<EOT
		<div class="wrap">		
	<div class="postbox-container" style="width:100%; margin-right:5%; " >
		<div class="metabox-holder">
			<div id="jq_effects" class="postbox">
				<div class="handlediv" title="Click to toggle"><br /></div>

				<h3><a class="togbox">+</a> {$__('TPG Get Posts Options','tpg-get-posts')} </h3>
				
				<div class="inside"  style="padding:10px;">
					<form name="getposts_options" method="post" action="{action-link}">
					
						<h4>{$__('Base Options','tpg-get-posts')} </h4>
						<table class="form-table">	
							<tr>		
							<td>{$__('Freeze Updates:','tpg-get-posts')}  </td><td><input type="checkbox" name="gp_opts[freeze]" id="id_freeze" value="true" $ck_freeze /></td><td>{$__('This option prevents the update notice from being displayed.  Use this if you wish to stop any future updates to the plugin.','tpg-get-posts')}</td>				
							</tr>
						</table>
							<hr width=80% />
						<h4>{$__('Premium Options - Current version','tpg-get-posts')} {cur-ver}</h4>
						<table class="form-table">	
							<tr>		
							<td>{$__('License Key:','tpg-get-posts')} </td><td><input type="text" name="gp_opts[lic-key]" value="{$this->gp_opts['lic-key']}" size="50"> </td><td>{$__('(the license key from email received after purchase of premium plugin)','tpg-get-posts')}</td>
							</tr>
							<tr>
							<td>{$__('License email:','tpg-get-posts')} </td><td><input type="text" name="gp_opts[lic-email]" value="{$this->gp_opts['lic-email']}" size="50"> </td><td>{$__('(the email used when purchasing the license)','tpg-get-posts')}</td>
							</tr>
							<tr><td></td><td><span style="color:maroon;">{valid-lic-msg}</span></td>
							</tr>
							<tr>
							<td>{$__('Keep Options on uninstall:','tpg-get-posts')}  </td><td><input type="checkbox" name="gp_opts[keep-opts]" id="id_keep_opts" value="false" $ck_keep_opts /></td><td>{$__('If checked, options will not be deleted on uninstall.  Useful when upgrading.  Uncheck to completely remove premium version.','tpg-get-posts')}</td>				
							</tr>
							<tr>
							<td>{$__('Check for Update Freq:','tpg-get-posts')}  </td><td><input type="text" name="gp_opts[updt-sec]" id="id_updt_sec" value="{$this->gp_opts['updt-sec']}" /></td><td>{$__('Set the number of seconds between checking for updates of the extension.  To check immediately, set value to zero, save options and refresh the page.','tpg-get-posts')}</td>				
							</tr>
							<tr>
							<td>{$__('Show Ids:','tpg-get-posts')}  </td><td><input type="checkbox" name="gp_opts[show-ids]" id="id_show_id" value="true" $ck_show_ids /></td><td>{$__('This option applies modifications to the show cat (and other admin pages) to show the id of the entires.  This number is needed for the some of the premium selection options and for the category selector.','tpg-get-posts')} </td>				
							</tr>
							<tr>
							<td>{$__('Activate in Widgets:','tpg-get-posts')}  </td><td><input type="checkbox" name="gp_opts[active-in-widgets]" id="id_widgets" value="true" $ck_widgets_opts /></td><td>{$__('If you want this plugin active in text widgets, check this box to activate the shortcodes for widgets.','tpg-get-posts')}</td>				
							</tr>
							<tr>
							<td>{$__('Activate in Backend:','tpg-get-posts')}  </td><td><input type="checkbox" name="gp_opts[active-in-backend]" id="id_backend" value="true" $ck_backend /></td><td>{$__('If you want this plugin active in the administrative (backend) section, check this box.  This adds extra processing to admin side, but is required from some plugins to work correctly, such as WPMU_eNewsletter.','tpg-get-posts')}</td>				
							</tr>
						</table>
					
							<!--//values are used in switch to determine processing-->
							<p class="submit">
							<button type="submit" class="button-primary tpg-settings-btn" name="func" value="updt_opts" />$btn_updt_opts_txt</button>
							&nbsp;&nbsp;
							<button type="submit" class="button-primary tpg-settings-btn" name="func" value="val_lic" />$btn_val_lic_txt</button>
							&nbsp;&nbsp;
							{update-button}
							</p>
								
						
					</form>
				</div>
			</div>
		</div>
	</div>
EOT;

		return $output;
	}	

}
?>
