<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset( $_SESSION['user'] ) ) {
  header("location: /index.php");
}

$photo_base_name = "foto";
include 'database_info.php';

//connect to database
$mysqli=mysqli_connect($GLOBALS['dbhost'],$GLOBALS['dbuser'],$GLOBALS['dbpass'],$GLOBALS['dbname']);
// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if($_SERVER["REQUEST_METHOD"] == "POST") {
        try {
            post($mysqli);
            header("location: /inserimento.php?inserita");
        } catch (Exception $e) {
          header("location: /inserimento.php?errore=". $e->getMessage());
        }
}

//get the number of original photos in the database
function get_current_photo_id($mysqli)
{
    //photo alterations have foto.INGREDIENTI set to empty string
    $sql = "SELECT COUNT(*) FROM foto WHERE foto.INGREDIENTI != ''";
    $result = mysqli_query($mysqli, $sql);
    $rows = mysqli_fetch_row($result);
    echo $rows[0];
}

//given an array of tag names, return an array containing the tags ID
function get_tags_id($mysqli, $attr_array) {
  $tags_id_array = array();
  $sql = "SELECT * FROM tag";
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

  return $tags_id_array;
}

//get a valid ID for a new photo
function get_valid_photo_id($mysqli) {
  $current_photo_id = 0;
  $sql = "SELECT MAX(ID) FROM foto";
  $result = mysqli_query($mysqli, $sql);
  if($result != NULL) {
    $row = $result->fetch_assoc();
    $current_photo_id =  ((int)$row["MAX(ID)"] + 1);
  }
  return $current_photo_id;
}
function is_image($photo_extension) {
  $expensions= array("jpeg","jpg","png");
  if(!in_array($photo_extension,$expensions)){
      return false;
  }
  return true;
}

//save data to database, insert image and description to folder /foto/
function post($mysqli){

    global $photo_base_name;
    $inclinazione = $_POST['inclinazione'];
    $angolazione = $_POST['angolazione'];
    $testoPresente = $_POST['testo'];
    $luce = $_POST['luce'];
    $etichettaPiana = $_POST['etichetta'];
    $caratteriDanneggiati = $_POST['caratteri'];
    $immagineNitida = $_POST['immagine'];
    $mossa = $_POST['mossa'];
    $risoluzione= $_POST['risoluzione'];
    $note = $_POST['note'];


    if("" == trim($_POST['ingredienti'])) {
      throw new Exception("lista ingredienti vuota");

    } else {
      $ingredienti = $_POST['ingredienti'];
    }

    $attr_array = array($inclinazione, $angolazione, $testoPresente, $luce, $etichettaPiana, $caratteriDanneggiati, $immagineNitida, $mossa, $risoluzione);

    //get tags id - creo un array di id (interi) che mi permetteranno di associare la foto ai tag
    $tags_id_array = get_tags_id($mysqli, $attr_array);


    //get last id inserted - ottendo l'ultimo id usato per identificare le foto, in modo da costruire poi il nome della foto
    //che verrà salvata in una cartella, il nome sarà del tipo photo + {ID}
    $current_photo_id = get_valid_photo_id($mysqli);
    $photo_base_name .= $current_photo_id;

    //prendo foto e la salvo in foto/
    $photo_extension = "";
    $original_photo_name = "";

    if(isset($_FILES['immagine'])) {
      $original_photo_name = $_FILES['immagine']['name'];
      $photo_extension =  pathinfo($_FILES['immagine']['name'], PATHINFO_EXTENSION);

      if(!is_image($photo_extension)) {
        throw new Exception("Formato immagine non valido");
      }
      if($_FILES['immagine']['size'] > 5242880){
          throw new Exception("Dimensione massima: 5 MB.");
      }

      if(!move_uploaded_file($_FILES['immagine']['tmp_name'], "foto/".$photo_base_name.".".$photo_extension)) {
        throw new Exception("Errore nel caricamento del file.");
      }
    } else {
      throw new Exception("File mancante.");
    }

    //aggiunta filte .txt descrittivo
    //setto testo descrizione in formato JSON
    /*
    {
      "ingredients": ["ing1,", "ing2"],
      "tags": ["tag1", "tag2"],
      "notes": "...",
      "original_name": "..."
    }
    */
    $ingredient_array = explode(",", $ingredienti);
    $description_json = json_encode(array("ingredients" => $ingredienti, "tags" => $attr_array, "notes" => $note, "original_name" => $original_photo_name));
    //create and write file with json data
    $description_path = "foto/" . $photo_base_name . ".txt";
    $description_file = fopen($description_path, "w");
    fwrite($description_file, $description_json);
    fclose($description_file);

    //insert photo attributes - inserimento nel db degli attributi necessari per reperire la foto
    //$current_photo_id is used as primary key
    $photo_name =$photo_base_name . '.' . $photo_extension;
    $stmt = $mysqli -> prepare("INSERT INTO foto (ID, NOME, INGREDIENTI, NOTE) VALUES(?, ?, ?, ?)");
    $stmt->bind_param("isss", $current_photo_id, $photo_name, $ingredienti, $note);
    $stmt -> execute();


    //inserimento nella tabella associativa molti a molti delle chiavi esterne (photo_id e i vari tag_id)
    foreach($tags_id_array as &$tag_id) {

      //senza chiavi esterne è necessario controllare non vi siano righe uguali -> controllo non vi siano duplicati
      $stmt = $mysqli -> prepare("SELECT * FROM fototag WHERE IDFOTO = ? AND IDTAG = ?");
      $stmt -> bind_param("ii", $current_photo_id, $tag_id);
      $stmt -> execute();
      $stmt->bind_result($id, $fotoid, $tagid);
      $stmt->fetch();
      $stmt->close();

      //se non vi sono duplicati associo foto al tag
      if($id == NULL) {
        $stmt = $mysqli -> prepare("INSERT INTO fototag (ID, IDFOTO, IDTAG) VALUES(NULL, ?, ?)");
        $stmt -> bind_param("ii", $current_photo_id, $tag_id);
        $stmt -> execute();
      }


    }

    //close connection
    mysqli_close($mysqli);

}

//given a type (originale or modifica) return an array of groups (inclinazione, angolazione, luce...) that belong to that type
function getTagGroups($mysqli, $tag_type) {
  $tags_group_array = array();

  $sql = "SELECT GRUPPO from tag WHERE tag.TIPO = '$tag_type'";
  $result = mysqli_query($mysqli, $sql);
  if(mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
      if(!in_array($row["GRUPPO"],$tags_group_array)) {
        array_push($tags_group_array, $row["GRUPPO"]);
      }
    }
  }
  return $tags_group_array;
}

//given a group (angolazione..) return an array of tag names that belong to that group (angolata, non_angolata..)
function getGroupNames($mysqli, $group) {
  $tag_group_names = array();

  $sql = "SELECT NOME from tag where GRUPPO = '$group'";
  $result = mysqli_query($mysqli, $sql);
  if(mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
      array_push($tag_group_names, $row["NOME"]);
    }
  }

  return $tag_group_names;
}


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
                <a class="navbar-brand" href="inserimento.php">Gestione foto - Elementi di Ingegneria</a>
            </div>
            <!-- /.navbar-header -->

            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        <li>
                            <a href="inserimento.php"><i class="fa fa-pencil fa-fw"></i> Inserimento dati</a>
                        </li>
                        <li>
                            <a href="visualizza.php?pag=0"><i class="fa fa-table fa-fw"></i> Visualizza dati</a>
                        </li>
                       <!-- <li>
                            <a href="download.php"><i class="fa fa-download fa-fw"></i> Download dati</a>
                        </li>-->
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
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Inserimento dati</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-photo fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge"><?php echo get_current_photo_id($mysqli); ?></div>
                                    <div>Foto inserite</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->
            <div class="row">
            <?php

                //check if any error
                if(isset($_GET["inserita"]))
                {
                    echo '<div class="alert alert-success alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            Foto caricata.
                        </div>';
                }
                if(isset($_GET["errore"]))
                {
                    echo '<div class="alert alert-danger alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'
                            . 'Errore: ' . $_GET['errore']
                            .'</div>';
                }
            ?>
              <div class="panel panel-default">
                <div class="panel-heading">
                      Inserimento valori
                </div>
                <div class="panel-body">

                    <form action="#" method="POST" enctype="multipart/form-data">

                        <div class="form-group">
                            <label>Immagine: </label>
                            <input type="file" name="immagine" accept="image/gif, image/jpeg, image/png" onchange="readURL(this);"><br>

                        </div>
                        <script>
                            function readURL(input) {
                                if (input.files && input.files[0]) {
                                    var reader = new FileReader();
                                    reader.onload = function (e) {
                                        $('#imgPreview')
                                            .attr('src', e.target.result)
                                    };
                                    reader.readAsDataURL(input.files[0]);
                                }
                            }
                        </script>

                        <div class="row">
                            <div class="col-md-4">
                                <img id="imgPreview" class="img-responsive" src="defaultIMG.jpg" style="height:auto; max-width:100%" />
                            </div>


                            <div class="col-md-8">


                              <?php
                                //generate dynamically the input form

                                $html = "";

                                //get original photos tags group
                                $tag_groups = getTagGroups($mysqli, "originale");

                                //var used to keep track of how many groups have been printed, every 3 groups write a new row
                                $current_column = 0;

                                foreach($tag_groups as $group) {

                                  //open a row
                                  if($current_column == 0) {
                                    $html .= "<div class='row'>";
                                  }

                                  $current_column += 1;

                                  $group = ucfirst($group);

                                  //open md-4 and form group
                                  $html .= "<div class='col-md-4'><div class='form-group'>";
                                  $html .= "<label>$group</label>";



                                  //get tag group names
                                  $group_tag_names = getGroupNames($mysqli, strtolower($group));
                                  $is_checked = false;

                                  foreach ($group_tag_names as $name) {
                                    $group = strtolower($group);
                                    //create radio button for each tag
                                    $html .= "<div class='radio'><label>";
                                    //<input type="radio" name="inclinazione" value="inclinata" >Inclinata
                                    if(!$is_checked) {
                                      $html .= "<input type='radio' name='$group' value='$name' checked=''>";
                                      $is_checked = true;
                                    } else {
                                      $html .= "<input type='radio' name='$group' value='$name'>";
                                    }

                                    //make 1st character uppercase
                                    $name = ucfirst($name);

                                    //replace '_' separator with space
                                    $tag_name = str_replace("_", " ", $name);
                                    $html .= $tag_name;

                                    //close radio button
                                    $html .= "</label></div>";

                                  }

                                  //close class col-md-4 and class form-group
                                  $html .= "</div></div>";
                                  //close row if 3° column
                                  if($current_column == 3) {
                                    $html .= "</div>";
                                    $current_column = 0;
                                  }
                                }
                                //if a row isnt closed then close it
                                if($current_column > 0) {
                                  $html .= "</div>";
                                }
                                
                                echo $html;

                              ?>



                                <div class="form-group">
                                        <label>Ingredienti</label>
                                        <textarea class="form-control" name="ingredienti" rows="3" placeholder="Per unificare gli stili dividere gli ingredienti con una virgola"></textarea>
                                </div>
                                <div class="form-group">
                                        <label>Note</label>
                                        <textarea class="form-control" name="note" rows="3" placeholder="Inserire qui eventuali note"></textarea>
                                </div>

                            </div>

                        </div>

                        <br>






                            <button type="dd" class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#myModal" onclick='upload_image();'>Invia</button>
                            <!-- Button trigger modal -->


                            <!-- Modal -->
                            <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                            <h4 class="modal-title" id="myModalLabel">CARICAMENTO</h4>
                                        </div>
                                        <div class="modal-body text-center">
                                            <img src="caricamento.gif">
                                        </div>
                                    </div>
                                    <!-- /.modal-content -->
                                </div>
                                <!-- /.modal-dialog -->
                            </div>


                        </form>
                </div>

            </div>



            <!-- /.row -->
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
