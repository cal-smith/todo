<?php
function db_connect() {
	$conn = pg_connect("host=cloud.reallyawesomedomain.com dbname=todo user=todouser password=todouser") or die(pg_last_error());
	return $conn;
}

function send_error($status, $message) {
	http_response_code($status);
	echo $message;
	exit;
}