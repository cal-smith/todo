<?php
require_once "common.php";
require_once "render-todo.php";

session_start();
$user_id = $_SESSION["user_id"];
session_write_close();

$conn = db_connect();
$query = "select * from todos where created_by = $1 order by created_timestamp desc";
$result = pg_query_params($conn, $query, [$user_id]) or die(pg_last_error());
$todos = [];
while ($todo = pg_fetch_array($result, null, PGSQL_ASSOC)) {
	array_push($todos, $todo);
}
?>
<ul class="todolist">
<?php foreach($todos as $todo): ?>
	<li class="todo <?= $todo["status"] == "done"?"done":"" ?>">
		<?= render_todo_body($todo); ?>
	</li>
<?php endforeach; ?>
</ul>
<?php
pg_free_result($result);
pg_close($conn);