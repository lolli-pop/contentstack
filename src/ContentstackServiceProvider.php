<?php

namespace Elliemae\Contentstack;

use Illuminate\Support\ServiceProvider;

class ContentstackServiceProvider extends ServiceProvider
{
	public function boot()
	{
		dd('boot');
	}

	public function register()
	{
		dd('register');
	}
}