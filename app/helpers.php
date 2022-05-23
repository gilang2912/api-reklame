<?php

if (!function_exists('public_path')) {
    function public_path($path = null)
    {
        return rtrim(app()->basePath('public/' . $path), '/');
    }
}
