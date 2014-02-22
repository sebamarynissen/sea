<?php
use Sea\Sea;

// Get composer
$composer = require './vendor/autoload.php';
$sea = new Sea($composer);
$sea->routing('./config/routes.json')->run()->send();