<?php
include 'database_info.php';
//$link = mysqli_connect($dbhost, $dbuser, $dbpass) or die("Unable to Connect to '$dbhost'");
$mysqli=mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//echo "Connected successfully\r\n";
functon post($mysqli){
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
    $sql = "SELECT * FROM TAG";
    $result = mysqli_query($mysqli, $sql);
    if (mysqli_num_rows($result) > 0) {
        // output data of each row
        while($row = mysqli_fetch_assoc($result)) {
            foreach($attr_array as &$value) {
              if($value == $row["NOME"]) {
                  array_push($tags_id_array, (int)$row["ID"]);
              }
            }
        }
    }
    //get last id inserted - ottendo l'ultimo id usato per identificare le foto, in modo da costruire poi il nome della foto
    //che verrà salvata in una cartella, il nome sarà del tipo photo + {ID}
    $sql = "SELECT MAX(ID) FROM FOTO";
    $result = mysqli_query($mysqli, $sql);
    $row = $result->fetch_assoc();
    $current_photo_id =  $row["MAX(ID)"] + 1;


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
    $stmt = $mysqli -> prepare("INSERT INTO FOTO (ID, NOME, DESCRIZIONE, INGREDIENTI) VALUES(NULL, ?, ?, ?)");
    $stmt->bind_param("sss", $photo_desc, $photo_name, $photo_ingredients);
    $stmt -> execute();

    //inserimento nella tabella associativa molti a molti delle chiavi esterne (photo_id e i vari tag_id)
    foreach($tags_id_array as &$tag_id) {
    $stmt = $mysqli -> prepare("INSERT INTO FOTOTAG (ID, IDFOTO, IDTAG) VALUES(NULL, ?, ?)");
    $stmt -> bind_param("ii", $current_photo_id, $tag_id);
    $stmt -> execute();
    }
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

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Elementi di Ingegneria</title>

    <!-- Bootstrap Core CSS -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="vendor/metisMenu/metisMenu.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="dist/css/sb-admin-2.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <script>
        setTimeout("location.href = 'index.html';",3000);
    </script>
</head>

<body>

    <div id="wrapper">

        <!-- Navigation -->
        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="index.html">Gestione foto - Elementi di Ingegneria</a>
            </div>
            <!-- /.navbar-header -->

            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <li>
                            <a href="index.html"><i class="fa fa-pencil fa-fw"></i> Inserimento dati</a>
                        </li>
                        <li>
                            <a href="visualizza.html"><i class="fa fa-table fa-fw"></i> Visualizza dati</a>
                        </li>
                        <li>
                            <a href="logout.php"><i class="fa fa-key fa-fw"></i> Logout</a>
                        </li>
                    </ul>
                </div>
                <!-- /.sidebar-collapse -->
            </div>
            <!-- /.navbar-static-side -->
        </nav>

        <div id="page-wrapper">
            <?php
                //se metodo di richiesta è POST, vogliamo inserire i dati nel db
                if($_SERVER["REQUEST_METHOD"] == "POST") {
                    post($mysqli);
                }
            ?>
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

    <!-- jQuery -->
    <script src="vendor/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="vendor/metisMenu/metisMenu.min.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="dist/js/sb-admin-2.js"></script>

    <!-- Morris Charts JavaScript -->
    <script src="vendor/raphael/raphael.min.js"></script>
    <script src="vendor/morrisjs/morris.min.js"></script>
    <script src="data/morris-data.js"></script>


</body>

</html>
