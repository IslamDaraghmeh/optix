<?php
/**
 * Front Controller
 *
 * Application entry point - handles all incoming requests
 *
 * @package Optix
 * @author Optix Development Team
 * @version 1.0
 */

// Load configuration
require_once __DIR__ . '/../config/config.php';

// Load constants
require_once CONFIG_PATH . '/constants.php';

// Error handling
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }

    $message = "Error [{$errno}]: {$errstr} in {$errfile} on line {$errline}";
    error_log($message . "\n", 3, LOG_PATH . '/errors.log');

    if (APP_DEBUG) {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px;'>";
        echo "<strong>Error:</strong> {$errstr}<br>";
        echo "<strong>File:</strong> {$errfile}<br>";
        echo "<strong>Line:</strong> {$errline}";
        echo "</div>";
    }

    return true;
});

set_exception_handler(function ($exception) {
    $message = "Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    error_log($message . "\n" . $exception->getTraceAsString() . "\n", 3, LOG_PATH . '/errors.log');

    if (APP_DEBUG) {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin: 10px;'>";
        echo "<h3>Exception:</h3>";
        echo "<strong>Message:</strong> " . $exception->getMessage() . "<br>";
        echo "<strong>File:</strong> " . $exception->getFile() . "<br>";
        echo "<strong>Line:</strong> " . $exception->getLine() . "<br>";
        echo "<pre>" . $exception->getTraceAsString() . "</pre>";
        echo "</div>";
    } else {
        http_response_code(500);
        echo "<h1>500 Internal Server Error</h1>";
        echo "<p>Something went wrong. Please try again later.</p>";
    }
});

/**
 * Simple Router Class
 */
class Router
{
    /**
     * Parse URL and route to controller
     *
     * @return void
     */
    public static function dispatch(): void
    {
        // Get URL path
        $url = self::getUrl();

        // Parse route
        $route = self::parseRoute($url);

        // Get controller, method, and parameters
        $controllerName = $route['controller'];
        $method = $route['method'];
        $params = $route['params'];

        // Build controller class name
        $controllerClass = "App\\Controllers\\{$controllerName}Controller";

        // Check if controller exists
        if (!class_exists($controllerClass)) {
            self::error404("Controller not found: {$controllerName}");
            return;
        }

        // Instantiate controller
        $controller = new $controllerClass();

        // Check if method exists
        if (!method_exists($controller, $method)) {
            self::error404("Method not found: {$method} in {$controllerName}");
            return;
        }

        // Call method with parameters
        call_user_func_array([$controller, $method], $params);
    }

    /**
     * Get URL from request
     *
     * @return string
     */
    private static function getUrl(): string
    {
        $url = $_GET['url'] ?? '';
        $url = rtrim($url, '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);
        return $url;
    }

    /**
     * Parse route from URL
     *
     * @param string $url URL string
     * @return array
     */
    private static function parseRoute(string $url): array
    {
        // Default route
        if (empty($url)) {
            return [
                'controller' => 'Auth',
                'method' => 'showLoginForm',
                'params' => [],
            ];
        }

        // Route aliases for common paths
        $aliases = [
            'login' => 'auth/showLoginForm',
            'logout' => 'auth/logout',
            'register' => 'auth/register',
        ];

        // Check if URL matches an alias
        if (isset($aliases[$url])) {
            $url = $aliases[$url];
        }

        // Split URL into segments
        $segments = explode('/', $url);

        // Controller name (capitalize first letter)
        $controller = ucfirst(strtolower($segments[0]));

        // Method name (default to 'index')
        $method = isset($segments[1]) ? strtolower($segments[1]) : 'index';

        // Parameters
        $params = array_slice($segments, 2);

        return [
            'controller' => $controller,
            'method' => $method,
            'params' => $params,
        ];
    }

    /**
     * Handle 404 error
     *
     * @param string $message Error message
     * @return void
     */
    private static function error404(string $message = 'Page not found'): void
    {
        http_response_code(404);

        if (APP_DEBUG) {
            echo "<h1>404 Not Found</h1>";
            echo "<p>{$message}</p>";
        } else {
            echo "<h1>404 Not Found</h1>";
            echo "<p>The page you are looking for could not be found.</p>";
        }
    }
}

// Start routing
Router::dispatch();
