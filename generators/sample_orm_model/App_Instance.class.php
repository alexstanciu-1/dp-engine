<?php

namespace Omi\TF\Provision;

/**
 * @storage.table App_Instances
 *
 * @class.name App_Instance
 */
abstract class App_Instance_tf_prov_model_ extends \QModel
{
	/**
	 * @var string
	 */
	protected $Linux_User;
	/**
	 * @var string
	 */
	protected $Web_Domain;
	
	/**
	 * @var string
	 */
	protected $Version;
	/**
	 * @storage.optionsPool App_Releases
	 * @storage.filter ["@CALL" => "Omi\TF\Provision\App_Release::Get_Releases"]
	 * @storage.no_default_row true
	 * 
	 * @var App_Release
	 */
	protected $Current_Release;
	
	/**
	 * @var boolean
	 */
	protected $Force_Mode;
	/**
	 * @validation mandatory
	 * 
	 * @storage.type enum('startup','premium')
	 * 
	 * @var string
	 */
	protected $Package_Type;
	/**
	 * 
	 * @storage.optionsPool Servers
	 * 
	 * @var Server
	 */
	protected $Deploy_Server;
	
	/**
	 * @storage.type LONGTEXT
	 * 
	 * @var string
	 */
	protected $Provision_Log;
	
	/**
	 * @var boolean
	 */
	protected $Copy_From_Old;
	/**
	 * @storage.optionsPool Servers
	 * 
	 * @var Server
	 */
	protected $Copy_From_Old_Server;
	/**
	 * @var boolean
	 */
	protected $Disable_On_Old;
	
	/**
	 * @storage.dependency subpart
	 * 
	 * @var App_Instance_Release[]
	 */
	protected $Releases;
		
	/**
	 * Gets a default for a listing selector if none was specified
	 * 
	 * @return string
	 */
	public static function GetListingQuery($selector = null)
	{
		$selector = $selector ?: static::GetListingEntity();
		
		$q = (is_array($selector) ? qImplodeEntity($selector) : $selector)." "
	
				. " WHERE 1 "
				. " ??Id?<AND[Id=?]"
				
				. " ??QINSEARCH_Linux_User?<AND[Linux_User LIKE (?)]"
				. " ??QINSEARCH_Web_Domain?<AND[Web_Domain LIKE (?)]"
				. " ??QINSEARCH_Version?<AND[Version LIKE (?)]"
				. " ??QINSEARCH_Package_Type?<AND[Package_Type LIKE (?)]"
				. " ??QINSEARCH_Deploy_Server?<AND[(Deploy_Server.Code LIKE (?))]"
				
				#. " ??WHR_Search?<AND[(City.Name LIKE (?) OR County.Name LIKE (?) OR Country.Name LIKE (?) OR PostCode LIKE (?) "
				#	. "OR Street LIKE (?) OR StreetNumber LIKE (?) OR Organization LIKE (?) OR Premise LIKE (?) OR Building LIKE (?) OR BuildingPart LIKE (?))]"
			/*. " ORDER BY "
					. "??OBY_CountryName?<,[Country.Name ?@]"
					. "??OBY_CountryCode?<,[Country.Code ?@]"
					. "??OBY_CountyName?<,[County.Name ?@]"
					. "??OBY_CountyCode?<,[County.Code ?@]"	
					. "??OBY_CityName?<,[City.Name ?@]"
					. "??OBY_CityCode?<,[City.Code ?@]"	*/
			. " ??LIMIT[LIMIT ?,?]";
		
		return $q;
	}
}
