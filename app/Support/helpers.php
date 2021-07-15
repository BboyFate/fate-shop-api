<?php

use Illuminate\Support\Facades\Date;

if (! function_exists('request')) {
    /**
     * Get an instance of the current request or an input item from the request.
     *
     * @param  array|string  $key
     * @param  mixed   $default
     * @return \Illuminate\Http\Request|string|array
     */
    function request($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('request');
        }

        if (is_array($key)) {
            return app('request')->only($key);
        }

        $value = app('request')->__get($key);

        return is_null($value) ? value($default) : $value;
    }
}

if (! function_exists('formatFloat')) {
    /**
     * 格式化浮点数
     * 奇进偶舍
     *
     * @param $value
     * @param int $scale
     * @return float
     */
    function formatFloat($value, $scale = 2)
    {
        $value = number_format($value, 2 + $scale, '.', '');

        return round($value, $scale, PHP_ROUND_HALF_EVEN);
    }
}

if (! function_exists('now')) {
    /**
     * 创建当前时间
     *
     * @param null $tz
     * @return \Illuminate\Support\Carbon
     */
    function now($tz = null)
    {
        return Date::now($tz);
    }
}
