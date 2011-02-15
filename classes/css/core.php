<?php defined('SYSPATH') or die('No direct script access.');
/**
 * CSS helps you embed css code in a page.
 *
 * @package    Asset
 * @category   Base
 * @author     Gregorio Ramirez <goyocode@gmail.com>
 * @copyright  (c) 2011 Gregorio Ramirez
 * @license    MIT
 * @see        http://kohanaftw.com/modules/asset/
 */
class CSS_Core {
		
	/**
	 * @var  array  code queue
	 */
	protected static $queue;
		
	/**
	 * Get the queued code.
	 *
	 *     $queue = CSS::queue();
	 *
	 * @return  array  queued code
	 */
	public static function queue()
	{
		// Store the queue locally
		$queue = CSS::$queue;
		
		// Reset the queue
		CSS::$queue = NULL;
		
		return $queue;
	}
	
	/**
	 * Queue css code.
	 *
	 *     // Embed some dynamic css
	 *     CSS::embed("#preview { display: $display; }");
	 *
	 *     // Render the code
	 *     <head>
	 *         <?php echo Asset::css() ?>
	 *
	 * @param   string  css code
	 * @return  bool
	 */
	public static function embed($code)
	{
		if (is_string($code) === FALSE)
			return FALSE;
		
		// Queue the code
		CSS::$queue[] = $code;
		
		return TRUE;
	}

	/**
	 * Wrap the code in HTML <style> tags.
	 *
	 *     $html = CSS::wrap($code);
	 *
	 * @param   string  css code
	 * @return  string
	 */
	public static function wrap($code)
	{
		return sprintf('<style type="text/css">%s</style>', $code);
	}

} // End CSS_Core