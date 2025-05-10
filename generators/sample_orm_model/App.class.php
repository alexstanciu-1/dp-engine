<?php

namespace Omi;

/**
 * @storage.table $App
 *
 * @class.name App
 */
abstract class App_tf_prov_model_ extends App_mods_model_
{
	/**
	 * @var TF\Provision\Server[]
	 */
	protected $Servers;
	/**
	 * @var TF\Provision\App_Instance[]
	 */
	protected $App_Instances;
	/**
	 * @var TF\Provision\App_Release[]
	 */
	protected $App_Releases;
	
	/**
	 * Returns the entity that will be used when listing views are generated
	 * @param string $viewTag
	 */
	public static function GetEntityForGenerateList($viewTag = null)
	{
		$ret = null;
		switch ($viewTag)
		{
			case "App_Instances":
			{
				return qParseEntity("Linux_User,Web_Domain,Version,Package_Type,Deploy_Server");
			}
			case "Servers":
			{
				return qParseEntity("Code,Provider_Code,IP,Private_IP,Provision_User");
			}
			case "App_Releases":
			{
				return qParseEntity("Version");
			}
			// DEFAULT
			default :
			{
				$ret = parent::GetEntityForGenerateList($viewTag);
				break;
			}
		}
		
		// return properties
		return $ret;
	}
	
	/**
	 * Returns the entity that will be used when listing views are generated
	 * @param string $viewTag
	 */
	public static function GetEntityForGenerateForm($viewTag = null)
	{
		$ret = null;
		switch ($viewTag)
		{
			case "App_Instances":
			{
				return qParseEntity("Linux_User,Web_Domain,Version,Current_Release,Package_Type,Deploy_Server,Provision_Log,Force_Mode,Copy_From_Old,Copy_From_Old_Server,Disable_On_Old,Releases.{Version,Provision}");
			}
			case "Servers":
			{
				return qParseEntity("Code,Provider_Code,IP,Private_IP,Provision_User,SSH_Fingerprint,Public_Key,Private_Key,Provision_Log,
									Copy_DB_From_Server,Copy_DB_From_File,Copy_DB_Remote_Name,Copy_DB_Local_Name");
			}
			case "App_Releases":
			{
				return qParseEntity("Version");
			}
			// DEFAULT
			default :
			{
				$ret = parent::GetEntityForGenerateForm($viewTag);
				break;
			}
		}
		
		// return properties
		return $ret;
	}
	
	/**
	 * Returns the entity that will be used when listing views are generated
	 * @param string $viewTag
	 */
	public static function GetFormEntity($viewTag = null)
	{
		$ret = null;
		switch ($viewTag)
		{
			case "App_Instances":
			{
				return qParseEntity("Linux_User,Web_Domain,Version,Package_Type,Deploy_Server.{Code,IP}");
			}
			// DEFAULT
			default :
			{
				$ret = parent::GetEntityForGenerateForm($viewTag);
				break;
			}
		}
		
		// return properties
		return $ret;
	}
	
	/**
	 * Returns the entity that will be used when listing views are generated
	 * @param string $viewTag
	 */
	public static function GetListEntity($viewTag = null)
	{
		$ret = null;
		switch ($viewTag)
		{
			case "App_Instances":
			{
				return qParseEntity("Linux_User,Web_Domain,Version,Package_Type,Deploy_Server.{Code,IP}");
			}
			// DEFAULT
			default :
			{
				$ret = parent::GetEntityForGenerateForm($viewTag);
				break;
			}
		}
		
		// return properties
		return $ret;
	}
}
