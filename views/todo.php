<?php
require_once "vendor/autoload.php";
use Michelf\Markdown;

function render_todo($todo) {
	$user_id = "";
	if (isset($_SESSION["user_id"])) {
		$user_id = $_SESSION["user_id"];
	}	
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
		<ul class="todolist">
			<li class="todo <?= $todo["status"] == "done"?"done":"" ?>">
				<div class="rendered-body">
					<?= Markdown::defaultTransform($todo["body"]) ?>
				</div>
				<div class="edit-todo">
					<textarea 
						class="todo-body edit-body"
						name="body" 
						form="edit-todo-<?= $todo["todo_id"] ?>"><?= $todo["body"] ?></textarea>
					<button 
						class="save" 
						value="<?= $todo["todo_id"] ?>">
						Save
					</button>
					<button class="cancel">Cancel</button>
				</div>
				<div class="todo-actions">
					<a href="/todo/<?= $todo["todo_id"] ?>">link</a>
					<?php if($user_id === $todo["user_id"]): ?>
						<button class="edit-button">Edit</button>
						<form
							class="done-form" 
							action="/put/todo" 
							autocomplete="off" 
							method="post"
							id="edit-todo-<?= $todo["todo_id"] ?>">
							<input type="hidden" name="status" value="<?= $todo["status"] ?>"/>
							<button 
								class="done" 
								name="id" 
								value="<?= $todo["todo_id"] ?>">
								Done
							</button>
						</form>
						<form 
							class="delete-form"
							action="/delete/todo" 
							autocomplete="off" 
							method="post" 
							id="delete-todo-<?= $todo["todo_id"] ?>">
							<button 
								class="delete"
								name="id" 
								value="<?= $todo["todo_id"] ?>">
								Delete
							</button>
						</form>
					<?php endif; ?>
				</div>
			</li>
		</ul>
	</main>
</body>
</html>
<?php
}