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
			<span><?= isset($_SESSION["given_name"])?$_SESSION["given_name"]:"Unknown" ?></span>
			<button id="logout">logout</button>
		</div>
		<form action="/post/todo" autocomplete="off" method="post" id="add-todo">
			<textarea name="body" placeholder="todo..."></textarea>
			<button>Add Todo</button>
		</form>
		<?php require_once "render-todos.php"; ?>
	</main>
	<script src="https://apis.google.com/js/platform.js"></script>
	<script type="text/javascript" src="index.js"></script>
</body>
</html>