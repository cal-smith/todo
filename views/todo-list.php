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
	<main id="app">
		<div class="controls">
			<img id="user-img" src="<?= isset($_SESSION["picture"])?$_SESSION["picture"]:"" ?>">
			<span id="user-name"><?= isset($_SESSION["given_name"])?$_SESSION["given_name"]:"Unknown" ?></span>
			<button id="logout">logout</button>
		</div>
		<app-todo-creator></app-todo-creator>
		<app-todo-toggles></app-todo-toggles>
		<app-todo-list :todos="todos"></app-todo-list>
	</main>
	<script src="https://cdn.jsdelivr.net/npm/vue"></script>
	<script src="https://cdn.jsdelivr.net/npm/marked@0.3.16/lib/marked.min.js"></script>
	<script src="https://apis.google.com/js/platform.js"></script>
	<script type="text/javascript" src="index.js"></script>
</body>
</html>