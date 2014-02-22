<?php
use Sea\Sea;

// Get composer
$composer = require './vendor/autoload.php';
$sea = new Sea($composer);
$sea->run()->send();