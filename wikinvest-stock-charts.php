<?php
	/*
	Plugin Name: Wikinvest Stock Charts
	Plugin URI: http://www.wikinvest.com/special/BloggerCentral
	Description: Display stock charts on your blog
	Author: Wikinvest
	Version: 1.01
	Author URI: http://www.wikinvest.com/special/BloggerCentral
	*/
	
	if( !class_exists( "WikinvestStockCharts" ) ) {
		class WikinvestStockCharts {
			
			//Start-- Class Variables
			
				//URL to send API Requests to
				var $wikinvestServer = "http://plugins.wikinvest.com";
				var $pluginApiUrl = '';
				var $backendJavascriptUrl = '';
				var $frontendJavascriptUrl = '';
				
				//Current version
				var $pluginVersion = '1.00';
				var $platform = 'wp';
				
				//change to bust old prefs
				var $userPrefVersion = "v1.8";
				
				//Change this to bust tinymce config file cache
				var $lastModifiedDate = "20090321";
				
				var $staticEmbedCode = '<script src="http://charts.wikinvest.com/wikinvest/wikichart/javascript/scripts.php?plugin=stockcharts&platform=##PLATFORM##" type="text/javascript"></script><object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="##WIDTH##" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" height="##HEIGHT##"><param name="movie" value="http://charts.wikinvest.com/WikiChartMini.swf" /><param name="wmode" value="opaque" /><param name="allowScriptAccess" value="always" /><param name="quality" value="high" /><param name="flashvars" value="##FLASHVARS##" /><!--[if !IE]>--><object style="outline:none" type="application/x-shockwave-flash" width="##WIDTH##" height="##HEIGHT##" data="http://charts.wikinvest.com/WikiChartMini.swf"><param name="wmode" value="opaque" /><param name="allowScriptAccess" value="always" /><param name="quality" value="high" /><param name="flashvars" value="##FLASHVARS##" /><!--<![endif]--><a target="_blank" href="http://get.adobe.com/flashplayer/"><img src="http://cdn.wikinvest.com/wikinvest/images/adobe_flash_logo.gif" alt="Flash" style="border-width: 0px;"/><br/>Flash Player 9 or higher is required to view the chart<br/><strong>Click here to download Flash Player now</strong></a><!--[if !IE]>--></object><!--<![endif]--></object><div style="font-size:9px;text-align:right;width:##WIDTH##;font-family:Verdana"><a href="http://www.wikinvest.com/chart/##TICKER##" style="text-decoration:underline; color:#0000ee;">View the full ##TICKER_ALIAS## chart</a> at <a href="http://www.wikinvest.com/">Wikinvest</a></div>';
				
				var $dynamicEmbedCode = '<object width="##WIDTH##" height="##HEIGHT##"><param name="movie" value="http://charts.wikinvest.com/WikiChartMini.swf" /><param name="wmode" value="opaque" /><param name="flashvars" value="##FLASHVARS##" /><embed src="http://charts.wikinvest.com/WikiChartMini.swf" type="application/x-shockwave-flash" wmode="opaque" flashvars="##FLASHVARS##" width="##WIDTH##" height="##HEIGHT##"></object><div style="font-size:9px;text-align:right;width:##WIDTH##;font-family:Verdana"><a href="http://www.wikinvest.com/chart/##TICKER##" style="text-decoration:underline; color:#0000ee;">View the full ##TICKER_ALIAS## chart</a> at <a href="http://www.wikinvest.com/">Wikinvest</a></div>';
				
				var $flashvars = array(
							"ticker"=>"ticker",
							"showannotations"=>"showAnnotations",
							"livequote"=>"liveQuote",
							"startdate"=>"startDate",
							"enddate"=>"endDate",
							"rollingdate"=>"rollingDate"
							);
			
			//End-- Class Variables
			
			//Start-- Constructor
			
				function WikinvestStockCharts() {
					$this->pluginApiUrl = $this->wikinvestServer . "/plugin/api.php";
					$this->backendJavascriptUrl = $this->wikinvestServer . "/plugin/javascript/stockCharts/backend/wordpress/";
					$this->frontendJavascriptUrl = $this->wikinvestServer . "/plugin/javascript/stockCharts/frontend/wordpress/";
				}
			
			//End-- Constructor
			
			
			//Start-- Functions that are called by WP actions/filter: windows to the different points in the app
				
				/**
				 * This function shows notices on admin pages
				 * @return none
				 */
				function processWordpressInitialization() {
					$this->addButtons();
					$this->updateEmbedCode();
				}
				
				function updateEmbedCode() {
					$p = $this->getUserPreferences();
					
					//update embed code once a day
					if(!$p['lastUpdated'] || $p['lastUpdated'] < time() - 24 * 3600) {
						$url = $this->pluginApiUrl;
						$url .= "?name=wikichart&action=getchartembedcode&format=serialized";
						$content = $this->getRemoteFile($url);
						if($content) {
							$resp = @unserialize($content);
							if($resp) {
								$p['staticEmbedCode'] = $resp['staticEmbedCode'];
								$p['dynamicEmbedCode'] = $resp['dynamicEmbedCode'];
								$p['flashvars'] = $resp['flashvars'];
								$p['lastUpdated'] = time();
								$this->setUserPreferences($p);
							}
						}
					}
				}
				
				function getEmbedCode() {
					$p = $this->getUserPreferences();
					if($p['staticEmbedCode']) {
						return $p['staticEmbedCode'];
					}
					else {
						return $this->staticEmbedCode;
					}
				}
				
				function getDynamicEmbedCode() {
					$p = $this->getUserPreferences();
					if($p['dynamicEmbedCode']) {
						return $p['dynamicEmbedCode'];
					}
					else {
						return $this->dynamicEmbedCode;
					}
				}
				
				function getFlashVars() {
					$p = $this->getUserPreferences();
					if($p['flashvars']) {
						return $p['flashvars'];
					}
					else {
						return $this->flashvars;
					}
				}
			
				/**
				 * This function adds elements -- like script and css files -- to the <head> section of the HTML documents
				 * @return none
				 */
				function addHeadInformation() {
					$javascriptUrl = $this->frontendJavascriptUrl;
					print("
						<script type='text/javascript'>
						wikinvestStockCharts_callbackUrl = '".$this->getPluginFolderUrl()."/wikinvest-stock-charts.php';
						wikinvestStockCharts_blogUrl = '".$this->getSiteUrl()."';
						wikinvestStockCharts_wpVersion = '".$this->getPlatformVersion()."';
					</script>
					<script type='text/javascript' src='{$javascriptUrl}'></script>
					");
				}
				
				/**
				 * This function adds elements -- like script and css files -- to the <head> section of the admin HTML documents
				 * @return none
				 */
				function addAdminHeadInformation() {
					global $pagenow;	//which page are we in
					
					$loadInPages = array(
							"post.php",
							"post-new.php",
							"page.php",
							"page-new.php"
							);

					if(in_array($pagenow,$loadInPages)) {
						print "<script type='text/javascript'>
							wikinvestStockCharts_callbackUrl = '".$this->getPluginFolderUrl()."/wikinvest-stock-charts.php';
							wikinvestStockCharts_blogUrl = '".$this->getSiteUrl()."';
							wikinvestStockCharts_wpVersion = '".$this->getPlatformVersion()."';
							wikinvestStockQuotes_blogUrl = '".$this->getSiteUrl()."';
							wikinvestStockCharts_buttonUrl = '". $this->wikinvestServer .
											"/plugin/images/wikinvest_btn_wp_chart.gif';
							wikinvestStockCharts_embedCode = '".$this->makeJsSafe($this->getDynamicEmbedCode())."';
							wikinvestStockCharts_flashvars = '".$this->arrayToQueryString($this->getFlashVars())."';
						</script>";
							print "<script src='{$this->backendJavascriptUrl}' type='text/javascript'></script>";
					}
				}
				
				function arrayToQueryString($arr) {
					$result = "";
					foreach($arr as $k=>$v) {
						if($result!="") {
							$result.= "&";
						}
						$result .= urlencode($k)."=".urlencode($v);
					}
					return $result;
				}
				
				function makeJsSafe($text) {
					return preg_replace(array("/\r|\n/","/'/")
							,array("","\\'"),$text);
				}
							
				/**
				 * This function does things that need to be done on the plugin page
				 * @return none
				 */
				function processPluginRow( $pluginFile ) {
					//Handle only the row for my plugin
					if( $pluginFile != 'wikinvest-stock-charts/wikinvest-stock-charts.php' ) {
						return;
					}
					
					if($this->getPlatformVersion() < 2.5) {
						print( "
						<tr><td colspan='5' class='plugin-update'>
							<span style='color:red'>NOTE: </span> This plugin is only designed for WordPress v2.5 and up
						</td></tr>
					" );
					}
				}					
				
			//End-- Functions to add elements -- like script and css files -- to the <head> section of the HTML documents
			
			//Start -- Functions for storing preferences
			function getDefaultUserPreferences(){
				$userPreferences = array();
				$userPreferences['staticEmbedCode'] = $this->staticEmbedCode;
				$userPreferences['dynamicEmbedCode'] = $this->dynamicEmbedCode;
				$userPreferences['flashvars'] = $this->flashvars;
				$userPreferences['lastUpdated'] = "";
				
				return $userPreferences;
			}
			
			function getUserPreferences(){
				$userPreferences = get_option("wikinvestStockChartsUserPreferences_".$this->userPrefVersion);
				
				$defaultUserPreferences = $this->getDefaultUserPreferences();
				if( empty( $userPreferences ) ) { $userPreferences = $defaultUserPreferences; }
							
				$this->setUserPreferences( $userPreferences );
				
				return $userPreferences;
			}
			
			function setUserPreferences($userPreferences){
				update_option("wikinvestStockChartsUserPreferences_".$this->userPrefVersion, $userPreferences);
				return;
			}
				
			
			//Start -- Functions to add button in editor toolbar
			// Make our buttons on the write screens
			function addButtons() {
				// Don't bother doing this stuff if the current user lacks permissions as they'll never see the pages
				if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) return;

				// Create the buttons based on the WP version number
				if ( 'true' == get_user_option('rich_editing') && $this->getPlatformVersion() >= 2.1 ) {
					// WordPress 2.5+ (TinyMCE 3.x)
					if ( $this->getPlatformVersion() >= 2.5 ) {
						add_filter( 'mce_external_plugins', array(&$this, 'mce_external_plugins') );
						add_filter( 'mce_buttons', array(&$this, 'mce_buttons') );
						add_filter( 'tiny_mce_before_init', array(&$this, 'tinymce_3_before_init'));
						add_filter( 'tiny_mce_version', array($this, 'tinymce_3_version'));
					}

					// WordPress 2.1+ (TinyMCE 2.x)
					else {
						//not supported - do nothing
					}
				} 
			}
			
			// TinyMCE integration hooks
			/* Give the last changed date to tinymce - used for busting cache of tinymce config */
			function tinymce_3_version( $ver ) {
				$lastModifiedDate = $this->lastModifiedDate;
				if($ver > $lastModifiedDate) {
					$lastModifiedDate = $ver;
				}
				return $lastModifiedDate;
			}
			
			/* Register TinyMCE plugin */
			function mce_external_plugins( $plugins ) {
				// WordPress 2.5
				$plugins['wikinvestStockCharts'] = $this->getPluginFolderUrl() . '/tinymce3.js';
				return $plugins;
			}
					
			/* Specify the buttons to add to tinyMCE */
			function mce_buttons($buttons) {
				array_push( $buttons, 'separator', 'wikinvestStockCharts' );	
				return $buttons;
			}
			
			/* Configure TinyMCE to allow all attributes for <span> and <a> tags */
			function tinymce_3_before_init($init) {
				// Command separated string of extended elements
				$ext = 'wikichart[*]';

				// Add to extended_valid_elements if it alreay exists
				if ( isset( $init['extended_valid_elements'] ) ) {
					$init['extended_valid_elements'] .= ',' . $ext;
				} else {
					$init['extended_valid_elements'] = $ext;
				}

				// Super important: return $init!
				return $init;
			}
			
			/* Add Embed Code */
			function processPostContent($content = '') {
				$matches = array();
				$j=0;
				if(preg_match_all("/\\[wikichart ([^\\]]*)\\]/msi",$content,$matches,PREG_SET_ORDER)) {
					foreach($matches as $match) {
						$ticker = "";
						$j++;
						$outer = $match[0];
						$inner = $match[1];
						if($inner) {
							$inner .= ' random="wikichart_'.time().$j.rand(100,999)."\"";
							$inner .= ' platform="wordpress"';
						}
						$attrs = preg_split("/(\"|')\s+/", $inner);
						$embedCode = '<div width="##WIDTH##" height="##HEIGHT##" class="wikichart-align##ALIGN##">'.$this->getEmbedCode().'</div>';
						$defFlashVars = $this->getFlashVars();
						$flashvars = array();
						foreach($attrs as $attr) {
							$attr = preg_replace("/\s/","", $attr);
							$i = strpos($attr,"=");
							if($i==false) continue;
							$key = trim(substr($attr,0,$i));
							$keyl = strtolower($key);
							$value = trim(substr($attr,$i+1));
							if($key && $value) {
								$key = urldecode($key);
								$value = urldecode($value);
								$value = str_replace("\"","",$value);
								if(trim(strtolower($key)) == "ticker") {
									$ticker = strtoupper($value);
									$parts = explode(":",$ticker);
									if(count($parts) == 2) {
										$ticker = $parts[1];
									}
								}
								if(isset($defFlashVars[$keyl])) {
									$flashvars[]= urlencode($defFlashVars[$keyl]) ."=".
											urlencode($value);
								}
								$embedCode = preg_replace("/".
												preg_quote("##".
													$key."##","/")."/msi", $value, $embedCode);
							}
						}
						
						$flashvars = implode("&",$flashvars);
						$embedCode = preg_replace("/##FLASHVARS##/msi",	$flashvars, $embedCode);
						$embedCode = preg_replace("/##TICKER_ALIAS##/msi", $ticker, $embedCode);
						$content = str_replace($outer,$embedCode,$content);
					}
				}
				return $content;
			}
					
			//End -- Functions to add button in editor toolbar
			
			function getPlatform() {
				return $this->platform;
			}
			
			function getPlatformVersion() {
				global $wp_version;
				return $wp_version;
			}
			
			
			//Start-- Utility functions
			
			function getRemoteFile( $url ) {
			  $errorReporting = error_reporting();
			  error_reporting(0);
			  
			  require_once(ABSPATH.WPINC.'/class-snoopy.php');
			  $content = false;
			  $sn = new Snoopy();
			  $sn->read_timeout = 2;
			  if( $sn->fetch( $url ) ) {
				  $content = $sn->results;
			  }
			  
			  error_reporting($errorReporting);
			  
				if ( $content === false ) { return false; }
				
				return $content;
			}
			
			function postToRemoteFile( $url, $data ) {
				$errorReporting = error_reporting();
				error_reporting(0);

				require_once(ABSPATH.WPINC.'/class-snoopy.php');
				
				$content = false;
				$sn = new Snoopy();
				$sn->read_timeout = 30;
				if( $sn->submit( $url, $data ) ) {
					$content = $sn->results;
				}
				error_reporting($errorReporting);

				if ( $content === false ) { 
					return false; 
				}

				return $content;
			}
			
			function getSiteUrl(){
				$siteUrl = get_bloginfo("wpurl");
				return $siteUrl;
			}
			
			function getPluginFolderUrl() {
				return get_bloginfo('wpurl') . '/wp-content/plugins/wikinvest-stock-charts';
			}
			
			function sanitizeServiceHtmlReponse( $htmlResponse ) {
				//TODO: Use reg-ex
				$startDelimiter = "<!--Wikinvest API HTML Response-->";
				$endDelimiter = "<!--/Wikinvest API HTML Response-->";
				
				$startDelimiterStartPos = strpos( $htmlResponse, $startDelimiter );
				$endDelimiterStartPos = strpos( $htmlResponse, $endDelimiter );
				
			  if( $startDelimiterStartPos === false	|| $endDelimiterStartPos === false ) {
				 return "";
			  }
			  
			  // Chunk of $htmlResponse after (and including) the first occourence of $startDelimiter
			  $startStripped = substr( $htmlResponse, $startDelimiterStartPos );
			  
			  // Explode the result by $endDelimiter
			  $endDelimiterChunks = explode( $endDelimiter, $startStripped );
				$endStripped = "";
				
			  // Piece it back together except the last item (we can also use implode but then we have to unset the last item)
				for($counter = 0; $counter < count($endDelimiterChunks) - 1; $counter++) {
					$endDelimiterChunk = $endDelimiterChunks[$counter];
					$endStripped .= $endDelimiterChunk;
					$endStripped .= $endDelimiter;
				}
				
				return trim( $endStripped );
		  }
		
			/**
			 * Returns a copy of $data with special character escaping removed. $data can be an array
			 */
			function decodeHtmlInput( $data ) {
				if( is_array( $data ) ) {
					$retdata = array();
					foreach( $data as $index => $val ) {
						$retdata[$index] = $this->decodeHtmlInput( $data[$index] );
					}
					return $retdata;
				}
				
				$retdata = stripslashes( $data );
				return $retdata;
			}
		
			/**
			 * Returns a copy of $data with special characters encoded. $data can be an array
			 */
			function encodeHtmlOutput( $data ) {
				if( is_array( $data ) ) {
					$retdata = array();
					foreach( $data as $index => $val ) {
						$retdata[$index] = $this->encodeHtmlOutput( $data[$index] );
					}
					return $retdata;
				}
				
				$retdata = htmlentities( $data, ENT_QUOTES );
				return $retdata;
			}
		
			/**
			 * Modifies $data so that it can be passed in a URL. $data can be an array
			 */
			function encodeUrlData( $data ) {
				if( is_array( $data ) ) {
					$retdata = array();
					foreach( $data as $index => $val ) {
						$retdata[$index] = $this->encodeUrlData( $data[$index] );
					}
					return $retdata;
				}
				
				$retdata = urlencode( $data );
				return $retdata;
			}			
			//End-- Utility functions
			
			//AJAX passthru function
			//for communicating with Plugin API
			//Send all GET/POST params to the API
			function ajaxPassThru() {
				if($_SERVER['REQUEST_METHOD'] == "GET") {
					//send all the get parameters
					$url = $this->pluginApiUrl;
					$getParams = array();
					foreach($_GET as $k=>$v) {
						$getParams[] = urlencode($k)."=".urlencode($v);
					}
					if(count($getParams) > 0) {
						$url .= "?" . implode("&", $getParams);
					}
					echo $this->getRemoteFile($url);
				}
				else {
					echo $this->postToRemoteFile($this->pluginApiUrl,array_merge($_GET,$_POST));
				}
			}
		}
	}
	
	
	//Start-- WP actions/filters
	if ( class_exists( "WikinvestStockCharts" ) ) {
		$wikinvestStockCharts = new WikinvestStockCharts();
		
		if(defined("ABSPATH") && defined("WPINC")) {
			if(!isset($wp_version) || $wp_version >= 2.5) {	//only support 2.5+
				//Initialization
				add_action( 'init', array(&$wikinvestStockCharts, "processWordpressInitialization") );
				
				//Adding the filter for adding content to the post content (using default priority)
				add_filter( "the_content", array( &$wikinvestStockCharts, "processPostContent" ) );
				
				//Adding scripts and stylesheets to the <head> (using default priority)
				add_action( "wp_footer", array( &$wikinvestStockCharts, "addHeadInformation" ) );
				
				//Adding scripts to the <head> of admin pages for edit pages
				add_action( "admin_head", array( &$wikinvestStockCharts, "addAdminHeadInformation" ) );
			}
			//Adding the plugin information row
			add_action( 'after_plugin_row', array(&$wikinvestStockCharts, "processPluginRow") );
		}
		else {
			//This file has been directly hit
			//Pass-thru data to the Wikinvest API
			define("ABSPATH",realpath("../../.."));
			define("WPINC","/wp-includes");
			$wikinvestStockCharts->ajaxPassThru();
		}
	}
	//End-- WP actions/filters

?>
