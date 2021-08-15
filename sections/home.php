<?php

	$bla = $user->getFilter();

	$tmp = '';
	foreach($bla as $row) {
		$tmps = $row['server'];
		
		if ($row['gruppe'] > 0) $tmps .= ' - '.$row['gruppe'];
		if (($row['noexpired'] =='a') && ($row['noperm'] != 'a')) $tmps .= ' (no exp.)';
		if (($row['noperm'] == 'a') && ($row['noexpired'] != 'a')) $tmps .= ' (no perm)';
		if (($row['noexpired'] =='a') && ($row['noperm'] == 'a')) $tmps .= ' (no exp./perm)';
		$tmp .= '<div id="btconx" style="display:inline-block"><button type="button" id="filterx" class="btn btn-lg btn-info bfil" style="margin-bottom: 15px; margin-right: 15px;">'.$tmps.'<i class="fa fa-minus-circle fa-fw" style="font-size:20px; color: #fb3939; position: relative; top: -25px; left: 7px; font-weight: normal; margin-right:-25px"></i></button></div>';
	}
		$tmp .= '
		<div id="btcont" style="display:inline-block">
		<button type="button" id="filteradd" data-toggle="modal" data-target=".bs-example-modal-lg" style="margin-bottom: 15px" class="btn btn-lg btn-info">Add filter<i class="fa fa-plus-circle fa-fw" style="font-size:20px; color: #86e32c; position: relative; top: -25px; left: 7px; font-weight: normal; margin-right:-25px"></i></button>
		</div>
		
		';
	
	
$settings['title'] = 'Home | '.$settings['title'];


// Inhalt:
$settings['left'] = '
			<h1><span>VIP List</span></h1>
			<div id="filter" style="text-align:left">
			
			'.$tmp.'
			</div>
			<h1 style="margin-top:5px"></h1>

			<div style="text-align:left">
				<table id="example" class="table table-striped table-bordered " width="100%"  cellspacing="0">
				<thead>
					<tr>
						<th></th>
						<th>ID</th>
						<th>Server</th>
						<th>Playername</th>
						<th>Status</th>
						<th>Changelog</th>
						<th>Comment</th>
					</tr>
				</thead>

				
				</table>
				
				</div>
			
				<h1 style="margin-top:15px"></h1>';

if ($user->getRights() != 2) {
$settings['left'] .= '<form>

<div class="form-group">
  <label for="name" class="col-lg-2">Selected VIPs</label>
    <div class="col-lg-8">
	<select class="form-control" id="exampleSelect4" style="float: left">
		<option value="0">-</option>
		<option value="1">Set to ACTIVE. Add +30 days</option>
		<option value="2">Set to ACTIVE. Add +60 days</option>
		<option value="3">Set to ACTIVE. Add +90 days</option>
		<option value="4">Set to ACTIVE. Add +180 days</option>
		<option value="5">Set to ACTIVE. Add +365 days</option>
		<option value="6">Set to ACTIVE. Valid for 30 days</option>
		<option value="7">Set to ACTIVE. Permanent VIP (e.g. admins)</option>
		<option value="8">Set to INACTIVE</option>
		<option value="9">Copy to new Server Group</option>
		<option value="10">DELETE (normal)</option>
		<option value="11">DELETE (force)</option>		
	</select>
    </div>
</div>
</form>
			<div id="clearfix"></div>

				<h1 style="margin-top:30px"></h1>

				<div style="text-align: left; margin-bottom:30px">
		<a class="btn btn-lg btn-info" href="index.php?section=vip">Add new VIP</a>
				</div>';
}


$sql = "SELECT DISTINCT servergroup FROM vsm_vips WHERE gametype='BF3'";
$dbr = $db->query($sql);
$tmp2 = '';
foreach ($dbr as $row) {
	$tmp2 .= '<option value="'.$row['servergroup'].'">'.$row['servergroup'].'</option>';
}

$tmp2 .= '<option value="-1">All</option>';

$tmp = '';
	$tmp .= '<option value="BF3">BF3</option>';
	$tmp .= '<option value="BF4">BF4</option>';
	$tmp .= '<option value="BFH">BFH</option>';
	$tmp .= '<option value="BC2">BC2</option>';

unset($dbr);

$tmp3 = '';
for ($i = 1; $i < 100; $i++) {
	$tmp3 .= '<option value="'.$i.'">'.$i.'</option>';
}


$settings['modal'] = '<div id="tmodal" class="modal fade bs-example-modal-lg bs-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Add Filter</h4>
      </div>
      <div class="modal-body">
<form>
  <div class="form-group">
    <label for="exampleSelect1" style="color:black">Game Type</label>
	<select class="form-control" id="exampleSelect1">
'.$tmp.'
</select>
  </div>
  <div class="form-group">
  
      <label for="exampleSelect2" style="color:black">Server Group</label>
	  <div id="statusEinheit" style="float:left"></div>	  
	  <select class="form-control" id="exampleSelect2">
'.$tmp2.'
</select>
</div>

<div class="form-check">
  <input class="form-check-input" type="checkbox" value="noexpired" id="noexpired">
  <label class="form-check-label" for="noexpired" style="color:black">
    No expired VIPs
  </label>
</div>
<div class="form-check">
  <input class="form-check-input" type="checkbox" value="noperm" id="noperm">
  <label class="form-check-label" for="noperm" style="color:black">
    No perm VIPs (+2000 days)
  </label>
</div>

</form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-info" id="btadd">Add filter</button>	  
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>



<div id="tmodal2" class="modal fade bs-example2-modal-lg bs-example2-modal-sm" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel2">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Select server group</h4>
      </div>
      <div class="modal-body">
<form>
  <div class="form-group">
    <label for="exampleSelect1b" style="color:black">Game Type</label>
	<select class="form-control" id="exampleSelect1b">
'.$tmp.'
</select>
  </div>
  <div class="form-group">
  
      <label for="exampleSelect2b" style="color:black">Server Group</label>
	  <select class="form-control" id="exampleSelect2b">
'.$tmp3.'
</select>
</div>


</form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-info" id="btok">OK</button>	  
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>








';
	
$mobadd = '';
if ($settings['mob']) $mobadd = ', 5, 6, 0';
$settings['jsAdd'] = '
<script>
var glob_fil = "";
$(document).ready(function() {
    $("#example").DataTable( {
		responsive: true,
        "ajax": "api.php",
    "iDisplayLength": 100,
    buttons: [
        "selectAll",
        "selectNone"
    ],	
        columnDefs: [ {
            orderable: false,
            className: "select-checkbox",
            targets:   0
        }, 
           {
                "targets": [ 1'.$mobadd.' ],
                "visible": false
            }
		],
        select: {
            style:    "multi+shift"
		},	
        order: [[ 4, "asc" ], [ 3, "asc" ]],
		search: {smart: false},
		language: {
        buttons: {
            selectAll: "Select all items",
            selectNone: "Select none"
        }
    }		
    } );
} );
	function logout() {
		document.frmLogout.submit();
	}
	
	
$(document).ready(function(){
	$("select#exampleSelect1").change(function(){

		var tmp_id = $("select#exampleSelect1 option:selected").attr("value"); 
		
		$("#exampleSelect2").html( "" );
		
			
		 $.ajax({
				type: "POST",
				url: "api.php",
				data: "type=server&id="+tmp_id,
				cache: false,
				beforeSend: function () { 
					$("#statusEinheit").html('."'<img src=".'"loader.gif" width="24" height="24">'."'".');				
				},
				success: function(html) {			
					$("#exampleSelect2").html(html);
					$("#statusEinheit").html("");
					

				}
			});
	
	
	});


	
	$("select#exampleSelect4").change(function(){

		var tmp_value = $("select#exampleSelect4 option:selected").attr("value"); 

	var rows = $("#example").DataTable().rows(".selected").data().length;

		if (rows == 0) {
			alert("Nothing seleted!");
		} else {
    var ids = $.map($("#example").DataTable().rows(".selected").data(), function (item) {
        return item[1];
    });
		var values = ids.toString();

		if (tmp_value == 9) {
			glob_fil = values;
			$("#tmodal2").modal("show");
		} else {
			
			var dcancel = false;
			 if (tmp_value == 11) {
				if (!(confirm("Do you really want to delete these vips?"))) {
					dcancel = true;
				}
			 }
			 
			 if (!dcancel) {
				 $.ajax({
						type: "POST",
						url: "api.php",
						data: "type=sel&id="+tmp_value+"&values="+values,
						cache: false,
						success: function(html) {
								$("#example").DataTable().ajax.reload();
						}
				});
			 }
		}
			
	
	}
		$("select#exampleSelect4").val("0");
		
	});

	
	
	
  $("#filter").on("click", ".bfil", function(){
		 $.ajax({
				type: "POST",
				url: "api.php",
				data: "type=del&id="+$(this).html(),
				cache: false,
				success: function(html) {
						$("#example").DataTable().ajax.reload();					
				}
			});
		$(this).parent().remove();	

});
	

	$( "#btok" ).click(function() {
		var myVar = $("select#exampleSelect2b option:selected").attr("value");
		if (typeof myVar == "undefined") {
			alert("No server group selected!");
		} else {
			
				
		
			var bla = $("select#exampleSelect1b option:selected").attr("value");
					$("#tmodal2").modal("hide");
	
		 $.ajax({
				type: "POST",
				url: "api.php",
				data: "type=sel&id="+9+"&values="+glob_fil+"&gametype="+bla+"&servergroup="+myVar,
				cache: false,
				success: function(html) {
						$("#example").DataTable().ajax.reload();
				},
				error: function (xhr, ajaxOptions, thrownError) {
					alert(xhr.status);
				}
		});
	
		}
	});
		
	$( "#btadd" ).click(function() {
		var myVar = $("select#exampleSelect2 option:selected").attr("value");
		var myFilter1 = $("#noexpired:checked").val();
		var myFilter2 = $("#noperm:checked").val();
		$noexpired = " - b";
		$noperm = " - b";
		$tmp_info = "";
		if (myFilter1 == "noexpired") { $noexpired = " - a"; $tmp_info = " (no exp.)";}
		if (myFilter2 == "noperm") { $noperm = " - a"; $tmp_info = " (no perm)";}
		if ((myFilter2 == "noperm") && (myFilter1 == "noexpired")) { $tmp_info = " (no exp./perm)"; }
		
		if (typeof myVar == "undefined") {
			alert("No server group selected!");
		} else {

		var bla = $("select#exampleSelect1 option:selected").attr("value");
		var bla2 = bla + " - " + myVar + $noexpired + $noperm;

		if (myVar > 0) $tmp_info = " - " + myVar + $tmp_info;
		$tmp_info = bla + $tmp_info;

		 $.ajax({
				type: "POST",
				url: "api.php",
				data: "type=add&id="+bla2,
				cache: false,
				success: function(html) {
					if (html == 0) {
		$("#btcont").remove();	
						
						$("#filter").append('."'".'<div id="btcontx" style="display:inline-block"><button type="button" id="filterx" class="btn btn-lg btn-info bfil" style="margin-bottom: 15px; margin-right: 15px;">'."'".'+$tmp_info+'."'".'<i class="fa fa-minus-circle fa-fw" style="font-size:20px; color: #fb3939; position: relative; top: -25px; left: 7px; font-weight: normal; margin-right:-25px"></i></button></div>' . "'". ')		
						$("#filter").append('."'".'<div id="btcont" style="display:inline-block"><button style="margin-bottom: 15px" type="button" id="filteradd" data-toggle="modal" data-target=".bs-example-modal-lg" class="btn btn-lg btn-info">Add filter<i class="fa fa-plus-circle fa-fw" style="font-size:20px; color: #86e32c; position: relative; top: -25px; left: 7px; font-weight: normal; margin-right:-25px"></i></button></div>' . "'". ');
		$("#tmodal").modal("hide");
						
						$("#example").DataTable().ajax.reload();
						
					}
					if (html == 1) {
						alert("Filter already exists!")
						
					}
					if (html == 2) {
		$("#tmodal").modal("hide");
						
						alert("Maximum amount of filters reached!")
					}					

				}
			});

			
		 }
	});

	
});


</script>';


?>
