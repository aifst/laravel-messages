<?php

if (! function_exists('getMessageManager')) {
    /**
     * @return string
     */
    function getMessageManager()
    {
        return config('messages.manager');
    }
}
