<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Snippet generator for direct usage or used in conjunction with the JS class.
 *
 * @package    Asset
 * @category   Base
 * @author     Gregorio Ramirez <goyocode@gmail.com>
 * @copyright  (c) 2011 Gregorio Ramirez
 * @license    MIT
 * @see        http://kohanaftw.com/modules/asset/
 */
class JS_Snippet_Core {
	
	/**
	 * Generate a Google Analytics script.
	 *
	 *     // Embed ga in the page
	 *         ...
	 *         <?php echo JS_Snippet::ga('UA-XXXXXXXX-X') ?>
	 *     </body>
	 *
	 * @param   string  account id (e.g. UA-XXXXXXXX-X)
	 * @param   bool    wrap the code in HTML tags?
	 * @return  string|bool
	 */
	public static function ga($id, $wrap = TRUE)
	{
		if (is_string($id) === FALSE)
			return FALSE;
		
		// Get the snippet
		$snippet = Arr::get(JS_Snippet::$code, __FUNCTION__);
		
		if ($snippet === NULL)
		{
			// No code found
			return FALSE;
		}
		
		// Embed the values in the snippet
		$snippet = sprintf($snippet, $id);

		if ($wrap === TRUE)
		{
			// Wrap the snippet in HTML tags
			$snippet = JS::wrap($snippet);
		}
		
		return $snippet;
	}
	
	/**
	 * Generate a Google Website Optimizer script.
	 *
	 *     // Render the tracking script
	 *     <head>
	 *         <?php echo JS_Snippet::gwo(JS::TRACKING, 'UA-XXXXXXXX-X', 0000000000) ?>
	 *
	 * @param   string  type of gwo script
	 * @param   string  account id (e.g. UA-XXXXXXXX-X)
	 * @param   string  campaign id (e.g. 0000000000)
	 * @param   bool    wrap the code in HTML tags?	
	 * @return  string|bool
	 */
	public static function gwo($type, $ga, $campaign, $wrap = TRUE)
	{	
		// Get the snippet
		$snippet = Arr::get(JS_Snippet::$code, $type);
		
		if ($snippet === NULL)
		{
			// No code found
			return FALSE;
		}
		
		// Embed the values in the snippet
		$snippet = strtr($snippet, array(
			':ga' => $ga, 
			':campaign' => $campaign
		));
		
		if ($wrap === TRUE)
		{
			// Wrap the snippet in HTML tags
			$snippet = JS::wrap($snippet);
		}
		
		return $snippet;
	}
	
	/**
	 * @var  array  code snippets (compressed with the Google Closure Compiler)
	 */
	protected static $code = array(
		'ga'         => 'var _gaq=_gaq||[];_gaq.push(["_setAccount","%s"]);_gaq.push(["_trackPageview"]);(function(){var a=document.createElement("script");a.type="text/javascript";a.async=true;a.src=("https:"==document.location.protocol?"https://ssl":"http://www")+".google-analytics.com/ga.js";var b=document.getElementsByTagName("script")[0];b.parentNode.insertBefore(a,b)})();',
		'control'    => 'function utmx_section(){}function utmx(){}(function(){function a(e){if(c){var d=c.indexOf(e+"=");if(d>-1){var f=c.indexOf(";",d);return escape(c.substring(d+e.length+1,f<0?c.length:f))}}}var b=document,g=b.location,c=b.cookie,h=a("__utmx"),i=a("__utmxx"),j=g.hash;b.write(\'<script src="http\'+(g.protocol=="https:"?"s://ssl":"://www")+".google-analytics.com/siteopt.js?v=1&utmxkey=:campaign&utmx="+(h?h:"")+"&utmxx="+(i?i:"")+"&utmxtime="+(new Date).valueOf()+(j?"&utmxhash="+escape(j.substr(1)):"")+\'" type="text/javascript" charset="utf-8"><\/script>\')})();utmx("url","A/B");var _gaq=_gaq||[];_gaq.push(["gwo._setAccount",":ga"]);_gaq.push(["gwo._trackPageview","/:campaign/test"]);(function(){var a=document.createElement("script");a.type="text/javascript";a.async=true;a.src=("https:"==document.location.protocol?"https://ssl":"http://www")+".google-analytics.com/ga.js";var b=document.getElementsByTagName("script")[0];b.parentNode.insertBefore(a,b)})();',
		'tracking'   => 'var _gaq=_gaq||[];_gaq.push(["gwo._setAccount",":ga"]);_gaq.push(["gwo._trackPageview","/:campaign/test"]);(function(){var a=document.createElement("script");a.type="text/javascript";a.async=true;a.src=("https:"==document.location.protocol?"https://ssl":"http://www")+".google-analytics.com/ga.js";var b=document.getElementsByTagName("script")[0];b.parentNode.insertBefore(a,b)})();',
		'conversion' => 'var _gaq=_gaq||[];_gaq.push(["gwo._setAccount",":ga]);_gaq.push(["gwo._trackPageview","/:campaign/goal"]);(function(){var a=document.createElement("script");a.type="text/javascript";a.async=true;a.src=("https:"==document.location.protocol?"https://ssl":"http://www")+".google-analytics.com/ga.js";var b=document.getElementsByTagName("script")[0];b.parentNode.insertBefore(a,b)})();',
	);
	
} // End JS_Snippet_Core