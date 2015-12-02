<?php
   session_start();
   if(!isset($_SESSION['security_level']))
       header("Location: https://www.aceitlab.com/rudi/login.php");
?>
<html>
<head>
</head>
<body>
</body>
</html>
