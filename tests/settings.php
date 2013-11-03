<?php

//SET THE CONNECTION VARS HERE
$dbuser = 'nsync';
$dbpassword  = 'aq2oPzTKCkS1';
$dbname = 'nsync_log';
$dbhost = 'localhost';
$driver = 'mysql';

$settingsArr = array(
    'notificationEmails' => array(
      'nsync.repo@supraliminalsolutions.com',
    ),
    'curloptUserPwd'=>"zmijevik:ambers25",
    'repoManagerViewUrl'=>"http://nsyncdata.net/logs/repositories/view/",
    'coName'=>'Nsync',
    'database'=>compact('dbuser','dbpassword','dbname','dbhost','driver'),
    'debugMode'=>true,
    'smtp_settings'=>array(
        'host'=>'mail.supraliminalsolutions.com',
        'auth'=>true,
        'username'=>'nsync.repo+supraliminalsolutions.com',
        'password'=>'nsync11!',
        //'security'=>'TLS'
    )
);

