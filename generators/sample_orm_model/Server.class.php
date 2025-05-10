<?php

namespace Omi\TF\Provision;

/**
 * @storage.table Servers
 *
 * @model.captionProperties Code,IP
 * 
 * @class.name Server
 */
abstract class Server_tf_prov_model_ extends \QModel
{
	/**
	 * @var string
	 */
	protected $Code;
	/**
	 * @var string
	 */
	protected $IP;
	/**
	 * @var string
	 */
	protected $Private_IP;
	/**
	 * @var string
	 */
	protected $SSH_Fingerprint;
	/**
	 * @var string
	 */
	protected $Provider_Code;
	/**
	 * @var string
	 */
	protected $Provision_User;
	
	/**
	 * @storage.type TEXT
	 * 
	 * @var string
	 */
	protected $Public_Key;
	
	/**
	 * @storage.type TEXT
	 * 
	 * @var string
	 */
	protected $Private_Key;
	
	/**
	 * @storage.type LONGTEXT
	 * 
	 * @var string
	 */
	protected $Provision_Log;
	
	/**
	 * @storage.optionsPool Servers
	 * 
	 * @var Server
	 */
	protected $Copy_DB_From_Server;
	/**
	 * @var string
	 */
	protected $Copy_DB_From_File;
	/**
	 * @var string
	 */
	protected $Copy_DB_Remote_Name;
	/**
	 * @var string
	 */
	protected $Copy_DB_Local_Name;
	
	public function getModelCaption($view_tag = null)
	{
		return $this->Code . ' , ip=' . $this->IP;
	}
}
