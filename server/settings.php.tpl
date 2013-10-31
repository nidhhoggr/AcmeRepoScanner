<?php

//SET THE CONNECTION VARS HERE
$dbuser = 'root';
$dbpassword  = 'root';
$dbname = 'acme_repo';
$dbhost = 'localhost';
$driver = 'mysql';

$settingsArr = array(
    'notificationEmails' => array(
      'joseph@test.com',
    ),
    'curloptUserPwd'=>"username:password",
    'repoManagerViewUrl'=>"http://test.com/repos/view/",
    'coName'=>'Supra',
    'database'=>compact('dbuser','dbpassword','dbname','dbhost','driver'),
    'debugMode'=>true,
    'repo_service_url'=>'http://clients/nsyncdata/AcmeRepoScanner/client/repo_service.php', 
    'smtp_settings'=>array(
        'host'=>'smtphost.com',
        'auth'=>true,
        'username'=>'joseph+test.com',
        'password'=>'password',
        'security'=>'TLS'
    )
);

