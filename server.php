<?php
if (preg_match('/\.(?:png|jpe?g|gif|bmp|js|css)$/i', $_SERVER['REQUEST_URI'])) {
    return false;
}
else {
    require './test.php';
}