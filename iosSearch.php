<?php
  include("include/conn.php");
  include("include/search/function.php");
  //TEST DATA
  $user = '1014';
  $OS = 'Windows 8.1';
  $scores[0][1] = 0;

  if(isset($_POST["searchTerm"])){
    $searchTerm = checkTerm($_GET["searchTerm"]);
  }else{
    $searchTerm = "";
  }

  $answer = "Yes";
  $allSol = searchCategory($OS);
  $solID = array_unique($allSol);

if($searchTerm != ""){
  for($f=0;$f < count($solID);$f++){
    $s = $solID[$f];
    $score = 0;
    $score = rankScore($searchTerm, $OS, $s);
    $scores[] = array($s,$score);
  }
  
//Source: http://stackoverflow.com/questions/28346767/sorting-a-multidecimal-array-in-php/28346954#28346954

  $sorter = function($leftArray, $rightArray) {
    if ($leftArray[1] == $rightArray[1]) {
        return 0;
    }
    if ($leftArray[1] < $rightArray[1]) {
        return 1;
    }
    return -1;
  };

  usort($scores, $sorter);
}
  if (isset($_POST["solID"])){
      $id = $_POST["solID"];
      $solution = getContent($id);
      date_default_timezone_set("Europe/Dublin");
      $str = utf8_encode($solution[0][1]);
      $arr = array('success' => 1, 'title' =>  $solution[0][0], 'content' => $str, 'date' => date("Y-m-d H:i:s"));
      echo json_encode($arr);
    
  }else{

  if($scores[0][1] != 0){
    $solution = getContent($scores[0][0]);
    date_default_timezone_set("Europe/Dublin");
    $str = utf8_encode($solution[0][1]);
    $arr = array('success' => 1, 'title' =>  $solution[0][0], 'content' => $str, 'date' => date("Y-m-d H:i:s"), 'solID' => $scores[0][0]);
    echo json_encode($arr);
  }else{
    $arr = array('success' =>  0); 
      echo json_encode($arr);
  }
    
  }

?>
