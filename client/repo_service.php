<?php

extract($_REQUEST);
if(!$action) die(0);

require_once(dirname(__FILE__) . '/bootload.php');

switch($action) {

    case 'repos':
        echo json_encode($ARC->getScannable()); 
    break;
    case 'run':
        $ARC->run(); 
    break;
    case 'pull-success':
        $ARC->processServerPullSuccess($data);
    break;
}
