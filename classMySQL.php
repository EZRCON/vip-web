<?php
/**
 * @file classMySQL.php
 * @brief Datenbanklayer
 * @author Hinrich Donner
 *
 * Dieser Quellcode ist urheberrechtlich geschuetzt. Er kann unter den Lizenzbedingungen der
 * GPL oder LGPL verwendet werden.
 * 27.03.2017 Update auf mysqli
 */

/**
 * @brief Abstrakte Interface-Definition
 */
interface IDatabase
{

    /**
     * @brief Datenbankverbindung aufbauen
     *
     * @param string $host IP oder Domain des Servers
     * @param string $user Berechtigter Benutzer
     * @param string $passwd Kennwort des Benutzers
     */
    public function connect($host, $user, $passwd, $name, $port);

    /**
     * @brief Datenbankverbindung loesen
     */
    public function close();

    /**
     * @brief Datenbank auswaehlen
     *
     * @param string $name Name der Datenbank
     */
    public function select($name);

    /**
     * @brief Datenbankanweisung ausfuehren
     *
     * @param string $sql Die Anweisung an die Datenbank
     *
     * @return mixed
     */
    public function execute($sql);

    /**
     * @brief Datenbankabfrage
     *
     * @param string $sql Die Anweisung
     * @param int $offset Relativer Beginn der Ergebnismenge
     * @param int $limit Maximale Anzahl der Zeilen in der Ergebnismenge
     *
     * @return TDatabaseResultSet
     */
    public function query($sql, $offset = 0, $limit = -1);

}

/**
 * @brief Basis-Exception f&uuml;r Datenbank-Anfragen
 */
class EDatabase extends Exception
{
}

/**
 * @brief Basis-Exception fuer MySQL-Abfragen
 */
class EMySql extends EDatabase
{
    /**
     * @brief Konstruktor
     */
    function __construct($link)
    {
        $message = sprintf('%04d: %s', mysqli_errno($link), mysqli_error($link));
        parent::__construct($message, mysqli_errno($link));
    }
}

/**
 * @brief Ergebnismenge einer Datenbank-Abfrage
 */
abstract class TDatabaseResultSet implements Iterator
{
    /**
     * @brief Anzahl der Zeilen in der Ergebnismenge
     * @var int
     */

    protected $_num_rows = 0;

    /**
     * @brief Die Ergebnismenge
     * @var array
     */
    protected $_rows = [];

    /**
     * @brief Konstruktor
     *
     * @param resource $resource Datenbank-Resource
     */
    abstract function __construct($resource);

    public function getCount()
    {
        return $this->_num_rows;
    }

    /**
     * @brief Das naechste Element (Iterator)
     * @return array
     */
    public function next()
    {
        return next($this->_rows);
    }

    /**
     * @brief Das letzte Element
     * @return array
     */
    public function end()
    {
        return end($this->_rows);
    }

    /**
     * @brief Der Index des aktuellen Elements (Iterator)
     * @return array
     */
    public function key()
    {
        return key($this->_rows);
    }

    /**
     * @brief Das erste Element (Ruecksetzen des Zeigers, Iterator)
     * @return array
     */
    public function rewind()
    {
        return reset($this->_rows);
    }

    /**
     * @brief Pruefen des aktuellen Elements (Iterator)
     * @return bool
     */
    public function valid()
    {
        return (bool)is_array($this->current());
    }

    /**
     * @brief Das aktuelle Element (Iterator)
     * @return array
     */
    public function current()
    {
        return current($this->_rows);
    }
}

/**
 * @brief Ergebnismenge einer MySQL-Abfrage
 */
class TMySqlResult extends TDatabaseResultSet
{

    /**
     * @brief Konstruktor
     *
     * @param resource $resource MySQL-Ergebnis-Resource
     */
    function __construct($resource)
    {
        while ($row = @mysqli_fetch_assoc($resource)) {
            $this->_rows[] = $row;
            ++$this->_num_rows;
        }
    }

}

/**
 * @brief Abstrakte Basis des Datenbank-Layers
 */
abstract class TDatabase implements IDatabase
{

    /**
     * @brief Name der aktuellen Datenbank
     * @var string
     */
    protected $database = '';

    /**
     * @brief Datenbankhandle
     * @var resource
     */
    protected $resource = false;

    /**
     * @brief Container mit den ausgefuehrten Anweisungen
     * @var array
     */
    protected $statements = [];

    /**
     * @brief Erste Zelle der ersten Zeile einer Abfrage
     *
     * @param string $sql Die Abfrage
     *
     * @return mixed Der Wert des Feldes
     */
    public function queryOne($sql)
    {
        $row = $this->queryRow($sql);
        return reset($row);
    }

    /**
     * @brief Erste Zeile einer Abfrage ermitteln
     *
     * @param string $sql Anweisung
     * @param int $offset Relativer Offset
     *
     * @return TDatabaseResultSet
     */
    public function queryRow($sql, $offset = 0)
    {
        $dbr = $this->query($sql, $offset, 1);
        return $dbr->current();
    }

}

/**
 * @brief MySQL-Layer
 */
class TMySql extends TDatabase
{

    var $lastRequest;

    /**
     * @brief Konstruktor
     *
     * @param string $host IP oder Domain des Servers
     * @param string $name Der Name der Daenbank
     * @param string $user Berechtigter Benutzer
     * @param string $passwd Kennwort des Benutzers
     *
     * @throws EMySql
     */
    public function __construct($host, $name, $user, $passwd, $port)
    {
        $this->connect($host, $user, $passwd, $name, $port);
//		$this->select($name);
        $this->database = $name;

    }

    /**
     * @brief Datenbankverbindung aufbauen
     *
     * @param string $host IP oder Domain des Servers
     * @param string $user Berechtigter Benutzer
     * @param string $passwd Kennwort des Benutzers
     *
     * @throws EMySql
     */
    public function connect($host, $user, $passwd, $name, $port)
    {

        $this->close();

        if (!$this->resource = mysqli_connect($host, $user, $passwd, $name, $port)) {
            throw new EMySql($this->resource);
        }

        /*
        //		$__er = error_reporting(E_ERROR);
                if (!$this->resource = mysql_connect($host, $user, $passwd)) {
                //	error_reporting($__er);
                    throw new EMySql();
                }
                                    echo "hallo";

                //error_reporting($__er);
                */
    }

    /**
     * @brief Datenbankverbindung loesen
     */
    public function close()
    {
        if (!$this->resource) {
            return;
        }
        mysqli_close($this->resource);
        $this->resource = false;
    }

    /**
     * @breif Destruktor
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @brief Datenbank auswaehlen
     *
     * @param string $name Name der Datenbank
     *
     * @throws EMySql
     */
    public function select($name)
    {
        if (!mysqli_select_db($this->resource, $name)) throw new EMySql($this->resource);
        $this->database = $name;
    }

    public function real_escape_string($str)
    {
        return mysqli_real_escape_string($this->resource, $str);
    }

    /**
     * @brief Datenbankabfrage
     *
     * @param string $sql Die Anweisung
     * @param int $offset Relativer Beginn der Ergebnismenge
     * @param int $limit Maximale Anzahl der Zeilen in der Ergebnismenge
     *
     * @return TDatabaseResultSet
     * @throws EMySql
     */
    public function query($sql, $offset = 0, $limit = -1)
    {
        $this->lastRequest = $sql;
        if ($limit != -1) $sql .= sprintf(' LIMIT %d, %d', $offset, $limit);
        return new TMySqlResult($this->execute($sql));
    }

    /**
     * @brief Datenbankanweisung ausfuehren
     *
     * @param string $sql Die Anweisung an die Datenbank
     *
     * @return mixed
     * @throws EMySql
     */
    public function execute($sql)
    {
        $this->statements[] = $sql;
        if (!($result = mysqli_query($this->resource, $sql, MYSQLI_USE_RESULT))) {
            throw new EMySql($this->resource);
        }
        return $result;
    }

    public function getInsertID()
    {
        return mysqli_insert_id($this->resource);
    }

    public function getLastRequest()
    {
        return $this->lastRequest;
    }
}


