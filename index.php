<?php

// Einstellungen importieren:
include_once('config.php');

$settings['menu'] = true;
if (!$user->loggedIn()) {

	// Auf Loginseite weiterleiten:	
	if ($settings['currentPage'] != 'login') {
		header('Location: index.php?section=login');
		die();
	}
	$settings['menu'] = false;
}


// Aktuelle Seite inkludieren:
$dest = 'sections';
//include_once($dest.'/'.$settings['currentPage'].'.php');


// Aktuelle Seite inkludieren:
$dest = 'sections';
include_once($dest.'/'.$settings['currentPage'].'.php');


$styles = '<link href="style.css" rel="stylesheet" type="text/css" />';
if ($settings['mob']) $styles = '<link href="style_mob.css" rel="stylesheet" type="text/css" />';

// HTML-Ausgabe:
echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

	<title>'.$settings['title'].'</title>

	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta http-equiv="content-language" content="en" />
	<meta name="robots" content="index,follow" />
	<meta name="keywords" content="vip slot manager, plugin, bf, procon">
	<meta name="revisit-after" content="7 days">
	<meta name="Content-Language" Content="en">
	<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
	<link rel="icon" href="/favicon.ico" type="image/x-icon">
	
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesnt work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

	<link rel="stylesheet" type="text/css" href="datatables.min.css"/> 
	<script type="text/javascript" src="datatables.min.js"></script>	

    '.$styles.'
	
    <!-- Custom Fonts -->
    <link href="font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
	
	
	
	<script type="text/javascript" src="moment/moment.js"></script>
<script type="text/javascript" src="bower_components/bootstrap/js/transition.js"></script>
<script type="text/javascript" src="bower_components/bootstrap/js/collapse.js"></script>
<script type="text/javascript" src="datetimepicker/build/js/bootstrap-datetimepicker.min.js"></script>




</head>';


echo '<body>
<div id="titlebox">
	<div id="blackbox"></div><div id="content"><a href="index.php" style="text-decoration: none"><h1>VIP Slot Manager</h1></a></div>';	

$link = 'index.php?section=user&id='.$user->getID();

if ($user->getRights() != 2) $link11 = '<a href="index.php?section=vip"><i class="fa fa-plus-circle fa-fw" style="font-size:22px; position: relative; top: 2px; left: 1px; font-weight: normal;"></i>&nbsp;</a>';
if ($user->getRights() == 0) $link = 'index.php?section=userlist';
if ($settings['menu']) {
	echo '<div id="menu">
		'.$link11.'
		<a href="'.$link.'"><i class="fa fa-list fa-fw" style="font-size:22px; position: relative; top: 2px; left: 1px; font-weight: normal;"></i>&nbsp;</a>
		<a onClick="javascript:logout();" style="cursor:pointer"><i class="fa fa-power-off fa-fw" style="font-size:22px; position: relative; top: 2px; left: 1px; font-weight: normal;"></i>&nbsp;</a>
		<form name="frmLogout" method="post" action="action.php?action=logout" autocomplete="off"></form>
		
	</div>';
}
echo '</div>

<div id="bottombox">
	<div id="infobox">
		<div id="tube">
'.$settings['left'].'

		</div>
	</div>
</div>

<div id="footer">
	Website developed for <a href="https://github.com/procon-plugin/vip-slot-manager">VIP Slot Manager Plugin</a> (Procon)
</div>

'.$settings['modal'].'
</body>
'.$settings['jsAdd'].'';

echo  '</html>';

// Aufraemen:
$db->close();
unset($db);
unset($settings);
unset($user);
unset($mobile);