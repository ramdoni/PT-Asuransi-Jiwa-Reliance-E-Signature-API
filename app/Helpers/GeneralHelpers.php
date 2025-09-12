<?php

if (! function_exists('asset')) {
    function asset($path, $secure = null)
    {
        return app('url')->asset($path, $secure);
    }
}