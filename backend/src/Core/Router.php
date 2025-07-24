<?php

namespace App\Core;

use App\Core\Response;

/**
 * 路由管理類
 */
class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private string $prefix = '';
    private array $groupMiddlewares = [];

    /**
     * 註冊 GET 路由
     */
    public function get(string $path, $handler): self
    {
        return $this->addRoute('GET', $path, $handler);
    }

    /**
     * 註冊 POST 路由
     */
    public function post(string $path, $handler): self
    {
        return $this->addRoute('POST', $path, $handler);
    }

    /**
     * 註冊 PUT 路由
     */
    public function put(string $path, $handler): self
    {
        return $this->addRoute('PUT', $path, $handler);
    }

    /**
     * 註冊 DELETE 路由
     */
    public function delete(string $path, $handler): self
    {
        return $this->addRoute('DELETE', $path, $handler);
    }

    /**
     * 註冊 PATCH 路由
     */
    public function patch(string $path, $handler): self
    {
        return $this->addRoute('PATCH', $path, $handler);
    }

    /**
     * 註冊 OPTIONS 路由
     */
    public function options(string $path, $handler): self
    {
        return $this->addRoute('OPTIONS', $path, $handler);
    }

    /**
     * 路由群組
     */
    public function group(array $attributes, callable $callback): void
    {
        $previousPrefix = $this->prefix;
        $previousMiddlewares = $this->groupMiddlewares;

        if (isset($attributes['prefix'])) {
            $this->prefix = $previousPrefix . '/' . trim($attributes['prefix'], '/');
        }

        if (isset($attributes['middleware'])) {
            $middlewares = is_array($attributes['middleware']) ? $attributes['middleware'] : [$attributes['middleware']];
            $this->groupMiddlewares = array_merge($this->groupMiddlewares, $middlewares);
        }

        $callback($this);

        $this->prefix = $previousPrefix;
        $this->groupMiddlewares = $previousMiddlewares;
    }

    /**
     * 添加中介軟體
     */
    public function middleware($middleware): self
    {
        if (is_array($middleware)) {
            $this->middlewares = array_merge($this->middlewares, $middleware);
        } else {
            $this->middlewares[] = $middleware;
        }
        return $this;
    }

    /**
     * 添加路由
     */
    private function addRoute(string $method, string $path, $handler): self
    {
        $fullPath = $this->prefix . '/' . trim($path, '/');
        $fullPath = '/' . trim($fullPath, '/');
        if ($fullPath === '/') {
            $fullPath = '/';
        }

        $this->routes[] = [
            'method' => $method,
            'path' => $fullPath,
            'handler' => $handler,
            'middlewares' => array_merge($this->groupMiddlewares, $this->middlewares)
        ];

        $this->middlewares = [];
        return $this;
    }

    /**
     * 解析路由
     */
    public function resolve(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // 移除 /api 前綴（如果存在）
        if (strpos($path, '/api') === 0) {
            $path = substr($path, 4) ?: '/';
        }

        // 處理 CORS
        $this->handleCors($method);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method) {
                $params = $this->matchPath($route['path'], $path);
                if ($params !== false) {
                    try {
                        // 執行中介軟體
                        foreach ($route['middlewares'] as $middleware) {
                            $this->executeMiddleware($middleware);
                        }

                        // 執行控制器
                        $this->executeHandler($route['handler'], $params);
                        return;
                    } catch (\Exception $e) {
                        Response::error($e->getMessage(), 500);
                        return;
                    }
                }
            }
        }

        // 沒有找到匹配的路由
        Response::error('Route not found', 404);
    }

    /**
     * 路徑匹配
     */
    private function matchPath(string $routePath, string $requestPath): array|false
    {
        // 將路由路徑轉換為正則表達式
        $pattern = preg_replace('/\{([^}]+)\}/', '(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $requestPath, $matches)) {
            // 過濾出參數
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return $params;
        }

        return false;
    }

    /**
     * 執行中介軟體
     */
    private function executeMiddleware($middleware): void
    {
        if (is_string($middleware)) {
            $middlewareClass = "App\\Middleware\\{$middleware}";
            if (class_exists($middlewareClass)) {
                $middlewareInstance = new $middlewareClass();
                $middlewareInstance->handle();
            }
        } elseif (is_callable($middleware)) {
            $middleware();
        }
    }

    /**
     * 執行處理器
     */
    private function executeHandler($handler, array $params): void
    {
        if (is_string($handler)) {
            // 格式: ControllerName@methodName
            [$controllerName, $methodName] = explode('@', $handler);
            $controllerClass = "App\\Controllers\\{$controllerName}";
            
            if (class_exists($controllerClass)) {
                $controller = new $controllerClass();
                if (method_exists($controller, $methodName)) {
                    $controller->$methodName($params);
                } else {
                    throw new \Exception("Method {$methodName} not found in {$controllerClass}");
                }
            } else {
                throw new \Exception("Controller {$controllerClass} not found");
            }
        } elseif (is_callable($handler)) {
            $handler($params);
        } else {
            throw new \Exception("Invalid handler type");
        }
    }

    /**
     * 處理 CORS
     */
    private function handleCors(string $method): void
    {
        // 設置 CORS 標頭
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');

        // 處理預檢請求
        if ($method === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    /**
     * 獲取所有路由（用於除錯）
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}