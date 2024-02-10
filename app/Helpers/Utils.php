<?php

if (!function_exists('getLocalIpAddress')) {
    function getLocalIpAddress(): string {
        return request()->getClientIp();
    }
}

if (!function_exists('generateNonceWithTimestamp')) {
    function generateNonceWithTimestamp(): string {
        return Carbon\Carbon::now()->timestamp;
    }
}
