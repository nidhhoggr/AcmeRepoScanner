<?php

require_once(dirname(__FILE__) . "/../BaseAcmeRepo.class.php");

require(dirname(__FILE__) . '/../vendor/PHPMailer/PHPMailerAutoload.php');

require_once(dirname(__FILE__) . '/settings.php');

//require_once(dirname(__FILE__) . '/../client/RepositoryModel.class.php');
//$repositoryModel = new RepositoryModel($settingsArr['database']);

//$bart->setDBMcompact('repositoryModel'));

class BaseAcmeRepoTest extends BaseAcmeRepo { 

    function __construct($settingsArr) {
            
            //configure mail server
            $mailer = new PHPMailer;

            //$mailer->isSMTP();
            $ss = $settingsArr['smtp_settings'];
            $this->setMailer($mailer);
    } 

    public function test_runMailQueueTest() {
        global $bart;

        $bart->debug = true;

        $bart->notificationEmails = array(
            'joseph@supraliminalsolutions.com'
        );

        $bart->processMailQueue();
    }
 
} 

$bart = new BaseAcmeRepoTest($settingsArr);
$bart->test_runMailQueueTest();
