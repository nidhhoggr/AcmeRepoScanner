<?php
require("AcmeRepoScanner.class.php");
require('settings.php');

$scanner = new AcmeRepoScanner($settingsArr);

$scanner->run();

//$scanner->displayOutput();

