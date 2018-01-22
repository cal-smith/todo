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
		<h1>Todos</h1>
		<div class="g-signin2" data-onsuccess="onSignIn"></div>
	</main>
	<script src="https://apis.google.com/js/platform.js" async defer></script>
	<script type="text/javascript">
		async function onSignIn(googleUser) {
			const profile = googleUser.getBasicProfile();
			console.log('ID: ' + profile.getId()); // Do not send to your backend! Use an ID token instead.
			console.log('Name: ' + profile.getName());
			console.log('Image URL: ' + profile.getImageUrl());
			console.log('Email: ' + profile.getEmail()); // This is null if the 'email' scope is not present.
			const token = googleUser.getAuthResponse().id_token;
			console.log("token =", token);
			const data = new FormData();
			data.set("token", token);
			try {
				const res = await fetch("/login", {
					method: "POST", 
					body: data,
					credentials: "include"
				});
				console.log(res);
				window.location = "/todos";
			} catch (ex) {
				console.log(ex);
			}
		}
	</script>
</body>
</html>