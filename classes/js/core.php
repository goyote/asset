<?php defined('SYSPATH') or die('No direct script access.');
/**
 * JS helps you embed javscript code in a page.
 *
 * @package    Asset
 * @category   Base
 * @author     Gregorio Ramirez <goyocode@gmail.com>
 * @copyright  (c) 2011 Gregorio Ramirez
 * @license    MIT
 * @see        http://kohanaftw.com/modules/asset/
 */
class JS_Core {
	
	// GWO script types
	const CONTROL = 'control';
	const TRACKING = 'tracking';
	const CONVERSION = 'conversion';
	
	/**
	 * @var  array  code queue
	 */
	protected static $queue;
	
	/**
	 * @var  array  temporary storage
	 */
	protected static $temp;
	
	/**
	 * Get the queued code.
	 *
	 *     $queue = JS::queue();
	 *
	 * @return  array  queued code
	 */
	public static function queue()
	{
		// Store the queue locally
		$queue = JS::$queue;
		
		// Reset the queue
		JS::$queue = NULL;
		
		return $queue;
	}
	
	/**
	 * Queue js code.
	 *
	 *     // Embed some dynamic js
	 *     $data = json_encode(Arr::map(array('HTML, 'chars'), $data));
	 *     JS::embed("window['data'] = $data;");
	 *
	 *     // Render the code
	 *         ...
	 *         <?php echo Asset::js() ?>
	 *     </body>
	 *
	 * @param   string  js code
	 * @return  bool
	 */
	public static function embed($code)
	{
		if (is_string($code) === FALSE)
			return FALSE;
		
		// Queue the code
		JS::$queue[] = $code;
		
		return TRUE;
	}
	
	/**
	 * Queue a Google Analytics script.
	 *
	 *     // Queue the ga script
	 *     JS::ga('UA-XXXXXXXX-X');
	 *
	 *     // Render the code
	 *         ...
	 *         <?php echo Asset::js() ?>
	 *     </body>
	 *
	 * @param   string  account id (e.g. UA-XXXXXXXX-X)
	 * @return  bool
	 */
	public static function ga($id)
	{
		if (is_string($id) === FALSE)
			return FALSE;
		
		// Get the ga snippet
		JS::$queue[] = JS_Snippet::ga($id, FALSE);
		
		return TRUE;
	}
	
	/**
	 * Run a Google Website Optimizer script.
	 *
	 *     // Set the tracking script
	 *     JS::gwo(JS::TRACKING, 'UA-XXXXXXXX-X', 0000000000);
	 *
	 *     // Render the tracking script
	 *     <head>
	 *         <?php echo JS::gwo() ?>
	 *
	 * @param   string  type of gwo script
	 * @param   string  account id (e.g. UA-XXXXXXXX-X)
	 * @param   string  campaign id (e.g. 0000000000)
	 * @return  string|bool
	 */
	public static function gwo($type = NULL, $ga = NULL, $campaign = NULL)
	{	
		if ($type === NULL)
		{
			if ( ! empty(JS::$temp[__FUNCTION__]))
			{
				// Get the snippet
				$code = JS::$temp[__FUNCTION__];
				
				// Delete the reference
				unset(JS::$temp[__FUNCTION__]);
				
				return $code;
			}
			else
			{
				// Nothing stored
				return FALSE;
			}
		}
		
		// Get the gwo snippet
		$snippet = JS_Snippet::gwo($type, $ga, $campaign, FALSE);
		
		if ($snippet === FALSE)
		{
			// Unsupported gwo script type provided
			return FALSE;
		}
		else
		{
			JS::$temp[__FUNCTION__] = $snippet;
				
			return TRUE;
		}
	}
	
	/**
	 * Wrap the code in HTML <script> tags.
	 *
	 *     $html = JS::wrap($code);
	 *
	 * @param   string  js code
	 * @return  string
	 */
	public static function wrap($code)
	{
		return sprintf('<script type="text/javascript">%s</script>', $code);
	}

} // End JS_Core
