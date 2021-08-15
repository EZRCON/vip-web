<?php
	
$settings['title'] = 'VIP | '.$settings['title'];

$id = -1;
if (isset($_GET['id'])) $id = intval($_GET['id']);

if ($id > -1) {
	$sql = "SELECT id FROM vsm_vips WHERE id = ".$id.";";
	$dbr = $db->query($sql);
	if ($dbr->getCount() == 0) $id = -1;
	unset($dbr);
}

$title = 'EDIT VIP';
if ($id == -1) $title = 'ADD VIP';

$edit = ($id > -1);

$dadd = '0';
$dadd2 = '0';
	$vstatus = ['', '', ''];
$values['timestamp'] = 'date1';
$values['comment'] = '';
if ($edit) {
	$sql = "SELECT id, status, playername, gametype, servergroup, TIMESTAMPDIFF(SECOND,'1970-01-01',timestamp) as timestamp2, TIMESTAMPDIFF(SECOND,'1970-01-01',UTC_TIMESTAMP()) as timestamp3, comment, TIMESTAMPDIFF(DAY,UTC_TIMESTAMP(),timestamp) AS days FROM vsm_vips WHERE id = ".$id.";";
	
	
	$dbr = $db->query($sql);
	$row = $dbr->rewind();
	if (!($row['status'] == 'active' or $row['status'] == 'inactive' or $row['status'] == 'expired')) {	
		header('Location: index.php');
		die();
	}
	
	if ($row['days'] >= 0) $dadd2 = $row['timestamp2'];
	if ($row['days'] < 0) $dadd2 = $row['timestamp3'];
	$dadd = $row['timestamp2'];
	
	$values['playername'] = $row['playername'];
	$values['gametype'] = $row['gametype'];
	$values['servergroup'] = $row['servergroup'];
	$values['timestamp'] = 'date2';
	$values['comment'] = $row['comment'];

	
	$vstatus = ['', '', ''];
	$sel = ' selected="selected"';
	if ($row['status'] == 'active') $vstatus[0] = $sel;
	if ($row['status'] == 'inactive') $vstatus[1] = $sel;
	if ($row['status'] == 'expired') $vstatus[2] = $sel;
		
	unset($dbr);
}

$tmp = '';
	$tmp .= '<option value="BF3">BF3</option>';
	$tmp .= '<option value="BF4">BF4</option>';
	$tmp .= '<option value="BFH">BFH</option>';
	$tmp .= '<option value="BC2">BC2</option>';

$sql = "SELECT DISTINCT servergroup FROM vsm_vips WHERE gametype='BF3'";
$dbr = $db->query($sql);
$tmp2 = '';
//foreach ($dbr as $row) {
for ($i = 1; $i < 100; $i++) {
	$tmp2 .= '<option value="'.$i.'">'.$i.'</option>';
}
unset($dbr);

// Inhalt:
$settings['left'] = '
			<h1><span>'.$title.'</span></h1>
		
		<div style="text-align: left">
<form>

<div class="form-group row">
  <label for="name" class="col-lg-2">Playername:</label>
    <div class="col-lg-10">';
	
if (!$edit) {	
	$settings['left'] .= '<input class="form-control" type="text" placeholder="(full playername with case senitive)" id="playername" />';
} else {
	$settings['left'] .= $values['playername'].'<input class="form-control" type="hidden" value="'.$values['playername'].'" placeholder="(full playername with case senitive)" id="playername" />';
}

$settings['left'] .= '</div>
</div>

  <div class="form-group row">
    <label for="exampleSelect1" class="col-lg-2" >Game Type: </label>
	    <div class="col-lg-4">';

if (!$edit) {
	$settings['left'] .= '<select class="form-control" id="exampleSelect1">
		'.$tmp.'
	</select>';
} else {
	$settings['left'] .= $values['gametype'];
}

$settings['left'] .= '</div>

      <label for="exampleSelect2" class="col-lg-2">Server Group:</label>
    <div class="col-lg-3">	  
		<div id="statusEinheit" style="width:20px; height:20px; overflow: hidden; display: inline;"></div>';


if (!$edit) {
	$settings['left'] .= '<select class="form-control" id="exampleSelect2">
	'.$tmp2.'
	</select>';
} else {
	$settings['left'] .= $values['servergroup'];
}


$settings['left'] .= '</div>
<div class="col-lg-1">';

if (!$edit) $settings['left'] .= '<button type="button" id="filteradd" class="btn btn-circle btn-success" href="index.php?section=vip" style="margin:0"><i class="fa fa-plus fa-fw"></i></button>';

$settings['left'] .= '</div>

</div>';

if (!$edit) {
$settings['left'] .= '<div class="form-group row">
  <label for="name" class="col-lg-2"></label>
    <div class="col-lg-10">

			<div id="filter" style="text-align:left">
			</div>
	</div>
</div>';
}
			
$settings['left'] .= '<div class="form-group row">
  <label for="name" class="col-lg-2">Valid till:</label>
    <div class="col-lg-10">
 
                <div class="input-group date" id="datetimepicker1">
                    <input type="text" class="form-control" />
                    <span class="input-group-addon" style="cursor: pointer">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
				
 </div>
</div>

<div class="form-group row">
  <label for="name" class="col-lg-2"></label>
    <div class="col-lg-10">
		<button type="button" id="add30" class="btn btn btn-info">+30 days</button>
		<button type="button" id="add60" class="btn btn btn-info">+60 days</button>
		<button type="button" id="add90" class="btn btn btn-info">+90 days</button>
		<button type="button" id="add180" class="btn btn btn-info">+180 days</button>
		<button type="button" id="add365" class="btn btn btn-info">+365 days</button>
 </div>
</div>';

 
if (!$edit) {		
$settings['left'] .= '<div class="form-group row">
  <label for="name" class="col-lg-2">Status:</label>
    <div class="col-lg-10">
	<select class="form-control" id="status">
		<option value="active"'.$vstatus[0].'>ACTIVE</option>
		<option value="inactive"'.$vstatus[1].'>INACTIVE</option>
		<option value="expired"'.$vstatus[2].'>EXPIRED</option>
	</select>
    </div>
</div>';
} else {
$settings['left'] .= '<div class="form-group row">
  <label for="name" class="col-lg-2">Status:</label>
    <div class="col-lg-10">
	<select class="form-control" id="status">
		<option value="active"'.$vstatus[0].'>ACTIVE</option>
		<option value="inactive"'.$vstatus[1].'>INACTIVE</option>
	</select>
    </div>
</div>';

}


$settings['left'] .= '<div class="form-group row">
  <label for="name" class="col-lg-2">Comment:</label>
    <div class="col-lg-10">
<textarea class="form-control" id="comment" rows="3" placeholder="(optional)">'.$values['comment'].'</textarea>
    </div>
</div>';


if ($edit) {
$settings['left'] .= '<div class="form-group row">
  <label for="name" class="col-lg-2"></label>
    <div class="col-lg-10">
		<button type="button" id="del" class="btn btn btn-info btn-block">Delete Player "'.$values['playername'].'"</button>
    </div>
</div>';
}

$settings['left'] .= '</div>


</form>
			<div id="clearfix"></div>

				<h1 style="margin-top:30px"></h1>

				<div style="text-align: left; margin-bottom:30px">
		<button id="save" class="btn btn-lg btn-info" href="index.php?section=vip">SAVE</button>
		<a class="btn btn-lg btn-info" href="index.php" style="margin-left: 10px">CANCEL</a>
		
				</div>

';





$settings['jsAdd'] = '
<script>	
			
var date1 = new Date();
var date2 = new Date(0);
date2.setUTCSeconds('.$dadd.');

var filterarr = [];

Array.prototype.contains = function(obj) {
    var i = this.length;
    while (i--) {
        if (this[i] == obj) {
            return true;
        }
    }
    return false;
}


Date.prototype.addDays = function(days) {
  var dat = new Date(this.valueOf());
  dat.setDate(dat.getDate() + days);
  return dat;
}

Date.prototype.addMinutes = function(minutes) {
  var dat = new Date(this.valueOf());
  dat.setMinutes(dat.getMinutes() + minutes);
  return dat;
}

$(document).ready(function(){

var currentdate = new Date(); 
//                $("#datetimepicker1").datetimepicker();

  $("#datetimepicker1").datetimepicker({
                    defaultDate: '.$values['timestamp'].',
					maxDate: "2037-12-24 12:54:58",
					format: "DD.MM.YYYY HH:mm:ss"
                });
	 
  $("#filter").on("click", ".bfil", function(){

	var bla = $(this).html();
	
	var arr1 = bla.split(" - "); 
	var server = arr1[0];
	var arr2 = arr1[1].split("<");
	var gruppe = arr2[0];
	
	var bla2 = server + " - " + gruppe
  
	var index = filterarr.indexOf(bla2);
	if (index > -1) {
		filterarr.splice(index, 1);
	}
	$(this).parent().remove();	
	
});	
	
	$( "#filteradd" ).click(function() {
		var myVar = $("select#exampleSelect2 option:selected").attr("value");
		if (typeof myVar == "undefined") {
			alert("No server group selected!");
		} else {
			

		var bla = $("select#exampleSelect1 option:selected").attr("value") + " - " + myVar;
			
		if (filterarr.contains(bla)) {
			alert("Entry allready exists!");
		} else {
						$("#filter").append('."'".'<div id="btcontx" style="display:inline-block"><button type="button" id="filterx" class="btn btn-lg btn-info bfil" style="margin-bottom: 15px; margin-right: 15px;">'."'".'+bla+'."'".'<i class="fa fa-minus-circle fa-fw" style="font-size:20px; color: #fb3939; position: relative; top: -25px; left: 7px; font-weight: normal; margin-right:-25px"></i></button></div>' . "'". ')		
			filterarr.push(bla);
		}
			
		 }
	});';
	
if (!$edit) {
	$settings['jsAdd'] .=  '$( "#add30" ).click(function() {
		var cdate = new Date(); //($("#datetimepicker1").data("DateTimePicker").date());
		cdate = cdate.addDays(30);
		 $("#datetimepicker1").data("DateTimePicker").date(cdate);
	});
	
	$( "#add60" ).click(function() {
		var cdate = new Date(); //($("#datetimepicker1").data("DateTimePicker").date());
		cdate = cdate.addDays(60);
		 $("#datetimepicker1").data("DateTimePicker").date(cdate);
	});

	$( "#add90" ).click(function() {
		var cdate = new Date(); //($("#datetimepicker1").data("DateTimePicker").date());
		cdate = cdate.addDays(90);
		 $("#datetimepicker1").data("DateTimePicker").date(cdate);
	});

	$( "#add180" ).click(function() {
		var cdate = new Date(); //($("#datetimepicker1").data("DateTimePicker").date());
		cdate = cdate.addDays(180);
		 $("#datetimepicker1").data("DateTimePicker").date(cdate);
	});

	$( "#add365").click(function() {
		var cdate = new Date(); //($("#datetimepicker1").data("DateTimePicker").date());
		cdate = cdate.addDays(365);
		 $("#datetimepicker1").data("DateTimePicker").date(cdate);
	});';
} else {
	
	$settings['jsAdd'] .=  '$( "#add30" ).click(function() {
		var cdate = new Date(0); //($("#datetimepicker1").data("DateTimePicker").date());
		cdate.setUTCSeconds('.$dadd2.');		
		cdate = cdate.addDays(30);
		 $("#datetimepicker1").data("DateTimePicker").date(cdate);
	});

	$( "#add60" ).click(function() {
		var cdate = new Date(0); //($("#datetimepicker1").data("DateTimePicker").date());
		cdate.setUTCSeconds('.$dadd2.');		
		cdate = cdate.addDays(60);		
		 $("#datetimepicker1").data("DateTimePicker").date(cdate);
	});

	$( "#add90" ).click(function() {
		var cdate = new Date(0); //($("#datetimepicker1").data("DateTimePicker").date());
		cdate.setUTCSeconds('.$dadd2.');		
		cdate = cdate.addDays(90);		
		 $("#datetimepicker1").data("DateTimePicker").date(cdate);
	});

	$( "#add180" ).click(function() {
		var cdate = new Date(0); //($("#datetimepicker1").data("DateTimePicker").date());
		cdate.setUTCSeconds('.$dadd2.');		
		cdate = cdate.addDays(180);		
		 $("#datetimepicker1").data("DateTimePicker").date(cdate);
	});
	
	$( "#add365").click(function() {
		var cdate = new Date(0); //($("#datetimepicker1").data("DateTimePicker").date());
		cdate.setUTCSeconds('.$dadd2.');		
		cdate = cdate.addDays(365);
		 $("#datetimepicker1").data("DateTimePicker").date(cdate);
	});';

	
}


$settings['jsAdd'] .=  '$( "#del").click(function() {
		if (confirm("Do you really want to delete this vip?")) {

			$.ajax({
				type: "POST",
				url: "api.php",
				data: "type=delvip&id="+'.$id.',
				cache: false,
				success: function(html) {
					window.location = "index.php";
				},
				error: function (xhr, ajaxOptions, thrownError) {
					alert(xhr.status);
				}
			});
		
			
		}
	});
	
	
	
	$( "#save").click(function() {';
	
if (!$edit) {
	$settings['jsAdd'] .=  'var cdate = new Date($("#datetimepicker1").data("DateTimePicker").date());
	
		var tmp_date = cdate.toISOString().slice(0, 19).replace("T", " ");

		var farr = "";
		for (var i = 0; i < filterarr.length; i++) {
			farr += filterarr[i] + "#";
		}
			
		var tmp_playername = encodeURIComponent(document.getElementById("playername").value);
		var tmp_status =  encodeURIComponent($("select#status option:selected").attr("value"));
		var tmp_comment = encodeURIComponent(document.getElementById("comment").value);

		$.ajax({
			type: "POST",
			url: "api.php",
			data: "type=insert&date="+tmp_date+"&farr="+farr+"&playername="+tmp_playername+"&status="+tmp_status+"&comment="+tmp_comment,				
			cache: false,
			success: function(html) {
				if (html == "err_date") {
					alert("Date expired!");
				}				
				if (html == "err_user_exists") {
					alert("Player name already exists!");
				}
				if (html == "err_no_server") {
					alert("No server selected! Click the green + BUTTON to add a server/group.");
				}
				if (html == "err_nouser") {
					alert("Please provide a player name!");
				}				
				if (html == "ok") {
					window.location = "index.php";
				}
			},
			error: function (xhr, ajaxOptions, thrownError) {
				alert(xhr.status);
			}				
		});

		
	});';
} else {
	$settings['jsAdd'] .=  'var cdate = new Date($("#datetimepicker1").data("DateTimePicker").date());
	
		var tmp_date = cdate.toISOString().slice(0, 19).replace("T", " ");
	
	
		var farr = "'.$values['gametype'].' - '.$row['servergroup'].'";

		var tmp_playername = encodeURIComponent(document.getElementById("playername").value);
		var tmp_status =  encodeURIComponent($("select#status option:selected").attr("value"));
		var tmp_comment = encodeURIComponent(document.getElementById("comment").value);
		$.ajax({
			type: "POST",
			url: "api.php",
			data: "type=change&date="+tmp_date+"&farr="+farr+"&id='.$id.'&status="+tmp_status+"&comment="+tmp_comment,				
			cache: false,
			success: function(html) {
				if (html == "ok") {
					window.location = "index.php";
				} else {
					alert("Please check the date!");
				}
			},
			error: function (xhr, ajaxOptions, thrownError) {
				alert(xhr.status);
			}				
		});
		
	});';
}
	
$settings['jsAdd'] .=  '});

	function logout() {
		document.frmLogout.submit();
	}

</script>';



?>
