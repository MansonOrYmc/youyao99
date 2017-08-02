<?php

/**
 * @var string Microsoft/Bing Primary Account Key
 */
if (!defined('ACCOUNT_KEY')) {
    define('ACCOUNT_KEY', 'bM5AkrH7hTJ9u+rHXBXfS9JVyAEV7/SLlnxkvaWbZ/k');
}
if (!defined('CACHE_DIRECTORY')) {
    define('CACHE_DIRECTORY', '/tmp/bing');
}
if (!defined('LANG_CACHE_FILE')) {
    define('LANG_CACHE_FILE', 'lang.cache');
}
if (!defined('ENABLE_CACHE')) {
    define('ENABLE_CACHE', true);
}
if (!defined('UNEXPECTED_ERROR')) {
    define('UNEXPECTED_ERROR', 'There is some un expected error . please check the code');
}
if (!defined('MISSING_ERROR')) {
    define('MISSING_ERROR', 'Missing Required Parameters ( Language or Text) in Request');
}
