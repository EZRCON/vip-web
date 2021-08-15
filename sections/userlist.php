<?php
	$settings['title'] = 'User list | '.$settings['title'];

if ($user->getRights() == 1 or $user->getRights() == 2) {
	header('Location: index.php');
	die();
}


// Inhalt:
$settings['left'] = '
			<h1><span>User List</span></h1>


			<div style="text-align:left">
				<table id="example" class="table table-striped table-bordered " width="100%"  cellspacing="0">
				<thead>
					<tr>
						<th></th>
						<th>ID</th>
						<th>Web user</th>
						<th>Privileges</th>
					</tr>
				</thead>

				
				</table>
				
				</div>
			
				<h1 style="margin-top:15px"></h1>

<form>

				<div style="text-align: left; margin-bottom:30px">
		<a class="btn btn-lg btn-info" href="index.php?section=user">Add new user</a>
		<a class="btn btn-lg btn-info" href="index.php">Back</a>
		
				</div>
			


';

$settings['jsAdd'] = '
<script>
$(document).ready(function() {
    $("#example").DataTable( {
		responsive: true,
        "ajax": "api.php?type=user",		
    "iDisplayLength": 25,		
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
                "targets": [ 1 ],
                "visible": false
            }		
		],
        select: {
            style:    "multi"
		},
        order: [[ 1, "asc" ]],
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

		 $.ajax({
				type: "POST",
				url: "api.php",
				data: "type=sel&id="+tmp_value+"&values="+values,
				cache: false,
				success: function(html) {
						$("#example").DataTable().ajax.reload();					
					alert(html);
				}
				});

			
	
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
		
	$( "#btadd" ).click(function() {
		var myVar = $("select#exampleSelect2 option:selected").attr("value");
		if (typeof myVar == "undefined") {
			alert("No server group selected!");
		} else {
			
				
		
		var bla = $("select#exampleSelect1 option:selected").attr("value") + " - " + myVar;
		
		
		
		 $.ajax({
				type: "POST",
				url: "api.php",
				data: "type=add&id="+bla,
				cache: false,
				success: function(html) {					
					if (html == 0) {
		$("#btcont").remove();	
						
						$("#filter").append('."'".'<div id="btcontx" style="display:inline-block"><button type="button" id="filterx" class="btn btn-lg btn-info bfil" style="margin-bottom: 15px; margin-right: 15px;">'."'".'+bla+'."'".'<i class="fa fa-minus-circle fa-fw" style="font-size:20px; color: #fb3939; position: relative; top: -25px; left: 7px; font-weight: normal; margin-right:-25px"></i></button></div>' . "'". ')		
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
