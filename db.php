<?php
/**
 * SQLite‑based DB wrapper that mimics the subset of mysqli API used in the project.
 * It provides: query(), prepare(), real_escape_string(), and error handling.
 * Prepared statements are wrapped to support bind_param(), execute(), get_result(), and close().
 */
class SQLiteResult {
    private $stmt;
    public function __construct($stmt) {
        $this->stmt = $stmt;
    }
    public function fetch_assoc() {
        $row = $this->stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: false;
    }
    public function num_rows() {
        // SQLite does not expose rowCount for SELECT; we count manually.
        $this->stmt->execute();
        $count = 0;
        while ($this->stmt->fetch(PDO::FETCH_ASSOC)) { $count++; }
        $this->stmt->execute(); // reset pointer
        return $count;
    }
}

class SQLiteStatement {
    private $pdoStmt;
    private $boundParams = [];
    public function __construct($pdoStmt) {
        $this->pdoStmt = $pdoStmt;
    }
    // Simple type‑agnostic bind_param implementation (type string like "i" or "ss")
    public function bind_param($types, &...$vars) {
        $len = strlen($types);
        for ($i = 0; $i < $len; $i++) {
            $type = $types[$i];
            $value = $vars[$i];
            // Determine PDO param type (default to string)
            switch ($type) {
                case 'i': $param = PDO::PARAM_INT; break;
                case 'd': $param = PDO::PARAM_STR; break; // PDO has no double constant
                case 'b': $param = PDO::PARAM_LOB; break;
                default:  $param = PDO::PARAM_STR; break;
            }
            $this->pdoStmt->bindValue($i + 1, $value, $param);
        }
        return true;
    }
    public function execute() {
        return $this->pdoStmt->execute();
    }
    public function get_result() {
        // Return a result wrapper that can fetch_assoc()
        return new SQLiteResult($this->pdoStmt);
    }
    public function close() {
        $this->pdoStmt = null;
    }
}

class DBWrapper {
    public $conn; // will hold PDO instance
    public $connect_error = null;
    private $dbFile;
    public function __construct($dbFile = null) {
        $this->dbFile = $dbFile ?: (__DIR__ . '/job_tracker.db');
        $dsn = 'sqlite:' . $this->dbFile;
        try {
            $this->conn = new PDO($dsn);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->initializeSchemaIfNeeded();
        } catch (Exception $e) {
            $this->connect_error = $e->getMessage();
        }
    }
    private function initializeSchemaIfNeeded() {
        if (!file_exists($this->dbFile) || filesize($this->dbFile) == 0) {
            $schema = file_get_contents(__DIR__ . '/job_tracker.sql');
            // Strip MySQL‑specific clauses
            $lines = explode("\n", $schema);
            $clean = '';
            foreach ($lines as $line) {
                $trim = trim($line);
                if (strpos($trim, '--') === 0) continue; // comment
                if (stripos($trim, 'CREATE DATABASE') === 0) continue;
                if (stripos($trim, 'USE') === 0) continue;
                // Replace AUTO_INCREMENT with AUTOINCREMENT
                $line = str_ireplace('AUTO_INCREMENT', 'AUTOINCREMENT', $line);
                // Remove ENGINE and charset
                $line = preg_replace('/ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;/', ';', $line);
                // Replace CURRENT_TIMESTAMP default
                $line = str_ireplace('DEFAULT CURRENT_TIMESTAMP', "DEFAULT (datetime('now'))", $line);
                $clean .= $line . "\n";
                if (substr(trim($line), -1) === ';') {
                    $this->conn->exec($clean);
                    $clean = '';
                }
            }
        }
    }
    // Mimic mysqli::query returning a result wrapper
    public function query($sql) {
        $stmt = $this->conn->query($sql);
        if ($stmt === false) return false;
        return new SQLiteResult($stmt);
    }
    public function prepare($sql) {
        $pdoStmt = $this->conn->prepare($sql);
        if ($pdoStmt === false) return false;
        return new SQLiteStatement($pdoStmt);
    }
    public function real_escape_string($s) {
        // PDO::quote adds surrounding quotes; we strip them.
        $quoted = $this->conn->quote($s);
        return substr($quoted, 1, -1);
    }
}

// Instantiate globally as $conn, just like original code.
$db = new DBWrapper();
$conn = $db;
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}
?>
