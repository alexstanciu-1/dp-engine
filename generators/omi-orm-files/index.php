<?= "<?php\n"; ?>

# config
	define('Q_CODE_DIR', dirname( __DIR__ ) . "/");
	const Q_RUNNING_PATH = __DIR__ . "/";
	const Q_CONFIG_FILE = __DIR__ . "/config.php";
# end config

chdir(Q_RUNNING_PATH);

require_once("../../../libs/omi-frame/src/init.php");
require_once("../../../libs/omi-frame/src/init_saas.php");

if (!in_array($_SERVER['REMOTE_ADDR'], [dev_ip, dev_ip_vpn, '127.0.0.1', '::1']))
{
	header('HTTP/1.1 401 Unauthorized');
	echo '401 Unauthorized';
	exit;
}

\QApp::Run(new \Omi\View\Controller());
