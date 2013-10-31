<?php 
exec("git status",$statusArr);
echo json_encode($statusArr);
