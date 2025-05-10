
// console.log("aaaaaa", process.argv);
// import {DLib} from './../../src/core/main.js';

// console.log(process.argv[2]);

// import(process.argv[2]);
// import { aaaa } from process.argv[2];

var exception = null;
var showPageContent = null;
var $t1 = null;
var $t2 = null;
try
{
	$t1 = Date.now();
	showPageContent = await import(process.argv[2]);
	$t2 = Date.now();
}
catch (ex) {
	exception = ex;
}
finally {
	
	const data_to_export = {took: ($t2 - $t1), data: exception ? false : showPageContent.default, error: (exception ? {message: exception.message, stack: exception.stack } : null)};
	
	console.log(JSON.stringify(data_to_export, function(key, value){
				return (typeof value === 'function' ) ? value.toString() : value;
			}));
}
/*
if (process.argv[2] !== undefined)
{
	// console.log(DLib);
}
*/