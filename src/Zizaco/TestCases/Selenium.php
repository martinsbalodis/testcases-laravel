<?php

namespace Zizaco\TestCases;
use Illuminate\Support\Facades\Facade;

class Selenium extends Facade {

	protected static function getFacadeAccessor() {

		return 'selenium';
	}
}