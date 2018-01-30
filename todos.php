<?php
require_once "vendor/autoload.php";
require_once "common.php";

class Todos {
	// connect to the db, and get some session vars
	function __construct() {
		$this->conn = db_connect();
		session_start();
		$this->session = $_SESSION;
		session_write_close();
		$this->user_id = $this->session["user_id"];
	}

	// clean up when the GC runs
	function __destruct() {
		pg_close($this->conn);
	}

	// use up a result as an array
	function result_to_array($result) {
		$array = [];
		while ($item = pg_fetch_array($result, null, PGSQL_ASSOC)) {
			array_push($array, $item);
		}
		return $array;
	}

	// run the query and get back an assoc array
	function do_query($query, $params) {
		$result = pg_query_params($this->conn, $query, $params) or die(pg_last_error());
		$array = $this->result_to_array($result);
		pg_free_result($result);
		return $array;
	}

	// get all the todos
	function get_all() {
		$query = "select * from todos where user_id = $1 order by created_timestamp desc";
		return $this->do_query($query, [$this->user_id]);
	}

	// get just the 'done' todos
	function get_done() {
		$query = "select * from todos where user_id = $1 and status = 'done' order by created_timestamp desc";
		return $this->do_query($query, [$this->user_id]);
	}

	// get just the 'open' todos
	function get_open() {
		$query = "select * from todos where user_id = $1 and status = 'open' order by created_timestamp desc";
		return $this->do_query($query, [$this->user_id]);
	}

	function get_one($todo_id) {
		$query = "select * from todos where todo_id = $1";
		return $this->do_query($query, [$todo_id]);
	}
}