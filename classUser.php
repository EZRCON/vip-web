<?php

class User {

	/**************************************************************************
	* Eigenschaften                                                           *
	**************************************************************************/

	var $db;        // Datenbankreferenz
	var $sessionID; // SessionID
	var $row;       // Abfrageergebnis
	var $exist;     // Existiert der Benutzer in der DB?
	
	/**************************************************************************
	* Konstruktor und Destruktor                                              *
	**************************************************************************/

	/**
	* @brief Konstruktor
	* @param TMySql $db MySql-Instanz
	* @param $sessionID Browser-SessionID
	*/
	function __construct(&$db, $sessionID) {

		$this->db = & $db;
		$this->sessionID = $sessionID;
		$this->isExist = false;

		$this->refresh();

	}

	/**
	* @brief Destruktor
	*/
    function __destruct() {
		unset($this->row);
	}

	/**************************************************************************
	* Methoden                                                                *
	**************************************************************************/

	/**
	* @brief Ergebnismenge abfragen
	*/
	function refresh() {

	$this->db->execute('SET character_set_client = utf8');
	$this->db->execute('SET character_set_results = utf8');
	$this->db->execute('SET character_set_connection = utf8');
							
		$sql = "SELECT * FROM vsm_tUser
				WHERE id = (SELECT userID FROM vsm_tBrowserSessions WHERE
							sessionID='".$this->sessionID."');";

		$dbr = $this->db->query($sql);
		$this->row = $dbr->rewind();
		
		$this->exist = ($dbr->getCount() > 0);

		unset($dbr);

	}

	/**
	* @brief Gibt zurueck, ob der Benutzer existiert.
	* @return boolean
	*/
	public function doesExist() {return $this->exist;}

	/**
	* @brief Erstellt einen String aus Zufallszahlen
	* @param $length Anzahl der Zeichen
	* @return String
	*/
	static function makeSalt($length = 5) {

		$arr = array();

		// Zufallszahlen erzeugen und an Array haengen:
		array_push($arr, mt_rand(1, 9));
		for ($i = 1; $i < $length; $i++) {
			array_push($arr, mt_rand(0, 9));
		}

		// Array zu String zusammenfuehren und zurueckgeben:
		return implode($arr);
		unset($arr);

	}
	
	function addUser($email, $pw, $rights) {

		$erg = '';
		if ($email == '') $erg = 'err_nouser';
		if ($erg == '') if ($pw == '') $erg = 'err_nopw';
		
		if ($erg == '') {
			$sql = "SELECT id FROM vsm_tUser WHERE email='".$email."';";
			$dbr = $this->db->query($sql);
			if ($dbr->getCount() == 0) {
				$salt = $this->makeSalt();			
				$pwadd = md5($pw.$salt);			
				
				$sql = "INSERT INTO vsm_tUser (email, password, salt, rights)
					VALUES ('".$email."', '".$pwadd."', '".$salt."', ".$rights.");";
				
				$this->db->execute($sql);
				$erg = 'ok';
			} else {
				$erg = 'user_exists';
			}			
			unset($dbr);
			unset($row);			
		}
		return $erg;
	}
	
	function changeUser($id, $email, $pw, $rights) {

		$erg = '';
		
		
		$sql = "SELECT * FROM vsm_tUser WHERE email='".$email."';";
		$dbr = $this->db->query($sql);

		if ($dbr->getCount() > 0) {
			$row = $dbr->rewind();
			if ($id != $row['id']) {
				$erg = 'user_exists';
			}
			unset($row);
		}
		unset($dbr);
		if ($erg == '') {		
			if ($pw != '') {
				$salt = $this->makeSalt();			
				$pwadd = md5($pw.$salt);			
			} else {				
				$sql = "SELECT salt, password FROM vsm_tUser WHERE id=".$id.";";
				$dbr = $this->db->query($sql);
				$row = $dbr->rewind();
				$salt = $row['salt'];
				$pwadd = $row['password'];
				unset($row);
				unset($dbr);
			}
		}		
		if ($erg == '') {			
			$sql = "UPDATE vsm_tUser SET email='".$email."', password='".$pwadd."',
						salt='".$salt."', rights=".$rights." WHERE id=".$id.";";
			$this->db->execute($sql);
			$erg = 'ok';
		}
		return $erg;
	}

	
	/**
	* @brief Benutzer anmelden
	* @param $email E-Mail-Adresse des Benutzers
	* @param $pw Passwort des Benutzers
	* @return true, wenn der Login erfolgreich war, false sonst
	*/
	function login($email, $pw) {

	
		// Sessions aktualisieren:
		GarbageCollection::updateSessions($this->db);


		$err = '';
		
		if ($email == '' and $pw == '') {
			$err = 'login&WRONG_PW;';
		}
		
		// Ist die Session gesperrt?
		$sql = "SELECT IFNULL(lockedUntil,0) AS lockedUntil FROM vsm_tBrowserSessions WHERE sessionID='".$this->sessionID."';";
		$dbr = $this->db->query($sql);
		$row = $dbr->rewind();
		if ($row['lockedUntil'] >= time()) $err = 'login&BLOCKED;';
		
		// Existiert der Benutzer?
		$sql = "SELECT id, email, password, salt from vsm_tUser
				WHERE email='".$email."';";		
				
		$dbr = $this->db->query($sql);

		if ($err == '') {
			
			if ($dbr->getCount() > 0) {
			
			
				// Stimmt das Passwort?
				$row = $dbr->rewind();

				if (($row['password'] == md5($pw.$row['salt']))) {
				
					// Pruefen, ob Benutzer bereits angemeldet ist:
//					$sql = "UPDATE vsm_tBrowserSessions SET userID = NULL WHERE userID=".$row['id'].";";
	//				$this->db->execute($sql);

	//				if ($err == '') {
					
						// Status auf Login-setzen:
						$sql = "UPDATE vsm_tBrowserSessions SET userID=".$row['id'].", error='' WHERE sessionID='".$this->sessionID."';";
						$this->db->execute($sql);
					
		//			}
					
					// Alte Inhalte entfernen:
					GarbageCollection::collectGarbage($this->db);
					
				} else {
					$err = 'login&WRONG_PW;';
				}
				
				unset($row);
			} else {
				$err = 'login&NO_USER;';
			}
		}
		
		// Eventuell Fehler speichern:
		if ($err != '') {
			$err .= '-'.$email;
			$sql = "UPDATE vsm_tBrowserSessions SET error='".$err."', lockedUntil = ".(time()+ 5).", 
					userID = NULL where sessionID='".$this->sessionID."';";
			$this->db->execute($sql);
		}
		
		unset($dbr);
		return ($err == '');
		
	}

	/**
	* @brief Gibt zurueck, ob der aktuelle Benutzer angemeldet ist
	* @return true, wenn der Benutzer eingeloggt ist, false sonst
	*/
	function loggedIn() {
		$sql = "SELECT userID FROM vsm_tBrowserSessions WHERE sessionID='".$this->sessionID."';";
		$dbr = $this->db->query($sql);
		$row = $dbr->rewind();		
		return (isset($row['userID']));
	}

	/**
	* @brief Benutzer ausloggen
	*/		
	function logout() {
		
		// Benutzer ausloggen:
		$sql = "DELETE FROM vsm_tBrowserSessions WHERE sessionID='".$this->sessionID."';";
		
		$this->db->execute($sql);
		
		// Session schliessen:
		session_start(); 
		session_unset(); 
		session_destroy(); 

		// Neue Session starten:
		session_start();
		if (!isset($_SESSION['id'])) $_SESSION['id']= md5(microtime());
	
	}

    /**
	* @brief Fehlermeldungen entfernen
	*/
	function clearError() {

		// Fehler entfernen:
		$sql = "UPDATE vsm_tBrowserSessions SET error=null WHERE
				sessionID='".$this->sessionID."';";
		$this->db->execute($sql);

	}

	/**************************************************************************
	* Getter- und Setter-Methoden                                             *
	**************************************************************************/
	
    /**
	* @brief Fehlermeldungen hinzufuegen
	* @param $err Fehler (String)
	*/
	function setError($err) {

		$sql = "UPDATE tBrowserSessions SET error='".$err."' WHERE
				sessionID='".$this->sessionID."';";
		$this->db->execute($sql);
	}

    /**
	* @brief Fehlertext zurueckgeben
	* @return Fehlertext
	*/	
	function getErrorString() {

		// Fehlerstring ermitteln:
		$sql = "SELECT error FROM vsm_tBrowserSessions WHERE 
				sessionID='".$this->sessionID."';";

		$dbr = $this->db->query($sql);

		$erg = '';

		if ($dbr->getCount() > 0) {
			$row = $dbr->rewind();
			$erg = $row['error'];
			unset($row);
		}
		unset($dbr);
		return $erg;

	}

	/**
	* @brief eMail-Adresse zurueckgeben
	* @return eMail-Adresse
	*/
	function getMail() {return $this->row['email'];}

	/**
	* @brief Rechte zurueckgeben
	* @return Rechte
	*/
	function getRights() {return $this->row['rights'];}

	/**
	* @brief Gesetzte Filter zurueckgeben:
	* @return Filter (String-Array)
	*/
	function getFilter() {
		$sql = "SELECT * FROM vsm_tFilter WHERE userID = ".$this->row['id'].";";
		$dbr = $this->db->query($sql);
		
		$erg = array();
		
		foreach ($dbr as $row) {
			$noexpired = "b";
			$noperm = "b";
			if (strpos($row['server'], "x") !== false) { $noexpired = "a"; }
			if (strpos($row['server'], "y") !== false) { $noperm = "a"; }
			if (strpos($row['server'], "z") !== false) { $noexpired = "a"; $noperm = "a"; }
			$erg[] = array('id' => $row['id'], 'server' => preg_replace("/[x|y|z|X|Y|Z]/", "", $row['server']), 'gruppe' => $row['gruppe'], 'noexpired' => $noexpired, 'noperm' => $noperm);
		}
		unset($dbr);
		return $erg;
	}

	/**
	* @brief Filter loeschen: 
	*/
	function deleteFilter($server, $gruppe, $noexpired, $noperm) {
		$tmp_server = $server;
		if ($noexpired == "a") $tmp_server = $server."x";
		if ($noperm == "a") $tmp_server = $server."y";
		if (($noexpired == "a") && ($noperm == "a")) $tmp_server = $server."z";
		
		$sql = "DELETE FROM vsm_tFilter WHERE server = '".$tmp_server."' AND gruppe = '".$gruppe."' AND userID = ".$this->row['id'].";";
		$this->db->execute($sql);
	}

	/**
	* @brief Filter setzen:
	* @return 0 (alles OK), 1 (Filter existiert bereits), 2 (Max Anzahl erreicht)
	*/
	function addFilter($server, $gruppe, $noexpired, $noperm) {
		
		$err = 0;
		$tmp_server = $server;
		if ($noexpired == "a") $tmp_server = $server."x";
		if ($noperm == "a") $tmp_server = $server."y";
		if (($noexpired == "a") && ($noperm == "a")) $tmp_server = $server."z";
		$sql = "SELECT id FROM vsm_tFilter WHERE server = '".$tmp_server."' AND gruppe = '".$gruppe."' AND userID = ".$this->row['id'].";";
		$dbr = $this->db->query($sql);
		if ($dbr->getCount() > 0) {
			$err = 1;
		} else {
			
			$sql = "SELECT id FROM vsm_tFilter WHERE userID = ".$this->row['id'].";";
			$dbr = $this->db->query($sql);
			if ($dbr->getCount() > 10) {
				$err = 2;
			} else {
				$sql = "INSERT INTO vsm_tFilter (server, gruppe, userID) VALUES ('".$tmp_server."', '".$gruppe."',".$this->row['id'].");";
				$this->db->execute($sql);
			}		
		}

		return $err;
	}

	
	/**
	* @brief Benutzer-ID zurueckgeben
	* @return Benutzer-ID
	*/
	function getID() {return $this->row['id'];}
	
	/**
	* @brief Seed fuer Zufallsgenerator erzeugen
	* @return Seed
	*/	
	private function make_seed() {
		list($usec, $sec) = explode(' ', microtime());
		return (float) $sec + ((float) $usec * 100000);
	}

}

?>
