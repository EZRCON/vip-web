<?php

if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require __DIR__ . '/../../vendor/autoload.php';

    $dotenv = new \Dotenv\Dotenv(__DIR__ . '/../../');
    $dotenv->load();
} else {
    die('Failed to load');
}

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);


// SETTINGS - SQL LOGIN DETAILS:

$settings['server'] = $_SERVER['DB_HOST'];
$settings['port'] = isset($_SERVER['DB_PORT']) ? $_SERVER['DB_PORT'] : 3306;
$settings['dbName'] = $_SERVER['DB_DATABASE'];
$settings['dbUser'] = $_SERVER['DB_USERNAME'];
$settings['dbPW'] = $_SERVER['DB_PASSWORD'];

// SETTINGS - SQL LOGIN DETAILS - END

error_reporting(E_ERROR | E_PARSE);

$settings['title'] = 'VIP Slot Manager ' . $_SERVER['APP_NAME'];
$settings['jsAdd'] = '';
$settings['left'] = '';

include('mainFunctions.php');

include('classMobile.php');
$mobile = new Mobile();

$settings['mob'] = $mobile->getMobileBrowser();

include('classMySQL.php');


try {
    $db = new TMySql($settings['server'], $settings['dbName'],
        $settings['dbUser'], $settings['dbPW'], $settings['port']);
} catch (EMySql $e) {
    die($e->getMessage());
}


// Setup:
$setup = false;

if ($setup) {
    $db->query('SET character_set_client = utf8');
    $db->query('SET character_set_results = utf8');
    $db->query('SET character_set_connection = utf8');

    $sql = "CREATE TABLE IF NOT EXISTS <tablename> ("
        . "id INT NOT NULL auto_increment,"
        . "sessionID VARCHAR(250) NOT NULL,"
        . "time INT NOT NULL,"
        . "lockedUntil INT NOT NULL,"
        . "error VARCHAR(300),"
        . "userID INT,"
        . "tSessionID INT,"
        . "PRIMARY KEY (id));";
    createTable($db, "vsm_tBrowserSessions", $sql);

    $sql = "CREATE TABLE IF NOT EXISTS <tablename> ("
        . "id int NOT NULL auto_increment,"
        . "sessionID varchar(250),"
        . "email varchar(100),"
        . "password varchar(40),"
        . "passwordDummy varchar(20),"
        . "salt VARCHAR(1000),"
        . "rights INT(0),"   // 0: admin, 1: leader, 2: view only
        . "PRIMARY KEY (id));";
    createTable($db, "vsm_tUser", $sql);

    $sql = "CREATE TABLE IF NOT EXISTS <tablename> ("
        . "id int NOT NULL auto_increment,"
        . "userID INT,"
        . "server varchar(10),"
        . "gruppe varchar(10),"
        . "PRIMARY KEY (id));";
    createTable($db, "vsm_tFilter", $sql);

    $pw = 'admin';
    $salt = base64_decode($_SERVER['APP_KEY']);
    $pwadd = md5($pw . $salt);
    $sql = "INSERT IGNORE INTO  `vsm_tUser` (email, password, salt, rights)
			VALUES ('admin', '" . $pwadd . "', '" . $salt . "', 0);";

    $db->execute($sql);
}

// Session :
@ini_set('session.use_only_cookies', 1);
@ini_set('session.use_trans_sid', 0);

session_start();

if (!isset($_SESSION['id'])) $_SESSION['id'] = md5(microtime());

// Sessions :
GarbageCollection::updateSessions($db);

// check:
$sections['login'] = [true, false];
$sections['home'] = [true, false];
$sections['vip'] = [true, false];
$sections['userlist'] = [true, false];
$sections['user'] = [true, false];

$settings['session'] = false;
$settings['currentPage'] = 'home';

if (isset($_GET['section']) && $sections[$_GET['section']][0]) {
    $settings['currentPage'] = $_GET['section'];
    $settings['session'] = $sections[$_GET['section']][1];
}


// user class
include_once('classUser.php');
$user = new User($db, $_SESSION['id']);

