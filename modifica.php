<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if (!isset( $_SESSION['user'])  || !isset( $_GET['id']) ) {
  header("location: /index.php");
}

$photo_base_name = "foto";
include 'database_info.php';
//$link = mysqli_connect($dbhost, $dbuser, $dbpass) or die("Unable to Connect to '$dbhost'");
$mysqli=mysqli_connect($GLOBALS['dbhost'],$GLOBALS['dbuser'],$GLOBALS['dbpass'],$GLOBALS['dbname']);
// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


//Ritorna la riga del database di una foto
function getFotoFields($mysqli,$idfoto){
    $sql = "SELECT * FROM foto WHERE ID = $idfoto";
    $result = mysqli_query($mysqli, $sql);
    $list = mysqli_fetch_array($result);
    return $list;
}
function getNumberTag($mysqli,$tagType){
    $sql = "SELECT COUNT(*) FROM tag WHERE TIPO = '$tagType'";
    $result = mysqli_query($mysqli, $sql);
    $taglist = mysqli_fetch_array($result);
    return $taglist[0];
}
function getTagName($mysqli,$tagType){
    $sql = "SELECT * FROM tag WHERE TIPO = '$tagType'";
    $result = mysqli_query($mysqli, $sql);
    $tagarray = array();
    if ($result && mysqli_num_rows($result) > 0) {
        $tagarray = mysqli_fetch_all($result,MYSQLI_ASSOC);
        mysqli_free_result($result);
    }
    return $tagarray;
    
}

//generazione dell'url dove reperire i file delle foto
function generateUrl($nomefile){
    return 'http://'.$_SERVER['HTTP_HOST'].'/foto/'.$nomefile;
}


//genera i radiobutton in base ai tag esistenti.
//UTILE PER PAGINA INSERIMENTO
/*function generaRadio($mysqli)
{
    echo $numTag=getNumberTag($mysqli,"M");
    
    echo '<div class="row">';
    //colonna tag parte da 0
    $currentCollumn=0;
    for($i=0;$i<$numTag;$i++)
    {
        if($currentCollumn==3) //Faccio 3 colonne
        {
            $currentCollumn=0;
            echo '</div>
            <div class="row">';
        }
        else
        {
            $currentCollumn++;
        }

        echo '<div class="col-md-4">
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
        </div>';

    }
    echo '</div>';
}*/

function generaRadio($mysqli)
{
    $numTag=getNumberTag($mysqli,"M");
    $taglist=getTagName($mysqli,"M");
    
    
    echo '<div class="row">
    <div class="col-md-4">
    <div class="form-group">
    <label>Tag modifiche </label>';
    foreach($taglist as $tag){
        echo '
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="modifiche[]" value="'.$tag["ID"].'">'.$tag["NOME"].'
                </label>
            </div>
        ';
    }
    echo '
    </div>
    </div>
    </div>';
}


if(isset($_POST["modifiche"]))
{
    
    try {
        post($mysqli);
        header("location: /inserimento.php?inserita");
    } catch (Exception $e) {
      header("location: /inserimento.php?errore=". $e->getMessage());
    }
    
    
}
function post($mysqli){
    global $photo_base_name;
    $modifiche = $_POST["modifiche"];
    $note = $_POST['note'];
    $idFotoOrginale= $_POST['idFotoOriginale'];

    //get last id inserted - ottendo l'ultimo id usato per identificare le foto, in modo da costruire poi il nome della foto
    //che verrà salvata in una cartella, il nome sarà del tipo photo + {ID}
    $photo_number = get_photo_number($mysqli);
    $photo_base_name .= $photo_number;

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
        throw new Exception("Errore nel caricamento del file. ");
      }
    } else {
      throw new Exception("File mancante.");
    }
    
    //insert photo attributes - inserimento nel db degli attributi necessari per reperire la foto
    //$photo_number is used as primary key
    $photo_name =$photo_base_name . '.' . $photo_extension;
    $stmt = $mysqli -> prepare("INSERT INTO foto (ID, NOME, INGREDIENTI, NOTE) VALUES(?, ?, ?, ?)");
    $val="";
    $stmt->bind_param("isss", $photo_number, $photo_name, $val, $val);
    $stmt -> execute();

    foreach($modifiche as $modifica) {
    {
        $stmt = $mysqli -> prepare("INSERT INTO modifiche (IDORIGINALE, IDMODIFICATA, TAGMODIFICA, NOTE) VALUES(?, ?, ?, ?)");
        $stmt->bind_param("iiis", $idFotoOrginale, $photo_number, $modifica, $note);
        $stmt -> execute();
    }
    
    }

    //close connection
    mysqli_close($mysqli);

}
function get_photo_number($mysqli) {
    $photo_number = 0;
    $sql = "SELECT MAX(ID) FROM foto";
    $result = mysqli_query($mysqli, $sql);
    if($result != NULL) {
      $row = $result->fetch_assoc();
      $photo_number =  ((int)$row["MAX(ID)"] + 1);
    }
    return $photo_number;
  }
function is_image($photo_extension) {
    $expensions= array("jpeg","jpg","png");
    if(!in_array($photo_extension,$expensions)){
        return false;
    }
    return true;
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
                    <h1 class="page-header">Modifica foto</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <div class="row">
            <?php
                $foto = getFotoFields($mysqli,$_GET['id']);
                echo '<div class="col-md-4">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Foto da modificare: '.$foto['NOME'].'
                            </div>
                            <div class="panel-body" style="font-size: 17px;">
                                <img src="'.generateUrl($foto['NOME']).'"
                                    style="width: 100%; height: auto;">
                            </div>
                        </div>
                    </div>';

            ?>

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
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>'
                            . 'Errore: ' . $_GET['errore']
                            .'</div>';
                }
            ?>
              <div class="panel panel-default">
                <div class="panel-heading">
                      Modifica valori
                </div>
                <div class="panel-body">

                    <form action="#" method="POST" enctype="multipart/form-data">

                        <div class="form-group">
                            <label>Nuova immagine: </label>
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
                            <div class="col-md-4">
                                <div class="row">
                                    <?php
                                        generaRadio($mysqli);
                                    ?>
                                    
                                </div>
                                
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Note</label>
                                    <textarea class="form-control" name="note" rows="3" placeholder="Inserire qui eventuali note"></textarea>
                                </div>
                            </div>
                        </div>
                        <br>
                        <input type="hidden" name="idFotoOriginale" value="<?php echo $_GET['id'];?>">

                            <button class="btn btn-primary btn-lg btn-block" data-toggle="modal" data-target="#myModal" onclick='upload_image();'>Invia</button>
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
