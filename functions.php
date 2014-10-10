<?php

session_start();
function home() {
?>
<html>
<head>
<title>inControl</title>
<link href="styles.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/ic.js"></script>
<link rel="stylesheet" href="js/jquery-ui/jquery-ui.css">
<script src="js/jquery-ui/jquery-ui.js"></script>
</head>
<body>
<div id="content">
<div id="devices"></div>
<div id="deviceStatus"></div>
</div>
</body>
</html>
<?php
}

function get_devices() {
  global $host,$port,$pass;
  $cur_dev = $_POST['curDev'];
  $devices = array();
  $url="http://".$host.":".$port."/zwave/devices?password=".$pass;
  $json=file_get_contents($url);
  if ($json === false) {
    return false;
  }
  $devices = json_decode($json, true);

  $html = '';
  foreach($devices as $device) {
    $_SESSION[$device['deviceId']] = $device;
    $dev = device_info($device);
    if ($dev['visible'] == 'false') continue;
    $background = '#ffffff'; 
    if ($cur_dev == $device['deviceId']) {
      $background = '#ccffff';
    }
    $html .= '<div class="device" id="' . $device['deviceId'] . '" style="background-color:' . $background . ';" onClick = "devSelect(this)">' 
    . $dev['icon']
    . '</div>';
  }
  $data = json_encode(array('success',$html));

  print $data;
}

function get_device_status() {
  global $type_name;
  $id = $_REQUEST['id'];
  $dev = $_SESSION[$id];
  $html .= '<h1>' . $dev['deviceName'] . '</h1>';
  $type = $dev['deviceType'];
  switch ($type) {

    case '0':
      $html .= '<p>'
      . 'Type: ' . $type_name[$dev['deviceType']]
      . '<br>Node Id: ' . $dev['nodeId']
      . '<br>Level: ' . $dev['level']
      . '</p>';
    break;

    case '1':
      $html .= '<p>'
      . 'Type: ' . $type_name[$dev['deviceType']]
      . '<br>Node Id: ' . $dev['nodeId']
      . '<br>Level: ' . $dev['level']
      . '</p>';
    break;

    case '3':
      $html .= '<p>'
      . 'Type: ' . $type_name[$dev['deviceType']]
      . '<br>Node Id: ' . $dev['nodeId']
      . '<br>Level: ' . $dev['level']
      . '</p>';

      $data = array();
      foreach ($dev['sr'] as $val) {
        $data[$val['name']] = $val['value'];
      } 
      $html .= '<script>'
      . 'var tempSet = $("#tempSet").spinner();$("#tempSet").spinner({ min: 55 });'
      . '$("#tempSet").spinner({ max: 99 });'
      . '$("#tempSet").spinner("value",' . $data['Heating1'] . ');'
      . '$("#tempSet").spinner({ change: function( event, ui ) {thermoTempChange(\'' . $dev['deviceId'] . '\',$("#tempSet").spinner("value"),"Heating1");} });'
      . 'var tempCool = $("#tempCool").spinner();$("#tempCool").spinner({ min: 55 });'
      . '$("#tempCool").spinner({ max: 99 });'
      . '$("#tempCool").spinner("value",' . $data['Cooling1'] . ');'
      . '$("#tempCool").spinner({ change: function( event, ui ) {thermoTempChange(\'' . $dev['deviceId'] . '\',$("#tempCool").spinner("value"),"Cooling1");} });'
      //. '$("#mode").selectmenu();'
      //. '$("#mode").selectmenu({ width: 100 });'
      //. '$("#mode").selectmenu({ change: function( event, ui ) {thermoMode($("#mode option:selected").val());} });'
      . '</script>'
      . '<p><table class="thermo"><tr><th>Heat</th><th>Cool</th></tr>'
      . '<tr><td><input id="tempSet" name="tempSet"></td>'
      . '<td><input id="tempCool" name="tempCool"></td></tr></table></p>';
      $html .= '<p><label for="mode">System Mode</label><br><select name="mode" id="mode" onChange="thermoSystemMode(\'' . $dev['deviceId'] . '\',this);">'
      . '<option>Off</option>'
      . '<option>Heat</option>'
      . '<option>Cool</option>'
      . '<option>Auto</option>'
      . '</select></p>';
      $html .= '<div id="fan">Fan Mode<br><input type="radio" name="fan" value="Auto" onClick="thermoFanMode(\'' . $dev['deviceId'] . '\',this);">Auto<br>'
      . '<input type="radio" name="fan" value="On" onClick="thermoFanMode(\'' . $dev['deviceId'] . '\',this);">On</div>';
//      $resp = put_command('thermoFanState',array('nodeId' => $dev['nodeId']));
//      global $host,$port,$pass;
//      $url="http://".$host.":".$port."/zwave/get_thermostatFanMode?nodeId=" . $dev['deviceId'] . "&password=".$pass;
//      $resp=file_get_contents($url);
//      $html .= '<p>-' . $resp . '</p>';
    break;

    case '11':
      $html .= '<p>'
      . 'Type: ' . $type_name[$dev['deviceType']]
      . '<br>Node Id: ' . $dev['nodeId']
      . '<br>Level: ' . $dev['level']
      . '</p>';
    break;

    default:
      $html .= '<p>'
      . 'Type: ' . $type_name[$dev['deviceType']]
      . '<br>Node Id: ' . $dev['nodeId']
      . '</p>';
    break;
  }

  if ($dev['sr'] != null) {
    $size = sizeof($dev['sr']);
    $html .= '<select name="attribute" size="' . $size . '">';
    foreach ($dev['sr'] as $att) {
      $html .= "<option>" . $att['name'] . ": " . $att['value'] . $att['label'] . "</option>";
    }
    $html .= "</select>";
  }

  $data = json_encode(array('success',$html,$type));

  print $data;
}

function device_info($dev) {
  $data = array();

  $type = $dev['deviceType'];
  switch ($type) {
    case '0':
      if ($dev['level'] == 0) {
        $img = 'switchOff.png';
      } else {
        $img = 'switchOn.png';
      }
      $data['icon'] = '<div class="status"'
      . ' onClick="toggleDev(\'' . $dev['deviceId'] . '\',' . $dev['level'] . ',255,255)"><img src="images/' . $img . '"></div>'
      . '<div class="deviceText">' . $dev['deviceName'] . '</div>';
    break;

    case '1':
      if ($dev['level'] == 0) {
        $img = 'lightOff.png';
      } else {
        $img = 'lightOn.png';
      }
      $data['icon'] = '<div class="status" id="' . $dev['nodeId'] 
      . '" onClick="toggleDev(\'' . $dev['deviceId'] . '\',' . $dev['level'] . ',99,255)"><img src="images/' . $img . '"></div>'
      . '<div class="deviceText">' . $dev['deviceName'] . '</div>'
      . '<div id="dim_' . $dev['nodeId'] . '" style="width:120px;font-size:10px;float:left;margin:10px 0 0 10px;"></div>'
      . '<span class="levelText" id="level_' . $dev['nodeId'] . '">' . $dev['level'] . '</span>'
      . '<script>'
      . '$("#dim_' . $dev['nodeId'] . '").slider();'
      . '$("#dim_' . $dev['nodeId'] . '").slider({ max: 99 });'
      . '$("#dim_' . $dev['nodeId'] . '").slider("value",' . $dev['level'] . ');'
      . '$("#dim_' . $dev['nodeId'] . '").slider({ change: function( event, ui ) {'
      . 'toggleDev(\'' . $dev['deviceId'] . '\',' . $dev['level'] . ',99,ui.value) } });'
      . '</script>';
    break;

    case '2':
      if ($dev['level'] == 0) {
        $img = 'outletOff.png';
      } else {
        $img = 'outletOn.png';
      }   
      $data['icon'] = '<div class="status" id="' . $dev['nodeId']
      . '" onClick="toggleDev(\'' . $dev['deviceId'] . '\',' . $dev['level'] . ',255,255)"><img src="images/' . $img . '"></div>'
      . '<div class="deviceText">' . $dev['deviceName'] . '</div>';
    break;

    case '3':
      $cur_temp = '';
      if ($dev['sr'] != null) {
        foreach ($dev['sr'] as $att) {
          if (strpos($att['name'],'Temperature') === 0) $cur_temp = $att['value'] . $att['label'];
        }
      }
      $data['icon'] = '<div class="status" id="' . $dev['nodeId'] . '">'
      . '<div class="textIcon">' . $cur_temp . '</div></div>'
      . '<div class="deviceText">' . $dev['deviceName'] . '</div>';
    break;

    case '4':
      $img = 'controller.png';
      $data['icon'] = '<div class="status" id="' . $dev['nodeId']
      . '"><img src="images/' . $img . '"></div>'
      . '<div class="deviceText">' . $dev['deviceName'] . '</div>';
    break;

    case '9':
      $img = 'sensor.png';
      $data['icon'] = '<div class="status" id="' . $dev['nodeId']
      . '"><img src="images/' . $img . '"></div>'
      . '<div class="deviceText">' . $dev['deviceName'] . '</div>';
    break;

    case '11':
      $data['icon'] = '<div class="status" id="' . $dev['nodeId'] . '">'
      . '<div class="textIcon">' . number_format($dev['level'],1) . '</div></div>'
      . '<div class="deviceText">' . $dev['deviceName'] . '</div>';
    break;

    default:
      $data['icon'] = '<div class="status" id="' . $dev['nodeId'] . '">'
      . '<div class="textIcon">?</div></div>'
      . '<div class="deviceText">' . $dev['deviceName'] . '</div>';
    break;
  }

  return $data;
}

function set_thermo_setpoint() {
  $nodeId = $_POST['nodeId'];
  $value = $_POST['value'];
  $name = $_POST['name'];

  $resp = put_command('thermoSetPoint',array('nodeId' => $nodeId,'setPointName' => $name,'temperatureValue' => $value));
  $data = json_encode(array('success',$resp));

  print $data;
}

function set_thermo_system_mode() {
  $mode = $_POST['mode'];
  $nodeId = $_POST['nodeId'];
  if ($mode == 'Auto' || $mode == 'Heat' || $mode == 'Cool' || $mode == 'Off') {
    $resp = put_command('thermoSetSystemMode',array('nodeId' => $nodeId,'systemMode' => $mode));
  }

  $data = json_encode(array('success',$resp));

  print $data;
}

function set_thermo_fan_mode() {
  $mode = $_POST['mode'];
  $nodeId = $_POST['nodeId'];
  if ($mode == 'Auto' || $mode == 'On') {
    $resp = put_command('thermoSetFanMode',array('nodeId' => $nodeId,'fanMode' => $mode));
  }

  $data = json_encode(array('success',$resp));

  print $data;
}

function set_device_state() {
  global $host,$port,$pass;

  $id = $_REQUEST['id'];
  $cur_level = $_REQUEST['cur_level'];
  $max_level = $_REQUEST['max_level'];
  $dim_level = $_REQUEST['dim_level'];

  if ($dim_level == '255') {
    if ($cur_level == '0') {
      $level = $max_level;
      $powered = 'True';
    } else {
      $level = 0;
      $powered = 'False';
    }
  } else {
    if ($dim_level == '0') {
      $level = 0;
      $powered = 'False';
    } else {
      $level = $dim_level;
      $powered = 'True';
    }  
  } 

  $url="http://".$host.":".$port."/zwave/setDeviceState?nodeId=".$id."&powered=" . $powered . "&level=".$level."&password=".$pass;
  $resp=file_get_contents($url);
  $data = json_encode(array('success',$powered));

  print $data;
}

function put_command($command,$data) {
  global $host,$port,$pass;

  $data['password'] = $pass;

  $fields = http_build_query($data);
  $url="http://" . $host . ":" . $port . "/zwave/" . $command . '?' . $fields;

  $ch = curl_init($url);
 
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_PUT, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: ' . strlen($fields))); 
//  curl_setopt($ch, CURLOPT_POSTFIELDS, $fields;

  $response = curl_exec($ch);
  if(!$response) {
    return false;
  }

  mwlog($response);
  return $response;
}

function mwlog($message) {
  $logFile = "api_error.log";
  $fh = fopen($logFile, 'a') or die("can't open file");
  fwrite($fh,date('c') . ' ' . $message . "\n");
  fclose($fh);
}

//Map device type name
$type_name = array(0 => 'StandardSwitch',
1 => 'DimmerSwitch',
2 => 'PowerOutlet',
3 => 'Thermostat',
4 => 'Controller',
5 => 'Unknown',
6 => 'BinarySensor',
7 => 'ZonePlayer',
8 => 'MotionSensor',
9 => 'MultiLevelSensor',
10 => 'EntryControl',
11 => 'LevelDisplayer',
12 => 'NotLicensed',
13 => 'IpCamera',
14 => 'EnergyMonitor',
15 => 'Alarm',
15 => 'Fan');

?>
