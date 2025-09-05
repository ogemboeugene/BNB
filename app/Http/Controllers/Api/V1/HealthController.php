<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class HealthController
 * 
 * Provides health check endpoints for monitoring the API status,
 * database connectivity, and other system components.
 * 
 * @package App\Http\Controllers\Api\V1
 */
class HealthController extends Controller
{
    /**
     * Basic health check endpoint.
     * 
     * Returns a simple status response indicating the API is operational.
     * This endpoint is lightweight and suitable for load balancer health checks.
     * 
     * @return JsonResponse
     */
    public function check(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'message' => 'BNB Management API is operational',
            'timestamp' => now()->toISOString(),
            'version' => 'v1',
        ]);
    }

    /**
     * Detailed health check endpoint.
     * 
     * Performs comprehensive health checks on various system components
     * including database connectivity, cache system, and other dependencies.
     * 
     * @return JsonResponse
     */
    public function detailed(): JsonResponse
    {
        $checks = [];
        $overall_status = 'healthy';
        $status_code = Response::HTTP_OK;

        // Database connectivity check
        try {
            DB::connection()->getPdo();
            $checks['database'] = [
                'status' => 'healthy',
                'message' => 'Database connection is working',
                'response_time_ms' => $this->measureExecutionTime(function () {
                    DB::select('SELECT 1');
                })
            ];
        } catch (\Exception $e) {
            $checks['database'] = [
                'status' => 'unhealthy',
                'message' => 'Database connection failed',
                'error' => $e->getMessage()
            ];
            $overall_status = 'unhealthy';
            $status_code = Response::HTTP_SERVICE_UNAVAILABLE;
        }

        // Cache system check
        try {
            $cache_key = 'health_check_' . time();
            $cache_value = 'test_value_' . uniqid();
            
            $response_time = $this->measureExecutionTime(function () use ($cache_key, $cache_value) {
                Cache::put($cache_key, $cache_value, 60);
                $retrieved = Cache::get($cache_key);
                Cache::forget($cache_key);
                
                if ($retrieved !== $cache_value) {
                    throw new \Exception('Cache value mismatch');
                }
            });
            
            $checks['cache'] = [
                'status' => 'healthy',
                'message' => 'Cache system is working',
                'response_time_ms' => $response_time
            ];
        } catch (\Exception $e) {
            $checks['cache'] = [
                'status' => 'unhealthy',
                'message' => 'Cache system failed',
                'error' => $e->getMessage()
            ];
            $overall_status = 'degraded';
            if ($status_code === Response::HTTP_OK) {
                $status_code = Response::HTTP_PARTIAL_CONTENT;
            }
        }

        // Application configuration check
        $config_checks = [];
        
        // Check required environment variables
        $required_env_vars = ['APP_KEY', 'APP_ENV', 'DB_CONNECTION'];
        foreach ($required_env_vars as $var) {
            $config_checks[$var] = [
                'status' => env($var) ? 'configured' : 'missing',
                'configured' => (bool) env($var)
            ];
        }

        $checks['configuration'] = [
            'status' => 'healthy',
            'message' => 'Configuration loaded successfully',
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'required_vars' => $config_checks
        ];

        // System resources check
        $checks['system'] = [
            'status' => 'healthy',
            'message' => 'System resources are available',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'memory_usage' => [
                'current' => $this->formatBytes(memory_get_usage()),
                'peak' => $this->formatBytes(memory_get_peak_usage()),
                'limit' => ini_get('memory_limit')
            ],
            'disk_space' => [
                'free' => $this->formatBytes(disk_free_space('.')),
                'total' => $this->formatBytes(disk_total_space('.'))
            ]
        ];

        // BNB-specific checks
        try {
            $bnb_count = DB::table('bnbs')->count();
            $checks['bnb_service'] = [
                'status' => 'healthy',
                'message' => 'BNB service is operational',
                'total_bnbs' => $bnb_count,
                'available_bnbs' => DB::table('bnbs')->where('availability', true)->count()
            ];
        } catch (\Exception $e) {
            $checks['bnb_service'] = [
                'status' => 'unhealthy',
                'message' => 'BNB service failed',
                'error' => $e->getMessage()
            ];
            $overall_status = 'unhealthy';
            $status_code = Response::HTTP_SERVICE_UNAVAILABLE;
        }

        return response()->json([
            'status' => $overall_status,
            'message' => $this->getOverallMessage($overall_status),
            'timestamp' => now()->toISOString(),
            'version' => 'v1',
            'checks' => $checks,
            'summary' => [
                'total_checks' => count($checks),
                'healthy_checks' => count(array_filter($checks, function ($check) {
                    return $check['status'] === 'healthy';
                })),
                'uptime_seconds' => $this->getUptime()
            ]
        ], $status_code);
    }

    /**
     * Measure execution time of a callable in milliseconds.
     * 
     * @param callable $callable
     * @return float
     */
    private function measureExecutionTime(callable $callable): float
    {
        $start = microtime(true);
        $callable();
        $end = microtime(true);
        
        return round(($end - $start) * 1000, 2);
    }

    /**
     * Format bytes into human readable format.
     * 
     * @param int $size
     * @param int $precision
     * @return string
     */
    private function formatBytes(int $size, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }

    /**
     * Get overall status message.
     * 
     * @param string $status
     * @return string
     */
    private function getOverallMessage(string $status): string
    {
        return match ($status) {
            'healthy' => 'All systems are operational',
            'degraded' => 'Some systems are experiencing issues',
            'unhealthy' => 'Critical systems are failing',
            default => 'System status unknown'
        };
    }

    /**
     * Get application uptime in seconds.
     * 
     * @return int
     */
    private function getUptime(): int
    {
        // This is a simplified uptime calculation
        // In production, you might want to store the start time in a file or cache
        $start_time = Cache::get('app_start_time');
        
        if (!$start_time) {
            $start_time = now()->timestamp;
            Cache::forever('app_start_time', $start_time);
        }
        
        return now()->timestamp - $start_time;
    }
}
