const todoList = document.querySelector(".todolist");
const addTodoForm = document.querySelector("#add-todo");

/*
 * todo functions
 */

const tabs = document.querySelectorAll(".tab");
const tabHeaders = document.querySelectorAll(".tab-header");

const switchTab = tabId => {
	for (let tab of tabs) {
		if (tab.id == tabId) {
			tab.classList.add("visible");
		} else {
			tab.classList.remove("visible");
		}
	}
	for (let tabHeader of tabHeaders) {
		if (tabHeader.getAttribute("tab-id") === tabId) {
			tabHeader.classList.add("selected");
		} else {
			tabHeader.classList.remove("selected");
		}
	}
};

for (const header of tabHeaders) {
	header.addEventListener("click", event => {
		event.preventDefault();
		switchTab(header.getAttribute("tab-id"));
	});
}

switchTab("all");

// add logout handling
document.querySelector("#logout").addEventListener("click", () => {
	gapi.load('auth2', function() {
		gapi.auth2.init()
		.then(() => gapi.auth2.getAuthInstance().signOut())
		.then(() => window.location = "/logout");
	});
});

// String{"all" | "done" | "open"} => Promise<String>
const getTodoHTML = (state = "all") => fetch(`/render/todos/${state}`, {credentials: "include"}).then(res => res.text());

// () => Promise<void>
const updateTodoList = () => {
	const allTabBody = document.querySelector("#all");
	const openTabBody = document.querySelector("#open");
	const doneTabBody = document.querySelector("#done");
	return Promise.all([
		getTodoHTML("all"),
		getTodoHTML("open"),
		getTodoHTML("done")
	]).then(values => {
		allTabBody.innerHTML = values[0];
		openTabBody.innerHTML = values[1];
		doneTabBody.innerHTML = values[2];
	})
};

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
	
	await updateTodoList();

	button.classList.remove("loading");
	bindListeners();
};

// callback to set todo to done
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

// callback to edit todo
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

// send a PUT request
const putTodo = async (id, status, body) => {
	await fetch(`/todo/${id}`, {
		method: "PUT",
		body: JSON.stringify({
			status: status,
			body: body
		}),
		credentials: "include"
	});
	await updateTodoList();
	bindListeners();
};

// function to bind ALL the listeners
const bindListeners = () => {
	bindDeleteListeners();
	bindEditListeners();
};

/*
 * these are concerned with binding listeners on all the elements
 */
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

// listen for submits on the add todo form
addTodoForm.addEventListener("submit", async ev => {
	const button = addTodoForm.querySelector("button");

	ev.preventDefault();
	button.classList.add("loading");

	await fetch("/todo", {
		method: "POST",
		body: new FormData(ev.target),
		credentials: "include"
	}).catch(err => console.error(err));
	await updateTodoList();
	bindDeleteListeners();

	addTodoForm.querySelector("textarea").value = "";
	button.classList.remove("loading");
	bindListeners();
});

// setup the inital listeners
bindListeners();