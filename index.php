<?php
//show errors
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

require_once "vendor/autoload.php";
require_once "common.php";
require_once "router.php";
use Ramsey\Uuid\Uuid;

$router = new Router();

$router->set_route_guard(function() {
	session_start();
	if (isset($_SESSION["user_id"])) {
		$user_id = $_SESSION["user_id"];
		session_write_close();
		$conn = db_connect();

		$result = pg_query_params($conn, "select * from users where id = $1 limit 1", [$user_id]);

		if (!$result) { send_error(500, "db error"); }

		$row = pg_fetch_array($result, 0, PGSQL_ASSOC);
		if ($row && isset($user_id) && $user_id === $row["id"]) {
			session_write_close();
			pg_free_result($result);
			pg_close($conn);
			return true;
		}
	}
	return false;
});

// all our fancy restful routes
$router->get("/", function() {
	require "views/main.php";
});

$router->get_guarded("/todos", function() {
	require "views/todo-list.php";
});

$router->post("/login", function() {
	$id_token = $_POST["token"];

	if ($id_token) {
		$client = new Google_Client(["client_id" => "1039409467454-e7tgm1e5vj32joa660tmukp0ob2m0td7.apps.googleusercontent.com"]);
		$payload = $client->verifyIdToken($id_token);
		if ($payload) {
			$user_id = $payload["sub"];
			$conn = db_connect();
			$result = pg_query_params($conn, "select * from users where id = $1", [$user_id]);
			
			if (!$result) { send_error(500, "db error"); }

			$row = pg_fetch_array($result, 0, PGSQL_ASSOC);
			pg_free_result($result);
			$stored_id = "";

			if ($row) {
				$stored_id = $row["id"];
			} else {
				$result = pg_query_params($conn, "insert into users (id) values ($1)", [$user_id]);
				if (!$result) { send_error(500, "db error"); }
				pg_free_result($result);
				$stored_id = $user_id;
			}

			pg_close($conn);
			
			if ($user_id === $stored_id) {
				session_start();
				$_SESSION["id_token"] = $id_token;
				$_SESSION["user_id"] = $user_id;
				$_SESSION["given_name"] = $payload["given_name"];
				$_SESSION["picture"] = $payload["picture"];
				session_write_close();
				echo $id_token;
				echo "/todos";
			}
		} else {
			http_response_code(400);
			echo "bad token";
		}
	} else {
		http_response_code(400);
		echo "no id_token";
	}
});

$router->get("/logout", function() {
	session_start();
	session_destroy();
	session_unset();
	// redirect back home
	header("Location: /");
});

$router->get("/todo/<id>", function($params) {
	require "render-todo.php";
	render_todo($params["id"]);
});

$router->post_guarded("/todo", function($params) {
	add_todo(true);
});

$router->put_guarded("/todo/<id>", function($params) {
	edit_todo($params["id"], true);
});

$router->delete_guarded("/todo/<id>", function($params) {
	delete_todo($params["id"], true);
});

// add compat routes
$router->post_guarded("/post/todo", function() {
	add_todo(false);
});

$router->post_guarded("/put/todo", function() {
	edit_todo($_POST["id"], false);
});

$router->post_guarded("/delete/todo", function() {
	delete_todo($_POST["id"], false);
});

$router->run();

function delete_todo($id, $using_rest) {
	// exit if the id is empty
	if ($id == "") { send_error(400, "id shouldn't be empty (delete)");	}

	$conn = db_connect();
	$query = "delete from todos where id = $1";
	$result = pg_query_params($conn, $query, [$id]);

	// exit if we get a false (error) result
	if (!$result) { send_error(500, "db error"); }

	pg_free_result($result);
	pg_close($conn);

	if ($using_rest) {
		http_response_code(200);
	} else {
		header("Location: /");
	}
	echo "deleted todo";
}

function edit_todo($id, $using_rest) {
	// exit if the id is empty
	if ($id == "") { send_error(400, "id shouldn't be empty (put)");	}

	$status = "";
	if ($using_rest) {
		$status = json_decode(file_get_contents("php://input"))->status;
	} else {
		$status = $_POST["status"];
	}

	$conn = db_connect();
	$query = "update todos set status = $1 where id = $2";
	$result = pg_query_params($conn, $query, [$status, $id]);

	// exit if we get a false (error) result
	if (!$result) { send_error(500, "db error" . $_SERVER["REQUEST_METHOD"]); }

	pg_free_result($result);
	pg_close($conn);

	if ($using_rest) {
		http_response_code(200);
	} else {
		header("Location: /");
	}
	echo "marked todo done";
}

function add_todo($using_rest) {
	$body = htmlspecialchars($_POST["body"]);
	$id = Uuid::uuid4();

	// exit if the body is empty
	if ($body == "") { send_error(400, "body shouldn't be empty (post)"); }

	session_start();
	$conn = db_connect();
	$query = "insert into todos (id, body, created_by) values ($1, $2, $3)";
	$result = pg_query_params($conn, $query, [$id, $body, $_SESSION["user_id"]]);
	session_write_close();

	if (!$result) { send_error(500, "db error"); }

	pg_free_result($result);
	pg_close($conn);

	if ($using_rest) {
		http_response_code(200);
	} else {
		header("Location: /");
	}
	echo "added todo";
}
