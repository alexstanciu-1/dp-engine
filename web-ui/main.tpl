<!doctype html>
<html>
<head>
	<title>Descriptive JS - DEV</title>
    <meta name="description" content="Descriptive APP DEV" />

	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	
	<base href="/dev/" />
	
</head>
<body>

	<!--
	<template q-tpl="./ui/struct/header.tpl"></template>
	<div class="flex flex-row bg-gray-200">
		<template q-tpl="./ui/struct/menu.tpl"></template>
		<div class="text-gray-700 px-4 py-2 flex-grow">
			<template :q-tpl="$.ui?.content?.template"></template>
		</div>
	</div>
	<template q-tpl="./ui/struct/footer.tpl"></template>
	-->
	
	<!-- <script type="importmap"><?= file_get_contents(".public/temp/importmap.json"); ?></script> -->
	
	<!-- <script src="/.public/temp/files_state.js"></script> -->
	<script type="module">
		// import '/config.js';
		import '/@djs/core/main.js';
		import '/@djs/core/dnode.js';
		import '/@djs/core/functions.js';
		import '/@djs/core/url-controller.js';
		import '/@djs/core/data-proxy.js';
		import '/@djs/core/api-data.js';
		import '/@djs/core/regex.js';
		
		// import '/ui/_url/index.js';
		// import '/ui/res/script.js';
	</script>
	
	<!-- test-only -->
	
	<div class="thingstocache">
		<div>
			<h3>Test Binds</h3>

			<input type="text" :@value="test_binds" autocomplete="off" />

			<div>
				The value is: <span q-text="test_binds ? test_binds : '<i>Empty</i>'" style="font-weight: bold;"></span>
			</div>

		</div>

	</div>
	
	<!-- globalThis.$_files_state_ = ... -->
	<!-- <script><?= file_get_contents('.public/temp/files_state.js'); ?></script> -->
	
	<!-- test-only -->
	<link rel="stylesheet" href="ui/res/style.css" />

</body>
</html>
