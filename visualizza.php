
<?php
session_start();
if (!isset( $_SESSION['user'] ) ) {
  header("location: /index.php");
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
                    <h1 class="page-header">Visualizzazione</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>

            <div class="row">

                <div class="panel panel-default">
                    <div class="panel-heading">
                        Filtro di ricerca
                    </div>
                    <div class="panel-body">
                        <form method="GET" action="#">

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Inclinazione</label>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="inclinazione" value="si">Inclinata
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="inclinazione" value="no">Non inclinata
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Angolazione</label>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="angolazione" value="si">Angolata
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="angolazione" value="no">Non angolata
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Testo</label>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="testoPresente" value="si">Presente
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="testoPresente" value="no">Non presente
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Luce</label>
                                        <select class="form-control">
                                            <option>Poca luce</option>
                                            <option>Luce ottimale</option>
                                            <option>Troppa luce</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Etichetta</label>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="etichettaPiana" value="si">Piana
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="etichettaPiana" value="no">Non piana
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Caratteri </label>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="caratteriDanneggiati" value="si">Opachi/Danneggiati
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="caratteriDanneggiati" value="no">Nitidi
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
                                                <input type="radio" name="immagineNitida" value="si">Nitida
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="immagineNitida" value="no">Sfuocata
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Mossa</label>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="mossa" value="si">Foto mossa
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="mossa" value="no">Foto non mossa
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Risoluzione della foto</label>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="risoluzione" value="si">Alta risoluzione
                                            </label>
                                        </div>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="risoluzione" value="no">Bassa risoluzione
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>


                            <div class="form-group">
                                <label>Nome foto</label>
                                <input class="form-control" name="nomeFoto" placeholder="Lasciare vuoto per non cercare in base al nome">
                            </div>
                            <button type="submit" class="btn btn-primary">Cerca</button>
                        </form>
                    </div>
                </div>


                <div class="row">
                    <div class="col-md-4">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Nome foto 1
                            </div>
                            <div class="panel-body" style="font-size: 17px;">
                                <img src="https://images.wallpaperscraft.com/image/everest_mountain_sky_tops_96976_1920x1080.jpg"
                                    style="width: 100%; height: auto;"></br></br>
                                <!--Consigliato da Leonardo Rossi di suddividere i tag per lista-->
                                <b>Tag</b>:
                                <ul>
                                    <li>Sfuocata</li>
                                    <li>Scura</li>
                                    <li>Inclinata</li>
                                </ul>
                                <b>Ingredienti</b>: "At vero eos et accusamus et iusto odio dignissimos ducimus qui
                                blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas
                                molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa
                                qui officia deserunt mollitia animi, id est laborum et dolorum fuga."
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Nome foto 1
                            </div>
                            <div class="panel-body" style="font-size: 17px;">
                                <img src="https://images.wallpaperscraft.com/image/everest_mountain_sky_tops_96976_1920x1080.jpg"
                                    style="width: 100%; height: auto;"></br></br>
                                <b>Tag</b>:
                                <ul>
                                    <li>Sfuocata</li>
                                    <li>Scura</li>
                                    <li>Inclinata</li>
                                </ul>
                                <b>Ingredienti</b>: "At vero eos et accusamus et iusto odio dignissimos ducimus qui
                                blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas
                                molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa
                                qui officia deserunt mollitia animi, id est laborum et dolorum fuga."
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Nome foto 1
                            </div>
                            <div class="panel-body" style="font-size: 17px;">
                                <img src="https://images.wallpaperscraft.com/image/everest_mountain_sky_tops_96976_1920x1080.jpg"
                                    style="width: 100%; height: auto;"></br></br>
                                <b>Tag</b>:
                                <ul>
                                    <li>Sfuocata</li>
                                    <li>Scura</li>
                                    <li>Inclinata</li>
                                </ul>
                                <b>Ingredienti</b>: "At vero eos et accusamus et iusto odio dignissimos ducimus qui
                                blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas
                                molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa
                                qui officia deserunt mollitia animi, id est laborum et dolorum fuga."
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Nome foto 1
                            </div>
                            <div class="panel-body" style="font-size: 17px;">
                                <img src="https://images.wallpaperscraft.com/image/everest_mountain_sky_tops_96976_1920x1080.jpg"
                                    style="width: 100%; height: auto;"></br></br>
                                <b>Tag</b>:
                                <ul>
                                    <li>Sfuocata</li>
                                    <li>Scura</li>
                                    <li>Inclinata</li>
                                </ul>
                                <b>Ingredienti</b>: "At vero eos et accusamus et iusto odio dignissimos ducimus qui
                                blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas
                                molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa
                                qui officia deserunt mollitia animi, id est laborum et dolorum fuga."
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Nome foto 1
                            </div>
                            <div class="panel-body" style="font-size: 17px;">
                                <img src="https://images.wallpaperscraft.com/image/everest_mountain_sky_tops_96976_1920x1080.jpg"
                                    style="width: 100%; height: auto;"></br></br>
                                <b>Tag</b>:
                                <ul>
                                    <li>Sfuocata</li>
                                    <li>Scura</li>
                                    <li>Inclinata</li>
                                </ul>
                                <b>Ingredienti</b>: "At vero eos et accusamus et iusto odio dignissimos ducimus qui
                                blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas
                                molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa
                                qui officia deserunt mollitia animi, id est laborum et dolorum fuga."
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Nome foto 1
                            </div>
                            <div class="panel-body" style="font-size: 17px;">
                                <img src="https://images.wallpaperscraft.com/image/everest_mountain_sky_tops_96976_1920x1080.jpg"
                                    style="width: 100%; height: auto;"></br></br>
                                <b>Tag</b>:
                                <ul>
                                    <li>Sfuocata</li>
                                    <li>Scura</li>
                                    <li>Inclinata</li>
                                </ul>
                                <b>Ingredienti</b>: "At vero eos et accusamus et iusto odio dignissimos ducimus qui
                                blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas
                                molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa
                                qui officia deserunt mollitia animi, id est laborum et dolorum fuga."
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Nome foto 1
                            </div>
                            <div class="panel-body" style="font-size: 17px;">
                                <img src="https://images.wallpaperscraft.com/image/everest_mountain_sky_tops_96976_1920x1080.jpg"
                                    style="width: 100%; height: auto;"></br></br>
                                <b>Tag</b>:
                                <ul>
                                    <li>Sfuocata</li>
                                    <li>Scura</li>
                                    <li>Inclinata</li>
                                </ul>
                                <b>Ingredienti</b>: "At vero eos et accusamus et iusto odio dignissimos ducimus qui
                                blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas
                                molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa
                                qui officia deserunt mollitia animi, id est laborum et dolorum fuga."
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Nome foto 1
                            </div>
                            <div class="panel-body" style="font-size: 17px;">
                                <img src="https://images.wallpaperscraft.com/image/everest_mountain_sky_tops_96976_1920x1080.jpg"
                                    style="width: 100%; height: auto;"></br></br>
                                <b>Tag</b>:
                                <ul>
                                    <li>Sfuocata</li>
                                    <li>Scura</li>
                                    <li>Inclinata</li>
                                </ul>
                                <b>Ingredienti</b>: "At vero eos et accusamus et iusto odio dignissimos ducimus qui
                                blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas
                                molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa
                                qui officia deserunt mollitia animi, id est laborum et dolorum fuga."
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Nome foto 1
                            </div>
                            <div class="panel-body" style="font-size: 17px;">
                                <img src="https://images.wallpaperscraft.com/image/everest_mountain_sky_tops_96976_1920x1080.jpg"
                                    style="width: 100%; height: auto;"></br></br>
                                <b>Tag</b>:
                                <ul>
                                    <li>Sfuocata</li>
                                    <li>Scura</li>
                                    <li>Inclinata</li>
                                </ul>
                                <b>Ingredienti</b>: "At vero eos et accusamus et iusto odio dignissimos ducimus qui
                                blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas
                                molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa
                                qui officia deserunt mollitia animi, id est laborum et dolorum fuga."
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Nome foto 1
                            </div>
                            <div class="panel-body" style="font-size: 17px;">
                                <img src="https://images.wallpaperscraft.com/image/everest_mountain_sky_tops_96976_1920x1080.jpg"
                                    style="width: 100%; height: auto;"></br></br>
                                <b>Tag</b>:
                                <ul>
                                    <li>Sfuocata</li>
                                    <li>Scura</li>
                                    <li>Inclinata</li>
                                </ul>
                                <b>Ingredienti</b>: "At vero eos et accusamus et iusto odio dignissimos ducimus qui
                                blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas
                                molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa
                                qui officia deserunt mollitia animi, id est laborum et dolorum fuga."
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Nome foto 1
                            </div>
                            <div class="panel-body" style="font-size: 17px;">
                                <img src="https://images.wallpaperscraft.com/image/everest_mountain_sky_tops_96976_1920x1080.jpg"
                                    style="width: 100%; height: auto;"></br></br>
                                <b>Tag</b>:
                                <ul>
                                    <li>Sfuocata</li>
                                    <li>Scura</li>
                                    <li>Inclinata</li>
                                </ul>
                                <b>Ingredienti</b>: "At vero eos et accusamus et iusto odio dignissimos ducimus qui
                                blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas
                                molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa
                                qui officia deserunt mollitia animi, id est laborum et dolorum fuga."
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Nome foto 1
                            </div>
                            <div class="panel-body" style="font-size: 17px;">
                                <img src="https://images.wallpaperscraft.com/image/everest_mountain_sky_tops_96976_1920x1080.jpg"
                                    style="width: 100%; height: auto;"></br></br>
                                <b>Tag</b>:
                                <ul>
                                    <li>Sfuocata</li>
                                    <li>Scura</li>
                                    <li>Inclinata</li>
                                </ul>
                                <b>Ingredienti</b>: "At vero eos et accusamus et iusto odio dignissimos ducimus qui
                                blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas
                                molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa
                                qui officia deserunt mollitia animi, id est laborum et dolorum fuga."
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Nome foto 1
                            </div>
                            <div class="panel-body" style="font-size: 17px;">
                                <img src="https://images.wallpaperscraft.com/image/everest_mountain_sky_tops_96976_1920x1080.jpg"
                                    style="width: 100%; height: auto;"></br></br>
                                <b>Tag</b>:
                                <ul>
                                    <li>Sfuocata</li>
                                    <li>Scura</li>
                                    <li>Inclinata</li>
                                </ul>
                                <b>Ingredienti</b>: "At vero eos et accusamus et iusto odio dignissimos ducimus qui
                                blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas
                                molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa
                                qui officia deserunt mollitia animi, id est laborum et dolorum fuga."
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Nome foto 1
                            </div>
                            <div class="panel-body" style="font-size: 17px;">
                                <img src="https://images.wallpaperscraft.com/image/everest_mountain_sky_tops_96976_1920x1080.jpg"
                                    style="width: 100%; height: auto;"></br></br>
                                <b>Tag</b>:
                                <ul>
                                    <li>Sfuocata</li>
                                    <li>Scura</li>
                                    <li>Inclinata</li>
                                </ul>
                                <b>Ingredienti</b>: "At vero eos et accusamus et iusto odio dignissimos ducimus qui
                                blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas
                                molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa
                                qui officia deserunt mollitia animi, id est laborum et dolorum fuga."
                            </div>
                        </div>

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Nome foto 1
                            </div>
                            <div class="panel-body" style="font-size: 17px;">
                                <img src="https://images.wallpaperscraft.com/image/everest_mountain_sky_tops_96976_1920x1080.jpg"
                                    style="width: 100%; height: auto;"></br></br>
                                <b>Tag</b>:
                                <ul>
                                    <li>Sfuocata</li>
                                    <li>Scura</li>
                                    <li>Inclinata</li>
                                </ul>
                                <b>Ingredienti</b>: "At vero eos et accusamus et iusto odio dignissimos ducimus qui
                                blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas
                                molestias excepturi sint occaecati cupiditate non provident, similique sunt in culpa
                                qui officia deserunt mollitia animi, id est laborum et dolorum fuga."
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 col-md-offset-3">

                            <ul class="pagination">
                                <li class="paginate_button previous disabled" aria-controls="dataTables-example"
                                    tabindex="0"><a href="#">Previous</a></li>
                                <li class="paginate_button active" aria-controls="dataTables-example" tabindex="0"><a
                                        href="#">1</a></li>
                                <li class="paginate_button " aria-controls="dataTables-example" tabindex="0"><a href="#">2</a></li>
                                <li class="paginate_button " aria-controls="dataTables-example" tabindex="0"><a href="#">3</a></li>
                                <li class="paginate_button " aria-controls="dataTables-example" tabindex="0"><a href="#">4</a></li>
                                <li class="paginate_button " aria-controls="dataTables-example" tabindex="0"><a href="#">5</a></li>
                                <li class="paginate_button " aria-controls="dataTables-example" tabindex="0"><a href="#">6</a></li>
                                <li class="paginate_button next" aria-controls="dataTables-example" tabindex="0"><a
                                        href="#">Next</a></li>
                            </ul>


                        </div>
                    </div>
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
