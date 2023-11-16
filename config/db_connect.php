
<?php
   $host = 'localhost';
   $user = 'root';
   $password = '';
   $db = 'stock_management';

   $conn = new mysqli($host, $user, $password, $db);

   if($conn->connect_error) {
      die('Connection failed : ' . $conn->connect_error);
   } else {
    # echo "Connected successfully with :: MySQL.";
   }
?>