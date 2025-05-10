<?php

namespace std\compiler\generators;

class process
{
	public static function generate(string $full_name, object $data, string $path)
	{
		# @TODO - the code is just a sample
		
		$p = strrpos($full_name, '/');
		$ns = ($p !== false) ? ucfirst(str_replace("/", "\\", substr($full_name, 0, $p) )) : null;
		$class = ucfirst(($p !== false) ? substr($full_name, $p + 1) : $full_name);
		$str = "<?php\n\n";
		if ($ns !== null)
			$str .= "namespace {$ns};\n\n";
		$str .= "class {$class} extends \Q_Process\n".
				"{\n";
		/**
		 */
		$str .= "}\n";
		
		$dir = dirname($path);
		if (!is_dir($dir))
			mkdir($dir, 0750, true);
		
		$path = $dir . "/" . ucfirst(basename($path));
		
		file_put_contents($path, $str);
		
		return $str;
	}
}


