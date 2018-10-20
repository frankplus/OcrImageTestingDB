<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset( $_SESSION['user'] ) ) {
  header("location: /index.php");
}


include 'database_info.php';
//$link = mysqli_connect($dbhost, $dbuser, $dbpass) or die("Unable to Connect to '$dbhost'");
$mysqli=mysqli_connect($dbhost,$dbuser,$dbpass,$dbname);
// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}



if($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        post($mysqli);
        header("location: /inserimento.php?inserita");
    } catch (Exception $e) {
        header("location: /inserimento.php?errore&tipo:$e->getMessage()");
    }

}

function printNumFoto($mysqli)
{
    $sql = "SELECT COUNT(*) FROM FOTO";
    $result = mysqli_query($mysqli, $sql);
    $rows = mysqli_fetch_row($result);
    echo $rows[0];
}

//echo "Connected successfully";
function post($mysqli){
    $inclinazione = $_POST['inclinazione'];
    $angolazione = $_POST['angolazione'];
    $testoPresente = $_POST['testoPresente'];
    $luce = $_POST['luce'];
    $etichettaPiana = $_POST['etichettaPiana'];
    $caratteriDanneggiati = $_POST['caratteriDanneggiati'];
    $immagineNitida = $_POST['immagineNitida'];
    $mossa = $_POST['mossa'];
    $risoluzione= $_POST['risoluzione'];

    $attr_array = array($inclinazione, $angolazione, $risoluzione, $testoPresente, $luce, $etichettaPiana, $caratteriDanneggiati, $immagineNitida, $mossa, $risoluzione);

    if("" == trim($_POST['ingredienti'])) {
      throw new Exception("lista ingredienti vuota");

    } else {
      $ingredienti = $_POST['ingredienti'];
    }


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

    $current_photo_id = 0;
    $sql = "SELECT MAX(ID) FROM FOTO";
    $result = mysqli_query($mysqli, $sql);
    if($result != NULL) {
      $row = $result->fetch_assoc();
      $current_photo_id =  ((int)$row["MAX(ID)"] + 1);
    }


    //prendo foto e la salvo in foto/
    $file_ext="";
    if(isset($_FILES['immagine'])){
        $file_name = "foto".$current_photo_id;
        $path = $_FILES['immagine']['name'];
        $file_tmp =$_FILES['immagine']['tmp_name'];
        $file_size = $_FILES['immagine']['size'];
        $file_type=$_FILES['immagine']['type'];

        //check extensione
        $file_ext = pathinfo($path, PATHINFO_EXTENSION);
        $expensions= array("jpeg","jpg","png");
        if(in_array($file_ext,$expensions)=== false){
            throw new Exception("Estensione file non supportata.");
        }

        if($file_size > 5242880){
            throw new Exception("Dimensione massima: 5 MB.");
        }
        if(!move_uploaded_file($_FILES['immagine']['tmp_name'], "foto/".$file_name.".".$file_ext)) {
          throw new Exception("Errore nel caricamento del file.");
        }
    } else {
      throw new Exception("File mancante.");
    }


    //insert photo attributes - inserimento nel db degli attributi necessari per reperire la foto
    $photo_name = "foto";
    $photo_name .= $current_photo_id;
    $photo_name .= ".".$file_ext;
    $stmt = $mysqli -> prepare("INSERT INTO FOTO (ID, NOME, INGREDIENTI) VALUES(?, ?, ?)");
    $stmt->bind_param("iss", $current_photo_id, $photo_name, $ingredienti);
    $stmt -> execute();

    //aggiunta filte .txt descrittivo
    //setto testo descrizione in formato JSON
    /*
    {
      "ingredients": ["ing1,", "ing2"],
      "tags": ["tag1", "tag2"]
    }
    */
    $ingredient_array = explode(",", $ingredienti);
    //create json desc
    $description_json = json_encode(array("ingredients" => $ingredienti, "tags" => $attr_array));
    //create and write file
    $description_path = "foto/foto" . $current_photo_id . ".txt";
    $description_file = fopen($description_path, "w");
    fwrite($description_file, $description_json);
    fclose($description_file);

    //inserimento nella tabella associativa molti a molti delle chiavi esterne (photo_id e i vari tag_id)
    foreach($tags_id_array as &$tag_id) {
      //senza chiavi esterne è necessario controllare non vi siano righe uguali
      $stmt = $mysqli -> prepare("SELECT * FROM FOTOTAG WHERE IDFOTO = ? AND IDTAG = ?");
      $stmt -> bind_param("ii", $current_photo_id, $tag_id);
      $stmt -> execute();
      $stmt->bind_result($id, $fotoid, $tagid);
      $stmt->fetch();
      $stmt->close();
      //se non vi sono duplicati associo foto al tag
      if($id == NULL) {
        $stmt = $mysqli -> prepare("INSERT INTO FOTOTAG (ID, IDFOTO, IDTAG) VALUES(NULL, ?, ?)");
        $stmt -> bind_param("ii", $current_photo_id, $tag_id);
        $stmt -> execute();
      }


    }

    //close connection
    mysqli_close($mysqli);

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
                            <a href="visualizza.php"><i class="fa fa-table fa-fw"></i> Visualizza dati</a>
                        </li>
                        <li>
                            <a href="download.php"><i class="fa fa-download fa-fw"></i> Download dati</a>
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
                                    <div class="huge"><?php echo printNumFoto($mysqli); ?></div>
                                    <div>Foto inserite</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--<div class="col-lg-3 col-md-6">
                    <div class="panel panel-green">
                        <div class="panel-heading">
                            <div class="row">
                                <div class="col-xs-3">
                                    <i class="fa fa-tasks fa-5x"></i>
                                </div>
                                <div class="col-xs-9 text-right">
                                    <div class="huge">12</div>
                                    <div>New</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>-->

            </div>
            <!-- /.row -->
            <div class="row">
            <?php


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
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            Errore. Assicurati che sia .jpg o .png e minore di 5MB
                        </div>';
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
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Inclinazione</label>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="inclinazione" value="inclinata" >Inclinata
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="inclinazione" value="non_inclinata" checked="">Non inclinata
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Angolazione</label>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="angolazione" value="angolata">Angolata
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="angolazione" value="non_angolata" checked="">Non angolata
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Testo</label>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="testoPresente" value="testo_presente" checked="">Presente
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="testoPresente" value="testo_non_presente">Non presente
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Luce</label>
                                            <select name = "luce" class="form-control">
                                                <option value="poca_luce">Poca luce</option>
                                                <option value="luce_ottimale">Luce ottimale</option>
                                                <option value="troppa_luce">Troppa luce</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Etichetta</label>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="etichettaPiana" value="etichetta_piana" checked="">Piana
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="etichettaPiana" value="etichetta_non_piana">Non piana
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Caratteri </label>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="caratteriDanneggiati" value="caratteri_danneggiati">Opachi/Danneggiati
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="caratteriDanneggiati" value="caratteri_non_danneggiati" checked="">Nitidi
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Immagine </label>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="immagineNitida" value="nitida" checked="">Nitida
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="immagineNitida" value="sfuocata">Sfuocata
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Mossa</label>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="mossa" value="foto_mossa">Foto mossa
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="mossa" value="foto_non_mossa" checked="">Foto non mossa
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Risoluzione della foto</label>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="risoluzione" value="alta_risoluzione" checked="">Alta risoluzione
                                                </label>
                                            </div>
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" name="risoluzione" value="bassa_risoluzione">Bassa risoluzione
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                        <label>Ingredienti</label>
                                        <textarea class="form-control" name="ingredienti" rows="3" placeholder="Per unificare gli stili dividere gli ingredienti con una virgola"></textarea>
                                    </div>
                            </div>
                        </div>
                        <br>
                        <button type="submit" class="btn btn-primary btn-lg btn-block">Invia</button>
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
