<?php defined('SYSPATH') or die('No direct script access.');

class Asset_Manager_Core {
	
	/**
	 * @var  Asset_Manager  singleton instance
	 */
	protected static $_instance;
	
	/**
	 * @var  array  configuration settings
	 */
	protected $_config;
	
	protected $output;

	protected $production_paths;
	
	/**
	 * Get a singleton instance.
	 *
	 *     $asset_manager = Asset_Manager::instance();
	 *
	 * @param   array  configuration settings
	 * @return  Asset_Manager
	 */
	public static function instance(array $settings = NULL)
	{
		if (Asset_Manager::$_instance === NULL) 
		{
			// Load the configuration
			$config = Kohana::config('asset')->as_array();
			
			if ( ! empty($settings)) 
			{
				// Overload the default configuration
				$config += $settings;
			}
			
			Asset_Manager::$_instance = new Asset_Manager($config);
		}
		
		return Asset_Manager::$_instance;
	}

	/**
	 * Save the config.
	 *
	 * @param  array  configuration settings
	 */
	protected function __construct(array $config)
	{
		$this->_config = $config;
	}
	
	/**
	 * Wrap CSS code in a specific @media.
	 *
	 * @param   string
	 * @param   string
	 * @return  string
	 */
	protected function media($media, $code)
	{
		return sprintf("@media %s {%s}", $media, $code);
	}
		
	public function concat()
	{
		// Make the root local
		$root = $this->_config['root'];
		
		foreach ($this->_config['build_dir'] as $type => $dir)
		{
			$this->output[$type] = array();

			if (is_dir($root.$dir) === FALSE)
			{
				MSG::instance()->set(MSG::NOTICE, '<code>%s</code> is not a directory',
					array($root.$dir));
					
				// Move on to the next build directory
				continue;
			}
			
			// Get the file names
			$files = Kohana::list_files(rtrim($dir, '/'), (array) $root);

			if (empty($files))
			{
				MSG::instance()->set(MSG::NOTICE, 'No build files found in: <code>%s</code>',
					array($root.$dir));
				continue;				
			}
			
			foreach ($files as $file)
			{
				// Remove the extension, we know the type already
				$file = substr($file, 0, -strlen(Asset::$ext[$type]));

				// Remove the directory
				$file = substr(strrchr($file, DIRECTORY_SEPARATOR), 1);

				$this->output[$type][$file] = array();

				// Get the file paths
				$paths = explode(',', $file);

				foreach ($paths as $path)
				{
					// Look for a @media attribute
					if (strpos($path, '}') !== FALSE)
					{
						$split = explode('}', $path);

						// Decode the @media
						$media = str_replace('_', ',', $split[0]);

						// Get the path
						$path = $split[1];
					}

					// Determine if $path is an external URL
					if (strpos($path, ']') !== FALSE)
					{
						$split = explode(']', $path);
						
						$path = sprintf('%s://%s.%s', $split[0], $split[1], $type);
					}

					if (strpos($path, '://') === FALSE)
					{
						// Decode the path
						$path = str_replace(')', DIRECTORY_SEPARATOR, $path);

						// Get the first directory found in $path
						$path_dir = substr($path, 0, strpos($path, DIRECTORY_SEPARATOR));

						// Get path name without the first directory
						$path_name = substr(strstr($path, DIRECTORY_SEPARATOR), 1);

						// Look for the file within the DocumentRoot
						if (is_file($found = $root.$path.'.'.$type) === FALSE)
						{
							// Look for the path in the cascading filesystem
							$found = Kohana::find_file($path_dir, $path_name, $type);
						}

						if ($found === FALSE)
						{
							MSG::instance()->set(MSG::ERROR, 'The file <code>%s</code> was not found within the DocumentRoot or cascading filesystem',
								array($path.'.'.$type));
							continue;
						}
					}
					else
					{
						// Decode external URL
						$path = str_replace(')', '/', $path);

						// External URLs are searched with fopen or cURL
						$found = $path;
					}

					if (($code = $this->get_code($found)) === FALSE)
					{
						// An error occurred while retrieving the code
						continue;
					}

					$this->production_paths[$type][$root.$dir.$file.'.'.$type][] = $found;

					if ( ! empty($media))
					{
						// Wrap the code in the @media block
						$code = $this->media($media, $code);
					}

					// Queue the code
					array_push($this->output[$type][$file], $code);
				}

				// Concatenate the files
				$this->save($root.$dir.$file.'.'.$type, $this->output[$type][$file]);
			}
		}

		return $this;
	}

	/**
	 * Get the code from the file.
	 *
	 * @param   string
	 * @return  bool|string
	 */
	public function get_code($file)
	{
		if (strpos($file, '://') !== FALSE)
		{
			$code = Request::factory($file)
				->execute()
				->body();
		}
		else
		{
			if ( ! is_file($file))
			{
				MSG::instance(MSG::ERROR, '<code>%s</code> is not a file', array($file));
				return FALSE;
			}

			// Get the code
			$code = file_get_contents($file);

			if ($code === FALSE)
			{
				MSG::instance()->set(MSG::ERROR, '<code>[function.file-get-contents]</code>: An error occurred while reading: <code>%s</code>',
					array($file));
				return FALSE;
			}
		}

		return $code;
	}
	
	/**
	 * Write the code to the specified file.
	 * 
	 * @param   string
	 * @param   string
	 * @return  bool
	 */
	public function save($file, $code)
	{
		if (strpos($file, '://') !== FALSE)
		{
			$split = explode('://', $file);

			$type = substr($split[1], strrpos($split[1], '.') + 1);

			if ($type === 'js')
			{
				// Encode the file name
				$file = $this->_config['build_dir'][$type].str_replace('/', ')', $split[1]);
			}
		}
		elseif ( ! is_file($file))
		{
			MSG::instance(MSG::ERROR, '<code>%s</code> is not a file',
				array($file));
			return FALSE;
		}

		// Write the code
		$bytes = file_put_contents($file, $code, LOCK_EX);

		if ($bytes === FALSE)
		{
			MSG::instance()->set(MSG::ERROR, '<code>[function.file_put_contents]</code>: Failed to write to <code>%s</code>',
				array($file));
			return FALSE;
		}

		return TRUE;
	}
	
	public function compress()
	{
		if (empty($this->production_paths))
		{
			return FALSE;
		}
		
		foreach ($this->production_paths as $type => $files)
		{
			$method = '_compress_'.$type;
			$this->$method($files);
		}
	}

	public function _compress_css(array $files)
	{
		foreach ($files as $file => $paths)
		{
			// Add the java tool
			$exec = escapeshellarg($this->_config['compression']['css']['java']);

			// Add the jar file
			$exec .= sprintf(' -jar %s ', escapeshellarg($this->_config['compression']['css']['jar']));

			// Compress as css
			$exec .= ' --type css ';

			if ( ! empty($this->_config['compression']['css']['charset']))
			{
				// Add the character set
				$exec .= sprintf(' --charset %s ', $this->_config['compression']['css']['charset']);
			}

			if ( ! empty($this->_config['compression']['css']['line_break']))
			{
				$exec .= sprintf(' --line-break %s ', $this->_config['compression']['css']['line_break']);
			}

			// Add the output file
			$exec .= sprintf(' -o %s ', escapeshellarg($file));

			// Add a file
			$exec .= sprintf(' %s ', escapeshellarg($file));

			// Compress the file
			exec($exec);

			MSG::instance()->set(MSG::SUCCESS, '<code>%s</code> was compressed successfully',
				array($file));
		}
	}

	public function _compress_js(array $files)
	{
		foreach ($files as $file => $paths)
		{
			// Add the java tool
			$exec = escapeshellarg($this->_config['compression']['js']['java']);

			// Add the jar file
			$exec .= sprintf(' -jar %s ', escapeshellarg($this->_config['compression']['js']['jar']));

			// Add a file
			$exec .= sprintf(' --js %s ', escapeshellarg($file));
			
			// Compress the file
			$code = shell_exec($exec);
			
			$this->save($file, $code);

			MSG::instance()->set(MSG::SUCCESS, '<code>%s</code> was compressed successfully',
				array($file));			
		}
	}
	
} // End Asset_Manager_Core


