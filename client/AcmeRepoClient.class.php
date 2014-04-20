<?php
class AcmeRepoClient extends BaseAcmeRepo {

    function __construct($settingsArr) {
        $this->notificationEmails = $settingsArr['notificationEmails'];
        $this->curloptUserPwd = $settingsArr['curloptUserPwd']; 
        $this->repoManagerViewUrl = $settingsArr['repoManagerViewUrl'];
        $this->coName = $settingsArr['coName'];
        $this->debug = $settingsArr['debugMode'];
	$this->userAgent = $settingsArr['userAgent'];
        $this->tier = "Client"; 
    }

    public function run() {

        foreach($this->getScannable() as $scannable) {

            $this->scannable = $scannable; 

            $this->processLastCommittFromGithubAPI();

            $this->processDirtyBranch();
        }

        $this->processMailQueue();
    }

    private function gitApiCall($url) {

        $handle = curl_init($url);

        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($handle, CURLOPT_USERPWD, $this->curloptUserPwd);
        curl_setopt($handle ,CURLOPT_USERAGENT, $this->userAgent);

        /* Get the HTML or whatever is linked in $url. */
        $response = curl_exec($handle);

        if($this->debug) var_dump($response); 

        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        curl_close($handle);

        try {
            if(in_array($httpCode,array(302,301,500,404))) {
                throw Exception("CURL Response is a bad status code: $httpCode");
            }
            return $response;
        }
        catch(Exception $e) {
            $this->handleException($e, $url);
            return false;
        }
    }
 
    private function processDirtyBranch() {

        $repo_status_node = $this->scannable['repo_status_node'];

        if($response = $this->gitApiCall($repo_status_node)) { 

            $cleanStr = "nothing to commit (working directory clean)";

            if(!strstr($response,$cleanStr)) {

                $this->notifyDirtyBranch($response); 
            }
        }
    }

    private function processLastCommittFromGithubAPI() {

        $url = $this->scannable['url'];

        if($response = $this->gitApiCall($url)) {

            $response = json_decode($response);

            $url = $response->object->url;

            if($response = $this->gitApiCall($url)) {

                $response = json_decode($response);

                $committ_date = substr($response->committer->date, 0, 10);

                    //commit date            //synch date
                if(strtotime($committ_date) > strtotime($this->scannable['last_synched'])) { 

                    $this->notifySynchOverdue($response->committer);
                }
            }
        }
    }

    /**
     * Retrieves a JSON string from the server and updates repo lastSynched date
     */
    public function processServerPullSuccess($data) {
     
        $repositoryModel = $this->dbm['repositoryModel'];
 
        $data = json_decode(stripslashes($data)); 
 
        foreach($data as $id=>$success) {

            try {
            
                if($success) {
                    $repositoryModel->id = $id;
                    $repositoryModel->last_synched = date("Y-m-d H:i:s");
                    $result = $repositoryModel->save(); 

                    if($result != $id) throw Exception('Error saving last synched date');
                }
  
            } catch(Exception $e) {

                $this->handleException($e,$repositoryModel);
            }
        }
    } 

    public function getScannable() {

        $repositoryModel = $this->dbm['repositoryModel'];

        return $repositoryModel->find(); 
    }

    //error notification methods

    private function notifySynchOverdue($committ) {

        $repo_name = $this->scannable['name'];

        $content = $committ->name . " updated $repo_name on " . $committ->date . " and it has not been updated in w pages\r\n" . 
                   "If this is not the case then update the repository last synched date in logs\r\n" . 
                   "Visit the repo settings here: " . $this->repoManagerViewUrl .  $this->scannable['id'];


        $this->mailQueue[] = array(
          'subject'=>$this->coName . " Repo Client - Backup Required",
          'message'=>$content
        );
    }

    private function notifyDirtyBranch($dirtyChanges) {

        $repo_name = $this->scannable['name'];

        $content = "There are some dirty changes tha have not yet been committed on $repo_name \r\n" .
                   "the status dump is below \r\n Visit the repo settings here: " . 
                   $this->repoManagerViewUrl . $this->scannable['id'] . ' ' . var_export(json_decode($dirtyChanges),true);

        $this->mailQueue[] = array(
          'subject'=>$this->coName . " Repo Client - Dirty Repo",
          'message'=>$content
        );
    }
}
