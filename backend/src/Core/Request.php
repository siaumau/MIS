<?php

namespace App\Core;

/**
 * HTTP 請求處理類
 */
class Request
{
    private array $data;
    private array $files;
    private array $headers;

    public function __construct()
    {
        $this->data = $this->parseInput();
        $this->files = $_FILES;
        $this->headers = $this->getAllHeaders();
    }

    /**
     * 解析輸入數據
     */
    private function parseInput(): array
    {
        $input = [];

        // GET 參數
        if (!empty($_GET)) {
            $input = array_merge($input, $_GET);
        }

        // POST 參數
        if (!empty($_POST)) {
            $input = array_merge($input, $_POST);
        }

        // JSON 或其他格式的請求體
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            $jsonData = json_decode($json, true);
            if ($jsonData) {
                $input = array_merge($input, $jsonData);
            }
        }

        return $input;
    }

    /**
     * 獲取所有 HTTP 標頭
     */
    private function getAllHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('_', '-', substr($key, 5));
                $headers[strtolower($header)] = $value;
            }
        }
        return $headers;
    }

    /**
     * 獲取請求方法
     */
    public function method(): string
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * 獲取請求 URI
     */
    public function uri(): string
    {
        return $_SERVER['REQUEST_URI'];
    }

    /**
     * 獲取請求路徑
     */
    public function path(): string
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    /**
     * 獲取查詢字串
     */
    public function query(): string
    {
        return $_SERVER['QUERY_STRING'] ?? '';
    }

    /**
     * 獲取指定參數
     */
    public function input(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * 獲取所有參數
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * 檢查參數是否存在
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * 獲取多個參數
     */
    public function only(array $keys): array
    {
        return array_intersect_key($this->data, array_flip($keys));
    }

    /**
     * 排除指定參數
     */
    public function except(array $keys): array
    {
        return array_diff_key($this->data, array_flip($keys));
    }

    /**
     * 獲取檔案
     */
    public function file(string $key): ?array
    {
        return $this->files[$key] ?? null;
    }

    /**
     * 檢查是否有檔案上傳
     */
    public function hasFile(string $key): bool
    {
        return isset($this->files[$key]) && $this->files[$key]['error'] === UPLOAD_ERR_OK;
    }

    /**
     * 獲取所有檔案
     */
    public function files(): array
    {
        return $this->files;
    }

    /**
     * 獲取 HTTP 標頭
     */
    public function header(string $key, $default = null)
    {
        return $this->headers[strtolower($key)] ?? $default;
    }

    /**
     * 獲取所有標頭
     */
    public function headers(): array
    {
        return $this->headers;
    }

    /**
     * 獲取 Bearer Token
     */
    public function bearerToken(): ?string
    {
        $authorization = $this->header('authorization');
        if ($authorization && strpos($authorization, 'Bearer ') === 0) {
            return substr($authorization, 7);
        }
        return null;
    }

    /**
     * 獲取客戶端 IP
     */
    public function ip(): string
    {
        // 檢查共享網路
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        // 檢查代理 IP
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        // 檢查遠端 IP
        elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        
        return '0.0.0.0';
    }

    /**
     * 獲取 User Agent
     */
    public function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    /**
     * 檢查是否為 AJAX 請求
     */
    public function isAjax(): bool
    {
        return strtolower($this->header('x-requested-with', '')) === 'xmlhttprequest';
    }

    /**
     * 檢查是否為 JSON 請求
     */
    public function isJson(): bool
    {
        return strpos($this->header('content-type', ''), 'application/json') !== false;
    }

    /**
     * 檢查請求方法
     */
    public function isMethod(string $method): bool
    {
        return strtoupper($this->method()) === strtoupper($method);
    }

    /**
     * 驗證參數
     */
    public function validate(array $rules): array
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $this->input($field);
            $ruleArray = is_string($rule) ? explode('|', $rule) : $rule;
            
            foreach ($ruleArray as $singleRule) {
                $error = $this->validateField($field, $value, $singleRule);
                if ($error) {
                    $errors[$field][] = $error;
                }
            }
        }
        
        return $errors;
    }

    /**
     * 驗證單一欄位
     */
    private function validateField(string $field, $value, string $rule): ?string
    {
        [$ruleName, $parameters] = $this->parseRule($rule);
        
        switch ($ruleName) {
            case 'required':
                if (empty($value)) {
                    return "{$field} 為必填欄位";
                }
                break;
                
            case 'email':
                if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return "{$field} 必須為有效的電子郵件地址";
                }
                break;
                
            case 'min':
                $min = (int)$parameters[0];
                if (strlen($value) < $min) {
                    return "{$field} 最少需要 {$min} 個字元";
                }
                break;
                
            case 'max':
                $max = (int)$parameters[0];
                if (strlen($value) > $max) {
                    return "{$field} 最多只能 {$max} 個字元";
                }
                break;
                
            case 'numeric':
                if ($value && !is_numeric($value)) {
                    return "{$field} 必須為數字";
                }
                break;
                
            case 'integer':
                if ($value && !filter_var($value, FILTER_VALIDATE_INT)) {
                    return "{$field} 必須為整數";
                }
                break;
                
            case 'in':
                if ($value && !in_array($value, $parameters)) {
                    return "{$field} 必須為：" . implode(', ', $parameters);
                }
                break;
        }
        
        return null;
    }

    /**
     * 解析驗證規則
     */
    private function parseRule(string $rule): array
    {
        if (strpos($rule, ':') !== false) {
            [$name, $params] = explode(':', $rule, 2);
            return [$name, explode(',', $params)];
        }
        
        return [$rule, []];
    }
}