<?php

session_start();

function display_page($header='',$content='') {
?>
<html>
<head>
<title>InControl Web</title>
<link href="styles.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/jquery.js"></script>
<link rel="stylesheet" href="js/jquery-ui/jquery-ui.css">
<script src="js/jquery-ui/jquery-ui.js"></script>
<?php print $header; ?>
</head>
<body>
<div id="main">
<div id="logo"><img source="images/InControl.png"></div>
<div id="menu">
<ul>
<li><a href="index.php?action=devices">Devices</a></li>
<li><a href="index.php?action=scenes">Scenes</a></li>
</ul>
</div>
<div id="content">
<?php print $content; ?>
</div>
</div>
</body>
</html>
<?php
}

function devices() {
  $header = '<script type="text/javascript" src="js/devices.js"></script>';
  $content = '<div id="devices"></div>'
           . '<div id="deviceStatus"></div>';
  display_page($header,$content);
}

function get_devices() {
  global $host,$port,$pass;
  $cur_dev = '';
  if (isset($_POST['curDev'])) $cur_dev = $_POST['curDev'];
  $devices = array();
  $url="http://".$host.":".$port."/zwave/devices?password=".$pass;
  $json=file_get_contents($url);
  if ($json === false) {
    return false;
  }
  $devices = json_decode($json, true);

  $url="http://".$host.":".$port."/zwave/rooms?password=".$pass;
  $json=file_get_contents($url);
  if ($json === false) {
    return false;
  }
  $roomdata = json_decode($json, true);
  $rooms = array('No Room' => null);
  foreach ($roomdata as $room) {
    $rooms[$room['roomName']] = $room['roomId'];
  } 

  $html = '';
  foreach (array_keys($rooms) as $room) {
    $html .= '<div style="clear:both;"></div><div class="room"><h2>' . $room . '</h2>';
    foreach($devices as $device) {
      $_SESSION[$device['deviceId']] = $device;
      $dev = device_info($device);
      if (isset($dev['visible']) && $dev['visible'] == 'false') continue;
      if($device['roomId'] == $rooms[$room]) {
        $background = '#ffffff'; 
        if ($cur_dev == $device['deviceId']) {
          $background = '#ccffff';
        }
        $html .= '<div class="device" id="' . $device['deviceId'] . '" style="background-color:' . $background . ';" onClick = "devSelect(this)">' 
        . $dev['icon']
        . '</div>';
      }
    }
    $html .= '</div>';
  }
  $data = json_encode(array('success',$html));

  print $data;
}

function get_device_status() {
  global $type_name;
  $id = $_REQUEST['id'];
  $dev = $_SESSION[$id];
//  if ($dev['nodeId'] == 0) $dev['nodeId'] = $dev['providerDeviceId'];
  $html .= '<h1>' . $dev['deviceName'] . '</h1>';
  $type = $dev['deviceType'];
  $date = preg_replace('/\/Date\((.*)-.*\)\//','$1',$dev['lastLevelUpdate']);
  $last_update = date('M j, Y g:iA ',$date/1000);
  switch ($type) {

    case '0':
      $html .= '<p>'
      . 'Type: ' . $type_name[$dev['deviceType']]
      . '<br>Node Id: ' . $dev['nodeId']
      . '<br>Level: ' . $dev['level']
      . '<br>Last Change: ' . $last_update
      . '</p>';
    break;

    case '1':
      $html .= '<p>'
      . 'Type: ' . $type_name[$dev['deviceType']]
      . '<br>Node Id: ' . $dev['nodeId']
      . '<br>Level: ' . $dev['level']
      . '<br>Last Change: ' . $last_update
      . '</p>';
    break;

    case '2':
      $html .= '<p>'
      . 'Type: ' . $type_name[$dev['deviceType']]
      . '<br>Node Id: ' . $dev['nodeId']
      . '<br>Level: ' . $dev['level']
      . '<br>Last Change: ' . $last_update
      . '</p>';
    break;


    case '3':
      $html .= '<p>'
      . 'Type: ' . $type_name[$dev['deviceType']]
      . '<br>Node Id: ' . $dev['nodeId']
      . '<br>Level: ' . $dev['level']
      . '<br>Last Change: ' . $last_update
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
      $tsmode = array('0' => 'Off','1' => 'Heat','2' => 'Cool');
      $html .= '<p><label for="mode">System Mode</label><br><select name="mode" id="mode" onChange="thermoSystemMode(\'' . $dev['deviceId'] . '\',this);">';
      foreach (array_keys($tsmode) as $mode) {
        $selected = '';
        if ($dev['thermostatSystemMode'] == $mode) $selected = ' selected';
        $html .= '<option' . $selected . '>' . $tsmode[$mode] . '</option>';
      }
      $html .= '</select></p>';
      if ($dev['thermostatFanState'] == '0') {
        $auto = ' checked';
        $on = '';
      } else {
        $auto = '';
        $on = ' checked';
      }
      $html .= '<div id="fan">Fan Mode<br><input type="radio" name="fan" value="Auto" onClick="thermoFanMode(\'' 
      . $dev['deviceId'] . '\',this);"' . $auto . '>Auto<br>'
      . '<input type="radio" name="fan" value="On" onClick="thermoFanMode(\'' 
      . $dev['deviceId'] . '\',this);"' . $on . '>On</div>';
//      $resp = put_command('thermoFanState',array('nodeId' => $dev['nodeId']));
//      global $host,$port,$pass;
//      $url="http://".$host.":".$port."/zwave/get_thermostatFanMode?nodeId=" . $dev['deviceId'] . "&password=".$pass;
//      $resp=file_get_contents($url);
//      $html .= '<p>-' . $resp . '</p>';
    break;

    case '6':
      $html .= '<p>'
      . 'Type: ' . $type_name[$dev['deviceType']]
      . '<br>Node Id: ' . $dev['nodeId']
      . '<br>Level: ' . $dev['level']
      . '<br>Last Change: ' . $last_update
      . '</p>';
    break;

    case '11':
      $html .= '<p>'
      . 'Type: ' . $type_name[$dev['deviceType']]
      . '<br>Node Id: ' . $dev['nodeId']
      . '<br>Level: ' . $dev['level']
      . '<br>Last Change: ' . $last_update
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
    $html .= '<p><select name="attribute" id="sr" size="' . $size . '">';
    foreach ($dev['sr'] as $att) {
      $html .= "<option>" . $att['name'] . ": " . $att['value'] . $att['label'] . "</option>";
    }
    $html .= "</select></p>";
  }

  $data = json_encode(array('success',$html,$type));

  print $data;
}

function device_info($dev) {
  $data = array();

//  if ($dev['nodeId'] == 0) $dev['deviceId'] = $dev['providerDeviceId'];
  $type = $dev['deviceType'];
  switch ($type) {
    case '0':
      if ($dev['level'] == 0) {
        $img = 'switchOff.png';
        $stat = '<span style="color:blue;">Off</span>';
      } else {
        $img = 'switchOn.png';
        $stat = '<span style="color:#33cc00;">On</span>';
      }
      $data['icon'] = '<div class="status"'
      . ' onClick="toggleDev(\'' . $dev['deviceId'] . '\',' . $dev['level'] . ',255,255)"><img src="images/' . $img . '"></div>'
      . '<div class="deviceText">' . $dev['deviceName'] . '</div>'
      . '<div class="deviceInfo">' . $stat . '</div>';
    break;

    case '1':
      if ($dev['level'] == 0) {
        $img = 'lightOff.png';
      } else {
        $img = 'lightOn.png';
      }
      $data['icon'] = '<div class="status" id="' . $dev['nodeId'] 
      . '" onClick="toggleDev(\'' . $dev['deviceId'] . '\',' . $dev['level'] . ',255,255)"><img src="images/' . $img . '"></div>'
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
        $stat = '<span style="color:blue;">Off</span>';
      } else {
        $img = 'outletOn.png';
        $stat = '<span style="color:#33cc00;">On</span>';
      }   
      $data['icon'] = '<div class="status" id="' . $dev['nodeId']
      . '" onClick="toggleDev(\'' . $dev['deviceId'] . '\',' . $dev['level'] . ',99,255)"><img src="images/' . $img . '"></div>'
      . '<div class="deviceText">' . $dev['deviceName'] . '</div>'
      . '<div class="deviceInfo">' . $stat . '</div>';
    break;

    case '3':
      $img = 'thermostat.png';
      $fm = array('0' => 'Auto','1' => 'On');
      $tsmode = array('0' => 'Off','1' => 'Heat','2' => 'Cool');
      $data['icon'] = '<div class="status" id="' . $dev['nodeId'] . '">'
      . '<img src="images/' . $img . '"></div>'
      . '<div class="deviceText">' . $dev['deviceName'] . '</div>'
      . '<div class="deviceInfo">' . $dev['currentThermTemp'] . '&deg; - ' . $fm[$dev['thermostatFanState']] 
      . ' - ' . $tsmode[$dev['thermostatSystemMode']] . ' (' . $tsmode[$dev['thermostatSystemState']] . ')' . '</div>';
    break;

    case '4':
      $img = 'controller.png';
      $data['icon'] = '<div class="status" id="' . $dev['nodeId']
      . '"><img src="images/' . $img . '"></div>'
      . '<div class="deviceText">' . $dev['deviceName'] . '</div>';
    break;

    case '6':
      if ($dev['level'] == 0) {
        $img = 'binaryOff.png';
        $stat = '<span style="color:blue;">Closed</span>';
      } else {
        $img = 'binaryOn.png';
        $stat = '<span style="color:#ff0000;">Open</span>';
      }
      $data['icon'] = '<div class="status"><img src="images/' . $img . '"></div>'
      . '<div class="deviceText">' . $dev['deviceName'] . '</div>'
      . '<div class="deviceInfo">' . $stat . '</div>';
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

  $dev = $_SESSION[$id];
  if ($dev['nodeId'] == 0) $id = $dev['providerDeviceId']; 

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

  $url="http://".$host.":".$port . "/zwave/setDeviceState?nodeId=". urlencode($id) . "&powered=" . $powered 
  . "&level=".$level."&password=".$pass;
  $resp=file_get_contents($url);
  $data = json_encode(array('success',$powered));

  print $data;
}

function get_scenes() {
  $json = put_command('getScenes',array(),true);
  if ($json) {
    $s = json_decode($json, true);
    foreach ($s as $scene) {
      $scenes[$scene['sceneId']] = $scene;
    }
    return $scenes;
  }
  return false;
}

function scenes() {
  $header = '<script type="text/javascript" src="js/scenes.js"></script>';
  $content = '<div id="scenes">';

  $scenes = get_scenes();
  if ($scenes) {
    foreach (array_keys($scenes) as $scene) {
      $content .= '<div class="scene" id="' . $scene 
      . '" onClick="sceneSelect(this)">'
      . '<div class="sceneButton"><button onClick="activateScene(\'' . $scenes[$scene]['sceneName'] 
      . '\')">Activate</button></div>' 
      . '<div class="deviceText">' . $scenes[$scene]['sceneName'] . '</div>'
      . '</div>';
    }
  }
  $content .=  '</div><div id="sceneStatus"></div>';

  display_page($header,$content);
}

function get_scene() {
  global $host,$port,$pass;
  $scenes = get_scenes();
  $scene_id = $_POST['sceneId'];
  mwlog(print_r($scenes[$scene_id],true));
  $html = '<h1>' . $scenes[$scene_id]['sceneName'] . '</h1>';
  $json = put_command('getSceneDevices',array('sceneId' => $scene_id),true);
  if ($json) {
    // Get all the devices
    $url="http://".$host.":".$port."/zwave/devices?password=".$pass;
    $json2=file_get_contents($url);
    if ($json2) {
      $d = json_decode($json2, true);
      foreach ($d as $dev) {
        $devices[$dev['deviceId']] = $dev;
      }
    }

    // Loop through all the scene devices
    $html .= '<ul>';
    $d = json_decode($json, true);
    //mwlog(print_r($d,true));
    foreach ($d as $dev) {
      $dd = $devices[$dev['deviceId']];
      $html .= '<li>' . $dd['deviceName'] . '&nbsp;' . $dev['actions'][0]['endValue'] . '</li>';
    }
    $html .= '</ul>';
  }
  $data = json_encode(array('success',$html));

  print $data;
}

function activate_scene() {
  $scene_name = $_REQUEST['sceneName'];
  mwlog('Activate: ' . $scene_id);
  $resp = put_command('activateScene',array('sceneName' => $scene_name),true);

  $data = json_encode(array('success',$resp));
  print $data;
}

function put_command($command,$data=array(),$json=false) {
  global $host,$port,$pass;

  $data['password'] = $pass;
  $ch = curl_init();

  if ($json) {
    // json encoded params
    $fields = json_encode($data);
    $f = http_build_query($data);
    $url="http://" . $host . ":" . $port . "/zwave/" . $command;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS,$fields);
    $cl =  strlen($fields);
  } else {
    // query string params
    $fields = http_build_query($data);
    $url="http://" . $host . ":" . $port . "/zwave/" . $command . '?' . $fields;
    curl_setopt($ch, CURLOPT_URL, $url); 
    curl_setopt($ch, CURLOPT_PUT, true);
    $cl = 0;
  }

  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . $cl)); 

  $response = curl_exec($ch);
  curl_close($ch);
  if(!$response) {
    return false;
  }

  mwlog($response);
  return $response;
}

function check_modules() {
  if (! in_array('curl', get_loaded_extensions())) {
?>
<html>
<head>
<title>InControl Web - Error</title>
</head>
<body>
<p>PHP CURL Module is missing</p>
<p>For Debian distributions (Ubuntu) try <b>apt-get install php5_curl</b></p>
<p>For Red Hat distributions try <b>yum install php_curl</b></p>
<p>For others, check documentation for the particular distribution.</p>
</body>
</html>
<?php
    exit;
  }
  return true;
}

function mwlog($message) {
  $logFile = "api_error.log";
  $fh = fopen($logFile, 'a') or die("Can not open log file. Make sure that api_error.log and your web directory are writable by the web server user.");
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
