<?php
class BaseAcmeRepo {

    protected 
        $mailQueue, 
        $tier,
        $mailer,
        $dbm;


    public function setDBM($dbm) {
        $this->dbm = $dbm;
    }

    public function setMailer(PHPMailer $mailer) {
        $this->mailer = $mailer;
    }

    protected function processMailQueue() {

        foreach($this->notificationEmails as $email) {
            $this->mailer->addAddress($email);
        } 


        if(count($this->mailQueue) > 1) {

            $msg = null;
            $subject = $this->coName . " Repo {$this->tier} - Important Notices";
            foreach($this->mailQueue as $job) {

                $msg .= $job['subject'] . "\r\n".$job['message']."\r\n-------------------------------------------------------------------\r\n";
            }

        } else if(count($this->mailQueue) == 1) {
            $subject = $this->mailQueue[0]['subject'];
            $msg = $this->mailQueue[0]['message'];
        }
        else {
            $subject = " Repo {$this->tier} - Process Success ";
            $msg = " No errors have been thrown for the {$this->tier}"; 
        }

        $this->mailer->Subject = $subject;
        $this->mailer->Body = $msg;
        $this->mailer->send(); 
    }

    protected function handleError(Exception $e, $err) {

        $this->mailQueue[] = array(
          'subject'=>$this->coName . " Repo {$this->tier} - " . $e,
          'message'=>"The following error information has been generated \r\n" . $this->errToString($err)
        );
    }

    protected function errToString($err) {

        foreach($err as $k=>$v) {

            $errMsg .= "\t$k: $v\r\n";
        }

        return $errMsg;
    }
}
