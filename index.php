<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Check PHP
if (version_compare(PHP_VERSION, '8.1.0') < 0) {
    die("Current PHP version is " . phpversion() . "! PHP version required for running this script is PHP 8.1. Please check and upgrade your current PHP version.");
}

// Check Ioncube Loader
// if (!function_exists('ioncube_loader_version')) {
//     die("ionCube Loader function is missing! This script requires ionCube Loader function to run, Please check and enable the extension or Contact with hosting provider.");
// }

// Get Ioncube Version
function GetIonCubeLoaderVersion()
{
	if (function_exists('ioncube_loader_version')) {
		$version = ioncube_loader_version();
		$a = explode('.', $version);
		$count = count($a);
		if ($count == 3) {
			return $version;
		} elseif ($count == 2) {
			return $version . ".0";
		} elseif ($count == 1) {
			return $version . ".0.0";
		}
		$version = implode('.', array_slice($a, 0, 3));
		return $version;
	}
	return 'Not Found!';
}

// Check Version
// if (version_compare(GetIonCubeLoaderVersion(), '12.0.0') < 0) {
//     die("Current ionCube Loader version is ". GetIonCubeLoaderVersion()  ."! minimum ionCube Loader version required for running This Script is 12.0.0. Please check and upgrade your current ionCube Loader version or Contact with hosting provider.");
// }

/*
|--------------------------------------------------------------------------
| Check If The Application Is Under Maintenance
|--------------------------------------------------------------------------
|
| If the application is in maintenance / demo mode via the "down" command
| we will load this file so that any pre-rendered content can be shown
| instead of starting the framework, which could cause an exception.
|
*/

if (file_exists($maintenance = __DIR__ . '/core/storage/framework/maintenance.php')) {
    require $maintenance;
}

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| this application. We just need to utilize it! We'll simply require it
| into the script here so we don't need to manually load our classes.
|
*/

require __DIR__ . '/core/vendor/autoload.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request using
| the application's HTTP kernel. Then, we will send the response back
| to this client's browser, allowing them to enjoy our application.
|
*/

$app = require_once __DIR__ . '/core/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
