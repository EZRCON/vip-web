<?php
include('config.php');

if(!isset($_GET['action'])) $_GET['action'] = '';
 
if (!$user->loggedIn()) {

	// Auf Loginseite weiterleiten:	
	if ($_GET['action'] != 'login') header('Location: index.php?section=login');
	
}


// Aktionen ausfuehren:
switch ($_GET['action']) {

		
	// Benutzer einloggen:
	case 'login':
		
		$_POST['email'] = filterStr(substr($_POST['email'], 0, 120));

		
		if ($user->login($_POST['email'], $_POST['password'])) {

		// Kein Fehler! In den Kontobereich wechseln:
			header('Location: index.php?section=home');
			
		} else {
		
			// Ein Fehler ist aufgetreten! Zurueck zur Loginseite...
			header('Location: index.php?section=login&err=1');		
		}
		break;

	// Benutzer ausloggen:
	case 'logout':
		$user->logout();

		// Sessions aktualisieren:
		GarbageCollection::updateSessions($db, true);
		
		header('Location: index.php?section=login');
		break;
	
}
	
// Aufraeumen:
$db->close();

unset($db);
unset($user);

?>
