function activateScene(sceneName) {
  $.post("index.php", { action: "activate_scene", sceneName: sceneName },
    function(data) {
      var stat = eval(data);
      if (stat[0] == 'success') {
      } else {
        alert("Error:  " + data);
      }
    }
  );
  return;
}

var curScene = '';
function sceneSelect(scene) {
  var sceneId = $(scene).attr('id');
  getSceneStatus(sceneId);
  $("#" + curScene).css("background-color","#ffffff");
  $("#" + sceneId).css("background-color","#ccffff");
  curScene = sceneId;
  //$("#sceneStatus").html(sceneId);
}

function getSceneStatus(sceneId) {
  $.post("index.php", { action: "get_scene", sceneId: sceneId },
    function(data) {
      var stat = eval(data);
      if (stat[0] == 'success') {
        $("#sceneStatus").html(stat[1]);
      } else {
        alert("Error:  " + data);
      }
    }
  );

  return;
}
