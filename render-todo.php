<?php
require_once "vendor/autoload.php";
require_once "common.php";
use Michelf\Markdown;

function render_todo($id) {
	$conn = db_connect();
	$query = "select * from todos where id = $1";
	$result = pg_query_params($conn, $query, [$id]);
	$todo = pg_fetch_array($result, null, PGSQL_ASSOC);
?>
	<!DOCTYPE html>
	<html>
	<head>
		<meta name="viewport" content="width=device-width">
		<meta charset="utf-8">
		<title>todo</title>
		<link rel="stylesheet" type="text/css" href="/style.css">
	</head>
	<body>
		<main>
			<div class="todo <?= $todo["status"] == 'done'?'done':'' ?>">
				<?= render_todo_body($todo) ?>
			</div>
		</main>
	</body>
	</html>
<?php
	pg_free_result($result);
	pg_close($conn);
	exit;
}
?>

<?php
function render_todo_body($todo) {
?>
	<?= Markdown::defaultTransform($todo["body"]) ?>
	<div class="todo-actions">
		<a href="/todo/<?= $todo["id"] ?>">link</a>
		<form class="done-form" action="/put/todo" autocomplete="off" method="post" id="done-todo-<?= $todo["id"] ?>">
			<input type="hidden" name="status" value="<?= $todo["status"] == "done"?"open":"done" ?>"/>
			<button class="done" name="id" value="<?= $todo["id"] ?>">Done</button>
		</form>
		<form class="delete-form" action="/delete/todo" autocomplete="off" method="post" id="delete-todo-<?= $todo["id"] ?>">
			<button class="delete" name="id" value="<?= $todo["id"] ?>">Delete</button>
		</form>
	</div>
<?php
}

