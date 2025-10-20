<?php

/**
 * Copyright 2022-2025 FOSSBilling
 *
 * SPDX-License-Identifier: Apache-2.0.
 */

namespace FOSSBilling\Routing;

use Box_App;
use FOSSBilling\Config;

class RouteGroup
{
    private Box_App $app;

    private string $prefix;

    public static function dashboard(Box_App $app): self
    {
        return new self($app, self::prefix());
    }

    public static function prefix(): string
    {
        $configured = Config::getProperty('client_area.dashboard_prefix', '/dashboard');
        $normalized = '/' . trim($configured, '/');

        return $normalized === '/' ? '' : $normalized;
    }

    public static function path(?string $suffix = null): string
    {
        $prefix = self::prefix();
        $suffix = $suffix ?? '';
        $suffix = trim($suffix);

        if ($suffix === '' || $suffix === '/') {
            return $prefix === '' ? '/' : $prefix;
        }

        return ($prefix === '' ? '' : $prefix) . '/' . ltrim($suffix, '/');
    }

    public static function isDashboardPath(string $path): bool
    {
        $prefix = self::prefix();
        if ($prefix === '') {
            return true;
        }

        $normalizedPath = '/' . ltrim($path, '/');

        return str_starts_with($normalizedPath, $prefix);
    }

    public static function ensureDashboardPath(Box_App $app, string $suffix, string $currentPath): void
    {
        if (!self::isDashboardPath($currentPath)) {
            $app->redirect(self::path($suffix));
        }
    }

    public function __construct(Box_App $app, string $prefix)
    {
        $this->app = $app;
        $this->prefix = $prefix;
    }

    public function get(string $url, string $methodName, ?array $conditions = [], ?string $class = null): self
    {
        $this->map('get', $url, $methodName, $conditions, $class);

        return $this;
    }

    public function post(string $url, string $methodName, ?array $conditions = [], ?string $class = null): self
    {
        $this->map('post', $url, $methodName, $conditions, $class);

        return $this;
    }

    public function put(string $url, string $methodName, ?array $conditions = [], ?string $class = null): self
    {
        $this->map('put', $url, $methodName, $conditions, $class);

        return $this;
    }

    public function delete(string $url, string $methodName, ?array $conditions = [], ?string $class = null): self
    {
        $this->map('delete', $url, $methodName, $conditions, $class);

        return $this;
    }

    private function map(string $httpMethod, string $url, string $methodName, ?array $conditions, ?string $class): void
    {
        $normalized = $this->normalize($url);
        $this->app->{$httpMethod}($normalized, $methodName, $conditions, $class);
    }

    private function normalize(string $url): string
    {
        $trimmed = trim($url);
        if ($trimmed === '' || $trimmed === '/') {
            return $this->prefix === '' ? '/' : $this->prefix;
        }

        return ($this->prefix === '' ? '' : $this->prefix) . '/' . ltrim($trimmed, '/');
    }
}
