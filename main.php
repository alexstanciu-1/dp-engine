<?php


# trigger a provision
{
	$exception = null;
	try
	{
		ob_start();
		echo "<pre>\n";
		require __DIR__ . "/compiler.php";
	}
	catch (\Exception $ex)
	{
		$exception = $ex;
	}
	finally
	{
		echo "</pre>\n";
		$provision_output = ob_get_clean();
		if ($exception) {
			echo $provision_output;
			throw $exception;
		}
	}
}

# require 'public_html/index.php';
# var_dump('alexsss', (PHP_SAPI === 'cli') ? $argv : null, getcwd(), __DIR__);

echo $provision_output;
