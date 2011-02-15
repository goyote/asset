<?php defined('SYSPATH') or die('No direct script access.');

return array(
	/**
	 * Enable when in production, set it to false when developing your app. 
	 * This flag decides whether to dump one optimized tag versus multiple 
	 * raw tags on the target page (among other low level stuff.)
	 * 
	 * @link  http://kohanaftw.com/setup-deployment/environment-flag/
	 */
	'enabled' => ENVIRONMENT <= Kohana::STAGING,

	/**
	 * Directories that will hold the compressed and optimized files.
	 */
	'build_dir' => array(
		'css' => 'assets/build/css/',
		'js' => 'assets/build/js/',
	),
		
	/**
	 * The DocumentRoot of the website (assuming you're storing your
	 * assets there.)
	 */
	'root' => DOCROOT,
	
	/**
	 * Compression settings.
	 *
	 * @link  http://code.google.com/closure/compiler/docs/api-ref.html
	 * @link  http://www.julienlecomte.net/yuicompressor/README
	 */
	'compression' => array(
		'css' => array(
			'java' => 'java',
			'jar' => MODPATH.'asset/vendor/yahoo/yuicompressor-2.4.2.jar',
			'charset' => 'utf-8',
			'line_break' => NULL,
		),
		'js' => array(
			'java' => 'java',
			'jar' => MODPATH.'asset/vendor/google/compiler-20110119.jar',
			'compilation_level' => 'SIMPLE_OPTIMIZATIONS',
		),
	),
);