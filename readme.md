# Laravel selenium testing

In phpunit.xml set SESSION_DRIVER to file.

config/app.php

providers => `Zizaco\TestCases\SeleniumServiceProvider::class,`
aliases => `'S'			=> Zizaco\TestCases\Selenium::class,`