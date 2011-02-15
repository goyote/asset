<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Asset extends Controller_Template {
	
	public $template = 'template/asset';
	
	public function before()
	{
		parent::before();
		
		if (Kohana::config('asset.enabled'))
		{
			throw new Http_Exception_404;
		}
	}
	
	public function action_compile()
	{
		Asset_Manager::instance()
			->concat()
			->compress();
	}

	public function after()
	{
		if ( ! isset($this->template->title))
		{
			$this->template->title = ucwords(Inflector::humanize($this->request->action()));
		}

		$this->template->title .= ' — Asset Manager';

		parent::after();
	}
	
} // End Controller_Asset