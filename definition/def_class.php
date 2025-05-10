<?php

namespace std\compiler;

final class def_class extends def_base
{
	public function __get(string $property): mixed
	{
		# will return def
		# return $this->_data_->__get($property);
	}
}
