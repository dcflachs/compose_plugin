<?PHP

require_once("/usr/local/emhttp/plugins/compose.manager/php/defines.php");
require_once("/usr/local/emhttp/plugins/compose.manager/php/util.php");

function createComboButton($text, $id, $onClick, $onClickParams, $items) {
  $o = "";

  $o .= "<div class='combo-btn-group'>";
  $o .= "<input type='button' value='$text' class='combo-btn-group-left' id='$id-left-btn' onclick='$onClick($onClickParams);'>";
  $o .= "<section class='combo-btn-subgroup dropdown'>";
  $o .= "<button type='button' class='dropdown-toggle combo-btn-group-right' data-toggle='dropdown'><i class='fa fa-caret-down'></i></button>";
  $o .= "<div class='dropdown-content'>";
  foreach ( $items as $item )
  {
    $o .= "<a href='#' onclick='$onClick($onClickParams, &quot;$item&quot;);'>$item</a>";
  }
  $o .= "</div>";
  $o .= "</section>";
  $o .= "</div>";

  return $o;
}

$vars = parse_ini_file("/var/local/emhttp/var.ini");

$stackstate = shell_exec($plugin_root."/scripts/compose.sh -c list");
$stackstate = json_decode($stackstate, TRUE);

$composeProjects = @array_diff(@scandir($compose_root),array(".",".."));
if ( ! is_array($composeProjects) ) {
  $composeProjects = array();
}
$o = "";
foreach ($composeProjects as $project) {
  if ( ( ! is_file("$compose_root/$project/docker-compose.yml") ) &&
       ( ! is_file("$compose_root/$project/indirect") ) ) {
    continue;
  }

  $projectName = $project;
  if ( is_file("$compose_root/$project/name") ) {
    $projectName = trim(file_get_contents("$compose_root/$project/name"));
  }
  $id = str_replace(".","-",$project);
  $id = str_replace(" ","",$id);

  $isrunning = FALSE;
  $isexited = FALSE;
  $ispaused = FALSE;
  $isrestarting = FALSE;
  $isup = FALSE; 
  foreach ( $stackstate as $entry )
  {
    if ( strcasecmp($entry["Name"], sanitizeStr($projectName)) == 0 ) {
      $isup = TRUE; 
      if ( strpos($entry["Status"], 'running') !== false ) {
        $isrunning = TRUE;
      }

      if ( strpos($entry["Status"], 'exited') !== false ) {
        $isexited = TRUE;
      }

      if ( strpos($entry["Status"], 'paused') !== false ) {
        $ispaused = TRUE;
      }

      if ( strpos($entry["Status"], 'restarting') !== false ) {
        $isrestarting = TRUE;
      }
    }
  }

  if ( is_file("$compose_root/$project/description") ) {
    $description = @file_get_contents("$compose_root/$project/description");
    $description = str_replace("\r","",$description);
    $description = str_replace("\n","<br>",$description);
  } else {
    $description = isset($variables['description']) ? $variables['description'] : "No description<br>($compose_root/$project)";
  }

  $autostart = '';
  if ( is_file("$compose_root/$project/autostart") ) {
    $autostarttext = @file_get_contents("$compose_root/$project/autostart");
    if ( strpos($autostarttext, 'true') !== false ) {
      $autostart = 'checked';
    }
  }

  $profiles = array();
  if ( is_file("$compose_root/$project/profiles") ) {
    $profilestext = @file_get_contents("$compose_root/$project/profiles");
    $profiles = json_decode($profilestext, false);
  }

  $o .= "<tr><td width='30%' style='text-align:initial'>";
  $o .= "<font size='2'><span class='ca_nameEdit' id='name$id' data-nameName='$projectName' data-isup='$isup' data-scriptName=".escapeshellarg($project)." style='font-size:1.9rem;cursor:pointer;color:#ff8c2f;'><i class='fa fa-gear'></i></span>&nbsp;&nbsp;<b><span style='color:#ff8c2f;'>$projectName</span>&nbsp;</b></font>";
  if ( $isup ) {
    if (  $isexited && !$isrunning) {
        $o .= "<i class='fa fa-square stopped red-text' style='margin-left: 5px;'></i>";
    }
    else {
      if ( $isrunning && !$isexited && !$ispaused && !$isrestarting) {
        $o .= "<i class='fa fa-play started green-text' style='margin-left: 5px;'></i>";
      }
      elseif( $ispaused && !$isexited && !$isrunning && !$isrestarting)
      {
        $o .= "<i class='fa fa-pause started orange-text' style='margin-left: 5px;'></i>";
      }
      elseif( $ispaused && !$isexited )
      {
        $o .= "<i class='fa fa-play started orange-text' style='margin-left: 5px;'></i>";
      }
      else
      {
        $o .= "<i class='fa fa-play started red-text' style='margin-left: 5px;'></i>";
      }
    }
  }
  $o .= "<br>";
  $o .= "<span class='ca_descEdit' data-scriptName=".escapeshellarg($project)." id='desc$id'>$description</span>";
  $o .= "</td>";
  $o .= "<td width=25%></td>";
  $buttons = [
    ["Compose Up", "ComposeUp", "up"], 
    ["Compose Down", "ComposeDown", "down"], 
    ["Update Stack", "UpdateStack", "update"]
  ];

  foreach ($buttons as $button)
  {
    $o .= "<td width=5%>";
    if ( $profiles ) {
      // $onclick = $button[1];
      $onClickParams = "&quot;$compose_root/$project&quot;";
      $o .= createComboButton($button[0], "$button[2]-$id", $button[1], $onClickParams, $profiles);
    } else {
      $o .= "<input type='button' value='$button[0]' class='$button[2]-button' id='$button[2]-$id' onclick='$button[1](&quot;$compose_root/$project&quot;);'>";
    }
    $o .= "</td>";
  }
  
  $o .= "<td width=5%>";
  $o .= "<input type='checkbox' class='auto_start' data-scriptName=".escapeshellarg($project)." id='autostart-$id' style='display:none' $autostart>";
  $o .= "</td>";
  $o .= "</tr>";
}
?>

<script src="/plugins/compose.manager/javascript/ace/ace.js" type= "text/javascript"></script>
<script src="/plugins/compose.manager/javascript/js-yaml/js-yaml.min.js" type= "text/javascript"></script>
<script>
var compose_root=<?php echo json_encode($compose_root); ?>;
var caURL = "/plugins/compose.manager/php/exec.php";
var compURL = "/plugins/compose.manager/php/compose_util.php";
var aceTheme=<?php echo (in_array($theme,['black','gray']) ? json_encode('ace/theme/tomorrow_night') : json_encode('ace/theme/tomorrow')); ?>;
const icon_label = <?php echo json_encode($docker_label_icon); ?>;
const webui_label = <?php echo json_encode($docker_label_webui); ?>;
const shell_label = <?php echo json_encode($docker_label_shell); ?>;

$('head').append( $('<link rel="stylesheet" type="text/css" />').attr('href', '<?autov("/plugins/compose.manager/styles/comboButton.css");?>') );

if (typeof swal2 === "undefined") {
    $('head').append( $('<link rel="stylesheet" type="text/css" />').attr('href', '<?autov("/plugins/compose.manager/styles/sweetalert2.css");?>') );
		$.getScript( '/plugins/compose.manager/javascript/sweetalert/sweetalert2.min.js');
}

function basename( path ) {
  return path.replace( /\\/g, '/' ).replace( /.*\//, '' );
}

function dirname( path ) {
  return path.replace( /\\/g, '/' ).replace( /\/[^\/]*$/, '' );
}

$(function() {
  var editor = ace.edit("itemEditor");
  editor.setTheme(aceTheme);
  editor.setShowPrintMargin(false);
})

$(function() {
	$(".tipsterallowed").show();
	$('.ca_nameEdit').tooltipster({
		trigger: 'custom',
		triggerOpen: {click:true,touchstart:true,mouseenter:true},
		triggerClose:{click:true,scroll:false,mouseleave:true},
		delay: 1000,
		contentAsHTML: true,
		animation: 'grow',
		interactive: true,
		viewportAware: true,
		functionBefore: function(instance,helper) {
			var origin = $(helper.origin);
			var myID = origin.attr('id');
			var name = $("#"+myID).html();
      var disabled = $("#"+myID).attr('data-isup') == "1" ? "disabled" : "";
      var notdisabled = $("#"+myID).attr('data-isup') == "1" ? "" : "disabled";
			var stackName = $("#"+myID).attr("data-scriptname");
      instance.content(stackName + "<br> \
                                    <center> \
                                    <input type='button' onclick='editName(&quot;"+myID+"&quot;);' value='Edit Name' "+disabled+"> \
                                    <input type='button' onclick='editDesc(&quot;"+myID+"&quot;);' value='Edit Description' > \
                                    <input type='button' onclick='editStack(&quot;"+myID+"&quot;);' value='Edit Stack'> \
                                    <input type='button' onclick='deleteStack(&quot;"+myID+"&quot;);' value='Delete Stack' "+disabled+"> \
                                    <input type='button' onclick='ComposeLogs(&quot;"+myID+"&quot;);' value='Logs' "+notdisabled+"> \
                                    <input type='button' onclick='ComposeCheckUpdates(&quot;"+myID+"&quot;);' value='Check for Updates'> \
                                    </center>");
		}
	});
  $('.auto_start').switchButton({labels_placement:'right', on_label:"On", off_label:"Off"});
  $('.auto_start').change(function(){
      var script = $(this).attr("data-scriptname");
      var auto = $(this).prop('checked');
      $.post(caURL,{action:'updateAutostart',script:script,autostart:auto});
    });
});

function addStack() {
  var form = document.createElement("div");
  // form.classList.add("swal-content");
  form.innerHTML = `<input type="text" id="stack_name" class="swal-content__input" placeholder="stack_name">
                    <br>
                    <details>
                      <summary style="text-align: left">Advanced</summary>
                      <br>
                      <div class="swal-text">Stack Directory</div>
                      <input type="text" id="stack_path" class="swal-content__input" pattern="\/mnt\/.*\/.*" oninput="this.reportValidity()" title="A path under /mnt/user/ or /mnt/cache/ or /mnt/pool/" style="margin-top: 20px" placeholder="default">
                      <div style="display:none;">
                        <div class="swal-text">Pull From Github</div>
                        <input type="url" id="git_url" class="swal-content__input" style="margin-top: 20px" placeholder="https://github.com/example/repo.git">
                      </div>
                    </details>`;
  swal2({
    title: "Add New Compose Stack",
    text: "Enter in the name for the stack",
    content: form,
    buttons: true,
  }).then((inputValue) => {
    if (inputValue) {
      var new_stack_name = document.getElementById("stack_name").value;
      var new_stack_dir = document.getElementById("stack_path").value;
      var git_url = document.getElementById("git_url").value;
      if (!new_stack_name) {
        swal2({
          title: "Failed to create stack.",
          text: "Stack name unspecified.",
          icon: "error",
        })
      }
      else {
        $.post(
          caURL,
          {action:'addStack',stackName:new_stack_name,stackPath:new_stack_dir},
          function(data) {
            var title = "Failed to create stack.";
            var message = "";
            var icon = "error";
            if (data) {
              var response = jQuery.parseJSON(data);
              if (response.result == "success") {
                title = "Success";
              }
              message = response.message;
              icon = response.result;
            }
            swal2({
              title: title,
              text: message,
              icon: icon,
            }).then(() => {
              location.reload();
            });
          }
        );        
      }
    }
  });
}

function deleteStack(myID) {
  var stackName = $("#"+myID).attr("data-scriptname");
  var project = $("#"+myID).attr("data-namename");
  var element = document.createElement("div")
  element.innerHTML = "Are you sure you want to delete <font color='red'><b>"+project+"</b></font> (<font color='green'>"+compose_root+"/"+stackName+"</font>)?"; 
  swal2({
    content: element,
    title: "Delete Stack?",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  }).then((willDelete) => {
    if (willDelete) {
      $.post(caURL,{action:'deleteStack',stackName:stackName},function(data) {
        if (data) {
          var response = jQuery.parseJSON(data);
          if (response.result == "warning") {
            title = "Success";
            swal2({
              title: "Files remain on disk.",
              text: response.message,
              icon: "warning",
            }).then(() => {
              location.reload();
            });
          } else {
            location.reload();
          }
        } else {
            location.reload();
        }
      });
    }
  });
}

function stripTags(string) {
	return string.replace(/(<([^>]+)>)/ig,"");
}

function editName(myID) {
	// console.log(myID);
  var currentName = $("#"+myID).attr("data-namename");
  $("#"+myID).attr("data-originalName",currentName);
  $("#"+myID).html("<input type='text' id='newName"+myID+"' value='"+currentName+"'><br><font color='red' size='4'><i class='fa fa-times' aria-hidden='true' style='cursor:pointer' onclick='cancelName(&quot;"+myID+"&quot;);'></i>&nbsp;&nbsp;<font color='green' size='4'><i style='cursor:pointer' onclick='applyName(&quot;"+myID+"&quot;);' class='fa fa-check' aria-hidden='true'></i></font>");
  $("#"+myID).tooltipster("close");
  $("#"+myID).tooltipster("disable");
}

function editDesc(myID) {
  var origID = myID;
  $("#"+myID).tooltipster("close");
  myID = myID.replace("name","desc");
  var currentDesc = $("#"+myID).html();
  $("#"+myID).attr("data-originaldescription",currentDesc);
  $("#"+myID).html("<textarea id='newDesc"+myID+"' cols='40' rows='5'>"+currentDesc+"</textarea><br><font color='red' size='4'><i class='fa fa-times' aria-hidden='true' style='cursor:pointer' onclick='cancelDesc(&quot;"+myID+"&quot;);'></i>&nbsp;&nbsp;<font color='green' size='4'><i style='cursor:pointer' onclick='applyDesc(&quot;"+myID+"&quot;); ' class='fa fa-check' aria-hidden='true'></i></font>");
  $("#"+origID).tooltipster("enable");
}

function applyName(myID) {
  var newName = $("#newName"+myID).val();
  var project = $("#"+myID).attr("data-scriptname");
  $("#"+myID).html(newName);
  $("#"+myID).tooltipster("enable");
  $("#"+myID).tooltipster("close");
  $.post(caURL,{action:'changeName',script:project,newName:newName},function(data) {
		window.location.reload();
	});
}

function cancelName(myID) {
  var oldName = $("#"+myID).attr("data-originalName");
  $("#"+myID).html(oldName);
  $("#"+myID).tooltipster("enable");
  $("#"+myID).tooltipster("close");
	window.location.reload();
}

function cancelDesc(myID) {
  var oldName = $("#"+myID).attr("data-originaldescription");
  $("#"+myID).html(oldName);
  $("#"+myID).tooltipster("enable");
  $("#"+myID).tooltipster("close");
}

function applyDesc(myID) {
  var newDesc = $("#newDesc"+myID).val();
  newDesc = newDesc.replace(/\n/g, "<br>");
  var project = $("#"+myID).attr("data-scriptname");
  $("#"+myID).html(newDesc);
  $.post(caURL,{action:'changeDesc',script:project,newDesc:newDesc});
}

function editStack(myID) {
  var buttonsList = {};

  buttonsList["compose_file"] = { text: "Compose File" };
  buttonsList["env_file"] = { text: "ENV File" };
  buttonsList["override_file"] = { text: "UI Labels" };
  buttonsList["stack_settings"] = { text: "Stack Settings" };

  buttonsList["Cancel"] = { text: "Cancel", value: null, };
  swal2({
    title: "Select Stack File to Edit",
    className: 'edit-stack-form',
    buttons: buttonsList,
  }).then((result) => {
    if (result) {
      switch(result) {
        case 'compose_file':
          editComposeFile(myID);
          break;
        case 'env_file':
          editEnv(myID);
          break;
        case 'override_file':
          generateOverride(myID);
          break;
        case 'stack_settings':
          editStackSettings(myID);
          break;
        default:
          return;
      }
    }
  });
}

function build_override_input_table( id, value, label, placeholder, disable=false) {
  var disabled = disable ? `disabled` : ``;
  html = `<div style="display:table; width:100%;">`;
  html += `<label for="${id}" style="width:75px; display:table-cell;">${label}</label>`;
  html += `<input type="text" id="${id}" class="swal-content__input" placeholder="${placeholder}" value="${value}" style="width:100%; display:table-cell;" ${disabled}>`;
  html += `</div>`;
  html += `<br>`;

  return html;
}

function override_find_labels( primary, secondary, label ) {
  var value = primary.labels[label] || "";
  if( !value && "labels" in secondary ) {
    value = secondary.labels[label] || "";
  }

  return value;
}

function generateOverride(myID, myProject=null) {
  var project = myProject;
  if( myID ) {
    $("#"+myID).tooltipster("close");
    project = $("#"+myID).attr("data-scriptname");
  }
    
  $.post(caURL,{action:'getOverride',script:project},function(rawOverride) {
    if (rawOverride) {
      var rawOverride = jQuery.parseJSON(rawOverride);
      $.post(caURL,{action:'getYml',script:project},function(rawComposefile) {
        if (rawComposefile) {
          var rawComposefile = jQuery.parseJSON(rawComposefile);

          if( (rawOverride.result == 'success') && (rawComposefile.result == 'success') ) {
            var override_doc = jsyaml.load(rawOverride.content);
            if( !override_doc ) {
              override_doc = { services: {} };
            }
            var main_doc = jsyaml.load(rawComposefile.content);

            for( var service_key in main_doc.services ) {
              if( !(service_key in override_doc.services) ) {
                override_doc.services[service_key] = { 
                  labels: {  
                    <?php echo json_encode($docker_label_managed); ?>: <?php echo json_encode($docker_label_managed_name); ?>,
                  } 
                };
              }
            }

            var html = ``;
            for( var service_key in override_doc.services ) {
              if( service_key in main_doc.services ) {
                var name = main_doc.services[service_key].container_name || service_key;
                html += `<div class="swal-text" style="font-weight: bold; padding-left: 0px; margin-top: 0px;">Service: ${name}</div>`;
                html += `<br>`;

                var icon_value = override_find_labels(override_doc.services[service_key], main_doc.services[service_key], icon_label);
                html += build_override_input_table(`${service_key}_icon`, icon_value, "Icon", "icon");
                
                var webui_value = override_find_labels(override_doc.services[service_key], main_doc.services[service_key], webui_label);
                html += build_override_input_table(`${service_key}_webui`, webui_value, "Web UI", "web ui");

                var shell_value = override_find_labels(override_doc.services[service_key], main_doc.services[service_key], shell_label);
                html += build_override_input_table(`${service_key}_shell`, shell_value, "Shell", "shell");
              }
            }
            var deleted_entries = ``;
            for( var service_key in override_doc.services ) {
              if( !(service_key in main_doc.services) ) {
                var name = override_doc.services[service_key].container_name || service_key;
                deleted_entries += `<div class="swal-text" style="font-weight: bold; padding-left: 0px; margin-top: 0px;">Service: ${name}</div>`;
                deleted_entries += `<br>`;

                var icon_value = override_find_labels(override_doc.services[service_key], override_doc.services[service_key], icon_label);
                deleted_entries += build_override_input_table(`${service_key}_icon_d`, icon_value, "Icon", "", true);
                
                var webui_value = override_find_labels(override_doc.services[service_key], override_doc.services[service_key], webui_label);
                deleted_entries += build_override_input_table(`${service_key}_webui_d`, webui_value, "Web UI", "", true);

                var shell_value = override_find_labels(override_doc.services[service_key], override_doc.services[service_key], shell_label);
                deleted_entries += build_override_input_table(`${service_key}_shell_d`, shell_value, "Shell", "", true);
              }
            }
            if( deleted_entries ) {
              html += `<details>
                       <summary style="text-align: left; font-weight: bold;">Entries to be Deleted</summary>
                       <br>`;
              html += deleted_entries;
              html += `</details>`;
            }

            var form = document.createElement("div");
            form.style["text-align"] = "left";
            form.innerHTML = html;
            swal2({
              title: "Edit Stack UI Labels",
              content: form,
              buttons: true,
            }).then((result) => {
              if(result) {
                for( var service_key in override_doc.services ) {
                  if( service_key in main_doc.services ) {
                    var new_icon = document.getElementById(`${service_key}_icon`).value;
                    var new_webui = document.getElementById(`${service_key}_webui`).value;
                    var new_shell = document.getElementById(`${service_key}_shell`).value;

                    override_doc.services[service_key].labels[icon_label] = new_icon;
                    override_doc.services[service_key].labels[webui_label] = new_webui;
                    override_doc.services[service_key].labels[shell_label] = new_shell;
                  }
                  else {
                    delete override_doc.services[service_key];
                  }
                }

                rawOverride = jsyaml.dump(override_doc, {'forceQuotes': true});
                // console.log(rawOverride);
                $.post(caURL,{action:"saveOverride",script:project,scriptContents:rawOverride},function(data) {
                  if (!data) {
                    swal2({
                      title: "Failed to update labels.",
                      icon: "error",
                    })
                  }
                });
              }
            });
          }
        }
      });
    }
  });
}

function generateProfiles(myID, myProject=null) {
  var project = myProject;
  if( myID ) {
    $("#"+myID).tooltipster("close");
    project = $("#"+myID).attr("data-scriptname");
  }

  $.post(caURL,{action:'getYml',script:project},function(rawComposefile) {
    var project_profiles = new Set();
    if(rawComposefile) {
      var rawComposefile = jQuery.parseJSON(rawComposefile);

      if( (rawComposefile.result == 'success') ) {
        var main_doc = jsyaml.load(rawComposefile.content);

        for( var service_key in main_doc.services ) {
          var service = main_doc.services[service_key];
          if( service.hasOwnProperty("profiles") ) {
            // console.log(service.profiles);
            for( const profile of service.profiles ) {
              project_profiles.add(profile);
            }
          }
        }
        
        // console.log(project_profiles);
        var rawProfiles = JSON.stringify(Array.from(project_profiles));
        // console.log(rawProfiles);
        $.post(caURL,{action:"saveProfiles",script:project,scriptContents:rawProfiles},function(data) {
          if (!data) {
            swal2({
              title: "Failed to update profiles.",
              icon: "error",
            })
          }
        });
      }
    }
  });
}

function editComposeFile(myID) {
  var origID = myID;
  $("#"+myID).tooltipster("close");
  var project = $("#"+myID).attr("data-scriptname");
  $.post(caURL,{action:'getYml',script:project},function(data) {
    if (data) {
      var response = jQuery.parseJSON(data);
      var editor = ace.edit("itemEditor");
      editor.getSession().setValue(response.content);
      editor.getSession().setMode("ace/mode/yaml");
      editor.getSession().setOptions({ tabSize: 2, useSoftTabs: true });

      $('#editorFileName').data("stackname", project);
      $('#editorFileName').data("stackfilename", "docker-compose.yml")
      $('#editorFileName').html(response.fileName)
      $(".editing").show();
			window.scrollTo(0, 0);
    }
  });
}

function editEnv(myID) {
  var origID = myID;
  $("#"+myID).tooltipster("close");
  var project = $("#"+myID).attr("data-scriptname");
  $.post(caURL,{action:'getEnv',script:project},function(data) {
    if (data) {
      var response = jQuery.parseJSON(data);
      var editor = ace.edit("itemEditor");
      editor.getSession().setValue(response.content);
      editor.getSession().setMode("ace/mode/sh");

      $('#editorFileName').data("stackname", project);
      $('#editorFileName').data("stackfilename", ".env")
      $('#editorFileName').html(response.fileName)
      $(".editing").show();
			window.scrollTo(0, 0);
    }
  });
}

function cancelEdit() {
  $(".editing").hide();
}

function saveEdit() {
  var project = $("#editorFileName").data("stackname");
  var fileName = $("#editorFileName").data("stackfilename");
  var editor = ace.edit("itemEditor");
  var scriptContents = editor.getValue();
  var actionStr = null

  switch(fileName) {
    case 'docker-compose.yml':
      actionStr = 'saveYml'
      break;

    case '.env':
      actionStr = 'saveEnv'
      break;

    default:
      $(".editing").hide();
      return;
  }

  $.post(caURL,{action:actionStr,script:project,scriptContents:scriptContents},function(data) {
    if (data) {
      $(".editing").hide();
      if (actionStr == 'saveYml') {
        generateOverride(null,project);
        generateProfiles(null,project);
      }
    }
  });

}

function editStackSettings(myID) {
  var project = $("#"+myID).attr("data-scriptname");

  $.post(caURL,{action:'getEnvPath',script:project},function(rawEnvPath) {
    if (rawEnvPath) {
      var rawEnvPath = jQuery.parseJSON(rawEnvPath);
      if(rawEnvPath.result == 'success') {
        var form = document.createElement("div");
        // form.classList.add("swal-content");
        form.innerHTML =  `<div class="swal-text" style="font-weight: bold; padding-left: 0px; margin-top: 0px;">ENV File Path</div>`;
        form.innerHTML += `<br>`;
        form.innerHTML += `<input type='text' id='env_path' class='swal-content__input' pattern="(\/mnt\/.*\/.+)" oninput="this.reportValidity()" title="A path under /mnt/user/ or /mnt/cache/ or /mnt/pool/" placeholder=Default value='${rawEnvPath.content}'>`;
        swal2({
          title: "Stack Settings",
          // text: "Enter in the name for the stack",
          content: form,
          buttons: true,
        }).then((inputValue) => {
          if (inputValue) {
            var new_env_path = document.getElementById("env_path").value;
            $.post(caURL,{action:'setEnvPath',envPath:new_env_path,script:project},function(data) {
                var title = "Failed to set stack settings.";
                var message = "";
                var icon = "error";
                if (data) {
                  var response = jQuery.parseJSON(data);
                  if (response.result == "success") {
                    title = "Success";
                  }
                  message = response.message;
                  icon = response.result;
                }
                swal2({
                  title: title,
                  text: message,
                  icon: icon,
                }).then(() => {
                  location.reload();
                });
            });        
          }
        });
      }
    }
  });
}

function ComposeUp(path, profile="") {
  var height = 800;
  var width = 1200;
  
  $.post(compURL,{action:'composeUp',path:path,profile:profile},function(data) {
    if (data) {
      openBox(data,"Stack "+basename(path)+" Up",height,width,true);
    }
  })
}

function ComposeDown(path, profile="") {
  var height = 800;
  var width = 1200;

  $.post(compURL,{action:'composeDown',path:path,profile:profile},function(data) {
    if (data) {
      openBox(data,"Stack "+basename(path)+" Down",height,width,true);
    }
  })
}

function UpdateStack(path, profile="") {
  var height = 800;
  var width = 1200;

  $.post(compURL,{action:'composeUpPullBuild',path:path,profile:profile},function(data) {
    if (data) {
      openBox(data,"Update Stack "+basename(path),height,width,true);
    }
  })
}

function ComposeLogs(myID) {
  var height = 800;
  var width = 1200;
  $("#"+myID).tooltipster("close");
  var project = $("#"+myID).attr("data-scriptname");
  var path = compose_root + "/" + project;
  console.log(path);
  $.post(compURL,{action:'composeLogs',path:path},function(data) {
    if (data) {
      openBox(data,"Stack "+basename(path)+" Logs",height,width,true);
    }
  })
}

function ComposeCheckUpdates(myID) {
  var height = 800;
  var width = 1200;
  $("#"+myID).tooltipster("close");
  var script = $("#"+myID).attr("data-scriptname");
  var path = compose_root + "/" + script;
  $.post(compURL,{action:'composeCheckUpdates',path:path},function(data) {
    if (data) {
      openBox(data,"Check for Available Updates to Stack "+basename(path),height,width,true);
    }
  })
}
</script>

<HTML>
<HEAD>
<style type="text/css">
.edit-stack-form .swal-footer {
  display: table;
  margin-left: auto;
  margin-right: auto;
}
.edit-stack-form .swal-footer .swal-button-container {
  display: table-row;
}
.edit-stack-form .swal-footer .swal-button-container .swal-button {
  width: 150px;
}
</style>
</HEAD>
<BODY>

<div class='editing' style="margin-bottom:34px;" hidden>
<!-- <center><b>Editing <?=$compose_root?>/<span id='editStackName'></span>/<span id='editStackFileName'></span></b><br> -->
<center><b>Editing <span id='editorFileName' data-stackname="" data-stackfilename=""></span></b><br>
<input type='button' value='Cancel' onclick='cancelEdit();'><input type='button' onclick='saveEdit();' value='Save Changes'><br>
<!-- <textarea class='editing' id='editStack' style='width:90%; height:500px; border-color:red; font-family:monospace;' ></textarea> -->
<div id='itemEditor' style='width:90%; height:500px; position: relative;'></div>
</center>
</div>

<span class='tipsterallowed' hidden></span>
<table class="tablesorter shift">
<thead><tr><th style="text-align:left">Stack</th><th></th><th style="text-align:left" colspan="3">Commands</th><th style="text-align:left">Auto Start</th></tr></thead>
<?=$o?>
</table>
<span class='tipsterallowed' hidden><input type='button' value='Add New Stack' onclick='addStack();'><span><br>

</BODY>
</HTML>