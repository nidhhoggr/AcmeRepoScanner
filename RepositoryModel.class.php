<?php

require_once(dirname(__FILE__) . '/SupraModel/SupraModel.class.php');

//SET THE CONNECTION VARS HERE
$dbuser = 'nsync';
$dbpassword  = 'aq2oPzTKCkS1';
$dbname = 'nsync_log';
$dbhost = 'localhost';
$driver = 'mysql';

$connection_args = compact('dbuser','dbname','dbpassword','dbhost','driver');

//EXTEND THE BASE MODEL
class RepositoryModel extends SupraModel {

    //SET THE TABLE OF THE MODEL AND THE IDENTIFIER
    public function configure() {

        $this->setTable("repositories");
    }
}

$repositoryModel = new RepositoryModel($connection_args);
