<?php

namespace std\compiler;

require_once __DIR__ . '/def_base.php';
require_once __DIR__ . '/def_class.php';
require_once __DIR__ . '/def_root.php';

/*

So what's the logic we will follow ?!


- collect data
- generate files for various systems (orm, ui, ...)

- data changes -
	update data, see changes , trigger only the minimal updates

	ACTION 1 -> from files/json -> `compiled` class as object definition
			 -> push back changes to the root element
	ACTION 2 -> for ORM
			 -> for generator (UI), ... other generators
			 -> for UI
			 -> for cron, ...

1. scan for file changes
		put the info in a file
		compute changes
	(done)

2. update per file cache for changed items
	(done)

3. merge data (join from multi-files, join layers, extends, use-traits)
	- join files (if file-split)
	- extends + traits

LOOP (1-2 times - if there are still changes, we have a problem)
	4. inform generators of changes and let them work
	(repeat this step 1-2 times)

 */


# able to start a "transaction" and get the list of files that were used inside the transaction

# support for layers
# support for files/split
# support for multi-polymorphism
# support for multi-generate system | info[level_1] => generate for level_2 => generate
# if a dir was not modifed, and had no sub-dirs - don't scan it :-)
# info may be `inherited` from, ex: model info from type
# if a folder only has one file base name and it has the same basename it will not add to the namespace !

final class def extends def_base
{
	
}

/*
final class def_categ
{
	# ui, model, type ... 
	public function __get(string $property): mixed
	{
		# will return def_class
		# return $this->_data_->__get($property);
	}
}
*/
