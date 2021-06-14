<?php
require_once 'init.php';
require_once 'app/reports/JasperGenerate.php';
require 'vendor/autoload.php';

$json = '{
    "legalities": [
        "brawl",
        "commander",
        "duel",
        "future",
        "gladiator",
        "historic",
        "legacy",
        "modern",
        "penny",
        "pioneer",
        "standard",
        "vintage"
    ]
}';

$a = json_decode($json);
var_dump($a);
