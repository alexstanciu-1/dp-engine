<?php

namespace std\compiler\generators;

class ui
{
	public static function generate(string $full_name, object $data, string $path)
	{
		# @TODO - the code is just a sample
		
		/*
		$p = strrpos($full_name, '/');
		$ns = ($p !== false) ? ucfirst(str_replace("/", "\\", substr($full_name, 0, $p) )) : null;
		$class = ucfirst(($p !== false) ? substr($full_name, $p + 1) : $full_name);
		$str = "<?php\n\n";
		if ($ns !== null)
			$str .= "namespace {$ns};\n\n";
		$str .= "class {$class} extends \QWebControl\n".
				"{\n";
		$str .= "}\n";
		*/
		
		$dir = dirname($path);
		if (!is_dir($dir))
			mkdir($dir, 0750, true);
		
		$out = new \stdClass();
		static::generate_item($data, $out);
		
		$str = var_export($data, true);
		
		$path = $dir . "/" . ucfirst(basename($path));
		
		file_put_contents($path, $str);
		
		return $str;
	}
	
	public static function generate_item(object|array $data, object $out, string $tag = null, object $parent = null, object $root = null, int $depth = 0)
	{
		$is_arr = is_array($data);
		if (!$root)
			$root = $data;
		
		echo str_repeat("\t", $depth) . "tag: ".($tag ?: '<i>null</i>').", | ".json_encode($data)." \n";
		
		# menu, table, 
		# data model
		
		foreach ($data as $k => $v)
		{
			if ($v === null) {
				
			}
			else if (is_scalar($v)) {
				
			}
			else {
				static::generate_item($v, $out, $k, $is_arr ? $parent : $data, $root, $depth + 1);
			}
		}
	}
	
}

/*
	// multi-import directives

	$engine: 'default', // the only one atm, web/pwa
	
	elements: {
		menu: {
			'@import' : '/std/menu'
		},
		content: {
			source : (() => this.data.content.source)
		}
	},
	
	menu: { $: 'menu',
		$orientation: 'horizontal',
		file: {
			$caption: 'File',
			save_as: {
				
			}
		},
		edit: {
			
		}
	},
	
	table: {
		
	},
	
	customers: {
		$: 'table',
		headings:{
			
			first_heading: {
				custom: 1
			},
			
			heading: {
				$each: () => this.data.customers.headings,
				caption: ($item, $key) => $item.caption,
				click: ($item, $key) => alert($item.id)
			},
			
			last_heading: {
				custom: 1
			}
		},
		records: {
			$if: () => this.data.customers.length,
			$each: () => this.data.customers.records
		},
		panel: {
			
		},
		footer: {
			
		}
	},
	
	'stats[table]': {
		$is: 'table'
	},
	more: 1
 */