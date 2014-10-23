<?php
//require('config.php');
require('functions.php');

error_reporting(E_ERROR | E_WARNING | E_PARSE);

check_modules();
$config = parse_ini_file('config.ini',true);
if (! $config) {
?>
<html>
<head>
<title>InControl Web - Error</title>
<h2>Error</h2>
<p>The configuration fine config.ini is missing or not readable.</p><p>If this is a new installation,
 or an upgrade from a previous version, please copy or rename config.ini.sample to config.ini
 and enter the appropriate values.</p><p>If this is an upgrade, you may delete config.php and config.php.sample
 they are no longer used.</p>
<?php
exit;
}

$action = '';
if (isset($_REQUEST['action'])) {
  $action = $_REQUEST['action'];
}

switch ($action)
{
  case 'devices':
    devices();
    break;

  case 'get_devices':
    get_devices();
    break;

  case 'get_device_status':
    get_device_status();
    break;

  case 'set_device_state':
    set_device_state();
    break;

  case 'set_thermo_setpoint':
    set_thermo_setpoint();
    break;

  case 'set_thermo_system_mode':
    set_thermo_system_mode();
    break;

  case 'set_thermo_fan_mode':
    set_thermo_fan_mode();
    break;

  case 'scenes':
    scenes();
    break;

  case 'get_scene':
    get_scene();
    break;

  case 'activate_scene':
    activate_scene();
    break;

  default:
    devices();
    break;
}

?>
