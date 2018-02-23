<?php
/**
 * Router
 * handles registering and dispatching routes
 */
class Router {

	private $routes = [];
	private $guard_callback;

	public function __construct() {
		$this->set_route_guard(function() {
			return true;
		});
	}

	public function route($method, $route, $callback) {
		$meta = [
			"method" => strtoupper($method),
			"route" => $route,
			"callback" => $callback,
			"guarded" => false,
			"fallback" => "/"
		];
		$this->routes[strtoupper($method) . $route] = $meta;
	}

	public function get($route, $callback) {
		$this->route("GET", $route, $callback);
	}

	public function post($route, $callback) {
		$this->route("POST", $route, $callback);
	}

	public function put($route, $callback) {
		$this->route("PUT", $route, $callback);
	}

	public function delete($route, $callback) {
		$this->route("DELETE", $route, $callback);
	}

	public function set_route_guard($callback) {
		$this->guard_callback = $callback;
	}

	public function route_guarded($method, $route, $callback) {
		$this->route($method, $route, $callback);
		$this->routes[strtoupper($method) . $route]["guarded"] = true;
	}

	public function get_guarded($route, $callback) {
		$this->route_guarded("GET", $route, $callback);
	}

	public function post_guarded($route, $callback) {
		$this->route_guarded("POST", $route, $callback);
	}

	public function put_guarded($route, $callback) {
		$this->route_guarded("PUT", $route, $callback);
	}

	public function delete_guarded($route, $callback) {
		$this->route_guarded("DELETE", $route, $callback);
	}

	public function run() {
		$path = $_SERVER["REQUEST_URI"];
		$path_array = explode("/", parse_url($path, PHP_URL_PATH));
		$matching = false;
		foreach ($this->routes as $route_info) {
			$route = $route_info["route"];
			$route_array = explode("/", $route);
			$route_method = $route_info["method"];
			$route_callback = $route_info["callback"];
			$args = [];
			// check if the whole path matches, and dispatch
			if ($path === $route && $_SERVER["REQUEST_METHOD"] === $route_method) {
				$matching = true;
			// check if the route parts + options match and dispatch
			} else if (count($path_array) === count($route_array) && $_SERVER["REQUEST_METHOD"] === $route_method) {
				for ($i = 0; $i < count($route_array); $i++) {
					$route_part = $route_array[$i];
					$path_part = $path_array[$i];
					// either the path should fill a path argument
					if (preg_match("/<(\w+)>/i", $route_part, $matches) !== false) {
						if (isset($matches[1])) {
							$args[$matches[1]] = $path_part;
							$matching = true;
						}
					// or the path parts should match
					} else if ($route_part === $path_part) {
						$matching = true;
					// otherwise the urls don't match
					} else {
						$matching = false;
					}
				}
			}
			if ($matching) {
				if ($route_info["guarded"]) {
					if (call_user_func($this->guard_callback)) {
						call_user_func($route_callback, $args);
					} else {
						header("Location: " . $route_info["fallback"]);
						echo "unauthorized";
					}
				} else {
					call_user_func($route_callback, $args);
				}
				break;
			}
		}

		if (!$matching) {
			if (php_sapi_name() == "cli-server") {
				return false;
			} else {
				echo "404";
			}
		}
	}
}
