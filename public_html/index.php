<?php

$root_dir = __DIR__."/../../../";

$dirs_to_scan = [
	# defaults
	"{$root_dir}instances/" => ['[instances]', '', 'inst', true],
	"{$root_dir}apps/" => ['[apps]', '@apps/', 'app', false],
	"{$root_dir}lib/" => ['[libs]', '@lib/', 'lib', false],
	# defaults
	# __DIR__."/../../lib/" => ['Libs Defaults', '@lib/'],
];

echo "<pre>";

# var_dump(__DIR__, realpath($root_dir));

echo "now is <b>", date("Y-m-d H:i:s"), "</b>\n\n";

$index = [];

foreach ($dirs_to_scan as $dir => $info)
{
	$dir = realpath($dir);
	if ($dir === false) # not there
		continue;
	
	list($caption, $url_prefix, $type, $is_default) = $info;
	
	# ensure proper deployment
	$instances = glob("{$dir}/*");
	# var_dump($dir, $instances);
	
	echo "<h3>{$caption}</h3>";
	$count = 0;
	foreach ($instances as $i) {
		if ($i === __DIR__)
			continue;
		
		if (isset($index[$type][basename($i)]))
			continue;
		else
			$index[$type][basename($i)] = true;
		
		if ($is_default && ((!is_dir(__DIR__."/../" . basename($i) . "/public_html/")) || 
							(!file_exists(__DIR__."/../" . basename($i) . "/public_html/index.php")))) {
			# && not exists
			mkdir(__DIR__."/../" . basename($i) . "/public_html/", 0755, true);
			$content = file_get_contents(__DIR__."/../../lib/compiler/boot.index.php");
			if ($content !== false) {
				file_put_contents(__DIR__."/../" . basename($i) . "/public_html/index.php", $content);
				file_put_contents(__DIR__."/../" . basename($i) . "/public_html/config.php", "<?php\n\ndefine('Q_APP_NAME', ".json_encode(basename($i)).");");
			}
		}
	
		echo '<a href="'.$url_prefix.basename($i).'?v='.uniqid().'">', basename($i), "</a>\n";
		$count++;
	}
	if (!$count) {
		echo "<i>None</i>\n";
	}
}

echo "... add an instance ... on-top of the default ones.";

