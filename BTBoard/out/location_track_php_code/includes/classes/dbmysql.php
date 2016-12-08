<?php

//$db = new dbmysql('localhost', 'root', '', 'aaa');
//$db->debug = true;



//$db->record_update('gallery_img', $db->rec(array('picture'=> date('r'), 'gallery_id' => null)), $db->cond(array("id = 3"), 'AND'));
//$db->record_update('gallery_img', $db->rec(array('picture'=> date('r'), 'gallery_id' => null)), $db->cond(array('id' => 3, 'gallery_id' => 7), 'AND'));


//addcslashes($searchkeyword, '%_')

/*
$db = new dbmysql('localhost', 'root', 'password', 'database');

$db->debug = true;

$result = $db->table_query($db->tbl($tbl['']), $db->col(array('column_a')), $db->cond(array("column_b = 1"), 'AND'), $db->order(array(array('id', 'ASC'))), 0, 1, dbmysql::TBLQUERY_DISTINCT | dbmysql::TBLQUERY_FOUNDROWS);
while ($record = $db->record_fetch($result)) {
	lib::prh($record);
}

$record = array(
	'' => '',
);
$db->record_insert($tbl[''], $db->rec($record));

$db->record_update($tbl[''], $db->rec($record), $db->cond(array("column_b = 1"), 'AND'));

$db->record_delete($tbl[''], $db->cond(array("column_b = 1"), 'AND'));

try {

	$db->transaction(dbmysql::TRANSACTION_START);

	//...

	$db->transaction(dbmysql::TRANSACTION_COMMIT);

} catch (Exception $exception) {

	$db->transaction(dbmysql::TRANSACTION_ROLLBACK);

	throw $exception;

}

$db->close()
$db->query($query)
$db->query_foundrows()
$db->table_showall()
$db->table_exists($tablename)
$db->table_delete($tablename)
$db->table_empty($tablename)
$db->table_rename($tablename, $tablenamenew)
$db->table_columnsinfo($tablename)
$db->table_columninfo($tablename, $columnname)
$db->table_columns($tablename)
$db->table_query($tablename, $records, $condtion='', $order='', $limit_offset='', $limit_count='', $options='')
$db->column_add($tablename, $columnname, $columntype)
$db->column_exists($tablename, $columnname)
$db->column_type($tablename, $columnname)
$db->column_rename($tablename, $columnname, $columnnamenew)
$db->column_delete($tablename, $columnname)
$db->column_columninfototypesql($columninfo)
$db->record_fetch($result)
$db->record_count($result)
$db->record_insert($tablename, $sqldata)
$db->record_insert_id()
$db->record_update($tablename, $sqldata, $condition='')
$db->record_delete($tablename, $condition)
$db->transaction($eventtype)
$db->tbl($tablename)
$db->rec($record)
$db->col($columnname)
$db->cond($condition, $operator, $paren=false)
$db->order($order)
$db->datetimenow($tzlocal=false)
$db->datetime_validate($yyyy, $mm, $dd, $hh=false, $ii=false, $ss=false)
$db->es($string)


SET FOREIGN_KEY_CHECKS = 0;
...
SET FOREIGN_KEY_CHECKS = 1;

//http://dev.mysql.com/doc/refman/5.0/en/innodb-foreign-key-constraints.html

*/

//Database access
class dbmysql {

	public $link;

	public $debug = false;

	const TRANSACTION_START = 1;
	const TRANSACTION_ROLLBACK = 2;
	const TRANSACTION_COMMIT = 3;

	const TBLQUERY_DISTINCT = 1;
	const TBLQUERY_FOUNDROWS = 2;

	const IGNORE = 1;

	//Init connection
	public function __construct($server, $username, $password, $database) {

		//Check input
		$chkvars = array('server', 'username', 'database');
		foreach ($chkvars as $var) {
			if (!$$var) {
				throw new Exception("\"{$var}\" not set");
			}
		}

		//Connect
		$this->link = mysql_connect($server, $username, $password);

		//Check connection
		if (!$this->link) {
			throw new Exception('Could not connect to server: ' . mysql_error($this->link));
		}

		//Select database
		if (!mysql_select_db($database, $this->link)) {
			throw new Exception('Could not select database: ' . mysql_error($this->link));
		}

		//Set strict mode
		//$this->query("SET SESSION sql_mode='TRADITIONAL'");

	}

	//Close connection
	public function close() {

		if (!mysql_close($this->link)) {
			throw new Exception('Could not close connection: ' . mysql_error($this->link));
		}

	}

	//Query
	public function query($query) {

		if (!is_resource($this->link)) {
			throw new Exception('Database link not valid');
		}

		if ($this->debug) {
			$this->debug_print($query);
		}

		$result = mysql_query($query, $this->link);

		//Check result
		if (!$result) {
			throw new Exception('Invalid query: ' . mysql_error($this->link) . "\n\nQuery: " . $query);
		}

		return $result;

	}

	//Last query found rows
	public function query_foundrows() {
		$result = $this->query('SELECT FOUND_ROWS() AS found');

		if (!($record = $this->record_fetch($result))) {
			throw new Exception('Unable to retrive found rows');
		}

		return $record['found'];

	}

	//List tables
	public function table_showall() {

		//Query for tables
		$result = $this->query('SHOW TABLES');

		//Parse out names
		$tables = array();
		while ($record = mysql_fetch_array($result, MYSQL_NUM)) {
			$tables[] = $record[0];
		}

		return $tables;

	}

	//Chech table exists
	public function table_exists($tablename) {

		$result = $this->query("SHOW TABLES LIKE '{$tablename}'");
		$count = $this->record_count($result);
		return ($count > 0) ? true : false;

	}

	//Delete table
	public function table_delete($tablename) {
		$this->query('DROP TABLE ' . $this->tbl($tablename));
	}

	//Empty table
	public function table_empty($tablename) {
		$this->query('TRUNCATE TABLE ' . $this->tbl($tablename));
	}

	//Rename table
	public function table_rename($tablename, $tablenamenew) {
		$this->query('ALTER TABLE ' . $this->tbl($tablename) . ' RENAME ' . $this->tbl($tablenamenew));
	}

	//Table columns info
	public function table_columnsinfo($tablename) {

		$result = $this->query('SHOW COLUMNS FROM ' . $this->tbl($tablename));

		$columnsinfo = array();
		while ($record = $this->record_fetch($result) ) {
			$columnsinfo[] = $record['Field'];
		}

		return $columnsinfo;

	}

	//Table column info
	public function table_columninfo($tablename, $columnname) {

		$result = $this->query('SHOW COLUMNS FROM ' . $this->tbl($tablename) . ' LIKE ' . "'" . $columnname . "'");
		$columninfo = $this->record_fetch($result);

		//Check result
		if (!$columninfo) {
			throw new Exception("Column \"{$columnname}\" not found in table \"{$tablename}\"");
		}

		$columninfo_klc = $this->arraykeys_tolower($columninfo);

		return $columninfo_klc;

	}

	//Table columns info
	public function table_columns($tablename) {

		$columns = array();
		$columnsinfo = $this->table_columnsinfo($tablename);
		foreach ($columnsinfo as $columninfo) {
			$columns[] = $columninfo['field'];
		}

		return $columnsinfo;

	}

	//Query table
	public function table_query($tablename, $records, $condtion='', $order='', $limit_offset='', $limit_count='', $options='') {

		if (!$tablename) {
			throw new Exception('Table name not specified');
		}

		if (!$records) {
			throw new Exception('Columns to retrieve not specified');
		}

		//Retrieve numeric option values
		$optionvalues = $this->bitwiseopt_toarray($options);

		//Check input
		$chkvars = array('limit_offset', 'limit_count');
		foreach ($chkvars as $var) {
			if ($$var) {
				if (preg_match("%[^0-9]%", $$var)) {
					throw new Exception("\"{$var}\" of \"{$$var}\" not integer");
				}
			}
		}

		$sql = 'SELECT';

		if (in_array(self::TBLQUERY_DISTINCT, $optionvalues)) {
			$sql .= ' DISTINCT';
		}

		if (in_array(self::TBLQUERY_FOUNDROWS, $optionvalues)) {
			$sql .= ' SQL_CALC_FOUND_ROWS';
		}

		$sql .= ' ' . $records;

		$sql .= ' FROM ' . $tablename;

		if ($condtion) {
			$sql .= ' WHERE ' . $condtion;
		}

		if ($order) {
			$sql .= ' ORDER BY ' . $order;
		}

		if ($limit_count) {

			if (!$limit_offset) {
				$limit_offset = 0;
			}

			$sql .= ' LIMIT ' . $limit_offset . ', ' . $limit_count;

		}

		return $this->query($sql);

	}

	/*
	//Column add
	public function column_add($tablename, $columnname, $columntype) {
		$this->query('ALTER TABLE ' . $this->tbl($tablename) . ' ADD ' . $this->col($columnname) . ' ' . $columntype);
	}
	*/

	//Column primary key

	//Column move


	//Column exists
	public function column_exists($tablename, $columnname) {

		$result = $this->query('SHOW COLUMNS FROM ' . $this->tbl($tablename) . ' LIKE ' . "'" . $columnname . "'");

		$count = $this->record_count($result);
		return ($count > 0) ? true : false;

	}

	//Column type
	public function column_type($tablename, $columnname) {
		$columninfo = $this->table_columninfo($tablename, $columnname);
		return strtoupper($columninfo['type']);
	}

	//Column rename
	public function column_rename($tablename, $columnname, $columnnamenew) {
		$columninfo = $this->table_columninfo($tablename, $columnname);
		$columntypesql = $this->column_columninfototypesql($columninfo);
		$this->query('ALTER TABLE ' . $this->tbl($tablename) . ' CHANGE ' . $this->col($columnname) . ' ' . $this->col($columnnamenew) . ' ' . $columntypesql);
	}

	//Column delete
	public function column_delete($tablename, $columnname) {
		$this->query('ALTER TABLE ' . $this->tbl($tablename) . ' DROP COLUMN ' . $this->col($columnname));
	}

	//Column type
	public function column_columninfototypesql($columninfo) {

		$columntypesql = strtoupper($columninfo['type']);

		if ($columninfo['null'] == 'YES') {
			$columntypesql .= ' NULL';
		} else {
			$columntypesql .= ' NOT NULL';
		}

		if (strlen($columninfo['default']) != 0) {
			$columntypesql .= " DEFAULT '" . $columninfo['default'] . "'";
		}

		if ($columninfo['extra']) {
			$columntypesql .= ' ' . strtoupper($columninfo['extra']);
		}

		return $columntypesql;

	}

	//Fetch record
	public function record_fetch($result) {

		$record = mysql_fetch_assoc($result);
		return $record;

	}

	//Count records
	public function record_count($result) {

		$numrows = mysql_num_rows($result);

		//Check rows returned
		if ($numrows === false) {
			throw new Exception('Unable to retrieve record count');
		}

		return $numrows;

	}

	//Insert record
	public function record_insert($tablename, $sqldata, $options='') {

		//Retrieve numeric option values
		$optionvalues = $this->bitwiseopt_toarray($options);

		if (!$tablename) {
			throw new Exception('Table name not specified');
		}

		if (!$sqldata) {
			throw new Exception('Sql data not specified');
		}

		$sql = 'INSERT';

		if (in_array(self::IGNORE, $optionvalues)) {
			$sql .= ' IGNORE';
		}

		$sql .= ' INTO ' . $this->tbl($tablename) . ' SET ' . $sqldata;

		$this->query($sql);

	}

	//Insert last record id
	public function record_insert_id() {

		$insert_id = mysql_insert_id($this->link);
		if ($insert_id === false) {
			throw new Exception('Unable to retrieve insert id');
		}

		return $insert_id;

	}

	//Update record
	public function record_update($tablename, $sqldata, $condition='') {

		if (!$tablename) {
			throw new Exception('Table name not specified');
		}

		if (!$sqldata) {
			throw new Exception('Sql data not specified');
		}

		$sql = 'UPDATE ' . $this->tbl($tablename) . ' SET ' . $sqldata;

		if ($condition) {
			$sql .= ' WHERE ' . $condition;
		}

		$this->query($sql);

	}

	//Delete record
	public function record_delete($tablename, $condition) {

		if (!$tablename) {
			throw new Exception('Table name not specified');
		}

		if (!$condition) {
			throw new Exception('Condition not specified');
		}

		$this->query('DELETE FROM ' . $this->tbl($tablename) . ' WHERE ' . $condition);

	}


	//Transaction handling
	public function transaction($eventtype) {

		$events = array(
			self::TRANSACTION_START => 'START TRANSACTION',
			self::TRANSACTION_ROLLBACK => 'ROLLBACK',
			self::TRANSACTION_COMMIT => 'COMMIT',
		);

		if (!isset($events[$eventtype])) {
			throw new Exception('Unknown transaction');
		}

		$this->query($events[$eventtype]);

	}

	//Prepare table name(s)
	public function tbl($tablename) {

		if (is_array($tablename)) {
			if (count($tablename) == 0) {
				throw new Exception('Table list empty');
			}
			$sql_tablename = '`'.implode("`, `", $tablename).'`';
		} else {
			if (!$tablename) {
				throw new Exception('Table not specified');
			}
			$sql_tablename = '`' . $tablename . '`';
		}

		return $sql_tablename;

	}

	//Prepare record data
	public function rec($record) {

		$sql_record = '';
		foreach ($record as $columnname => $value) {
			if ($value === null) {
				$sql_value = 'NULL';
			} else {
				$sql_value = '\'' . $this->es($value) . '\'';
			}
			$sql_record .= $this->col($columnname) . ' = ' . $sql_value . ",\n";
		}

		$sql_record = rtrim($sql_record, ",\n");

		return $sql_record;

	}

	//Prepare column name(s)
	public function col($columnname) {

		if (is_array($columnname)) {
			$columnname_list = $columnname;
		} else {
			$columnname_list = array($columnname);
		}

		if (count($columnname_list) == 0) {
			throw new Exception('Column list empty');
		}

		$sql_columnname = '';
		foreach ($columnname_list as $value) {
			if (is_array($value)) {

				if (!$value[0]) {
					throw new Exception('Table for column not specified');
				}

				if (!$value[1]) {
					throw new Exception('Column not specified');
				}

				$sql_columnname .= '`' . $value[0] . '`.`' . $value[1] . '`,';
			} else {

				if (!$value) {
					throw new Exception('Column not specified');
				}

				$sql_columnname .= '`' . $value . '`, ';
			}
		}

		$sql_columnname = rtrim($sql_columnname, ', ');

		return $sql_columnname;

	}

	//Prepare condition(s)
	public function cond($condition, $operator, $paren=false) {

		if ($paren) {
			$sql_paren_l = '(';
			$sql_paren_r = ')';
		} else {
			$sql_paren_l = '';
			$sql_paren_r = '';
		}

		$sql_cond = $sql_paren_l . implode(" {$operator} ", $condition) . $sql_paren_r;
		return $sql_cond;
	}

/*
	//Prepare condition(s)
	public function cond($condition, $operator, $paren=false) {

		if ($paren) {
			$sql_paren_l = '(';
			$sql_paren_r = ')';
		} else {
			$sql_paren_l = '';
			$sql_paren_r = '';
		}

		$sql_cond = '';
		$i = 0;
		$cond_total = count($condition);
		foreach ($condition as $name => $value) {
			$i++;

			if ($value === null) {
				$sql_value = 'NULL';
			} else {
				$sql_value = '\'' . $this->es($value) . '\'';
			}

			$sql_cond .= $this->col($name) . ' = ' . $sql_value;

			if ($i != $cond_total) {
				$sql_cond .= ' ' . $operator;
			}

		}

		$sql_cond = ($sql_cond) ? $sql_paren_l . $sql_cond . $sql_paren_r : $sql_cond;

//		$sql_cond = $sql_paren_l . implode(" {$operator} ", $condition) . $sql_paren_r;
		return $sql_cond;
	}
*/

	//Prepare order(s)
	public function order($order) {

		$sql_order = '';
		foreach ($order as $order_pair) {

			if (is_array($order_pair[0])) {

				if (!$order_pair[0][0]) {
					throw new Exception('Order column table not specified');
				}

				if (!$order_pair[0][0]) {
					throw new Exception('Order column not specified');
				}

				$sql_order .= '`' . $order_pair[0][0] . '`.`' . $order_pair[0][1] . '`';
			} else {

				if (!$order_pair[0]) {
					throw new Exception('Order column not specified');
				}

				$sql_order .= '`' . $order_pair[0] . '`';
			}

			if (isset($order_pair[1])) {

				if (!( ($order_pair[1] == 'ASC') || ($order_pair[1] == 'DESC') )) {
					throw new Exception('Order type must be (ASC/DESC) only');
				}

				$sql_order .= ' ' . $order_pair[1];
			}

			$sql_order .= ', ';

		}

		$sql_order = rtrim($sql_order, ', ');

		return $sql_order;

	}

	//Date / Time now
	public function datetimenow($tzlocal=false) {
		if ($tzlocal == true) {
			return date('Y-m-d H:i:s');
		} else {
			return gmdate('Y-m-d H:i:s');
		}
	}

	//Validate date / time
	public function datetime_validate($yyyy, $mm, $dd, $hh=false, $ii=false, $ss=false) {

		//Perform checking on yyyy, mm, dd
		$chkvars = array('yyyy', 'mm', 'dd');
		foreach ($chkvars as $var) {

			//Numeric only
			if (preg_match("/[^0-9]/", $$var)) {
				return false;
			}

			//Specified
			if (strlen($$var) < 1) {
				return false;
			}

			$$var = intval($$var);

		}

		//Convert '00' year to '2000'
		//$yyyy = ($yyyy == '00') ? 2000 : $yyyy;

		//Convert 2 digit year to 4 digit year
		if (strlen($yyyy) <= 2) {
			if ($yyyy >= 70) {
				$yyyy = $yyyy + 1900;
			} else {
				$yyyy = $yyyy + 2000;
			}
		}

		//Check date in valid range
		if (!checkdate((int)$mm, (int)$dd, (int)$yyyy)) {
			return false;
		}

		$sql_mm = ($mm < 10) ? '0' . $mm : $mm;
		$sql_dd = ($dd < 10) ? '0' . $dd : $dd;

		if (strlen($yyyy) != 4) {
			return false;
		}

		$datetime = "{$yyyy}-{$sql_mm}-{$sql_dd}";

		//If time is specified, check it is numeric
		if ( ($hh !== false) || ($ii !== false) || ($ss !== false) ) {

			//Perform checks on hh, ii, ss 
			$chkvars = array('hh', 'ii', 'ss');
			foreach ($chkvars as $var) {

				//Numeric only
				if (preg_match("/[^0-9]/", $$var)) {
					return false;
				}

				//Specified
				if (strlen($$var) < 1) {
					return false;
				}

				$$var = intval($$var);

			}

			//Check time in valid range
			if (!( ($hh >= 0) && ($hh < 24) && ($ii >= 0) && ($ii < 60) && ($ss >= 0) && ($ss < 60) )) {
				return false;
			}

			$hh = ($hh < 10) ? '0' . $hh : $hh;
			$ii = ($ii < 10) ? '0' . $ii : $ii;
			$ss = ($ss < 10) ? '0' . $ss : $ss;

			$datetime .= " {$hh}:{$ii}:{$ss}";

		}

		return $datetime;

	}

	//Escape string
	public function es($string) {
		return mysql_real_escape_string($string);
	}

	//Convert bitwise "or" options list to array with option values
	private function bitwiseopt_toarray($options) {

		$options_list = array();

		if ($options) {
			$binoptions = decbin($options);
			$totaldigit = strlen($binoptions);

			for ($i=1; $i <= $totaldigit; $i++) {
				$option = substr($binoptions, -$i, 1);
				$binoption = str_pad($option, $i, 0, STR_PAD_RIGHT);
				$optionint = bindec($binoption);
				if ($optionint) {
					$options_list[] = bindec($binoption);
				}

			}

		}

		return $options_list;

	}

	//Keys to lowercase
	private function arraykeys_tolower($array) {

		$arraynew = array();
		foreach ($array as $name => $value) {
			$namelc = strtolower($name);
			$arraynew[$namelc] = $value;
		}

		return $arraynew;

	}

	//Print debugging messages
	private function debug_print($message) {

		//If run from browser, format for browser display
		if (isset($_SERVER['REMOTE_ADDR'])) {
			$message_h = htmlentities($message);
			echo <<<EOHTML
SQL Debug: {$message_h}<br />
<br />
EOHTML;
		} else {
			echo <<<EOHTML
SQL Debug: {$message}\n\n
EOHTML;
		}

	}

}

?>