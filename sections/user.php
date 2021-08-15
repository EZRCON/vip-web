<?php
		
$settings['title'] = 'User | '.$settings['title'];


$id = -1;
if (isset($_GET['id'])) $id = intval($_GET['id']);


if ($user->getRights() != 0) {
	
	if ($user->getID() != $id) {
		header('Location: index.php?section=user&id='.$user->getID());
		die();
	}
}
	

	if ($id > -1) {
	$sql = "SELECT id FROM vsm_tUser WHERE id = ".$id.";";
	$dbr = $db->query($sql);
	if ($dbr->getCount() == 0) $id = -1;
	unset($dbr);
}


$title = 'EDIT USER';
if ($id == -1) $title = 'ADD NEW USER';

$edit = ($id > -1);

$values['timestamp'] = 'currentdate';
$values['comment'] = '';
if ($edit) {
	$sql = "SELECT * FROM vsm_tUser WHERE id = ".$id.";";
	$dbr = $db->query($sql);
	$row = $dbr->rewind();
	
	$values['playername'] = $row['email'];

	
	$vstatus = ['', '', ''];
	$sel = ' selected="selected"';
	if ($row['rights'] == 0) $vstatus[0] = $sel;
	if ($row['rights'] == 1) $vstatus[1] = $sel;
	if ($row['rights'] == 2) $vstatus[2] = $sel;
	unset($dbr);
} else {
	$vstatus = ['', '', ''];
	$sel = ' selected="selected"';
	$vstatus[2] = $sel;
	
	
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
  <label for="name" class="col-lg-2">Username:</label>
    <div class="col-lg-10">';
	
if (!$edit) {	
	$settings['left'] .= '<input class="form-control" type="text" placeholder="(full username with case senitive)" id="playername" />';
} else {
	$settings['left'] .= '<input class="form-control" type="text" value="'.$values['playername'].'" placeholder="(full username with case senitive)" id="playername" />';
}
			
$settings['left'] .= '
</div>
</div>

<div class="form-group row">
  <label for="name" class="col-lg-2">New password:</label>
    <div class="col-lg-10">
	<input class="form-control" type="password" id="pw" />
			
</div>
</div>';

if ($user->getRights() == 0) {
$settings['left'] .= '
<div class="form-group row">
  <label for="name" class="col-lg-2">Privileges:</label>
    <div class="col-lg-10">
	<select class="form-control" id="status">
		<option value="0"'.$vstatus[0].'>ADMIN (can view, add, edit and remove VIP Slots. Can add, edit and remove website users)</option>
		<option value="1"'.$vstatus[1].'>LEADER (can view, add, edit and remove VIP Slots)</option>
		<option value="2"'.$vstatus[2].'>VIEW ONLY (can view VIP Slots)</option>
	</select>
    </div>
</div>';


if ($edit) {
$settings['left'] .= '<div class="form-group row">
  <label for="name" class="col-lg-2"></label>
    <div class="col-lg-10">
		<button type="button" id="del" class="btn btn btn-info btn-block">Delete user "'.$values['playername'].'"</button>
    </div>
</div>';
}
}

$settings['left'] .= '</div>


</form>
			<div id="clearfix"></div>

				<h1 style="margin-top:30px"></h1>

				<div style="text-align: left; margin-bottom:30px">
		<button id="save" class="btn btn-lg btn-info" href="index.php?section=vip">SAVE</button>
		<a class="btn btn-lg btn-info" href="index.php?section=userlist" style="margin-left: 10px">CANCEL</a>
		
				</div>

';





$settings['jsAdd'] = '
<script>	

$(document).ready(function(){

	$( "#del").click(function() {
		if (confirm("Do you really want to delete this user?")) {

			$.ajax({
				type: "POST",
				url: "api.php",
				data: "type=deluser&id="+'.$id.',
				cache: false,
				success: function(html) {
					window.location = "index.php?section=userlist";
				},
				error: function (xhr, ajaxOptions, thrownError) {
					alert(xhr.status);
				}
			});
		
			
		}
	});
	
	
	
	$( "#save").click(function() {';
	
if (!$edit) {
	$settings['jsAdd'] .=  '
		var tmp_username = encodeURIComponent(document.getElementById("playername").value);
		var tmp_pw = encodeURIComponent(document.getElementById("pw").value);		
		var tmp_status =  encodeURIComponent($("select#status option:selected").attr("value"));

		$.ajax({
			type: "POST",
			url: "api.php",
			data: "type=insertuser&username="+tmp_username+"&status="+tmp_status+"&pw="+tmp_pw,				
			cache: false,
			success: function(html) {
				if (html == "err_nouser") {
					alert("No user provided.");
				}
				if (html == "err_nopw") {
					alert("No password provided.");
				}
				if (html == "user_exists") {
					alert("User already exists.");
				}				
				if (html == "ok") {
					window.location = "index.php?section=userlist";
				}
			},
			error: function (xhr, ajaxOptions, thrownError) {
				alert(xhr.status);
			}
		});

		
	});';
} else {
	$settings['jsAdd'] .=  'var tmp_username = encodeURIComponent(document.getElementById("playername").value);
		var tmp_pw = encodeURIComponent(document.getElementById("pw").value);		
		var tmp_status = encodeURIComponent($("select#status option:selected").attr("value"));

		$.ajax({
			type: "POST",
			url: "api.php",
			data: "type=changeuser&id='.$id.'&username="+tmp_username+"&status="+tmp_status+"&pw="+tmp_pw,				
			cache: false,
			success: function(html) {
				if (html == "ok") {
					window.location = "index.php?section=userlist";
				} else {
					alert("User already exists!");
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
