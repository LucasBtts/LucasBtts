<?php
    session_start();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Page de connexion</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<body>
    <div class="container">
        <?php
            if (isset($_POST['connexion'])){
                $username = $_POST['username'];
                $pwd = $_POST['pwd'];
                
                if ($username == "admin" && $pwd == "admin"){
                    $_SESSION['id'] = 1;
                    header('Location:restaurants.php');
                }
                if ($username == "test" && $pwd == "test") {
                    $_SESSION['id'] = 2;
                    header('Location:restaurants.php');
                } else {
                    echo "
                    <div class='alert alert-danger' role='alert'>
                        Identifiant ou mot de passe incorrect
                    </div>";
                }
            }
        ?>
        <h2>Page de connexion</h2>
        <form action="#" method="post">
            <div class="form-group">
                <label for="id">Identifiant :</label>
                <input type="text" class="form-control" id="username" placeholder="Enter email" name="username">
            </div>
            <div class="form-group">
                <label for="pwd">Mot de passe :</label>
                <input type="password" class="form-control" id="pwd" placeholder="Enter password" name="pwd">
            </div>
            <div class="form-group form-check">
                <label class="form-check-label">
                    <input class="form-check-input" type="checkbox" name="remember"> Se souvenir de moi
                </label>
            </div>
            <button name="connexion" type="submit" class="btn btn-primary">Se connecter</button>
        </form>
    </div>
</body>

</html>