<?php

/******************************************************************************
* GarbageCollection-Klasse                                                    *
******************************************************************************/

/**
* @brief Veraltete Eintraege loeschen
*/
class GarbageCollection {

	// Sessionvorhaltezeit (in Sekunden):
	static $EXPIRATION_TIME_SECONDS = 432000;

	/**
	* @brief Sessions aktualisieren
	* @param TMySql $db MySql-Instanz
	*/
	static function updateSessions(&$db, $logout = false) {
		
		// Ist die Session schon gespeichert?
		$sql = "SELECT id FROM vsm_tBrowserSessions
				WHERE sessionID = '".$_SESSION['id']."';";

		$dbr = $db->query($sql);

		// Wenn nicht, neuen Eintag anlegen:
		if ($dbr->getCount() == 0) {

			$ref = 0;
			
			$sql = "INSERT INTO vsm_tBrowserSessions
					(sessionID, time)
					VALUES ('".$_SESSION['id']."',".time().");";

		} else {
			$row = $dbr->rewind();
			$ref = 0;
			
			
			// Wenn ja, Eintrag aktualisieren:
			$sql = "UPDATE vsm_tBrowserSessions SET time=".time()." 
					WHERE sessionID = '".$_SESSION['id']."';";
		}
		
        $db->execute($sql);
		unset($dbr);

	}

	/**
	* @brief Sessionvariable in Datenbank setzen
	* @param TMySql $db MySql-Instanz
	* @param $name Feldname
	* @param $value zu setzender Wert
	*/
	static function setSessionValue(&$db, $name, $value) {
		updateSessions();
		$sql = "UPDATE vsm_tBrowserSessions SET ".mysql_real_escape_string($name)."='".
				mysql_real_escape_string($value)."'".
			   " WHERE sessionID = '".$_SESSION['id']."';";
		$db->execute($sql);
	}

	/**
	* @brief Alte Sessions loeschen
	* @param TMySql $db MySql-Instanz
	*/
	static function collectGarbage(&$db) {

		// Abgelaufende Browser-Sessions loeschen:
		$sql = "DELETE FROM vsm_tBrowserSessions WHERE
				  ".time()." - time > ".GarbageCollection::$EXPIRATION_TIME_SECONDS.";";
		$db->execute($sql);

		// Zugehoerige Filter loeschen:
		$sql = "DELETE vsm_tFilter FROM vsm_tFilter LEFT JOIN vsm_tBrowserSessions ON 
				vsm_tFilter.userID = vsm_tBrowserSessions.userID WHERE vsm_tBrowserSessions.userID IS NULL;";
				
		$db->execute($sql);

	}

}

/**
* @brief Absoluten Pfad der Homepage ermitteln
* @param $ssl true, wenn die URL auf eine SSl-Seite verweisen soll,
*             false sonst (Standard)
* @return Pfad
*/
function absolutePath($ssl = false) {
	
	// Skriptpfad bestimmen:
	$tmp = $_SERVER['SCRIPT_URI'];
	
	// Skriptname abschneiden:
	$l = strrpos($tmp, '/');
	if ($l !== false) {
		$tmp = substr($tmp, 0, $l);
		if (substr($tmp, -1) != '/') $tmp .= '/';
	}
	
	// Auf Wunsch SSL-Prefix voranstellen:
	if ($ssl) {
		if (strpos($tmp, 'http://www.') !== false) {
			$tmp = str_replace('http://www.', 'https://www.ssl-id.de/', $tmp);
		} else {
			$tmp = str_replace('http://', 'https://www.ssl-id.de/', $tmp);
		}		
	}
	
	return $tmp;
 
}

/**
 * @brief Pruefen, ob eine Tabelle existiert.
 * @param TMySql $db DB-Verbindung
 * @param string $name Name der Datenbank
 * @return boolean
 */
function table_exists(&$db, $name) {
	$tmp = "SELECT * FROM ".$name;
	try {
		$db->execute($tmp);
	} catch (exception $e) {
		return false;
	}
	return true;
}

/**
 * @brief SQL-Tabelle erzeugen
 * @param TMySQL $db Datenbankreferenz
 * @param $tableName Name der Tabelle
 * @param $sql Erzeugungsstring
 */
function createTable(&$db, $tableName, $sql) {

	// Tabelle loeschen, wenn schon vorhanden:
	if (table_exists($db, $tableName)) $db->execute("DROP TABLE ".$tableName);

	// Tabellennamen setzen:
	$sql = str_replace("<tablename>", $tableName, $sql);

	// Tabelle in Datenbank setzten:
	try {
//		$db->execute($sql);
	 echo $sql;
	} catch (exception $e) {
		echo 'Exception abgefangen: ',  $e->getMessage(), "\n";
	}

}

/**
* @brief Sonderzeichen in HTML-Code konvertieren
* @param $text zu pruefender Text
* @return konvertierter text
*/
function htmlChars($text) {
	$erg = str_replace('Ä', '&Auml;', $text);
	$erg = str_replace('ä', '&auml;', $erg);
	$erg = str_replace('Ö', '&Ouml;', $erg);
	$erg = str_replace('ö', '&ouml;', $erg);
	$erg = str_replace('Ü', '&Uuml;', $erg);
	$erg = str_replace('ü', '&uuml;', $erg);
	$erg = str_replace('ß', '&szlig;', $erg);
	$erg = str_replace('©', '&copy;', $erg);
	$erg = str_replace('€', '&euro;', $erg);
	$erg = str_replace('®', '&reg;', $erg);
	return $erg;
}

/**
* @brief Gefaehrliche Zeichen aus String filtern
* @param $str zu pruefender String
* @return gefilterter String
*/
function filterStr($str) {
	$erg = substr($str, 0, 300);	
	$erg = str_replace(';', '', $erg);
	$erg = str_replace('eval(', '', $erg);
	$erg = str_replace('<', '', $erg);
	$erg = str_replace('>', '', $erg);
	$erg = escape($erg);
	return $erg;
}

// replace any non-ascii character with its hex code.
function escape($value) {
    $return = '';
    for($i = 0; $i < strlen($value); ++$i) {
        $char = $value[$i];
        $ord = ord($char);
        if($char !== "'" && $char !== "\"" && $char !== '\\' && $ord >= 32 && $ord <= 126)
            $return .= $char;
        else
            $return .= '\\x' . dechex($ord);
    }
    return $return;
}

/**
* @brief Einzelnes Datenbank-Feld zurueckgeben
* @param $db Datenbank-Referenz
* @param $sql SQL-String
* @param $field Feld-Bezeichnung
* @return Inhalt des Datenbank-Felds
*/
function getSingleField(&$db, $sql, $field) {
	$dbr = $db->query($sql);
	$erg = $dbr->rewind();
	unset($dbr);
	return $erg[$field];
}

?>
