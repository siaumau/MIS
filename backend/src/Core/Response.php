<?php

namespace App\Core;

/**
 * HTTP 回應管理類
 */
class Response
{
    /**
     * 發送成功回應
     */
    public static function success($data = null, string $message = 'Success', int $code = 200): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * 發送錯誤回應
     */
    public static function error(string $message = 'Error', int $code = 400, $errors = null): void
    {
        self::json([
            'success' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }

    /**
     * 發送分頁回應
     */
    public static function paginated(array $paginatedData, string $message = 'Success'): void
    {
        self::json([
            'success' => true,
            'message' => $message,
            'data' => $paginatedData['data'],
            'pagination' => $paginatedData['pagination']
        ]);
    }

    /**
     * 發送 JSON 回應
     */
    public static function json(array $data, int $code = 200): void
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($code);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }

    /**
     * 發送驗證錯誤回應
     */
    public static function validationError(array $errors, string $message = 'Validation failed'): void
    {
        self::error($message, 422, $errors);
    }

    /**
     * 發送未授權回應
     */
    public static function unauthorized(string $message = 'Unauthorized'): void
    {
        self::error($message, 401);
    }

    /**
     * 發送禁止訪問回應
     */
    public static function forbidden(string $message = 'Forbidden'): void
    {
        self::error($message, 403);
    }

    /**
     * 發送未找到回應
     */
    public static function notFound(string $message = 'Not found'): void
    {
        self::error($message, 404);
    }

    /**
     * 發送伺服器錯誤回應
     */
    public static function serverError(string $message = 'Internal server error'): void
    {
        self::error($message, 500);
    }

    /**
     * 發送建立成功回應
     */
    public static function created($data = null, string $message = 'Created successfully'): void
    {
        self::success($data, $message, 201);
    }

    /**
     * 發送更新成功回應
     */
    public static function updated($data = null, string $message = 'Updated successfully'): void
    {
        self::success($data, $message, 200);
    }

    /**
     * 發送刪除成功回應
     */
    public static function deleted(string $message = 'Deleted successfully'): void
    {
        self::success(null, $message, 200);
    }

    /**
     * 發送無內容回應
     */
    public static function noContent(): void
    {
        http_response_code(204);
        exit();
    }

    /**
     * 重定向
     */
    public static function redirect(string $url, int $code = 302): void
    {
        header("Location: $url", true, $code);
        exit();
    }

    /**
     * 下載檔案
     */
    public static function download(string $filepath, string $filename = null): void
    {
        if (!file_exists($filepath)) {
            self::notFound('File not found');
        }

        $filename = $filename ?? basename($filepath);
        $mimeType = mime_content_type($filepath);

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');

        readfile($filepath);
        exit();
    }

    /**
     * 顯示檔案（在瀏覽器中）
     */
    public static function file(string $filepath, string $filename = null): void
    {
        if (!file_exists($filepath)) {
            self::notFound('File not found');
        }

        $filename = $filename ?? basename($filepath);
        $mimeType = mime_content_type($filepath);

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: public, max-age=3600');

        readfile($filepath);
        exit();
    }
}