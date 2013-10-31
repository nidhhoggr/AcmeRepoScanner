<?php
 
require_once(dirname(__FILE__) . '/../SupraModel/SupraModel.class.php');

//EXTEND THE BASE MODEL
class RepositoryModel extends SupraModel {

    //SET THE TABLE OF THE MODEL AND THE IDENTIFIER
    public function configure() {

        $this->setTable("repositories");
    }
}
