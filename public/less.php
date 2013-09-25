<?php
header('Content-Type: text/css');

error_reporting(E_ALL | E_STRICT | E_NOTICE | E_DEPRECATED);
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);

require '../lib/lessc.inc.php';
$less = array();
if (array_key_exists('f', $_GET) && !empty($_GET['f'])) {
	$less = explode(',', $_GET['f']);
} else {
	exit(0);
}

$compiler = 'lessphp';
if (array_key_exists('c', $_GET) && in_array(strtolower($_GET['c']), array('lessc', 'lessphp'))) {
	$compiler = strtolower($_GET['c']);
}

if (extension_loaded('XCache')) {
	$cache_var = 'miranda_less_' . md5($_GET['f']);
}

$compiled_css = '';
$in_cache = false;
if (extension_loaded('XCache')) {
	if (xcache_isset($cache_var)) {
		$compiled_css = xcache_get($cache_var);
		$compile_time = 0;
		if (xcache_isset($cache_var . '_ts')) {
			$compile_time = xcache_get($cache_var . '_ts');
		}
		$in_cache = true;
		
		if (count($less)) {
			foreach ($less as $less_file) {
				if (filemtime($less_file) >= $compile_time) {
					$in_cache = false;
					break;
				}
			}
		}
		foreach (glob("css/*.less") as $filename) {
			if (filemtime($filename) >= $compile_time) {
				$in_cache = false;
				break;
			}
		}
	}
}

if (!$in_cache && count($less)) {
	$compiled_css = '';
	foreach ($less as $less_file) {
		if (pathinfo($less_file, PATHINFO_EXTENSION) == 'less') {
			switch ($compiler) {
				case 'lessphp':
					$lessc = new lessc();
					$compiled_css .= $lessc->compileFile($less_file) . "\n";
					$lessc = null;
					break;
				case 'lessc':
					putenv("LESSCHARSET=utf-8");
					$output = array();
					exec("lessc $less_file", $output);
					$compiled_css .= join("\n", $output);
					break;
			}
		} else {
			$compiled_css .= file_get_contents($less_file) . "\n";
		}
	}
	if (extension_loaded('XCache')) {
		xcache_set($cache_var, $compiled_css);
		xcache_set($cache_var . '_ts', time());
	}
}

echo $compiled_css;