$(document).ready(
  function() {
    getDevices();
    setInterval(function() {
      getDevices();
      if (curDev != '' && curDevType != '3') {
        getDeviceStatus(curDev);
      }
    }, 5000);
  }
);

var curDev = '';
var curDevType = '';
function devSelect(dev) {
  var devId = $(dev).attr('id');
  getDeviceStatus(devId);
  $("#" + curDev).css("background-color","#ffffff");
  $("#" + devId).css("background-color","#ccffff");
  curDev = devId;
}

function getDeviceStatus(devId) {
  $.post("index.php", { action: "get_device_status",id: devId },
    function(data) {
      var stat = eval(data);
      if (stat[0] == 'success') {
        $("#deviceStatus").html(stat[1]);
        curDevType = stat[2];
      } else {
        alert("Error:  " + stat[1]);
      }
    }
  );

  return;
}

function toggleDev(id,curLevel,maxLevel,dimLevel) {
  $('#devices').css('cursor','wait');
  $.post("index.php", { action: "set_device_state",id: id, cur_level: curLevel,max_level: maxLevel,dim_level: dimLevel },
    function(data) {
      var stat = eval(data);
      if (stat[0] == 'success') {
        getDevices();
        getDeviceStatus(id);
        $('#devices').css('cursor','default');
      } else {
        $('#devices').css('cursor','default');
        alert("Error:  " + stat[1]);
      }
    }
  );

  return;
}

function getDevices() {
  $.post("index.php", { action: "get_devices", curDev: curDev },
    function(data) {
      var stat = eval(data);
      if (stat[0] == 'success') {
        $("#devices").html(stat[1]);
      } else {
        alert("Error:  " + data);
      }
    }
  );

  return;
}

function thermoTempChange(nodeId,setPoint,name) {
  //alert(nodeId + ',' + setPoint);
  $.post("index.php", { action: "set_thermo_setpoint", nodeId: nodeId, name: name, value: setPoint },
    function(data) {
      var stat = eval(data);
      if (stat[0] == 'success') {
//        alert(stat[1]);
        //getDeviceStatus(nodeId);
      } else {
        alert("Error:  " + data);
      }
    }
  );

  return;

}

function thermoFanMode(nodeId,e) {
  var mode = $(e).val();
  $.post("index.php", { action: "set_thermo_fan_mode", nodeId: nodeId, mode: mode },
    function(data) {
      var stat = eval(data);
      if (stat[0] == 'success') {
//        alert(stat[1]);
      } else {
        alert("Error:  " + data);
      }
    }
  );

  return;
}

function thermoSystemMode(nodeId,e) {
  var mode = $(e).val();
  $.post("index.php", { action: "set_thermo_system_mode", nodeId: nodeId, mode: mode },
    function(data) {
      var stat = eval(data);
      if (stat[0] == 'success') {
//        alert(stat[1]);
      } else {
        alert("Error:  " + data);
      }
    }
  );

  return;
}

