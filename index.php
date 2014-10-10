<?php
require('config.php');
require('functions.php');

$action = '';
if (isset($_REQUEST['action'])) {
  $action = $_REQUEST['action'];
}

switch ($action)
{
  case 'home':
    home();
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

  default:
    home();
    break;
}

?>
