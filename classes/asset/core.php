<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Asset is an asset managing solution for the Kohana framework.
 *
 * @package    Asset
 * @category   Base
 * @author     Gregorio Ramirez <goyocode@gmail.com>
 * @copyright  (c) 2011 Gregorio Ramirez
 * @license    MIT
 * @see        http://kohanaftw.com/modules/asset/
 */
class Asset_Core {

	/**
	 * @var  array  configuration settings
	 */
	protected static $config;
	
	/**
	 * @var  array  file paths
	 */
	protected static $paths;
	
	/**
	 * @var  array  build (production) file paths
	 */	
	protected static $production_paths;
		
	/**
	 * @var  array  type to method mappings
	 */
	protected static $method = array(
		'css' => 'style',
		'js' => 'script',
	);
	
	/**
	 * @var  array  file extensions
	 */
	public static $ext = array(
		'css' => '.css',
		'js' => '.js',
	);
	
	// Supported file types
	const CSS = 'css';
	const JS = 'js';
	
	/**
	 * Add a single or batch of file paths. If no args are supplied, 
	 * render the file paths into a <link> element.
	 * 
	 *     // Add a single css path
	 *     Asset::css('assets/css/reset');
	 *
	 *     // Use the 'print' media attribute
	 *     Asset::css('assets/css/print', 'print');
	 *
	 *     // Add multiple css paths
	 *     Asset::css(array(
	 *     	   'assets/css/reset',
 	 *     	   'assets/css/global',
 	 *     ));
	 *
	 *     // Give some @media attributes
	 *     Asset::css(array(
	 *         'assets/css/reset',
	 *         'assets/css/global',
	 *         'assets/css/print' => 'print',
	 *         'assets/css/user/login' => 'screen, projection',
	 *     ));
	 *
	 *     // Render the css
	 *     <?php echo Asset::css() ?>
	 *
	 * @param   mixed   string or numeric/associative array of file paths, NULL to render
	 * @param   string  media attribute (e.g. 'print')
	 * @return  string|bool
	 */
	public static function css($path = NULL, $media = NULL)
	{
		if ($path === NULL)
		{
			return Asset::render(Asset::CSS);
		}
		else
		{
			return Asset::add(Asset::CSS, $path, $media);
		}
	}
	
	/**
	 * Add a single or batch of file paths. If no args are supplied,
 	 * render the file paths into a <script> element.
	 * 
	 *     // Add a single js path
	 *     Asset::js('http://ajax.googleapis.com/ajax/libs/jquery/1.5.0/jquery.min');
	 *
	 *     // Add multiple js paths
	 *     Asset::js(array(
	 *     	   'assets/js/libs/mootools',
 	 *     	   'assets/js/plugins/Roar',
 	 *     ));
	 *
	 *     // Render the js
	 *     <?php echo Asset::js() ?>
	 *
	 * @param   mixed   string or array of file paths, NULL to render
	 * @return  string|bool
	 */
	public static function js($path = NULL)
	{
		if ($path === NULL)
		{
			return Asset::render(Asset::JS);
		}
		else
		{
			return Asset::add(Asset::JS, $path);
		}
	}

	/**
	 * Add a file path into the appropriate array type.
	 *
	 *     // Add a css file path
	 *     Asset::add(Asset::CSS, 'assets/css/print', 'print');
	 *
	 *     // Add multiple file paths
	 *     Asset::add(Asset::JS, array(
	 *         'assets/js/ie-compat',
	 *         ...
	 *     ));
	 *
	 * @param   string  file type (e.g. Asset::CSS)
	 * @param   mixed   string or numeric/associative array of file paths
	 * @param   string  media attribute (e.g. 'print')
	 * @return  bool
	 */
	public static function add($type, $path, $media = NULL)
	{		
		if (is_string($path))
		{
			if (is_string($media))
			{
				// Encode the media type
				$media1 = str_replace(' ', '', $media);
				$media1 = str_replace(',', '_', $media1);
				$media1 .= '}';
			}
			else
			{
				// No media provided
				$media1 = '';
			}

			if (strpos($path, '://') !== FALSE)
			{
				$split = explode('://', $path);

				// Encode the protocol
				$protocol = $split[0].']';

				// Remove the protocol from the path
				$path1 = $split[1];
			}
			else
			{
				// $path is not an external URL
				$protocol = '';
			}

			// Encode the file path
			$path1 = str_replace('/', ')', empty($path1) ? $path : $path1);

			Asset::$production_paths[$type][$media1.$protocol.$path1] = NULL;
			
			if (Asset::$config['enabled'] === FALSE)
			{
				// Append the file extension
				$path .= Asset::$ext[$type];

				if (is_string($media))
				{
					// Add a @media attribute, defaults to 'all'
					$media = array('media' => $media);
				}

				Asset::$paths[$type][$path] = $media;
			}
		}
		elseif (is_array($path))
		{
			// Handle associative arrays
			array_walk($path, 'Asset::walk', $type);
		}
		else
		{
			// Nothing added
			return FALSE;
		}
		
		// Added!
		return TRUE;
	}

	/**
	 * Properly deal with hybrid numeric+associative arrays.
	 * 
	 * @param  mixed
	 * @param  mixed
	 * @param  string  file type (e.g. Asset::CSS)
	 * @uses   Asset::add
	 */
	protected static function walk($item, $path, $type)
	{
		if (is_string($path))
		{
			Asset::add($type, $path, $item);
		}
		else
		{
			Asset::add($type, $item);
		}
	}

	/**
	 * Implode the file paths into a working html element.
	 *
	 *     // Render the css
	 *     Asset::render(Asset::CSS);
	 *
	 *     // Render the js
	 *     Asset::render(Asset::JS);
	 *
	 * @param   string  file type (e.g. Asset::CSS)
	 * @return  string|bool
	 */
	public static function render($type)
	{
		if (empty(Asset::$production_paths[$type]))
		{
			// Nothing to render
			return FALSE;
		}

		// Get the full file name
		$file = implode(',', array_keys(Asset::$production_paths[$type])).Asset::$ext[$type];

		// Get a partial directory path
		$partial_dir = Asset::$config['build_dir'][$type];

		// Build the full directory path
		$dir = Asset::$config['root'].$partial_dir;

		if (is_file($dir.$file) === FALSE)
		{
			if (Asset::$config['enabled'])
			{
				// Log the event
				Kohana::$log->add(Log::CRITICAL, 'Can\'t locate \':file\' on disk',
					array(':file' => $dir.$file));
			}
			else
			{
				if (is_dir($dir) === FALSE)
				{
					// Create the build directory
					mkdir($dir, 0777, TRUE);

					// chmod to solve potential umask issues
					chmod($dir, 0777);
				}

				// Create the file
				touch($dir.$file);
			}
		}

		if (Asset::$config['enabled'])
		{
			// Build a single HTML tag (H)
			$html = call_user_func(array('HTML', Asset::$method[$type]), $partial_dir.$file).PHP_EOL;

		}
		else
		{
			// Build each path in its own tag
			$html = implode(PHP_EOL, array_map(
				array('HTML', Asset::$method[$type]),
				array_keys(Asset::$paths[$type]),
				array_values(Asset::$paths[$type])
			)).PHP_EOL;
		}

		if (($queue = $type::queue()) !== NULL)
		{
			// Wrap the code in HTML
			$html .= $type::wrap(implode(PHP_EOL, $queue)).PHP_EOL;
		}

		return $html;
	}
	
	/**
	 * Store the configurations in a protected field for security purposes.
	 *
	 *     // Store the configuration settings
	 *     Asset::config(Kohana::config('asset')->as_array());
	 *
	 * @param   array  configuration settings
	 * @return  bool
	 */
	public static function config(array $config)
	{
		if (empty(Asset::$config))
		{
			// Load the configurations
			Asset::$config = $config;
			return TRUE;
		}
		
		return FALSE;
	}

} // End Asset_Core
