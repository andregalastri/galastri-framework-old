<?php
/** Configura o debug, com base na configuração de galastr/config/debug.php */
$debug = require('../app/config/debug.php');

ini_set('display_errors', $debug['debug'] ? 'On' : 'Off');
error_reporting(E_ALL);
