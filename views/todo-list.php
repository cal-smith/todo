<?php 
	require_once "render-todo.php";
	require_once "todos.php";
	$todos = new Todos();
?>
<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width">
	<meta charset="utf-8">
	<meta name="google-signin-client_id" content="1039409467454-e7tgm1e5vj32joa660tmukp0ob2m0td7.apps.googleusercontent.com">
	<title>todo</title>
	<link rel="stylesheet" type="text/css" href="/style.css">
</head>
<body>
	<main>
		<div class="controls">
			<img id="user-img" src="<?= isset($_SESSION["picture"])?$_SESSION["picture"]:"" ?>">
			<span id="user-name"><?= isset($_SESSION["given_name"])?$_SESSION["given_name"]:"Unknown" ?></span>
			<button id="logout">logout</button>
		</div>
		<form action="/post/todo" autocomplete="off" method="post" id="add-todo">
			<textarea class="todo-body" name="body" placeholder="todo..."></textarea>
			<button>Add Todo</button>
		</form>
		<ul class="tabs">
			<li class="tab-header" tab-id="all"><a href="#all">All</a></li>
			<li class="tab-header" tab-id="open"><a href="#open">Todo</a></li>
			<li class="tab-header" tab-id="done"><a href="#done">Done</a></li>
		</ul>
		<div class="tab visible" id="all">
			<?= render_todo_list($todos->get_all()); ?>
		</div>
		<div class="tab" id="open">
			<?= render_todo_list($todos->get_open()); ?>
		</div>
		<div class="tab" id="done">
			<?= render_todo_list($todos->get_done()); ?>
		</div>
	</main>
	<script src="https://apis.google.com/js/platform.js"></script>
	<script type="text/javascript" src="index.js"></script>
</body>
</html>