<?php

namespace std\compiler;

require_once __DIR__ . "/../generators/type.php";
require_once __DIR__ . "/../generators/ui.php";

final class def_root extends def_base
{
	/**
	 * @var string
	 */
	protected $_path_ = null;
	/**
	 * @var array
	 */
	protected $_layers_ = [];
	/**
	 * @var object
	 */
	protected $_config_ = null;
	
	protected function __construct(string $path)
	{
		# ui->namespace_and_class->class->data ...
		
		# stage 1. get config & layers
		list($config, $error) = static::extract_from_js_module($path . "/config.js");
		
		$layers = $config->use ?? (new \stdClass());
		foreach ($layers ?? [] as $layer_key => $layer_inf) {
			$layer = $layer_inf->from;
			
			if (is_dir($layer)) {
				# $layers[$layer_key] = (object)['path' => realpath($layer)."/", 'as' => $layer_inf->as];
				$layer_inf->from = realpath($layer)."/";
			}
			else {
				throw new \Exception('Missing path ' . $layer);
			}
		}
		
		if (is_dir($path)) {
			if (isset($layers->main))
				throw new \Exception('Layer name overlap. Last layer`s name was asigned `main` by default');
			$layers->main = (object)['from' => realpath($path)."/"];
		}
		else
			throw new \Exception('Missing path ' . $path);
			
		$t0 = microtime(true);
		$files_map = (object)[];
		
		$newest_timestamp = [0, null];
		foreach ($layers as $key => $layer) {
			# $files_map[$key] = [$layer, static::get_files_map($layer, $layers)];
			static::get_files_map($layer->from, $key, $layers, $files_map, $newest_timestamp);
		}
		
		$old_files_map = null;
		if (file_exists("~cache/files_map.php")) {
			(function () use (&$old_files_map) {
				# $load_t0 = microtime(true);
				require '~cache/files_map.php';
				$old_files_map = $_DATA;
				# $load_t1 = microtime(true);
				# var_dump('$load_time: ' . (($load_t1 - $load_t0)*1000) . " ms");
			})();
		}
		
		# now we need to apply polymorphism extends and traits | explicit traits ... pull this / from this / and some rules
		# access to ::parent
		if (!is_dir('~cache/'))
			mkdir('~cache/', 0750);
		
		$rc = null;
		
		$tvex_0 = microtime(true);
		$export_str = "<?php \$_DATA = ".var_export($files_map, true).";";
		$tvex_1 = microtime(true);
		
		if (($old_files_map === null) || ($export_str !== file_get_contents('~cache/files_map.php'))) {
			$rc = file_put_contents("~cache/files_map.php", $export_str);
		}
		else {
			echo "NO CACHE CHANGES\n";
		}
		
		$t1 = microtime(true);
		
		echo "var_export took: ".round(($tvex_1 - $tvex_0)*1000), " | all took: " . round(($t1 - $t0)*1000, 3)." ms\n";
		
		# sync files cache
		static::sync_files_cache($files_map, $old_files_map, $layers);

		$final_defs = new \stdClass();
		static::compute_files_definitions($files_map, $old_files_map, $layers, $final_defs);
		
		# @TODO - trigger generators, then if required, compute_files_definitions again
		list($re_run_compute) = static::run_definition_generators($final_defs, $files_map, $old_files_map, $layers);
		if ($re_run_compute) {
			static::compute_files_definitions($files_map, $old_files_map, $layers);
		}
		
		# Then generate the code ... or do whatever is needed to make this work
		static::run_code_generators($final_defs, $files_map, $old_files_map, $layers);
		
		# tag all layers with a number (internally only), reverse order 
		# last layer is 1, computed value is 0
		/*
		$this->_path_ = $path;
		$this->_data_ = new def(null, $this);
		*/
	}
	
	public static function create(string $path)
	{
		return new static($path);
	}
	
	public function __get(string $property): mixed
	{
		# will return def_categ
		# return $this->_data_->__get($property);
	}
	
	public function __set(string $property, mixed $value): void
	{
		$this->_data_->__set($property, $value);
	}
	
	public static function get_files_map(string $layer, int|string $layer_key, object $all_layers = null, &$files_map = null, array &$newest_timestamp = null)
	{
		$other_layers = null;
		if ($all_layers) {
			foreach ($all_layers as $l) {
				if ($layer !== $l->from) {
					$other_layers[$l->from] = $l->from;
				}
			}
		}
				
		static::scan_dir($layer, $other_layers, $layer_key, $newest_timestamp, $files_map, ['js' => true, 'ui.js' => true, 'model.js' => true, 'data.js' => true], strlen($layer));
		
		return $files_map;
		/*
		$flags = \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO | \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS;
		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($layer, $flags)) as $path => $file) {
			
			if ($file->isFile() && ($bn = basename($path)) && 
					# with file like `.htaccess` that start with a dot, we don't consider it an ext
					($full_ext = ($p = strpos($bn, ".")) ? substr($bn, $p + 1) : null) && 
					($full_ext === 'js')) {
				
				$t0 = microtime(true);
				$s = stat($path);
				$t1 = microtime(true);
				var_dump($path, (($t1 - $t0)*1000)." ms", $s);
			}
		}
		*/
		# remove files that are on other layers
	}
	
	public static function scan_dir(string $dir, array $skip_dirs, int|string $layer_key, array &$newest_timestamp, &$ret = null, array $only_ext = null, int $relative_length = null, string $start_dir = null)
	{
		if ($start_dir === null)
			$start_dir = $dir;
		
		$rets_array = is_array($ret);
		
		$items = scandir($dir, SCANDIR_SORT_NONE);
		foreach ($items ?? [] as $i) {
			if (($i === '.') || ($i === '..') || ($i[0] === '~'))
				continue;

			$fp = "{$dir}{$i}";
			if (is_file($fp)) {
				# with file like `.htaccess` that start with a dot, we don't consider it an ext
				if ((!$only_ext) || ( ($full_ext = ($p = strpos($i, ".")) ? substr($i, $p + 1) : null) && ($only_ext[$full_ext] ?? null) )) {
					# $path_no_ext = substr($i, 0, $p);
					$mtime = filemtime($fp);
					if (($newest_timestamp !== null) && ($mtime > $newest_timestamp[0])) {
						$newest_timestamp[0] = $mtime;
						$newest_timestamp[1] = $fp;
					}
					$key_1 = substr( $relative_length ? substr($fp, $relative_length) : $fp , 0, -(strlen($full_ext) + 1));
					$key_2 = $layer_key;
					$data_to_set = [substr($fp, $relative_length), $mtime, filesize($fp)];
					
					# it's a bit faster with arrays
					if ($rets_array) {
						$ret[$key_1][$key_2][$full_ext] = $data_to_set;
					}
					else
					{
						$p1 = &$ret->$key_1;
						if (!isset($p1))
							$p1 = new \stdClass();
						$p2 = &$p1->$key_2;
						if (!isset($p2))
							$p2 = new \stdClass();
						$p2->$full_ext = $data_to_set;
						unset($p1, $p2);
					}
				}
			}
			else if (is_dir($fp) && (!isset($skip_dirs[$fp."/"]))) {
				static::scan_dir($fp."/", $skip_dirs, $layer_key, $newest_timestamp, $ret, $only_ext, $relative_length, $start_dir);
			}
		}
		return $ret;
	}
	
	public static function sync_files_cache($new_data, $old_data, $layers, bool $force = false)
	{
		# $force = true;
		# also join files/parts here
		# error for overlapping data non-identical
		
		# we need to define a standard here ... know what files are parts ... and what files define a differnt aspect
		# var_dump($new_data, $old_data);
		if (!is_dir('~cache/files/php'))
			mkdir('~cache/files/php', 0750, true);
		
		foreach ($new_data ?? [] as $nd_key => $nd_layers)
		{
			$old_layers = $old_data ? ($old_data->$nd_key ?? null) : null;
			foreach ($nd_layers ?? [] as $layer_key => $files)
			{
				$old_files = $old_layers ? ($old_layers->$layer_key ?? null) : null;
				foreach ($files ?? [] as $f_type => $f_info)
				{
					$old_f_info = $old_files ? ($old_files->$f_type ?? null) : null;
					list ($rel_path, $file_time, $size) = $f_info;
					
					# if changed
					$cache_path = "~cache/files/php/{$layer_key}/{$rel_path}.php";

					if ((!$force) && ($size === $old_f_info[2]) && file_exists($cache_path))
					{
						# no changes
					}
					else
					{
						$cache_dir = dirname($cache_path);
						if (!is_dir($cache_dir))
							mkdir($cache_dir, 0750, true);

						$full_path = $layers->$layer_key->from . $rel_path;

						# var_dump($nd_key. ' : '. $full_path . " ||| " . json_encode($old_f_info));
						# var_dump('cache path : ' . $cache_path);
						
						self::extract_from_js_module($full_path, $cache_path, $force);
						touch($cache_path, $file_time);
						/*
						{
							$cmd = "nodejs " . escapeshellarg(__DIR__ . "/model_to_json.js") . " " . escapeshellarg($full_path);
							$json = shell_exec($cmd);
							
							if (strlen($json) <= (strlen('undefined') + 8)) {
								$json = preg_replace("/^undefined\\s*\$/uis", 'null', $json);
							}
							$data_from_json = json_decode($json);
							if ($data_from_json === false)
								throw new \Exception('Failed to get json data from: ' . $full_path);
							
							$data_to_write = "<?php \$_DATA = ".var_export($data_from_json, true). ";\n";

							# if ((!file_exists($cache_path)) || ($data_to_write !== file_get_contents($cache_path)))
							{
								file_put_contents($cache_path, $data_to_write);
								touch($cache_path, $file_time);
							}
							# else ... no change took place
						}
						*/
					}
				}
			}
		}
		
		# @TODO - remove items that are no longer there
		
		# foreach ($old_data ?? [] as )
		
		# remove from old data ... stuff that is no longer there
		
		# foreach ($new_data as $)
	}
	
	public static function compute_files_definitions($new_data, $old_data, $layers, object $final_defs, bool $force = false)
	{
		# we need only $new_data ... flagged with what's new , what's deleted, changed and not-changed
		$remaining = (array)$new_data;
		while ($remaining)
		{
			$item = reset($remaining);
			$item_key = key($remaining);
			static::cfd_item($final_defs, $item_key, $item, $old_data->$item_key ?? null, $layers, $force, $remaining);
			# should not be needed
			unset($remaining[$item_key]);
		}
	}
	
	protected static function cfd_item(object $final_defs, string $item_key, object $item, object|null $old_data, $layers, bool $force, array $remaining)
	{
		# @TODO - $is, $namespace, $name, ..., $import, $from|$parent
		# var_dump('cfd_item @' . $item_key, $item);
		
		$data = null;
		$prev_layer_data = null;

		$old_layers = $old_data ? ($old_data->$item_key ?? null) : null;
		foreach ($item ?? [] as $layer_key => $files)
		{
			# @TODO : only compute changes !
				# $old_files = $old_layers ? ($old_layers->$layer_key ?? null) : null;
			
			$data = null;

			# 1. join/merge files split (warn/error on overlaps)
			foreach ($files ?? [] as $f_type => $f_info)
			{
				$n_data = static::load_data($f_info[0], $layer_key);
				if ($n_data === null) {
					# @TODO empty files, to setup a warn 
				}
				else if ($data) {
					static::copy_from($data, $n_data);
				}
				else {
					$data = $n_data;
				}
			}
			
			# merge layers
			if ($prev_layer_data) {
				static::copy_from($prev_layer_data, $data);
			}
			
			# @TODO, put this in a method, as it will also be recursive
			# 3. $inherit, $import, $use-traits
			{
				# we need some masks for copying, except inherit
				/*
				for ($i = 1; $i < $files_in_right_order_count; $i++) {
					static::copy_from($files_in_right_order[$i - 1], $files_in_right_order[$i]);
				}
				*/
			}
			# info: extends and traits (join from multi-files, join layers, extends, use-traits)
			
			$prev_layer_data = $data;
		}
		
		$export_str = "<?php \$_DATA = ".var_export($data, true).";";
		
		$cache_path = "~cache/final/{$item_key}.php";
		$dir = dirname($cache_path);
		if (!is_dir($dir))
			mkdir($dir, 0750, true);
		
		$rc = file_put_contents($cache_path, $export_str);
		
		$final_defs->$item_key = (object)['cache_path' => $cache_path];
		
		unset($remaining[$item_key]);
		
		return [$data, $rc];
	}
	
	protected static function copy_from(array|object $src, array|object &$dest)
	{
		if ($src === null)
			return;
		$dest_is_obj = ($dest instanceof \stdClass);
		# we've put a bit more code here for performance
		if ($dest_is_obj) {
			foreach ($src as $k => $v)
			{
				# do not use a reference on $dest (ex: $ref = &$dest->{$k}) before calling property_exists as it will create that property, even after unset($ref) it will stay as a null
				$has_prop = property_exists($dest, $k);
				if (!$has_prop) {
					$dest->{$k} = $v;
				}
				else if (is_object($v)) {
					static::copy_from($v, $dest->{$k});
				}
			}
		}
		else {
			foreach ($src as $k => $v) {
				# do not use a reference on $dest (ex: $ref = &$dest[$k]) before calling array_key_exists as it will create they key entry, even after unset($ref) it will stay as a null
				$has_key = array_key_exists($k, $dest);
				if (!$has_key) {
					$dest[$k] = $v;
				}
				else if (is_array($v)) {
					static::copy_from($v, $dest[$k]);
				}
			}
		}
	}
	
	public static function load_data(string $rel_path, string|int $layer_key = null)
	{
		$cache_path = ($layer_key === null) ? $rel_path : "~cache/files/php/{$layer_key}/{$rel_path}.php";
		
		$data = null;
		if (file_exists($cache_path)) {
			(function () use ($cache_path, &$data) {
				require $cache_path;
				$data = $_DATA;
			})();
		}
		else
			throw new \Exception('Not found cache file: ' . $cache_path);
		return $data;
	}
	
	# @TODO - trigger generators, then if required, compute_files_definitions again
	public static function run_definition_generators(object $final_defs, $files_map, $old_files_map, $layers)
	{
		
	}

	# Then generate the code ... or do whatever is needed to make this work
	public static function run_code_generators(object $final_defs, $files_map, $old_files_map, $layers)
	{
		# var_dump('run_code_generators', $final_defs);
		
		$config = isset($final_defs->config->cache_path) ? static::load_data($final_defs->config->cache_path) : new \stdClass();
		
		# var_dump('$config', $config);
		
		$model_ns = $config->{'$model_ns'};
		
		# var_dump('$model_ns', $model_ns);
		
		if (!is_dir('~code/orm-omi/code_inst/model/'))
			mkdir('~code/orm-omi/code_inst/model/', 0755, true);
		if (!is_dir('~code/orm-omi/code_inst/view/'))
			mkdir('~code/orm-omi/code_inst/view/', 0755, true);
		if (!is_dir('~code/orm-omi/code_inst/classes/'))
			mkdir('~code/orm-omi/code_inst/classes/', 0755, true);
		
		foreach ($final_defs as $full_name => $info) {
			
			$m = null;
			preg_match("/[^\\/]+\\//uis", $full_name, $m);
			$first_dir = $m[0] ?? null;
			
			# var_dump('$first_dir @'.$full_name." = " . json_encode($first_dir, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
			
			$data = static::load_data($info->cache_path);
			if (!$data) {
				# @TODO - put some warning
				continue;
			}
			if ($first_dir === 'ui/') {
				# generate UI
				echo "Generate UI: {$full_name}\n";
				static::generate_ui($full_name, $data, "~code/orm-omi/code_inst/view/{$full_name}.php");
			}
			else if (['model/' => true, 'type/' => true, 'types/' => true, 'data/' => true][$first_dir] ?? false) {
				# generate model
				echo "Generate Type: {$full_name}\n";
				static::generate_type($full_name, $data, "~code/orm-omi/code_inst/model/{$full_name}.class.php", $model_ns);
			}
			# process/processes
			
			# lib/util
		}
		
		# var_dump(glob(dirname(__DIR__) . "/generators/omi-orm-files/{,.}[!.,!..]*", GLOB_NOSORT | GLOB_BRACE));
		
		file_put_contents("~code/orm-omi/.htaccess", static::compile_template(__DIR__ . "/../generators/omi-orm-files/.htaccess"));
		file_put_contents("~code/orm-omi/config.php", static::compile_template(__DIR__ . "/../generators/omi-orm-files/config.php"));
		file_put_contents("~code/orm-omi/index.php",  static::compile_template(__DIR__ . "/../generators/omi-orm-files/index.php"));
		
		$c_path = realpath(__DIR__ . "/../generators/omi-orm-files/code/");
		if ($c_path) {
			$cmd = "rsync -a " . escapeshellarg("{$c_path}/") . " " . escapeshellarg("~code/orm-omi/code");
			echo $cmd, "\n";
			echo shell_exec($cmd);
		}
		
		# cache_path
		
		# data types
		# model

		# for ORM
		# for UI
	}
	
	public static function compile_template(string $path)
	{
		if (!file_exists($path))
			return false;
		# $error_lvl = error_reporting();
		try {
			ob_start();
			# error_reporting(0);
			require $path;
			return ob_get_clean();
		}
		finally {
			# put back error reporting
			# error_reporting($error_lvl);
		}
	}
	
	public static function generate_type(string $full_name, object $data, string $path, string $model_ns = null)
	{
		return generators\type::generate($full_name, $data, $path, $model_ns);
	}
	
	public static function generate_ui(string $full_name, object $data, string $path)
	{
		return generators\ui::generate($full_name, $data, $path);
	}
}

/*
	$dest = (object)[
		'only_one' => 1111,
		'nested' => (object)[
			'prop_x' => 'test',
		],
		'list' => [
			2 => 222,
		],
		'null' => null,
	];
	$src = (object)[
		'null' => 'haha',
		# 'only_one' => 1111,
		'nested' => (object)[
			'prop_x' => 'from src test',
			'prop_y' => 'yyyyy',
		],
		'more_props' => 'daaaa',
		'list' => [
			0 => 'zero',
			4 => 4444,
		]
	];

	$t1 = microtime(true);
	static::copy_from($src, $dest);
	$t2 = microtime(true);
	var_dump((($t2 - $t1)*1000) . " ms", $dest);
	die;
 */
