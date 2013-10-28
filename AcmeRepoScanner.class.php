<?php
require_once('RepositoryModel.class.php');

class AcmeRepoScanner {


    function __construct($settingsArr) {
        $this->toScan = $this->getScannable();
        $this->notificationEmails = $settingsArr['notificationEmails'];
        $this->curloptUserPwd = $settingsArr['curloptUserPwd']; 
        $this->repoManagerViewUrl = $settingsArr['repoManagerViewUrl'];
        $this->coName = $settingsArr['coName'];
    }

    function run() {

        foreach($this->toScan as $scannable) {

            $this->scannable = $scannable; 

            $this->processLastCommittFromGithubAPI();

            $this->processDirtyBranch();
        }
    }


    function processDirtyBranch() {

        $repo_status_node = $this->scannable['repo_status_node'];

        $handle = curl_init($repo_status_node);

        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
        /* Get the HTML or whatever is linked in $url. */
        $response = curl_exec($handle);

        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        curl_close($handle);

        if($httpCode == 200) {
            $this->processDirtyBranchResponse($response);
        }
        else if(in_array($httpCode,array(302,301,500,404))) {
            $this->handleError($url,array('errorThrown'=>$httpCode));
        }
    }

    function processDirtyBranchResponse($response) {

        $cleanStr = "nothing to commit (working directory clean)";

        if(!strstr($response,$cleanStr)) {

            $this->notifyDirtyBranch($response); 
        }
    }

    function processLastCommittFromGithubAPI() {

        $url = $this->scannable['url'];

        $handle = curl_init($url);

        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($handle, CURLOPT_USERPWD, $this->curloptUserPwd);
        /* Get the HTML or whatever is linked in $url. */
        $response = curl_exec($handle);

        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        curl_close($handle);

        if($httpCode == 200) {
            $this->processResponse($response);
        }
        else if(in_array($httpCode,array(302,301,500,404))) {
            $this->handleError($url,array('errorThrown'=>$httpCode));
        }
    }

    function processResponse($response) {

        $response = json_decode($response);

        $url = $response->object->url;

        $handle = curl_init($url);

        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($handle, CURLOPT_USERPWD, $this->curloptUserPwd);
        /* Get the HTML or whatever is linked in $url. */
        $response = curl_exec($handle);
 
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        curl_close($handle);

        if($httpCode == 200) {
            $this->processCommit($response);
        }
        else if(in_array($httpCode,array(302,301,500,404))) {
            $this->handleError($url,array('errorThrown'=>$httpCode));
        }
    }

    function processCommit($response) {

        $response = json_decode($response);

        $committ_date = substr($response->committer->date, 0, 10);

                    //commit date            //synch date

        if(strtotime($committ_date) > strtotime($this->scannable['last_synched'])) { 

            $this->notifySynchOverdue($response->committer);
        }
    }

    function getScannable() {
      global $repositoryModel;

      return $repositoryModel->find(); 
    }

  function notifySynchOverdue($committ) {

      $repo_name = $this->scannable['name'];

      $content = $committ->name . " updated $repo_name on " . $committ->date . " and it has not been updated in w pages\r\n If this is not the case then update the repository last synched date in logs\r\n Visit the repo settings here:" . $this->repoManagerViewUrl .  $this->scannable['id'];

      foreach($this->notificationEmails as $email) {

          mail($email,$this->coName . " Repo Scanner Synch Required",$content);
      }
  }

  function notifyDirtyBranch($dirtyChanges) {

      $repo_name = $this->scannable['name'];

      $content = "There are some dirty changes tha have not yet been committed on $repo_name \r\n" .
                 "the status dump is below \r\n Visit the repo settings here: " . $this->repoManagerViewUrl . $this->scannable['id'] . 
                 var_export(json_decode($dirtyChanges),true);

      foreach($this->notificationEmails as $email) {
          mail($email,$this->coName . " Repo Scanner - Dirty Repo (Changes Made)",$content);
      }
  }

  function handleError($url,$err) {

      foreach($this->notificationEmails as $email) {

          mail($email,$this->coName . " Repo Scanner Error Report - " . $url,"The following error information has been generated for $url \r\n" . $this->errToString($err));
      }
  }

  function errToString($err) {

     foreach($err as $k=>$v) {

       $errMsg .= "\t$k: $v\r\n";
     }

     return $errMsg;
  }
}
