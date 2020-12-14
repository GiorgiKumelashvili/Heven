<?php

namespace app\core\configs;

class Constants {
    public static function RootPath(string $path = ''): string {
        $root = dirname(__DIR__, 2);
        return "{$root}/{$path}";
    }
}