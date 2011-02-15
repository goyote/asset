<?php defined('SYSPATH') or die('No direct script access.');

// Load the configuration settings
Asset::config(Kohana::config('asset')->as_array());

Route::set('asset', 'asset/<action>')
	->defaults(array(
		'controller' => 'asset',
	));
