<?php
/*
checkTerm = checks the search term and trims the term.
searchCategory – This function is for future development and is used to catagorize the solutions based on the users input. For now it is set to all solutions with not filtering.
getContent – Call the content from the database for a solutionID
findTopSolution – Once RankScore is completed, The app has an array of unsorted scores. This function sorts them and finds the top candidate solution.
rankScore – Get the score for both content and keywords
searchKeywords – checks for both combination and joint keywords and gives a score of 300.
searchContent – Checks content for search term match per word, two words and three word combination.
saveToHistory – Saves Post Data from iOS to History
showHistory – Calls history Items
checkCommon – Checks searchterm for common words
questionAnswers – ask a question between search terms and respond with a question to decide which article is best suited.
getObject – get the Operating System from the search Term
getSolTitle – gets the title from MySQL
saveToFeedback – Saves user feedback about history items.
addDevice – Adds a device for the user
getHistoryID – retrieves history ID
GetDeviceID – retrives devices ID
*/

//checkTerm = checks the search term and trims the term.
function checkTerm($term){
    $trimTerm = trim($term);
    return $trimTerm;
  }

//SEARCH DATABASE WITH CONTENT
//searchCategory – This function is for future development and is used to catagorize the solutions based on the users input. For now it is set to all solutions with not filtering.
  function searchCategory($OS) {
    $OS;
    global $conn;
   // $sql = "SELECT solID FROM category WHERE catagories='$OS'";
    $sql = "SELECT idsolutions FROM solutions";
// SEARCH CATEGORY IN DATABASE
    $result = $conn->query($sql);
// Empty Array to hold the list of solution IDs
    $IDList = array();
    if ($result->num_rows > 0) {
      $x = 0;
    // output data of each row
      while($row = $result->fetch_assoc()) {
        //if this is the first time, create array, otherwise.
        $IDList[] = $row["idsolutions"];
      }
    } else {
    //Exception 
      echo "0 results - SearchCategory";
    }
    return $IDList;
  }


//getContent – Call the content from the database for a solutionID
  function getContent($s){
    global $conn;
    $sql = "SELECT * FROM solutions WHERE idsolutions='$s'";
// SEARCH CATEGORY IN DATABASE
    $result = $conn->query($sql);
// Empty Array to hold the list of solution IDs
    $IDList = array();
    if ($result->num_rows > 0) {
      $x = 0;
    // output data of each row
      while($row = $result->fetch_assoc()) {
        $solution[] = array($row["title"], $row["solutions"], $row["keywords"]);
      }
    } else {
    //Exception 
      echo "0 results GetContent";
    }   
    return $solution;
  }
  

 //findTopSolution – Once RankScore is completed, The app has an array of unsorted scores. This function sorts them and finds the top candidate solution.
  function findTopSolution($OS, $searchTerm){
    $allSol = searchCategory($OS);
    $solID = array_unique($allSol);
    echo 'Your Search Term: "' . $searchTerm . '". Articles Searched: ' . count($solID);
    for($f=0;$f < count($solID);$f++){
       $s = $solID[$f];
       $score = 0;
       $score = rankScore($searchTerm, $OS, $s);
       $scores[] = array($s, $score);
       $sorter = function($leftArray, $rightArray) {
       if ($leftArray[1] == $rightArray[1]) {
         return 0;
       }
       if ($leftArray[1] < $rightArray[1]) {
         return 1;
       }
       return -1;
       };
    }
    usort($scores, $sorter);
    return $scores;
  }


//rankScore – Get the score for both content and keywords
  function rankScore($searchTerm, $OS, $id){
    $score = 0;
    $score = $score + searchKeywords($searchTerm, $OS, $id);
    
    $score = $score + searchContent($searchTerm, $OS, $id);
 
    return $score;
  }

  function mysort($leftArray, $rightArray) {
    if ($leftArray[1] == $rightArray[1]) {
      return 0;
    }
    if ($leftArray[1] > $rightArray[1]) {
        return 1;
    }
    return -1;
  }


 //COMPARE USER INPUT WITH KEYWORDS IN DATABASE
//searchKeywords – checks for both combination and joint keywords and gives a score of 300.
 function searchKeywords($searchTerm, $OS, $s) {
   $idScore = array();
  $score = 0;
   //Give access to global variable.
   global $conn;
   //Find the category for this searchTerm
   $ID = $s;
   //eaks searchTerm into an Array for each element, seperating at the spaces
   $st = explode(' ', $searchTerm);
   // Gets all keywords from database in the category above.
   $sql = "SELECT keywords FROM solutions WHERE idsolutions='$ID'";
   //Query's the database
   $result = $conn->query($sql);
   //Creates Array to hold keywords.
   $Keywords = array();
   //Checks if Array returns 0 results
   if ($result->num_rows > 0) {
     //While Loop counter.
     $x = 0;
     // output data of each row
     while($row = $result->fetch_assoc()) {
       //add keyword row of data to array keywords
       $Keywords[] = $row["keywords"];
     }
   } else {
     //Exception Handling
     $score = 0;
   }
   //Loop for each keywords row
   
   for($i = 0;$i < count($Keywords);$i++){
     
     //For each row of keywords create an array of keyword elements, seperated by comma's
      $keyword = explode(',', $Keywords[$i]);
      //Array to hold the scores
      $scores = array();
      //Local Counter
      //Loops each searchTerm elements
      for($b = 0;$b < count($st);$b++){
        //for each element of search term.
        $searchElement = $st[$b];
        //Compare to all the keywords.
        if(count($keyword) >= 2){
          for($k = 0; $k < count($keyword); $k++){
           if(count($keyword) >= 2){
            $key = explode(' ', $keyword[$k]);
             
            for($a=1;$a < count($key);$a++){
             
               $keywordFiltered = $key[$a];
               if ($searchElement != null && $keywordFiltered != null){
                 
            //Use PHP5 library strtolower to convert the string to all lower cases and check if they are the same.
              if(strtolower($searchElement) == strtolower($keywordFiltered)) {
                //If both objects are the same add a score. 
                $score = $score + 300;  
                
              }        
             }
            }
           }
          
            //incase of windows7 without the spacing
            //nth keyword convert to String
            $str = $keyword[$k];
            //Regular Expression to remove all spacing
            $keywordFiltered = preg_replace('/\s+/', '', $str);
            //Check that nither the searchterm or keyword is empty
            if ($searchElement != null && $keywordFiltered != null){
            //Use PHP5 library strtolower to convert the string to all lower cases and check if they are the same.
              if(strtolower($searchElement) == strtolower($keywordFiltered)) {
                //If both objects are the same add a score. 
                $score = $score + 300;         
              }        
             }else{
              
            }
          }
        }
        }
      }
   //creates an array that holds both the Solution ID and the score in one array.
   $score = 0;
     return $score;
  }

//CAN A SOLUTION BE FOUND
//searchContent – Checks content for search term match per word, two words and three word combination.
  function searchContent($searchTerm, $OS, $s) {
    global $conn;
    $sql = "SELECT solutions FROM solutions WHERE idsolutions IN ($s)";
    // SEARCH SOLUTIONS IN DATABASE
    $result = $conn->query($sql);
   //Checks if Array returns 0 results
   if ($result->num_rows > 0) {
     $x = 0;
     // if ($result->num_rows > 0) {
     // output data of each row
     while($row = $result->fetch_assoc()) {
       //if this is the first time, create array, otherwise.
       $content = $row["solutions"];
       // split up the string into an array
       $SearchWord = preg_split("/[ ,.]/", $searchTerm);
       $score = 0;
       $matches = preg_split("/[ ,.]/", $content);
       
       for($i=0;$i < count($SearchWord);$i++){
         $j = $i + 1;
         $k = $j + 1;
         $word = $SearchWord[$i];  
//String that holds two words
         if ($j < count($SearchWord)){
           $word2 = $SearchWord[$i] . ' ' . $SearchWord[$j];
         }
         //String that holds three words
         if ($k < count($SearchWord)){
           $word3 = $SearchWord[$i] . ' ' . $SearchWord[$j] . ' ' . $SearchWord[$k];
         } 
         //search all of content for each word in the search term, All information is relevent.
         //loop each match
        for($a = 0;$a < count($matches);$a++){
          $b = $a + 1;
          $c = $b + 1;
          $match = $matches[$a];
          
          if ($b < count($matches)){
           $match2 = $matches[$a] . ' ' . $matches[$b];
         }
         if ($c < count($matches)){
           $match3 = $matches[$a] . ' ' . $matches[$b] . ' ' . $matches[$c];
         } 
          
          //if the word appears, score.
          if($match != null && $word != null){
            if(strtolower($word) == strtolower($match)){
              $score = $score + 10; 
             
           } 
         }  
          //if two words appear, score
         if(count($SearchWord) >= 2){
         
           if($match2 != null && $word2 != null){
             if(strtolower($word2) == strtolower($match2)){
                $score = $score + 50;
               
             } 
           } 
         }
          //if three words appear, score.
         if(count($SearchWord) >= 3){
           if($match3 != null && $word3 != null){
             if(strtolower($word3) == strtolower($match3)){
                $score = $score + 200;
              } 
            } 
          }
        } 
      }
    }
  } else {
    $score = 0;
    return $score;
  }

    return $score;
  
  }


//SAVE DATA TO HISTORY
//saveToHistory – Saves Post Data from iOS to History
  function saveToHistory($searchTerm, $solutionID, $userID, $deviceID) {
    global $conn;
    $result = $searchTerm;
    $sql = "INSERT INTO history (searchTerm, solutionID, userID, date) VALUES ('$searchTerm', '$solutionID', '$userID', now())";
    if ($conn->query($sql) === TRUE) {
      $historyID = $conn->insert_id;
  
    } else {
      $arr = array('success' =>  0); 
       echo json_encode($arr);
    }
    
  
$result = $searchTerm;
    $sql = "INSERT INTO DeviceHistoryUser (historyID, userID, DeviceID) VALUES ('$historyID', '$userID', '$deviceID')";
    if ($conn->query($sql) === TRUE) {
     $arr = array('success' =>  1); 
       echo json_encode($arr);
    } else {
      $arr = array('success' =>  0); 
       echo json_encode($arr);
    }
    $conn->close();
  }

//SHOW HISTORY
//showHistory – Calls history Items
  function showHistory() {
   //TODO
    global $user;
    global $conn;
      $sql = "SELECT searchTerm, userFeedback FROM history WHERE userID ='$user'";
      $result = $conn->query($sql);
      if ($result->num_rows > 0) {
        // output data of each row
        $i = 0;
        while($row = $result->fetch_assoc()) {
         $i = $i+1;
        return "<br>". $i .". " . $row["searchTerm"]. "Used Solution " . $row["userFeedback"]. "<br>";
        }
      } else {
        echo "No History";
      }
      $conn->close();
   }

//QUSTION & ANSWERS
//checkCommon – Checks searchterm for common words
  function checkCommon($common){
    global  $conn;
    $sql = "SELECT qID from commonWords where commonWords = '$common'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
     //output data of each row
      if($row = $result->fetch_assoc()) {
        $questionID = $row['qID'];
      }
    } else {
      //Exception Handling
      echo "0 results";
    }
    return questionID;
  }

//questionAnswers – ask a question between search terms and respond with a question to decide which article is best suited.
  function questionAnswers($answer){
  //if answer yes produce related SolutionID's to that question.
    global  $conn;
    $sql = "SELECT solID from relatedAnswer where questionID = '$answerID'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
     //output data of each row
     while($row = $result->fetch_assoc()) {
       $sid = $sid . "," . $row['solID'];
     }
    } else {
     //Exception Handling
      echo "0 results";
    }
    return solutionID;
  }

//getObject – get the Operating System from the search Term
  function getObject($searchTerm){
    global  $conn;
    //get relevent information
    $SearchWord = preg_split("/[ ,.]/", $searchTerm);
    $sql = "SELECT accurateObjects from accurateObject";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
     //output data of each row
     while($row = $result->fetch_assoc()) {
       $accObject[] = $row['accurateObjects'];
       } 
     } else {
     //Exception Handling
      echo "getObject SQL Error.";
    }
    $found = false;
    //Action
    for($h=0;$h < count($SearchWord);$h++){
      $word = $SearchWord[$h];
      for($i=0;$i < count($accObject);$i++){
        $selfObject = $accObject[$i];
        //if object has a version or two words. for example Windows 8 is array(windows, 8).
        //It need to get both the first and second part of the array to compare to the first
        //and second part of the search term. 
        
        //Count the Characters in the object
        $wordSelfCount = strlen($selfObject);
        //Count the characters in the search Word
        $wordCount = strlen($word);
        //if the character could in the searchTerm is less then the objects character 
        //count then we get the next array item and make the searchTerm word bigger then the object.
        if($wordCount < $wordSelfCount){
          $hh = $h + 1;

          if($hh < count($SearchWord)){
            $word2 = $SearchWord[$hh];
            $totalWord = $word . " " . $word2;
          }else{
            $totalWord = "nil";
          }
         
          if(strtolower($totalWord) == strtolower($selfObject)){
            $found[] = $totalWord;
          }
        }
        //Compares each word in the search term with each object
        if(strtolower($word) == strtolower($selfObject)){
          $foundp[] = $totalWord;
        }
      }
    }    
    if (count($found) > 0){
     return $found;
    }else{
      return "noOSFound";
    }
  }

  
//getSolTitle – gets the title from MySQL
function getSolTitle($solID){
   global  $conn;
    $sql = "SELECT title from solutions where idsolutions = '$solID'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
     //output data of each row
     while($row = $result->fetch_assoc()) {
       $sid = $row['title'];
        return $sid;
     }
    } else {
     //Exception Handling
      echo "0 results";
    }
 
}

//saveToFeedback – Saves user feedback about history items.
function saveToFeedback($feedback, $solID, $userID) {
    global $conn;
    
    $sql = "INSERT INTO feedback (feedback, userID, solutionID, date) VALUES ('$feedback', '$userID', '$solID', now())";
    if ($conn->query($sql) === TRUE) {
       $arr = array('success' =>  1);    
       echo json_encode($arr);
    } else {
    //Exception 
      $arr = array('success' =>  0); 
       echo json_encode($arr);
    }
    $conn->close();
  }


//addDevice – Adds a device for the user
function addDevice($name, $os, $type, $model, $cpu, $gpu, $ram, $nCard, $userID){
  global $conn;
    
    $sql = "INSERT INTO devices (name, os, type, model, cpu, gpu, ram, ncard, userID) VALUES ('$name', '$os', '$type', '$model', '$cpu','$gpu','$ram','$nCard', '$userID')";
    if ($conn->query($sql) === TRUE) {
       $arr = array('success' =>  1);    
       echo json_encode($arr);
    } else {
    //Exception 
      $arr = array('success' =>  0); 
       echo json_encode($arr);
    }
    $conn->close();
}


//getHistoryID – retrieves history ID
function getHistoryID($deviceID){
   global  $conn;
    $sql = "SELECT historyID from DeviceHistoryUser where deviceID = '$deviceID'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
     //output data of each row
     while($row = $result->fetch_assoc()) {
       $sid[] = $row['historyID'];
     }
      if(count($sid) > 1){
      $return = implode(", ", $sid);
      }else{
        $return = $sid[0];
      }
      
      return $return;
    } else {
     //Exception Handling
      $arr = array('success' =>  0); 
      echo json_encode($arr);
    }
 
}

//GetDeviceID – retrives devices ID
function GetDeviceID($userID){
  global  $conn;
    $sql = "SELECT iddevices from devices where userID = '$userID'";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
     //output data of each row
     while($row = $result->fetch_assoc()) {
       $sid[] = $row['iddevices'];  
     }
      $result = array_unique($sid);
      return $result;
    } else {
     //Exception Handling
     $arr = array('success' =>  0); 
      echo json_encode($arr);
    }
}
?>