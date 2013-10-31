<?php
require_once(dirname(__FILE__) . '/../vendor/Git.php/Git.php');
require_once(dirname(__FILE__) . '/../vendor/PHPMailer/PHPMailerAutoload.php');
require_once(dirname(__FILE__) . '/../BaseAcmeRepo.class.php');
require_once("AcmeRepoServer.class.php");
require_once('settings.php');
require_once("DirtyBranchException.class.php");

//configure mail server
$mailer = new PHPMailer;

$mailer->isSMTP();           

$ss = $settingsArr['smtp_settings'];

$mailer->Host = $ss['host']; 
$mailer->SMTPAuth = $ss['auth'];    
$mailer->Username = $ss['username'];
$mailer->Password = $ss['password'];                         
$mailer->SMTPSecure = $ss['security'];

$server = new AcmeRepoServer($settingsArr);

$server->setMailer($mailer);

$server->run();
