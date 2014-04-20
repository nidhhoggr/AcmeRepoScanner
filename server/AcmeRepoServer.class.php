<?php
class AcmeRepoServer extends BaseAcmeRepo {

    private $pullSuccess = array();

    function __construct($settingsArr) {
        $this->notificationEmails = $settingsArr['notificationEmails'];
        $this->curloptUserPwd = $settingsArr['curloptUserPwd'];
        $this->repoManagerViewUrl = $settingsArr['repoManagerViewUrl'];
        $this->coName = $settingsArr['coName'];
        $this->debug = $settingsArr['debugMode'];
        $this->repo_service_url = $settingsArr['repo_service_url'];
	$this->userAgent = $settingsArr['userAgent'];
        $this->tier = "Server";
        if(!empty($settingsArr['git_bin'])) 
	    $this->git_bin = $settingsArr['git_bin'];
    }

    function run() {

        foreach($this->getRepositories() as $repo) {

            $this->repo = $repo;
            $this->resetPullAndStatus();
        }

        $this->sendPullSuccess(); 

        $this->processMailQueue();

        //invoke client process
        $this->invokeClientProcess();
    }

    function getResponseFromUrl($url, $post=false, $debugMsg=false) {

        $handle = curl_init($url);

        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($handle, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($handle ,CURLOPT_USERAGENT, $this->userAgent);

        if($post) {
            $fields_string = null;
            foreach($post as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
            rtrim($fields_string, '&');
            curl_setopt($handle,CURLOPT_POST, count($post));		
            curl_setopt($handle,CURLOPT_POSTFIELDS, $fields_string);
        }
        /* Get the HTML or whatever is linked in $url. */
        $response = curl_exec($handle);

        if($this->debug) var_dump(($debugMsg) ? $debugMsg . $response : $response);

        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);

        curl_close($handle);

        if($httpCode !== 200 && in_array($httpCode,array(302,301,500,404))) {
            $this->handleError($url,array('errorThrown'=>$httpCode));
        }

        return $response;
    }

    function getRepositories() {

        return json_decode($this->getResponseFromUrl($this->repo_service_url,array('action'=>'repos')));
    }

    function sendPullSuccess() {
  
        $request = array(
            'action'=>'pull-success',
            'data'=>json_encode($this->pullSuccess)
        );
		
        $this->getResponseFromUrl($this->repo_service_url,$request,"PULL SUCCESS JSON");
    }

    private function invokeClientProcess() {

        $request = array(
            'action'=>'run',
        );
        $this->getResponseFromUrl($this->repo_service_url,$request,"RESPONSE FROM CLIENT INVOCATION");
    }

    function resetPullAndStatus() {

        $branch_name = end(explode('/',$this->repo->url));
        $repo_location = $this->repo->local_backup_location;

        if($this->git_bin)
            Git::set_bin($this->git_bin); 

        $repo = Git::open($repo_location);

        try {

          $resetRsp = $repo->resetHard();

          $pullRsp = $repo->pull('origin',$branch_name);

          $fetchRsp = $repo->fetch();

        } catch(Exception $e) {

            if($this->debug) var_dump($e->getMessage());
	    $this->handleError($e, $e->getMessage());
            $this->pullSuccess[$this->repo->id] = false;
            return;
	} 

        $statusRsp = $repo->status();

        $responses = (compact('resetRsp','pullRsp','fetchRsp','statusRsp'));

        if($this->debug) var_dump($responses);

        try {

            if(!strstr($resetRsp,"HEAD is now at "))
                Throw new Exception("Git Reset Hard Error");

            $pullErrs = array('rejected','non-fast-forward');

            foreach($pullErrs as $pullErr) {
                if(strstr($pullRsp,$pullErr)) {
                    Throw new Exception("Git Pull Error");
                }
            }

            $fetchErrs = array('fatal','error');

            foreach($fetchErrs as $fetchErr) {
                if(strstr($fetchRsp,$fetchErr)) {
                    Throw new Exception("Git Fetch Error");
                }
            }

            $this->pullSuccess[$this->repo->id] = true;

            if(!strstr($statusRsp,"nothing to commit (working directory clean)"))
                Throw new DirtyBranchException("Branch {$this->repo->name} has some dirty changes");
        }
        catch(DirtyBranchException $e) {
            if($this->debug) var_dump($e->getMessage());
            $this->handleError($e, array($statusRsp));
        } 
        catch(Exception $e) {
            if($this->debug) var_dump($e->getMessage());
            $this->handleError($e, $responses);
            $this->pullSuccess[$this->repo->id] = false;
        }
    }
}
