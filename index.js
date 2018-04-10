// add logout handling
document.querySelector("#logout").addEventListener("click", () => {
	gapi.load('auth2', function() {
		gapi.auth2.init()
		.then(() => gapi.auth2.getAuthInstance().signOut())
		.then(() => window.location = "/logout");
	});
});

// vue stuff
const eventBus = new Vue();

const app = new Vue({
	el: "#app",
	data: {
		allTodos: [],
		todos: [],
		currentFilter: "all"
	},
	created: function () {
		fetch("/todos.json", { credentials: "include" })
		.then(res => res.json())
		.then(json => {
			this.allTodos = json;
			this.todos = json;
		});

		eventBus.$on("delete", this.deleteTodo);
		eventBus.$on("add", this.addTodo);
		eventBus.$on("filter", this.filterTodos);
	},
	methods: {
		deleteTodo: async function (id) {
			const savedTodo = this.todos.find(todo => todo.todo_id === id);
			const todoIndex = this.todos.indexOf(savedTodo);
			this.allTodos = this.allTodos.filter(todo => todo.todo_id !== id);
			this.filterTodos(this.currentFilter);
			try {
				await fetch(`/todo/${id}`, {
					method: "DELETE", 
					credentials: "include"
				});
			} catch (error) {
				console.error(error);
				this.allTodos.splice(todoIndex, 0, savedTodo);
				this.filterTodos(this.currentFilter);
			}
		},
		addTodo: function (todo) {
			this.allTodos.unshift(todo);
			this.filterTodos(this.currentFilter);
		},
		filterTodos: function (status) {
			this.currentFilter = status;
			if (status === "all") {
				this.todos = this.allTodos;
			} else {
				this.todos = this.allTodos.filter(todo => todo.status === status);
			}
		}
	}
});

Vue.component("app-todo-creator", {
	data: function () {
		return {
			loading: false
		};
	},
	template: `
	<form autocomplete="off" id="add-todo" @submit.prevent.stop="add">
		<textarea class="todo-body" name="body" placeholder="todo..."></textarea>
		<button :class="{ loading: loading }">Add Todo</button>
	</form>
	`,
	methods: {
		add: async function () {
			this.loading = true;
			const newTodo = await fetch("/todo", {
				method: "POST",
				body: new FormData(this.$el),
				credentials: "include"
			}).catch(err => console.error(err))
			.then(res => res.json())
			.then(json => json[0]);
			eventBus.$emit("add", newTodo);
			this.loading = false;
		}
	}
});

Vue.component("app-todo-toggles", {
	data: function () {
		return {
			selected: "all"
		};
	},
	template: `
	<div>
		<ul class="tabs">
			<li 
				class="tab-header"
				@click="all"
				:class="{ selected: selected === 'all' }">
				All
			</li>
			<li 
				class="tab-header" 
				@click="todo"
				:class="{ selected: selected === 'todo' }">
				Todo
			</li>
			<li 
				class="tab-header" 
				@click="done"
				:class="{ selected: selected === 'done' }">
				Done
			</li>
		</ul>
	</div>
	`,
	methods: {
		all: function () {
			this.selected = "all";
			eventBus.$emit("filter", "all");
		},
		todo: function () {
			this.selected = "todo";
			eventBus.$emit("filter", "open");
		},
		done: function () {
			this.selected = "done";
			eventBus.$emit("filter", "done");
		}
	}
});

Vue.component("app-todo-list", {
	props: ["todos"],
	template: `
	<ul class="todolist">
		<app-todo v-for="todo in todos" :todo="todo"></app-todo>
	</ul>
	`
});

Vue.component("app-todo", {
	props: ["todo"],
	data: function () {
		return {
			editingTodo: false
		};
	},
	template: `
	<li 
		class="todo" 
		:class="{ done: todo.status === 'done' }">
		<app-todo-editor 
			:todo-id="todo.todo_id" 
			:body="todo.body"
			:editing="editingTodo"
			@cancel="cancelEdit"
			@save="saveChanges">
		</app-todo-editor>
		<div class="todo-actions">
			<a href="#share" @click.prevent="share">Share</a>
			<button class="edit-button" @click="toggleEdit">Edit</button>
			<button class="done" @click="markDone">Done</button>
			<button class="delete" @click="deleteTodo">Delete</button>
		</div>
		<dialog class="share" ref="dialog">
			<header>
				<h3>Share</h3>
			</header>
			<section>
				<label>
					Link: 
					<input 
						type="text" 
						ref="url"
						:value="location.origin + '/todo/' + todo.todo_id"/>
				</label>
				<button @click="copyShareLink">Copy</button>
			</section>
			<footer>
				<button @click="closeShare">Close</button>
			</footer>
		</dialog>
	</li>
	`,
	methods: {
		toggleEdit: function () {
			this.editingTodo = !this.editingTodo;
		},
		cancelEdit: function () {
			this.editingTodo = false;
		},
		deleteTodo: function () {
			eventBus.$emit("delete", this.todo.todo_id);
		},
		_putTodo: function (status, body) {
			return fetch(`/todo/${this.todo.todo_id}`, {
				method: "PUT",
				body: JSON.stringify({
					status: status,
					body: body
				}),
				credentials: "include"
			});
		},
		markDone: async function () {
			const oldStatus = this.todo.status;
			const newStatus = oldStatus === "done" ? "open" : "done";
			this.todo.status = newStatus;
			try {
				await this._putTodo(newStatus, this.todo.body);
			} catch (error) {
				console.error(error);
				this.todo.status = oldStatus;
			}
		},
		saveChanges: async function (body) {
			// save the body so we can restore it if the PUT fails
			const saveBody = this.todo.body;
			this.todo.body = body;
			this.cancelEdit();
			try {
				await this._putTodo(this.todo.status, this.todo.body)
			} catch (error) {
				console.error(error);
				this.todo.body = saveBody;
			}
		},
		share: function () {
			dialogPolyfill.registerDialog(this.$refs.dialog);
			this.$refs.dialog.showModal();
			this.$refs.url.select();
		},
		copyShareLink: function () {
			this.$refs.url.select();
			document.execCommand("copy");
		},
		closeShare: function () {
			this.$refs.dialog.close();
		}
	}
});

Vue.component("app-todo-editor", {
	props: ["todoId", "body", "editing"],
	data: function () {
		return {
			rawText: this.body,
			rows: this.body.split("\n").length
		};
	},
	template: `
	<div>
		<div 
			class="rendered-body" 
			v-html="marked(body)"
			:class="{ editing: editing }">
		</div>
		<div class="edit-todo" :class="{ editing: editing }">
			<textarea
				:rows="rows"
				@keyup="resize"
				class="todo-body edit-body"
				v-model="rawText">{{ body }}</textarea>
			<button class="save" @click="save">Save</button>
			<button class="cancel" @click="cancel">Cancel</button>
		</div>
	</div>
	`,
	methods: {
		cancel: function () {
			this.$emit("cancel");
		},
		save: function () {
			this.$emit("save", this.rawText);
		},
		resize: function () {
			this.rows = this.rawText.split("\n").length;
		}
	}
});
