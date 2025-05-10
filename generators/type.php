<?php

namespace std\compiler\generators;

class type
{
	public static function generate(string $full_name, object $data, string $path, string $model_ns = null)
	{
		$last_layer_tag = 'test';
		# @TODO - this needs to be better !!!
		if (['model/main' => true, 'type/main' => true, 'type/main' => true, 'types/main' => true, 'data/main' => true][$full_name] ?? false) {
			$ns = "Omi";
			$class = "App_{$last_layer_tag}_model_"; # App_tf_prov_model_
			# @TODO - dynamic determination of path
			$path = "~code/orm-omi/code_inst/model/App.class.php";
			$extends = "App_mods_model_";
			$storage_table = '$App';
			$class_name = "App";
		}
		else {
			$p = strrpos($full_name, '/');
			$ns = $model_ns ?? (($p !== false) ? substr($full_name, 0, $p) : null);
			if ($ns) {
				$chunks = preg_split("/([\\\\\\/])/uis", $ns);
				foreach ($chunks as &$c)
					$c = ucfirst($c);
				$ns = implode("\\", $chunks);
			}
			$class = ucfirst(($p !== false) ? substr($full_name, $p + 1) : $full_name);
			$extends = "\QModel";
			
			$storage_table = $class;
			$class_name = $class;
			$class .= "_{$last_layer_tag}_model_";
		}

		$str = "<?php\n\n";
		if ($ns !== null)
			$str .= "namespace {$ns};\n\n";
		$str .= "
/**
 * @storage.table {$storage_table}
 *
 * @class.name {$class_name}
 */
class {$class} extends {$extends}
{\n";
		foreach ($data as $k => $v)
		{
			# if does not start with a letter
			if (!ctype_alpha($k[0]))
				continue;
			
			# $is: 'int'
			$data_type = $v->{'$is'} ?? 'string';
			
			$str .= "\t/**\n".
					"\t * @var {$data_type}\n".
					"\t */\n".
					"\tprotected \$".ucfirst($k).";\n";
		}
		$str .= "}\n";
		
		$dir = dirname($path);
		if (!is_dir($dir))
			mkdir($dir, 0750, true);
		
		$path = $dir . "/" . ucfirst(basename($path));
		
		file_put_contents($path, $str);
		
		return $str;
	}
}
/*
namespace Omi;

/**
 * @storage.table $App
 *
 * @class.name App
 */
# abstract class App_tf_prov_model_ extends App_mods_model_
# {