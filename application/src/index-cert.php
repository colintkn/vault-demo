<!DOCTYPE html>
<html>
   <head>
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
      <style>
         h1 {
         text-align: center;
         font-family: Bahnschrift SemiBold;
         }
         table, td, th {
         border: 1px solid black;
         height: 50px;
         }
         table {
         border-collapse: collapse;
         width: 100%;
         }
         .center {
         display: block;
         margin-left: auto;
         margin-right: auto;
         width: 50%;
         }
         td {
         text-align: center;
         height: 50px;
         vertical-align: center;
         font-family: Bahnschrift SemiBold;
         }
         tr {
         text-align: center;
         height: 50px;
         vertical-align: center;
         font-family: Bahnschrift SemiBold;
         }
      </style>
   </head>
   <body>
      <?php

echo "<table border='3'>
         <tr>
             <th><h2>Demo</h2></th>
         </tr>
         <tr>
             <td><h3>HashiCorp Vault - Secret Injection through VSO</h3></td>
         </tr>";
echo "</table><br/>";

echo "<body style='background-color:darkgreen'>";

echo '<h1 
        style="border: 2px solid DodgerBlue;color:white;"> 
            Certificate from Vault is used 
        <i 
            class="fa fa-thumbs-up" 
            style="font-size:36px;color:white">
        </i>
        <i 
            class="fa fa-thumbs-up" 
            style="font-size:36px;color:lightblue">
        </i>
        <i 
            class="fa fa-thumbs-up" 
            style="font-size:36px;color:yellow">
        </i>
        </h1>
        <br/><br/>';
?>
  </body>
</html>