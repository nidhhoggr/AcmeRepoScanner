<?php
require_once(dirname(__FILE__) . '/../BaseAcmeRepo.class.php');
require_once(dirname(__FILE__) . '/AcmeRepoClient.class.php');

require(dirname(__FILE__) . '/../vendor/PHPMailer/PHPMailerAutoload.php');

require_once(dirname(__FILE__) . '/settings.php');
$ARC = new AcmeRepoClient($settingsArr);


require_once(dirname(__FILE__) . '/RepositoryModel.class.php');
$repositoryModel = new RepositoryModel($settingsArr['database']);

$ARC->setDBM(compact('repositoryModel'));

//configure mail server
$mailer = new PHPMailer;

$ss = $settingsArr['smtp_settings'];

if(count($ss)) { 

    $mailer->isSMTP();           
    $mailer->Host = $ss['host']; 
    $mailer->SMTPAuth = $ss['auth'];
    $mailer->Username = $ss['username'];
    $mailer->Password = $ss['password'];                         
    $mailer->SMTPSecure = $ss['security'];
}
$ARC->setMailer($mailer); 
