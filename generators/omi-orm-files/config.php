<?= "<?php\n"; ?>

const dev_ip = '10.0.2.2';
const dev_ip_vpn = '142.132.176.11';
const dev_email = 'ealexs@gmail.com';
const Q_DEFAULT_ENCRYPT_KEY = 'kLhZG2Ngwq+0wisB';
const Default_Logo = "uploads/branding/logos/logo.png";

const BASE_HREF = '/dev-app/@orm/';

const TF_VERSION_2 = true;

const Q_SAAS_PREFIX = 'test';
# const Q_Gen_Namespace = "Omi\\TF\\Provision\\View";

const Q_DEV_MODE_KEY = 'b74a0deb77940451686a736ed432f1833807f070';

const Q_IS_TFUSE = true;

const MyProject_MysqlUser = "alex";
const MyProject_MysqlPass = 'Palm25tree!';
if (!defined('MyProject_MysqlDb'))
	define('MyProject_MysqlDb', "tf_provision_2023");

const Q_USE_XSS_INPUT_PROTECTION = false;
