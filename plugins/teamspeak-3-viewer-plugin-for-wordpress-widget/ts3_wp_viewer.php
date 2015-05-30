<?php
/*
Plugin Name: Teamspeak 3 Widget for Wordpress
Plugin URI: http://michaelplas.de
Description: Allows to show the Users and Channels of a Teamspeak3 as a Widget ( TS VIEWER )
Author: Michael Plas
Version: 1.0.3
Author URI: http://www.michaelplas.de
License: GPL 2.0, @see http://www.gnu.org/licenses/gpl-2.0.html
*/

class ts3_wp_viewer{

    function init() {
    	// check for the required WP functions, die silently for pre-2.2 WP.
    	if (!function_exists('wp_register_sidebar_widget'))
    		return;

    	// load all l10n string upon entry
        load_plugin_textdomain('ts3_wp_viewer');

        // let WP know of this plugin's widget view entry
    	wp_register_sidebar_widget('ts3_wp_viewer', __('TS3 Viewer Widget', 'ts3_wp_viewer'), array('ts3_wp_viewer', 'widget'),
            array(
            	'classname' => 'ts3_wp_viewer',
            	'description' => __('Allows to show the Users and Channels of a Teamspeak3 as a Widget ( TS VIEWER )', 'ts3_wp_viewer')
            )
        );

        // let WP know of this widget's controller entry
    	wp_register_widget_control('ts3_wp_viewer', __('Teamspeak3 Viewer', 'ts3_wp_viewer'), array('ts3_wp_viewer', 'control'),
    	    array('width' => 300)
        );

        // short code allows insertion of ts3_wp_viewer into regular posts as a [ts3_wp_viewer] tag.
        // From PHP in themes, call do_shortcode('ts3_wp_viewer');
        add_shortcode('ts3_wp_viewer', array('ts3_wp_viewer', 'shortcode'));
    }

	// back end options dialogue
	function control() {
	    $options = get_option('ts3_wp_viewer');
		if (!is_array($options))
			$options = array('serverip'=>'127.0.0.1','queryport'=>'10011', 'virtualserverport'=>'9987', 'name'=>'Teamspeak 3 Viewer');
		if ($_POST['ts3_wp_viewer-submit']) {
			$options['serverip'] = strip_tags(stripslashes($_POST['ts3_wp_viewer-serverip']));
			$options['virtualserverport'] = strip_tags(stripslashes($_POST['ts3_wp_viewer-virtualserverport']));
			$options['queryport'] = strip_tags(stripslashes($_POST['ts3_wp_viewer-queryport']));
			$options['name'] = strip_tags(stripslashes($_POST['ts3_wp_viewer-name']));
			$options['displaynamesonly'] = strip_tags(stripslashes($_POST['ts3_wp_viewer-displaynamesonly']));
			update_option('ts3_wp_viewer', $options);
		}
		$serverip = htmlspecialchars($options['serverip'], ENT_QUOTES);
			$queryport = htmlspecialchars($options['queryport'], ENT_QUOTES);
		$virtualserverport = htmlspecialchars($options['virtualserverport'], ENT_QUOTES);
		$name = htmlspecialchars($options['name'], ENT_QUOTES);
		$displaynamesonly=$options['displaynamesonly'];
		if($displaynamesonly=="true"){
		$selected="selected";
		}
		echo '<p style="text-align:right;"><label for="ts3_wp_viewer-name">Titel <input style="width: 200px;" id="ts3_wp_viewer-name" name="ts3_wp_viewer-name" type="text" value="'.$name.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="ts3_wp_viewer-serverip">Server IP or DNS<input style="width: 200px;" id="ts3_wp_viewer-serverip" name="ts3_wp_viewer-serverip" type="text" value="'.$serverip.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="ts3_wp_viewer-queryport">Queryport<input style="width: 200px;" id="ts3_wp_viewer-queryport" name="ts3_wp_viewer-queryport" type="text" value="'.$queryport.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="ts3_wp_viewer-virtualserverport">Virtual Serverport<input style="width: 200px;" id="ts3_wp_viewer-virtualserverport" name="ts3_wp_viewer-virtualserverport" type="text" value="'.$virtualserverport.'" /></label></p>';
		echo '<p style="text-align:right;"><label for="ts3_wp_viewer-displaynamesonly">Display Option<select name="ts3_wp_viewer-displaynamesonly" size="1"><option value="false">Show Channellist</option><option '.$selected.' value="true">Show Names Only</option></select></label></p>';
		echo '<p style="text-align:right;"><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=9328389">Donate for this Plugin</a> </p>';
		echo '<p style="text-align:right;"><a href="http://www.michaelplas.de">Visit the Author</a> </p>';	
		echo '<input type="hidden" id="ts3_wp_viewer-submit" name="ts3_wp_viewer-submit" value="1" />';
	}

    function view($is_widget, $args=array()) {
    	if($is_widget) extract($args);

    	// get widget options
    	$options = get_option('ts3_wp_viewer');
    	$serverip = $options['serverip'];
		$queryport = $options['queryport'];
    	$virtualserverport = $options['virtualserverport'];
		$displaynamesonly=$options['displaynamesonly'];
		$name = $options['name'];
		$path =  plugins_url( 'teamspeak-3-viewer-plugin-for-wordpress-widget/images/viewer/' );
		
		// Load the Lib
		
		require_once("libraries/TeamSpeak3/TeamSpeak3.php");
    
		


try
{
  /* connect to server and get TeamSpeak3_Node_Server object by URI */
  if($queryport == ""){
  $ts3_VirtualServer = TeamSpeak3::factory("serverquery://" . $serverip."/?server_port=". $virtualserverport."#no_query_clients");
}else{
 $ts3_VirtualServer = TeamSpeak3::factory("serverquery://" . $serverip.":" . $queryport."/?server_port=". $virtualserverport."#no_query_clients");
}
  if($displaynamesonly=="true"){
   $clients = $ts3_VirtualServer->clientList();  
  foreach ($clients as $clientObject) {
            $clientInfo = $clientObject->getInfo();
            
                $mystatus .= $clientInfo['client_nickname'] . "\<br>";
            
        }  
  }else{
  /* display virtual server viewer using HTML interface */
  $mystatus .= $ts3_VirtualServer->getViewer(new TeamSpeak3_Viewer_Html($path));
  }
  /* display runtime from default profiler */
 // echo "<br />Generated in " . TeamSpeak3_Helper_Profiler::get()->getRuntime() . " seconds";
 
 
 
 
 
}
catch(Exception $e)
{
  $mystatus = "Error (ID " . $e->getCode() . ") <b>" . $e->getMessage() . "</b>";
}


	// the widget's form
	$out[] 	='<div id="ts3_div">';
             
		$out[] = $before_widget . $before_title . $name . $after_title;
		
		$out[] = $mystatus;
		$out[] 	='</div>';
    	$out[] = $after_widget;
    	return join($out, "\n");
    }

    function shortcode($atts, $content=null) {
        return ts3_wp_viewer::view(false);
    }

    function widget($atts) {
        echo ts3_wp_viewer::view(true, $atts);
    }
}

add_action('widgets_init', array('ts3_wp_viewer', 'init'));

?>