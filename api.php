<?php
include('config.php');

if (!$user->loggedIn()) {

	// Auf Loginseite weiterleiten:	
	if ($settings['currentPage'] != 'login') {
		header('Location: index.php?section=login');
		die();
	}
	$settings['menu'] = false;
}

function secondsToTime($seconds) {
	date_default_timezone_set('UTC'); // Potential for mistakes
    $dtF = new DateTime('@0');
    $dtT = new DateTime("@$seconds");
    return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
}

if (!isset($_POST['type'])) {

	if (!isset($_GET['type'])) {
		$bla = $user->getFilter();

		$tmp = '';
		$i = 0;
		foreach($bla as $row) {
			if ($i > 0) $tmp .= " OR";
			if ($row['gruppe'] > -1) {
				$tmp .= " gametype = '".$row['server']."' AND servergroup = '".$row['gruppe']."'";
			} else {
				$tmp .= " gametype = '".$row['server']."'";				
			}
			if ($row['noexpired'] == "a") $tmp .= " AND status != 'inactive' AND status != 'expired'";
			if ($row['noperm'] == "a") $tmp .= " AND TIMESTAMPDIFF(DAY,UTC_TIMESTAMP(), timestamp) < 2000";

			$i++;
		}
		if ($tmp != '') $tmp = ' WHERE'.$tmp;
			
		$sql = "SELECT id, gametype, servergroup, playername, admin, status, timestamp, comment, TIMESTAMPDIFF(HOUR,UTC_TIMESTAMP(),timestamp) AS hour, TIMESTAMPDIFF(SECOND,UTC_TIMESTAMP(), timestamp) AS days FROM vsm_vips".$tmp." LIMIT 5000;";

		$dbr = $db->query($sql);
		$erg = '{"data": [' . "\n";
		$i = 0;
		$m = $dbr->getCount();
		$rights = $user->getRights();
		foreach ($dbr as $row) {
			
			$tmp_time = '';
			if ($row['hour'] > 24) {
				$tmp_days = round(($row['hour'] / 24));	  
				$tmp_time = number_format(round(($row['hour'] / 24))).' day';
				if ($tmp_days != 1) $tmp_time .= 's';		
			} else {
				if ($row['hour'] >= 0) {
					$tmp_time =  number_format($row['hour']).' hour';
					if ($tmp_time != 1) $tmp_time .= 's';
				}
			}
			
			if ($row['status'] == 'actived') $row['status'] = 'active';
			
			$linkl = ''; $linkr = '';
			if ($rights != 2) {
				if ($row['status'] == 'active' or $row['status'] == 'inactive' or $row['status'] == 'expired') {	
					$linkl = '<a href=\"index.php?section=vip&id='.$row['id'].'\" class=\"plink\">';
					$linkr = '</a>';
				}
			}
			
			if ($row['status'] == 'active') { $helper = 'Player is a valid VIP on Gameserver'; }
			if ($row['status'] == 'inactive') { $helper = 'Player is NOT a VIP on Gameserver'; }
			if ($row['status'] == 'expired') { $helper = 'Player is NOT a VIP on Gameserver. Player will get a VIP Slot Expired Message on next spawn/join event.'; }
			if ($row['status'] == 'adding') { $helper = 'Still in progress. Gameserver will receive this information on next Sync.'; }
			if ($row['status'] == 'deleting') { $helper = 'Still in progress. Gameserver will receive this information on next Sync.'; }
			if ($row['status'] == 'removing') { $helper = 'Still in progress. Gameserver will receive this information on next Sync.'; }
			
			$tmp_time = '<span title=\"'.secondsToTime($row['days']).'\">'.$tmp_time.'</span>';
			
			$comment = preg_replace("/[\n\r]/"," ",$row['comment']);
			if (strlen($comment) > 15) $comment = '<span title=\"'.$comment.'\">'.mb_substr($comment, 0, 15).'...</span>';
			
			$player = $row['playername'];
			if (strlen($player) > 15) $player = '<span title=\"'.$player.'\">'.mb_substr($player, 0, 15).'...</span>';
			$admin = $row['admin'];
			if (strlen($admin) > 15) $player = '<span title=\"'.$admin.'\">'.mb_substr($admin, 0, 15).'...</span>';
			
			$tmp = '["'. ''. '", "'.$row['id'].'", "' . $row['gametype']. ' - '. $row['servergroup']. '", " '.$linkl.$player.$linkr.'", "<span title=\"'. $helper . '\" class=\"st_'.$row['status'].'\" >'.$row['status']. '</span> '. $tmp_time . '", "'. $admin . '", "'. $comment. '"]';
			$i++;
			$erg .= $tmp;	
			if ($i < $m) $erg .= ',';	
			
			$erg .= "\n";
		}
		$erg .= ']}';
		echo $erg;
		unset($dbr);
	} else {
		if ($_GET['type'] == 'user') {
			
			if ($user->getRights() != 0) die();
			$sql = "SELECT id, email, rights FROM vsm_tUser;";

			$dbr = $db->query($sql);
			$erg = '{"data": [' . "\n";
			$i = 0;
			$m = $dbr->getCount();
			foreach ($dbr as $row) {
				if ($row['rights'] == 0) $rights = 'admin';
				if ($row['rights'] == 1) $rights = 'leader';
				if ($row['rights'] == 2) $rights = 'view only';
				$linkl = '<a href=\"index.php?section=user&id='.$row['id'].'\" class=\"plink\">';
				$linkr = '</a>';				
				$tmp = '["",'.$row['id'].', "'.$linkl.$row['email'].$linkr.'", "'. $rights. '"]';
				$i++;
				$erg .= $tmp;	
				if ($i < $m) $erg .= ',';				
				$erg .= "\n";
			}
			$erg .= ']}';
			echo $erg;
		
		}
	}
	
	
} else {

	if ($_POST['type'] == 'server') {
			
		if ($gametype == 'BF3' || $gametype == 'BF4' || $gametype == 'BFH' || $gametype == 'BC2') {
			$gametype = $_POST['id'];
		} else {
			$gametype = 'BC2';
		}		
			
		$sql = "SELECT DISTINCT servergroup FROM vsm_vips WHERE gametype='".escape($_POST['id'])."';";
		$dbr = $db->query($sql);
		$tmp = '';
		foreach ($dbr as $row) {
			$tmp .= '<option value="'.$row['servergroup'].'">'.$row['servergroup'].'</option>';
		}
		$tmp .= '<option value="-1">All</option>';
		
		echo $tmp;
		unset($dbr);

	}
	if ($_POST['type'] == 'add') {
		$value = escape($_POST['id']);
		
		$arr = explode(' - ', $value);
		$server = $arr[0];
		$gruppe = intval($arr[1]);
		$noexpired = $arr[2];
		$noperm = $arr[3];
		
			if ($server == 'BF3' || $server == 'BF4' || $server == 'BFH' || $server == 'BC2') {
				$server = $arr[0];
			} else {
				$server = 'BC2';
			}

		$erg = $user->addFilter($server, $gruppe, $noexpired, $noperm);

		echo $erg;
	}

	if ($_POST['type'] == 'del') {
		
		if ($user->getRights() == 2) die();


		$value = escape($_POST['id']);

		$arr2 = explode('<', $value);
		$value = $arr2[0];
		$noexpired = 'b';
		$noperm = 'b';
		
		if (strpos($value, ' (no exp.)')  !== false) { $value = str_replace(' (no exp.)', '', $value); $noexpired = 'a'; }
		if (strpos($value, ' (no perm)')  !== false) { $value = str_replace(' (no perm)', '', $value); $noperm = 'a'; }
		if (strpos($value, ' (no exp./perm)')  !== false) { $value = str_replace(' (no exp./perm)', '', $value); $noexpired = 'a'; $noperm = 'a'; }
		if (strpos($value, '-')  === false) $value .= ' - -1';
		$arr = explode(' - ', $value);
		$server = $arr[0];

			if ($server == 'BF3' || $server == 'BF4' || $server == 'BFH' || $server == 'BC2') {
				$server = $arr[0];
			} else {
				$server = 'BC2';
			}
		
		$gruppe = intval($arr[1]);
		$user->deleteFilter($server, $gruppe, $noexpired, $noperm);

		echo $server." ".$gruppe." ".$value;
//		echo 0;
	}

	if ($_POST['type'] == 'sel') {
		
		if ($user->getRights() == 2) die();
		
		$type = intval(escape($_POST['id']));
		$values = escape($_POST['values']);

		
		$arr = explode(',', $values);
		
		for ($i = 0; $i < sizeof($arr); $i++) {
			$arr[$i] = intval($arr[$i]);
		} 

		$tmp = 'id = '.$arr[0];
		
		for ($i = 1; $i < sizeof($arr); $i++) {
			$tmp .= ' OR id='.$arr[$i];
		} 
		$tmp = '('.$tmp.')';

		if ($type < 0 || $type > 11) $type = 0;
	
		if ($type >= 1 and $type <= 5) {
			if ($type == 1) $days = 30;
			if ($type == 2) $days = 60;
			if ($type == 3) $days = 90;
			if ($type == 4) $days = 180;
			if ($type == 5) $days = 365;
			$sql = "UPDATE vsm_vips SET timestamp=DATE_ADD(IF(TIMESTAMPDIFF(DAY,timestamp,UTC_TIMESTAMP()) > 0, UTC_TIMESTAMP(), timestamp), INTERVAL ".$days." DAY),
				status = IF(status = 'active', 'active', 'adding'), admin='".$user->getMail()."'
				WHERE (status = 'active' or status = 'inactive' or status = 'expired') and ".$tmp.";";
			$db->execute($sql);
		}
		if ($type == 6) {
			$sql = "UPDATE vsm_vips SET timestamp=DATE_ADD(UTC_TIMESTAMP(), INTERVAL 30 DAY),
				status = IF(status = 'active', 'active', 'adding'), admin='".$user->getMail()."'
				WHERE (status = 'active' or status = 'inactive' or status = 'expired') and ".$tmp.";";
			$db->execute($sql);				
		}
		if ($type == 7) {
			$sql = "UPDATE vsm_vips SET timestamp=DATE_ADD(UTC_TIMESTAMP(), INTERVAL 7 YEAR),
				status = IF(status = 'active', 'active', 'adding'), admin='".$user->getMail()."'
				WHERE (status = 'active' or status = 'inactive' or status = 'expired') and ".$tmp.";";
			$db->execute($sql);				
		}
		if ($type == 8) {			
			$sql = "UPDATE vsm_vips SET status = IF(status = 'active', 'removing', 'inactive'),
					admin='".$user->getMail()."' WHERE (status='active' or status='inactive' or status='expired') AND ".$tmp.";";
			$db->execute($sql);					
		}
		if ($type == 9) {
			$gametype = escape($_POST['gametype']);
			
			if (!($gametype == 'BF3' || $gametype == 'BF4' || $gametype == 'BFH' || $gametype == 'BC2')) {
				$gametype = 'BC2';
			}		
			$servergroup = intval(escape($_POST['servergroup']));		
			$sql = $gametype.' - '.$servergroup.' '.$tmp;
			$sql = "SELECT * FROM vsm_vips WHERE ".$tmp.";";
			$dbr = $db->query($sql);
			
			foreach ($dbr as $row) {
				if ($row['servergroup'] != $servergroup or $row['gametype'] != $gametype) {
					$sql2 = "INSERT INTO vsm_vips (gametype, servergroup, playername, timestamp, status, admin, comment)   
							VALUES ('".$gametype."', ".$servergroup.", '".$row['playername']."', '".$row['timestamp']."',
							'".$row['status']."', '".$user->getMail()."', '".$row['comment']."');"; 
							
					$db->execute($sql2);
				}					
			}
			unset($dbr);
			$sql = '';
		}
		if ($type == 10) {
			$sql = "DELETE FROM vsm_vips WHERE (status='inactive' or status='expired') AND ".$tmp.";";
			$db->execute($sql);
			
			$sql = "UPDATE vsm_vips SET status = 'deleting', admin='".$user->getMail()."' WHERE status='active' AND ".$tmp.";";
			$db->execute($sql);
			
		}
		if ($type == 11) {
			$sql = "DELETE FROM vsm_vips WHERE ".$tmp.";";
			$db->execute($sql);
			
		}

		
//		echo $sql;		
		
	}

	if ($_POST['type'] == 'change') {

		if ($user->getRights() == 2) die();
	
		$id = intval($tmp_date = escape($_POST['id']));
		$tmp_date = escape($_POST['date']);
		$farr = $db->real_escape_string(urldecode($_POST['farr']));
		$status = $db->real_escape_string(urldecode($_POST['status']));
		$comment = $db->real_escape_string(urldecode($_POST['comment']));		
		$v = explode('#', $farr);
		$v2 = explode(' - ', $v[0]);
		$gametype = $v2[0];
		$servergroup = $v2[1];
		unset($v);
		unset($v2);

		$sql = "SELECT status, TIMESTAMPDIFF(SECOND, UTC_TIMESTAMP(), '".$tmp_date."') AS diff FROM vsm_vips WHERE id=".$id.";";

		$dbr = $db->query($sql);
		$row = $dbr->rewind();
		$dateok = ($row['diff'] > 0);
		$old_status = $row['status'];
		unset($dbr);

		$err = 'ok';
		if (($status == 'active') and ($old_status == 'inactive' || $old_status == 'expired')) {
			if ($dateok) {
				$status = 'adding';
			} else {
				$err = 'date';
			}			
		}
		
		if ($old_status == 'active') {
			if (!$dateok) {
				$err = 'date';
			}
		}


		if ($status == 'inactive') {
			if ($old_status == 'active') {
				$err = 'ok';
				$status = 'removing';
			}
			if ($old_status == 'expired') {
				$status = 'inactive';				
			}			
		}
			 
		if ($err == 'ok') {
			$sql = "UPDATE vsm_vips SET gametype='".$gametype."', servergroup=".$servergroup.",
						timestamp='".$tmp_date."', status='".$status."', admin='".$user->getMail()."',
						comment='".$comment."' WHERE id=".$id.";";
			$db->execute($sql);
		}
		echo $err;
	}
	
	if ($_POST['type'] == 'insert') {

		if ($user->getRights() == 2) die();
	
		$tmp_date = escape($_POST['date']);
//		$farr = escape(urldecode($_POST['farr']));
//		$playername = trim(escape(urldecode($_POST['playername'])));
//		$status = escape(urldecode($_POST['status']));
//		$comment = escape(urldecode($_POST['comment']));

		//$farr = $db->real_escape_string($_POST['farr']);
		$playername = trim($db->real_escape_string(urldecode($_POST['playername'])));
	//	$status = $db->real_escape_string($_POST['status']);
	//	$comment = $db->real_escape_string($_POST['comment']);

				$farr = $db->real_escape_string(urldecode($_POST['farr']));
		$status = $db->real_escape_string(urldecode($_POST['status']));
		$comment = $db->real_escape_string(urldecode($_POST['comment']));		
		
		$v = explode('#', $farr);
		if ($playername == '') $erg = 'err_nouser';
		
		if ($erg == '') {
			$playerexists = false;
			
			if (sizeof($v) <= 1) {
				$erg = 'err_no_server';
			} else {
				
				$sql = "SELECT id, TIMESTAMPDIFF(SECOND, UTC_TIMESTAMP(), '".$tmp_date."') AS diff FROM vsm_tUser WHERE id=".$user->getID().";";
				$dbr = $db->query($sql);
				$row = $dbr->rewind();
				$dateok = ($row['diff'] > 0);
				unset($row);
				unset($dbr);
				
				if (!$dateok) {
					$erg = 'err_date';
				} else {
				
					for ($i = 0; $i < sizeof($v) - 1; $i++) {
						$v2 = explode(' - ', $v[$i]);
						$gametype = $v2[0];
						$servergroup = $v2[1];
						unset($v2);			
						$sql = "SELECT id FROM vsm_vips WHERE playername='".$playername."' AND gametype='".$gametype."' AND servergroup=".$servergroup.";";
						$dbr = $db->query($sql);
						if ($dbr->getCount() > 0) {
							$playerexists = true;
							break;
						}
					}
					if (!$playerexists) {
						$erg = '';
						for ($i = 0; $i < sizeof($v) - 1; $i++) {
							$v2 = explode(' - ', $v[$i]);
							$gametype = $v2[0];
							$servergroup = $v2[1];
							unset($v2);
							if ($status == 'active') $status = 'adding';				
							$sql = "INSERT INTO vsm_vips (gametype, servergroup, playername, timestamp, status, admin, comment)
									VALUES ('".$gametype."', ".$servergroup.", '".$playername."', '".$tmp_date."', '".$status."', '".$user->getMail()."', '".$comment."');";
							$db->execute($sql);
							$erg = "ok";
						}
					} else {
						$erg = 'err_user_exists';
					}
				}
			}
		}
		
		echo $erg;
		unset($v);
		
	}

	if ($_POST['type'] == 'delvip') {

		if ($user->getRights() == 2) die();
	
		$id = intval(escape(urldecode($_POST['id'])));
		
		$sql = "SELECT status FROM vsm_vips WHERE id=".$id.";";
		$dbr = $db->query($sql);
		$row = $dbr->rewind();
		
		$sql = "";
		if ($row['status'] == 'inactive' or $row['status'] == 'expired') {
			$sql = "DELETE FROM vsm_vips WHERE id=".$id.";";
			$db->execute($sql);
		}

		if ($row['status'] == 'active') {
			$sql = "UPDATE vsm_vips SET status='deleting' WHERE id=".$id.";";
			$db->execute($sql);			
		}
		unset($db);
	}
	
	
	if ($_POST['type'] == 'insertuser') {

		if ($user->getRights() == 2) die();

		$username = trim(escape(urldecode($_POST['username'])));
		$rights = escape(urldecode($_POST['status']));
		$pw = escape(urldecode($_POST['pw']));
	
		$erg = $user->addUser($username, $pw, $rights);
		echo $erg;
	}

	if ($_POST['type'] == 'deluser') {

		if ($user->getRights() == 2) die();
	
		$id = intval(escape(urldecode($_POST['id'])));
		
		$sql = "DELETE FROM vsm_tUser WHERE id=".$id.";";
		$db->execute($sql);

	}

	if ($_POST['type'] == 'changeuser') {


		$username = trim(escape(urldecode($_POST['username'])));
		$rights = intval(escape(urldecode($_POST['status'])));
		$pw = escape(urldecode($_POST['pw']));
		$id = intval(escape(urldecode($_POST['id'])));

		if ($user->getRights() != 0) {
			$id = $user->getID();
			$rights = $user->getRights();
		}
	
		$erg = $user->changeUser($id, $username, $pw, $rights);
		echo $erg;
	}
	
}
	
// Aufraeumen:
$db->close();
unset($db);
unset($user);

?>
