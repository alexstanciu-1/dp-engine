<?php

namespace std\compiler;

abstract class def_base
{
	/**
	 * @var def_root
	 */
	protected $_root_ = null;
	/**
	 * @var def_base
	 */
	protected $_parent_ = null;
	/**
	 * @var array
	 */
	protected $_data_ = [];
	
	public function __construct(self $parent = null, def_root $root = null)
	{
		$this->_parent_ = $parent;
		$this->_root_ = $root;
	}
	
	public function __get(string $property): mixed
	{
		# index the final value at position '0'
		return $this->_data_[$property][0] ?? null;
	}
	
	public function get(string $property, string $layer = null): mixed
	{
		# @TODO - work here ofc !
		return $this->_data_[$property][0] ?? null;
	}
	
	public function set(string $property, mixed $value, string $layer = null): void
	{
		# @TODO - work here ofc !
		$this->_data_[$property][0] = $value;
	}
	
	# public static $extract_from_js_module_count = 0;
	
	public static function extract_from_js_module(string $path, string $cache_path = null, bool $force = false)
	{
		/*
		static::$extract_from_js_module_count++;
		if (static::$extract_from_js_module_count > 16) {
			var_dump("too many JS CALLS!");
		}
		*/
		
		$path = realpath($path);
		if ((!$path) || (!is_file($path))) {
			echo "NOT FILE: {$path}\n";
			return [false];
		}
		
		if ($cache_path === null)
			$cache_path = $path.".cache.php";
		
		if ((!$force) && file_exists($cache_path) && (filemtime($cache_path) === filemtime($path))) {
			# return from cache
			$data_from_json = (function () use ($cache_path) {
				include $cache_path;
				return $_DATA ?? false;
			})();
			return [$data_from_json];
		}
		else {
			
			try
			{
				$response_data = null;
				$error_file = "/tmp/dp_nodejs_err_".sha1(uniqid("", true));
				$cmd = "nodejs " . escapeshellarg(__DIR__ . "/model_to_json.js") . " " . escapeshellarg($path). " 2>" . escapeshellarg($error_file);

				echo "cmd: {$cmd}\n";

				$t1 = microtime(true);
				$json = shell_exec($cmd);
				
				echo "cmd took: ".round((microtime(true) - $t1) * 1000, 3)." ms\n";
				
				$error = null;
				if ($json === false) {
					$error = error_get_last(); # we hope this is ok, not tested !
					var_dump('$error 1q234', $error);
					/*	[type] => 8
						[message] => Undefined variable: a
						[file] => C:\WWW\index.php
						[line] => 2 */
				}
				else if (is_string($json)) {
					if (strlen($json) <= (strlen('undefined') + 8))
						$json = preg_replace("/^undefined\\s*\$/uis", 'null', $json);
					
					$data_from_json = json_decode($json, null, 512, JSON_INVALID_UTF8_SUBSTITUTE);
					if ($data_from_json !== false) {
						
						$response_data = $data_from_json->data ?? null;
						
						echo "cmd took internal: ".round($data_from_json->took, 3)." ms\n";
						
						if (isset($response_data)) {
							$response_data = static::normalize($path, $response_data);
						}
						
						$response_error = $data_from_json->error ?? null;
						
						if ($response_error) {
							$error = $response_error;
							$response_data = null;
						}
						else {
							file_put_contents($cache_path, "<?php \$_DATA = ".var_export($response_data, true). ";\n");
							touch($cache_path, filemtime($path));
						}
					}
					else {
						$error = json_last_error();
					}
				}
				return [$response_data ?? false, $error];
			}
			finally
			{
				if (file_exists($error_file))
					unlink($error_file);
			}
		}
	}
	
	public static function normalize(string $path, array|object &$response_data)
	{
		$m = null;
		preg_match("/[^\\/]+\\//uis", $path, $m);
		$first_dir = $m[0] ?? null;
		
		if (!isset($first_dir))
			return;

		if ($first_dir === 'ui/') {
			# generate UI
			echo "Normalize UI: {$path}\n";
			return static::normalize_ui($response_data);
		}
		else if (['model/' => true, 'type/' => true, 'types/' => true, 'data/' => true][$first_dir] ?? false) {
			# generate model
			echo "Normalize Type: {$path}\n";
			return static::normalize_type($response_data);
		}
	}
	
	
	public static function normalize_ui(array|object &$response_data)
	{
		# $elements / $cfg / $is / $ / 
		# menu
		$is_obj = ($response_data instanceof object);
		
		# tag|is|classes|id
		#	properties|attributes
		#	elements
		#	events|triggers
		#	model
		#	
		
		foreach ($response_data as $k => &$v) {
			if ($is_obj) {
				# reserved: config
				switch ($k) {
					case '$': {
						break;
					}
					default:
						break;
				}
			}
			else {
				static::normalize_ui($v);
			}
		}
		
		return $response_data;
	}
	
	public static function normalize_type(array|object &$response_data)
	{
		return $response_data;
	}
	
}
