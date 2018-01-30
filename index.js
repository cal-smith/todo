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
const deleteTodoCallback = async event => {
	event.preventDefault();
	event.target.parentElement.classList.add("delete");
	const button = event.target.querySelector("button");

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

const doneTodoCallback = async event => {
	event.preventDefault();

	const todo = event.target.closest(".todo");
	const button = todo.querySelector(".done-form .done");
	const textarea = todo.querySelector(".edit-todo .edit-body");
	const status = todo.querySelector("[name=status]");

	todo.classList.toggle("done");

	button.classList.add("loading");
	button.setAttribute("disabled", true);

	putTodo(button.value, status.value === "done" ? "open" : "done", textarea.value);

	button.classList.remove("loading");
	button.setAttribute("disabled", false);
};

const editTodoCallback = async event => {
	const todo = event.target.closest(".todo");
	const textarea = todo.querySelector(".edit-todo .edit-body");
	const status = todo.querySelector("[name=status]");
	const save = todo.querySelector(".edit-todo .save");

	save.classList.add("loading");
	save.setAttribute("disabled", true);

	putTodo(save.value, status.value, textarea.value);

	save.classList.remove("loading");
	save.setAttribute("disabled", false);

};

const putTodo = async (id, status, body) => {
	await fetch(`/todo/${id}`, {
		method: "PUT",
		body: JSON.stringify({
			status: status,
			body: body
		}),
		credentials: "include"
	});
	todoList.innerHTML = await getTodoHTML();
	bindListeners();
};

const bindListeners = () => {
	bindDeleteListeners();
	bindEditListeners();
};

const bindDeleteListeners = () => {
	const deleteTodoForms = document.querySelectorAll(".delete-form");
	for (const deleteForm of deleteTodoForms) {
		deleteForm.addEventListener("submit", deleteTodoCallback);
	}
};

const bindEditListeners = () => {
	const todos = document.querySelectorAll(".todo");
	for (const todo of todos) {
		const doneForm = todo.querySelector(".done-form");
		const editTextArea = todo.querySelector(".edit-body");
		const editButton = todo.querySelector(".edit-button");

		doneForm.addEventListener("submit", doneTodoCallback);
		editTextArea.style.height = editTextArea.rows = editTextArea.value.split("\n").length;
		editTextArea.addEventListener("keyup", event => event.target.rows = event.target.value.split("\n").length);
		editButton.addEventListener("click", event => {
			const todo = event.target.closest(".todo");
			const save = todo.querySelector(".edit-todo .save");
			const cancel = todo.querySelector(".edit-todo .cancel");
			const editTodo = todo.querySelector(".edit-todo");
			const renderedBody = todo.querySelector(".rendered-body");

			editTodo.classList.toggle("editing");
			renderedBody.classList.toggle("editing");
			cancel.addEventListener("click", () => {
				editTodo.classList.remove("editing");
				renderedBody.classList.remove("editing");
			});
			save.addEventListener("click", editTodoCallback);
		});
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