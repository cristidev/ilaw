<?php
session_start();
$username = 'demo';
$password = 'bozza123';

$login_errors = '';
if( isset($_POST['username']) && isset($_POST['password']) ){
    if( $_POST['username'] != $username ) $login_errors = 'Invalid username';
    if( $_POST['password'] != $password ) $login_errors = 'Invalid password';

    if( empty($login_errors)){
        $_SESSION['login'] = 1;
    }
}

?>
<html>
    <head>
        <!-- Latest compiled and minified CSS -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

        <!-- Optional theme -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
        <!-- Latest compiled and minified JavaScript -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
        <style>
            .bodyclass{
                height: 150px;
                overflow:hidden;
                cursor: pointer;
            }
            .img-m{
                max-width: 150px;
            }
            table td{
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <div class="container theme-showcase">
            <div class="page-header">
                <h1>DEMO</h1>
            </div>
            <?php
            if( empty($_SESSION['login']) )
                include( 'login.php' );
            else
                include( 'mainpage.php' );
            ?>
        </div>
    </body>
</html>