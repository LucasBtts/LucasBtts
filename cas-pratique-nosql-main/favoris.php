<?php
    include 'connection.php';
    session_start();
    // si l'utilisateur n'est pas connecté, on le redirige vers la page de connexion
    if(!isset($_SESSION['id'])){
        header('location:index.php');
    }
?>

<!DOCTYPE html>
<html>
<head>
    <title>Liste des restaurants</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">Restaurants</span>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <div class="navbar-nav">
                    <a class="nav-link" href="restaurants.php">List Restaurants</a>
                    <a class="nav-link active" aria-current="page" href="favoris.php">List Favoris</a>
                    <a class="nav-link" href="deconnection.php">Deconnection</a>
                </div>
            </div>
        </div>
    </nav>
    <div class="container">
        <form class="modal-body" action="" method="get">
            <div class="row mb-3">
                <div class="col-12 col-md-4 mb-2">
                    <input type="text" class="form-control" name="search" placeholder="Rechercher par nom">
                </div>
                <div class="col-12 col-md-4 mb-2">
                    <select class="form-control" name="cuisine">
                        <option value="">Sélectionner une cuisine</option>
                        <?php
                        $cuisines = $favoris->distinct("0.cuisine");
                        foreach ($cuisines as $cuisine) {
                            echo '<option value="' . $cuisine . '">' . $cuisine . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="col-12 col-md-4 mb-2">
                    <select class="form-control" name="borough">
                        <option value="">Sélectionner un arrondissement</option>
                        <?php
                        $boroughs = $favoris->distinct("0.borough");
                        foreach ($boroughs as $borough) {
                            echo '<option value="' . $borough . '">' . $borough . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="col-12 col-md-4 mb-2">
                    <input type="text" class="form-control" name="zipcode" placeholder="Rechercher par code postal">
                </div>
                <div class="col-12 col-md-4 mb-2">
                    <select class="form-control" name="sort">
                        <option value="">Trier par...</option>
                        <option value="name_asc">Nom (A-Z)</option>
                        <option value="name_desc">Nom (Z-A)</option>
                        <option value="id_asc">ID (ascendant)</option>
                        <option value="id_desc">ID (descendant)</option>
                        <option value="insert_asc">Insertion (ascendant)</option>
                        <option value="insert_desc">Insertion (descendant)</option>
                    </select>
                </div>
                <div class="col-12 col-md-4 mb-2">
                    <button type="submit" class="btn btn-primary">Filtrer</button>
                </div>
            </div>
        </form>
        <?php
            //mise en place de la pagination
            if (isset($_GET['page'])) {
                $current_page = intval($_GET['page']);
            } else {
                $current_page = 1;
            }

            $resultats_par_page = 10;
            $skip = ($current_page - 1) * $resultats_par_page;

            //mise en place des filtres
            $filter = [];
            $filter['user_id'] = $_SESSION['id'];

            if (!empty($_GET['search'])) {
                $filter['0.name'] = ['$regex' => $_GET['search'], '$options' => 'i'];
            }

            if (!empty($_GET['cuisine'])) {
                $filter['0.cuisine'] = $_GET['cuisine'];
            }

            if (!empty($_GET['borough'])) {
                $filter['0.borough'] = $_GET['borough'];
            }

            if (!empty($_GET['zipcode'])) {
                $filter['0.address.zipcode'] = ['$regex' => $_GET['zipcode'], '$options' => 'i'];
            }

            //mise en place de la pagination 1
            $current_page = isset($_GET['page']) ? $_GET['page'] : 1;
            $resultats_par_page = 10;
            $pages_limit = 10;
            $first_page = max($current_page - floor($pages_limit / 2), 1);
            $skip = ($current_page - 1) * $resultats_par_page;

            //mise en place des options (tri et limites)
            $options = [];
            $options['limit'] = $resultats_par_page;
            $options['skip'] = $skip;

            if (!empty($_GET['sort'])) {
                switch ($_GET['sort']) {
                    case 'name_asc':
                        $options['sort'] = ['0.name' => 1];
                        break;
                    case 'name_desc':
                        $options['sort'] = ['0.name' => -1];
                        break;
                    case 'id_asc':
                        $options['sort'] = ['0.restaurant_id' => 1];
                        break;
                    case 'id_desc':
                        $options['sort'] = ['0.restaurant_id' => -1];
                        break;
                    case 'insert_asc':
                        $options['sort'] = ['_id' => 1];
                        break;
                    case 'insert_desc':
                        $options['sort'] = ['_id' => -1];
                        break;
                }
            }

            //application des filtres et des options
            $cursor = $favoris->find($filter, $options);

            //mise en place de la pagination 2
            $resultats_total = $favoris->count($filter);
            $pages_totales = ceil($resultats_total / $resultats_par_page);
            $last_page = min($first_page + $pages_limit - 1, $pages_totales);

            if (isset($_POST['supprimer'])){
                // recuperer l'id du restaurant
                $id = $_POST['supprimer'];
                // récpération du restaurant
                $restaurant = $favoris->findOne(['_id' => new MongoDB\BSON\ObjectId($id)]);
                // supprimer le restaurant des favoris
                $favoris->deleteOne(['_id' => new MongoDB\BSON\ObjectId($id)]);

                //rafraichir la page
                header('location:favoris.php');
                echo "
                <div class='alert alert-danger' role='alert'>
                    Restaurant supprimé des favoris
                </div>";
            }
        ?>
        <div class="card">
            <div class="card-header">
                <h2>Liste des favoris<h2>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">Nom</th>
                            <th scope="col">Restaurant ID</th>
                            <th scope="col">Type de cuisine</th>
                            <th scope="col">Arrondissement</th>
                            <th scope="col">Code postal</th>
                            <th scope="col">Suprimmer favori</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // affichage des restaurants favoris
                            foreach ($cursor as $document) {
                                echo "<tr>";
                                echo "<td>".$document[0]['name']."</td>";
                                echo "<td>".$document[0]['restaurant_id']."</td>";
                                echo "<td>".$document[0]['cuisine']."</td>";
                                echo "<td>".$document[0]['borough']."</td>";
                                echo "<td>".$document[0]['address']['zipcode']."</td>";
                                echo "<form action='favoris.php' method='post'>";
                                echo "<td><button type='submit' class='btn btn-danger' name='supprimer' value=".$document['_id'].">Supprimer Favori</button></td>";
                                echo "</form>";
                                echo "</tr>";
                            }
                        ?>
                    </tbody>
                </table>
                <div class="modal-body">
                    <?php
                    echo '<nav aria-label="Page navigation">';
                    echo '<ul class="pagination">';
                    if ($current_page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?page=' . ($current_page - 1) . '">Précédent</a></li>';
                    }
                    for ($i = $first_page; $i <= $last_page; $i++) {
                        if ($i == $current_page) {
                            echo '<li class="page-item active"><a class="page-link" href="#">' . $i . '</a></li>';
                        } else {
                            echo '<li class="page-item"><a class="page-link" href="?page=' . $i . '">' . $i . '</a></li>';
                        }
                    }
                    if ($current_page < $pages_totales) {
                        echo '<li class="page-item"><a class="page-link" href="?page=' . ($current_page + 1) . '">Suivant</a></li>';
                    }

                    echo '</ul>';
                    echo '</nav>';
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>