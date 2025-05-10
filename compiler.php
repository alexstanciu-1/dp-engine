<?php

namespace std\compiler;

ini_set('display_errors', 1);
error_reporting( error_reporting() & ~E_NOTICE & ~E_WARNING);

require_once __DIR__ . '/definition/def.php';

# $items = scandir("./");
$cwd = getcwd() . "/";

$defs = def_root::create($cwd);

/*
file::find(pattern: "/\\.(js|php|tpl)\$/uis", callback: function (string $name, string $dir, string $path, string $type, string $start_dir) {
	if ($type === 'file')
		# echo $path, "\n";
	
		$json_path = substr($path, 0, -3) . ".gen.json";
		if ((substr($name, -3) === '.js') && (filemtime($path) !== filemtime($json_path))) {
			$cmd = "nodejs " . escapeshellarg(__DIR__ . "/definition/model_to_json.js") . " " . escapeshellarg(realpath($path));
			$json = shell_exec($cmd);
			
			if (strlen($json) <= (strlen('undefined') + 8)) {
				$json = preg_replace("/^undefined\\s*\$/uis", 'null', $json);
			}
			# echo "cmd: {$cmd}\n";
			# echo "json: {$json}\n";
			# echo "tofile: {$json_path}\n";
			file_put_contents($json_path, $json);
			#touch($json_path, filemtime($path));
			
			file_put_contents(substr($path, 0, -3) . ".gen.php", "<?php \$_DATA = ".var_export(json_decode($json, true), true). ";\n");
			touch($json_path, filemtime($path));
			
			provision::compile($json_path, $start_dir);
		}
		else if (file_exists($json_path)) {
			provision::compile($json_path, $start_dir);
		}
	});
*/

class file
{
	public static function find(string $dir = null, string $pattern = null, string $skip_pattern = null, callable $callback = null, 
									bool $recursive = true, bool $relative = true, array &$ret = null, string $start_dir = null)
	{
		$dir = ($dir === null) ? getcwd() : realpath($dir);
		if ($dir === false)
			return false;
		$itms = scandir($dir, SCANDIR_SORT_NONE);
		if ($itms === false)
			return false;
		if ($start_dir === null)
			$start_dir = $dir;
		if ($ret === null)
			$ret = [];
		$start_dir_len = strlen($start_dir);
		foreach ($itms as $i) {
			if (($i === '.') || ($i === '..'))
				continue;
			$fp = $dir. DIRECTORY_SEPARATOR . $i;
			# Possible values are fifo, char, dir, block, link, file, socket and unknown. 
			$type = filetype($fp);
			if ((!isset($pattern)) || preg_match($pattern, $fp))
				$ret[$relative ? substr($fp, $start_dir_len + 1) : $fp] = $type;
			if ($callback) {
				$callback($i, $dir, $relative ? substr($fp, $start_dir_len + 1) : $fp, $type, $start_dir);
			}
			if ($recursive && ($type === 'dir')) {
				static::find($fp, $pattern, $skip_pattern, $callback, $recursive, $relative, $ret, $start_dir);
			}
		}
		return $ret;
	}
}

class provision
{
	public static function compile(string $path, string $cwd)
	{
		$is_ui = substr($path, 0, 3) === 'ui/';
		# this is a json
		echo "compile: ", $path, " | ", file_get_contents($path), "\n";
		$data = json_decode(file_get_contents($path));
		
		$put_it_in = dirname($path);
		$b_name = basename($path);
		$name = (($p = strpos($b_name, '.')) !== false) ? substr($b_name, 0, $p) : $b_name;
		# var_dump($put_it_in, $b_name, $name);
		# die;
		
		if ($is_ui) {
			$is_main = substr($path, 0, strlen('ui/main.')) === 'ui/main.';
			echo 'is main UI', "\n";
			if (!is_dir("public_html/"))
				mkdir("public_html/", 0750);
			
			if ($is_main) {
				$code = (function() {
					ob_start();
					require __DIR__ . '/web-ui/main.tpl';
					return ob_get_clean();
				})();
				file_put_contents($put_it_in . DIRECTORY_SEPARATOR . "{$name}.gen.tpl", $code);
				
				file_put_contents($cwd . DIRECTORY_SEPARATOR . "public_html/index.php", "<?php require('ui/main.gen.tpl');");
			}
		}
		
		# decide what we compile
		
	}
}

class defs
{
	# wrap around the info we have
	public static function get()
	{
		
	}
}
