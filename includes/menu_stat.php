<?php
$stat = $_GET["s"];
setcookie("menustat", ($stat=="open"?'open':'closed'),time()+30758400, "/");
