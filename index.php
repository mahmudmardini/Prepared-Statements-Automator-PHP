<?php
//new line
$nl = '
';
$startNote = "<?php/* Your processed code
⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇⬇*/".$nl.$nl;

$converted_code = isset($_POST['phpCode'])? $_POST['phpCode']:'';
$input_code = isset($_POST['phpCode'])? $_POST['phpCode']:'';

  if (isset($_POST['phpCode'])) {

    // spilt linse by semicolons
     $lines = explode(';', $converted_code);

     $stmt_counter = 0;

     // analyze code line by line
     foreach ($lines as $line) {
       $variable = null;

       // if the line contain sql statement that uses 'query()' execute the following algorithm
       if (strpos($line, 'query(') ) {
         // spilt the line by 'query' expression
         $str = explode('query(', $line);
         //define the statement variable if exists
         $exists_stmt_variable = trim(explode('=', $line)[0]);

         //set the new statement variable
         //if the statement already has a variable use it, if not set a new varible
         // (substr) used to avoid comment lines by starting from varible position
         $stmt_variable =  strpos($str[0], '=')? substr($exists_stmt_variable, strpos($exists_stmt_variable,'$')): '$stmt'.$stmt_counter;

           if (strpos($str[0], '=')) {
             $equPos = strpos($str[0], '=');
             $connection_stmt = substr($line, $equPos);
           }else {
             $connection_stmt = trim(substr($line, strpos($line, '$')));
           }

           //start setting the new converted line with the new database connection statement
            $newLine =  strpos($str[0], '=')? $line: str_replace($connection_stmt, $stmt_variable .' = '. trim($connection_stmt), $line) ;

            //select sql statement starting by 'query' expression
            $sql_stmt =  $newStmt = substr($connection_stmt, strpos($connection_stmt, 'query('));

            // check if any varibles exist
           if (strpos($sql_stmt, '$')) {

             //count varibles that used in sql query statement
             $sql_variable_count =  substr_count($sql_stmt, '$');

             //analyze all used varibles
             for ($i=0; $i < $sql_variable_count; $i++) {

              // define  varible position
              $variablePos = strpos($newStmt, '$')-1;

              // define used quotation mark
              $quotMark = substr($newStmt,$variablePos, 1);
              //first quotation mark
              $quotMark1Pos = strpos($newStmt, $quotMark, $variablePos);
              //second quotation mark
              $quotMark2Pos = strpos($newStmt, $quotMark, $quotMark1Pos+1);
              //varible length
              $varLen = $quotMark2Pos - $quotMark1Pos-1;
              //define the varible
              $var = substr($newStmt, $quotMark1Pos+1, $varLen);
              //put all varibles in an array to use them later
              $variable[$i] = $var;

              //replace varible definer
               $newStmt = str_replace($var, str_replace('$', ':', $var), $newStmt);
              }
              //set the new prepared statement
            $newStmt = str_replace('query', 'prepare', $newStmt);
            $newStmt = str_replace($quotMark, '', $newStmt) . ';';

            //start build the execute statement and set the variables in it
            $execute_stmt = $nl.$stmt_variable . '->execute(array( ';
            foreach ($variable as $var) {
              $execute_stmt = $execute_stmt . "'" . str_replace('$', ':', $var) . "'" . '=>' . $var;
              if(array_search($var, $variable)+1 < count($variable) ){
                  $execute_stmt = $execute_stmt .', ';
                }
            }
            //end of execute statement
            $execute_stmt = $execute_stmt . '))';

            //combine converted statement with execute statement
            $newStmt = $newStmt . $execute_stmt;
            // replace orginal sql statement with the new statement
            $newLine =  str_replace($sql_stmt, $newStmt, $newLine);
            //finish converted code
            $converted_code = str_replace($line, $newLine, $converted_code);
            $stmt_counter++;
            }
       }

       //second algorithm (queries uses prepared statements unsecurly)
       else if (strpos($line, 'prepare(')) {

         //define line position
         $linePos = array_search($line, $lines);
         // spilt the line by 'prepare' expression
         $str = explode('prepare(', $line);

         //define the statement variable if exists
         $exists_stmt_variable = explode('=', $line)[0];
         //set the new statement variable
         //if the statement already has a variable use it, if not set a new varible
         // (substr) used to avoid comment lines by starting from varible position
         $stmt_variable =  strpos($str[0], '=')? substr($exists_stmt_variable, strpos($exists_stmt_variable,'$')): '$stmt'.$stmt_counter;

           if (strpos($str[0], '=')) {
             $equPos = strpos($str[0], '=');
             $connection_stmt = substr($line, $equPos);
           }else {
             $connection_stmt = trim($line);
           }

           //start setting the new converted line with the new database connection statement
            $newLine =  strpos($str[0], '=')? $line: str_replace($connection_stmt, $stmt_variable .' = '. trim($connection_stmt), $line) ;

            //select sql statement starting by 'query' expression
            $sql_stmt = $newStmt = substr($connection_stmt, strpos($connection_stmt, 'prepare('));

            // check if any varibles exist
            if (strpos($sql_stmt, '$')) {

              //count varibles that used in sql query statement
              $sql_variable_count =  substr_count($sql_stmt, '$');
              //analyze all used varibles
              for ($i=0; $i <$sql_variable_count ; $i++) {

                // define  varible position
                $variablePos = strpos($newStmt, '$')-1;

                // define used quotation mark
                $quotMark = substr($newStmt,$variablePos, 1);
                //first quotation mark
                $quotMark1Pos = strpos($newStmt, $quotMark, $variablePos);
                //second quotation mark
                $quotMark2Pos = strpos($newStmt, $quotMark, $quotMark1Pos+1);
                //varible length
                $varLen = $quotMark2Pos - $quotMark1Pos-1;
                //define the varible
                $var = substr($newStmt, $quotMark1Pos+1, $varLen);

                //put all varibles in an array to use them later
                $variable[$i] = $var;

                //replace varible definer
                $newStmt = str_replace($var, str_replace('$', ':', $var), $newStmt);
              }

              //delete varibles quotation marks
              $newStmt = str_replace($quotMark, '', $newStmt);

              //start build the new execute statement and set the variables in it
              $execute_stmt = trim($stmt_variable).'->'.'execute(array( ';
            foreach ($variable as $var) {
              $execute_stmt = $execute_stmt . "'" . str_replace('$', ':', $var) . "'" . '=>' . $var;
              if(array_search($var, $variable)+1 < count($variable) ){
                  $execute_stmt = $execute_stmt .', ';
                }
            }
            //end of execute statement
            $execute_stmt = $execute_stmt . '));';

            // replace orginal sql statement with the new statement
            $newLine =  str_replace($sql_stmt, $newStmt, $newLine);

            $Lines = explode(';', $converted_code);
            for ($i=0; $i < count($Lines); $i++) {

              //check execute statements
              if (strpos($Lines[$i], trim($stmt_variable)) && strpos($Lines[$i], '->') && strpos($Lines[$i], 'execute') ) {

                //if the execute statement is blank replaceit with the new execute statement
                $executeParams = trim(substr($Lines[$i], strpos($Lines[$i], '(')+1));
                if($executeParams == ')'){
                $varPos = strpos($converted_code, trim($stmt_variable), strpos($converted_code, $Lines[$i]) );
                $converted_code = substr_replace ($converted_code, $execute_stmt, $varPos, strlen(trim($Lines[$i]))+1);
                $i = count($Lines);
              }
              }
            }

            // replace orginal sql connection lines with the converted line
            $converted_code = str_replace($line, $newLine, $converted_code);
            $stmt_counter++;

            }
       }
     }
     $converted_code = $startNote . $converted_code;
  }

  function alert($msg){
    echo '<script type="text/javascript"> alert("'.$msg.'"); </script>';
  }
 ?>

 <html lang="en" dir="ltr">
   <head>
     <meta charset="utf-8">
     <title>Prepared Statements Automator</title>
     <link rel="stylesheet" href="css/bootstrap/bootstrap.min.css">
     <link rel="stylesheet" href="css/style.css?key=<?=time()?>">
   </head>
   <body>
     <header>
       <h1>Prepared Statements Automator</h1>
     </header>
    <div class="">
   <form method="post">
     <textarea class="col-md-6 inputCode" name="phpCode" placeholder="Enter your PHP code here: "><?=$input_code?></textarea>
     <div class="col-md-6 convertedCode"><p><?=substr(highlight_string($converted_code),1)?></p></div>
     <p class="submitButtonP"><input class="col-md-12 submitButton" type="submit" value="Convert Code"></p>
   </form>
  </div>
   </body>
 </html>


 <?php

 // test all sql pdo statement scenarios...
// // "Insert statement" without a connection variable
 // $pdo->query("INSERT INTO email_activation (email, recover_key) VALUES ('$email', '$key')");
 // $stmtTest1 = $pdo->query("INSERT INTO email_activation (email, recover_key) VALUES ('$email', '$key')");
 //
 // $stmtTest1 = $pdo->prepare("INSERT INTO email_activation (email, recover_key) VALUES ('$email', '$key')");
 // $stmtTest1->execute();
 //
 // $pdo->query("SELECT recover_key from email_activation where email = '$email'");

 // // "Select statement" with a connection variable
 // $stmtTest2 = $pdo->query("SELECT recover_key from email_activation where email = '$email'");
 //
 // $stmtTest2 = $pdo->prepare("SELECT recover_key from email_activation where email = '$email'");
 // $stmtTest2->execute();
 //
 // $pdo->query("UPDATE users SET password = '$hashed_pass' WHERE email = '$email'");
 // $stmtTest3 = $pdo->query("UPDATE users SET password = '$hashed_pass' WHERE email = '$email'");
 //
 // // "Update statement" using an unsecure prepared statement
 // $stmtTest3 = $pdo->prepare("UPDATE users SET password = '$hashed_pass' WHERE email = '$email'");
 // $stmtTest3->execute();
 //
 //
 // $pdo->query("DELETE FROM email_activation WHERE email = '$email'");
 // $stmtTest4 = $pdo->query("DELETE FROM email_activation WHERE email = '$email'");
 //
 // $stmtTest4 = $pdo->prepare("DELETE FROM email_activation WHERE email = '$email'");
 // $stmtTest4->execute();




//  $pdo->query("INSERT INTO email_activation (email, recover_key) VALUES ('$email', '$key')");

//  $stmtTest2 = $pdo->query("SELECT recover_key from email_activation where email = '$email'");

//  $stmtTest3 = $pdo->prepare("UPDATE users SET password = '$hashed_pass' WHERE email = '$email'");
//  $stmtTest3->execute();
  ?>
