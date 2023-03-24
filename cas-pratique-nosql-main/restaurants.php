<?php
    include 'connection.php';
    session_start();
    // si l'utilisateur n'est pas connecte, on le redirige vers la page de connexion
    if(!isset($_SESSION['id'])){
        header('location:index.php');
    }
    
    if (!isset($_SESSION['filter'])) {
        $_SESSION['filter'] = array(
            'name' => null,
            'cuisine' => null,
            'borough' => null,
            'address.zipcode' => null,
        );
    }

    if (!isset($_SESSION['option'])) {
        $_SESSION['option'] = array(
                'sort' => null,
        );
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
                    <a class="nav-link active" aria-current="page" href="restaurants.php">List Restaurants</a>
                    <a class="nav-link" href="favoris.php">List Favoris</a>
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
                            $cuisines = $restaurants->distinct("cuisine");
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
                        $boroughs = $restaurants->distinct("borough");
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
            //mise en place des filtres.php
            if ($_SESSION['filter']) {
                $filter = [];
            } else {
                $filter = $_SESSION['filter'];
            }

            if (!empty($_GET['search'])) {
                $filter['name'] = ['$regex' => $_GET['search'], '$options' => 'i'];
                $_POST['filter']['name'] = $filter['name'];
            }

            if (!empty($_GET['cuisine'])) {
                $filter['cuisine'] = $_GET['cuisine'];
                $_POST['filter']['cuisine'] = $filter['cuisine'];
            }

            if (!empty($_GET['borough'])) {
                $filter['borough'] = $_GET['borough'];
                $_POST['filter']['borough'] = $filter['borough'];
            }

            if (!empty($_GET['zipcode'])) {
                $filter['address.zipcode'] = ['$regex' => $_GET['zipcode'], '$options' => 'i'];
                $_SESSION['filter']['address.zipcode'] = $filter['address.zipcode'];
            }

            //mise en place de la pagination 1
            $current_page = isset($_GET['page']) ? $_GET['page'] : 1;
            $resultats_par_page = 10;
            $pages_limit = 10;
            $first_page = max($current_page - floor($pages_limit / 2), 1);
            $skip = ($current_page - 1) * $resultats_par_page;

            //mise en place des options (tri et limites)
            if ($_SESSION['option']) {
                $options = [];
            } else {
                $options = $_SESSION['option'];
            }
            $options['limit'] = $resultats_par_page;
            $options['skip'] = $skip;

            if (!empty($_GET['sort'])) {
                switch ($_GET['sort']) {
                    case 'name_asc':
                        $options['sort'] = ['name' => 1];
                        break;
                    case 'name_desc':
                        $options['sort'] = ['name' => -1];
                        break;
                    case 'id_asc':
                        $options['sort'] = ['restaurant_id' => 1];
                        break;
                    case 'id_desc':
                        $options['sort'] = ['restaurant_id' => -1];
                        break;
                    case 'insert_asc':
                        $options['sort'] = ['_id' => 1];
                        break;
                    case 'insert_desc':
                        $options['sort'] = ['_id' => -1];
                        break;
                }
                $_SESSION['option']['sort'] = $options['sort'];
            }

            //application des filtres.php et des options
            $cursor = $restaurants->find($filter, $options);

            //mise en place de la pagination 2
            $resultats_total = $restaurants->count($filter);
            $pages_totales = ceil($resultats_total / $resultats_par_page);
            $last_page = min($first_page + $pages_limit - 1, $pages_totales);

            //ajout dans les favoris
            if (isset($_POST['ajout'])){
                // récupération de l'id du restaurant
                $id = $_POST['ajout'];
                // récupération du restaurant
                $restaurant = $restaurants->findOne(['_id' => new MongoDB\BSON\ObjectID($id)]);
                // conter le nombre de restaurant dans la collection favoris de l'utilisateur connecté
                $count = $favoris->count([$restaurant,'user_id' => $_SESSION["id"]]);
                if($count == 1){
                    echo "
                    <div class='alert alert-warning' role='alert'>
                        Restaurant déjà dans vos favoris
                    </div>";
                }else{
                    // ajout du restaurant dans la collection favoris de l'utilisateur connecté
                    $favoris->insertOne([$restaurant,'user_id' => $_SESSION["id"]]);
                    echo "
                    <div class='alert alert-success' role='alert'>
                        Restaurant ajouté aux favoris
                    </div>";
                }
            }
        ?>
        <div class="card">
            <div class="card-header">
                <h2>Liste des restaurants<h2>
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
                            <th scope="col">Ajoute aux favori</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            // affichage des restaurants
                            foreach ($cursor as $document) {
                                echo "<tr>";
                                echo "<td>".$document['name']."</td>";
                                echo "<td>".$document['restaurant_id']."</td>";
                                echo "<td>".$document['cuisine']."</td>";
                                echo "<td>".$document['borough']."</td>";
                                echo "<td>".$document['address']['zipcode']."</td>";
                                echo "<form action='' method='post'>";
                                echo "<td><button type='submit' class='btn btn-success' name='ajout' value=".$document['_id'].">Ajouter Favori</button></td>";
                                echo "</form>";
                                echo "</tr>";
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
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
</body>
</html>