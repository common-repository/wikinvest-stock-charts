/**
 * Wikinvest Stock Charts Plugin for TinyMCE3
 * @author Wikinvest
 * @copyright Copyright © 2004-2007, Wikinvest
 */
 
(function() {
	
	tinymce.create('tinymce.plugins.wikinvestStockCharts', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {
			if(typeof wikinvestStockCharts_buttonUrl != "undefined") {
				ed.addButton('wikinvestStockCharts', {
					title : 'Display in-line stock charts on your post',
					image : wikinvestStockCharts_buttonUrl,
					onclick : function() {
						if(typeof(wikichart_plugin) != "undefined") {
							window.wikichart_plugin.ShowDialog();
						}
						else {
							alert("Unable to load the Wikinvest Stock Charts Plugin. Please refresh the page and try again");	
						}
					}
					
					// Add a node change handler, selects the button in the UI when a image is selected
				});				
			}
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : "Wikinvest Stock Charts",
				author : 'Wikinvest',
				authorurl : 'http://www.wikinvest.com/',
				infourl : 'http://www.wikinvest.com/blogger/wikinvest_stockcharts',
				version : "0.1"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('wikinvestStockCharts', tinymce.plugins.wikinvestStockCharts);
})();