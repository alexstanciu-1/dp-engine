<?php

namespace Omi\TF\Provision;

/**
 * @storage.table App_Releases
 * @model.captionProperties Version
 *
 * @class.name App_Release
 */
abstract class App_Release_tf_prov_model_ extends \QModel
{
	/**
	 * @var string
	 */
	protected $Version;
	
	public function getModelCaption($view_tag = null)
	{
		return $this->Version;
	}
}
