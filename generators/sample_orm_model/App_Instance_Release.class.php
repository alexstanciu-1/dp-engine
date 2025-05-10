<?php

namespace Omi\TF\Provision;

/**
 * @storage.table App_Instance_Releases
 *
 * @class.name App_Instance_Release
 */
abstract class App_Instance_Release_tf_prov_model_ extends \QModel
{
	/**
	 * @var string
	 */
	protected $Version;
	/**
	 * @var boolean
	 */
	protected $Provision;
}