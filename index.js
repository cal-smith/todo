const todoList = document.querySelector(".todolist");
const addTodoForm = document.querySelector("#add-todo");

// add logout handling
document.querySelector("#logout").addEventListener("click", () => {
	gapi.load('auth2', function() {
		gapi.auth2.init()
		.then(() => gapi.auth2.getAuthInstance().signOut())
		.then(() => window.location = "/logout");
	});
});

// () => Promise<String>
const getTodoHTML = () => fetch("render-todos.php", {credentials: "include"}).then(res => res.text());

// callback for delete requests
const deleteTodoCallback = async ev => {
	ev.preventDefault();
	ev.target.parentElement.classList.add("delete");
	const button = ev.target.querySelector("button");

	button.classList.add("loading");
	button.setAttribute("disabled", true);

	await fetch(`/todo/${button.value}`, {
		method: "DELETE", 
		credentials: "include"
	});
	todoList.innerHTML = await getTodoHTML();

	button.classList.remove("loading");
	bindListeners();
};

const doneTodoCallback = async ev => {
	ev.preventDefault();
	ev.target.parentElement.classList.add("done");
	const button = ev.target.querySelector("button");
	const status = ev.target.querySelector("[name=status]");

	button.classList.add("loading");
	button.setAttribute("disabled", true);

	await fetch(`/todo/${button.value}`, {
		method: "PUT",
		body: JSON.stringify({status: status.value}),
		credentials: "include"
	});
	todoList.innerHTML = await getTodoHTML();

	button.classList.remove("loading");
	button.setAttribute("disabled", false);
	bindListeners();
};

const bindListeners = () => {
	bindDeleteListeners();
	bindDoneListeners();
};

const bindDeleteListeners = () => {
	const deleteTodoForms = document.querySelectorAll(".delete-form");
	for (const deleteForm of deleteTodoForms) {
		deleteForm.addEventListener("submit", deleteTodoCallback);
	}
};

const bindDoneListeners = () => {
	const doneTodoForms = document.querySelectorAll(".done-form");
	for (const doneForm of doneTodoForms) {
		doneForm.addEventListener("submit", doneTodoCallback);
	}
};

addTodoForm.addEventListener("submit", async ev => {
	const button = addTodoForm.querySelector("button");

	ev.preventDefault();
	button.classList.add("loading");

	await fetch("/todo", {
		method: "POST",
		body: new FormData(ev.target),
		credentials: "include"
	}).catch(err => console.error(err));
	todoList.innerHTML = await getTodoHTML();
	bindDeleteListeners();

	addTodoForm.querySelector("textarea").value = "";
	button.classList.remove("loading");
	bindListeners();
});

// setup the inital listeners
bindListeners();