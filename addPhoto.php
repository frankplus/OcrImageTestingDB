<?php
$dbname = 'eleme104_db';
$dbuser = 'eleme104';
$dbpass = 'Pluto420!';
$dbhost = 'localhost';
//$link = mysqli_connect($dbhost, $dbuser, $dbpass) or die("Unable to Connect to '$dbhost'");

$mysqli=mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);

if (!$mysqli)

die("Can't connect to MySQL: ".mysqli_connect_error());
//echo "Connected successfully\r\n";

//get data via post 
//i seguenti dati sono di test - in seguito verranno reperiti via POST
$photo_desc = "una gran bella foto";
$photo_name = "photo";
$photo_ingredients = "cacio, blallo, fiore";
$attr1 = "illuminated";
$attr2 = "tilt";
$attr_array = array($attr1, $attr2);



//get tags id - creo un array di id (interi) che mi permetteranno di associare la foto ai tag
$tags_id_array = array();

$sql = "SELECT * FROM tags";
$result = mysqli_query($mysqli, $sql);
if (mysqli_num_rows($result) > 0) {
    // output data of each row
    while($row = mysqli_fetch_assoc($result)) {
        foreach($attr_array as &$value) {
          if($value == $row["tag_name"]) {
            array_push($tags_id_array, (int)$row["tag_id"]);
          }
        }
    }
}



//get last id inserted - ottendo l'ultimo id usato per identificare le foto, in modo da costruire poi il nome della foto
//che verrà salvata in una cartella, il nome sarà del tipo photo + {ID}
$sql = "SELECT MAX(photo_id) FROM photos";
$result = mysqli_query($mysqli, $sql);
$row = $result->fetch_assoc();
$current_photo_id =  $row["MAX(photo_id)"] + 1;

//load photo - DA COMPLETARE!!

//TODO make the photo name like "photo"+current_photo_id
if(isset($_FILES['photo'])){
      $errors= array();
      $file_name = $_FILES['photo']['name'];
      $file_size =$_FILES['photo']['size'];
      $file_tmp =$_FILES['photo']['tmp_name'];
      $file_type=$_FILES['photo']['type'];
      $file_ext=strtolower(end(explode('.',$_FILES['photo']['name'])));

      $expensions= array("jpeg","jpg","png");

      if(in_array($file_ext,$expensions)=== false){
         $errors[]="extension not allowed, please choose a JPEG or PNG file.";
      }

      if($file_size > 5242880){
         $errors[]='File size must be excately 5 MB';
      }

      if(empty($errors)==true){
         move_uploaded_file($file_tmp,"photos/".$file_name);
         echo "Success";
      }else{
         print_r($errors);
      }
   }

//insert photo attributes - inserimento nel db degli attributi necessari per reperire la foto
$photo_name .= $current_photo_id;
$photo_desc .= $current_photo_id;

$stmt = $mysqli -> prepare("INSERT INTO photos (photo_id, photo_desc, photo_ingredients, photo_name) VALUES(NULL, ?, ?, ?)");
$stmt->bind_param("sss", $photo_desc, $photo_ingredients, $photo_name);
$stmt -> execute();


//inserimento nella tabella associativa molti a molti delle chiavi esterne (photo_id e i vari tag_id)
foreach($tags_id_array as &$tag_id) {
  $stmt = $mysqli -> prepare("INSERT INTO jnc_photos_tags (photo_id_fk, tag_id_fk) VALUES(?, ?)");
  $stmt -> bind_param("ii", $current_photo_id, $tag_id);
  $stmt -> execute();
}

//close connection
mysqli_close($mysqli);

//insert description and photo data with BLOB
//credits https://blogs.oracle.com/oswald/phps-mysqli-extension:-storing-and-retrieving-blobs
/*
$stmt = $mysqli->prepare("INSERT INTO photos (photo_id, photo_desc, photo_data) VALUES(NULL, ?, ?)");

$null = NULL;

$stmt->bind_param("sb", $photo_desc, $null);

//1 indicates which parameter to associate the data with
$stmt->send_long_data(1, file_get_contents("test_photo.jpg"));

$stmt->execute();
$stmt->close();
*/


?>
