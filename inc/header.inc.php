<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8">
    <title><?php
            $request = explode("/", $_SERVER['REQUEST_URI']);
            $request_file = end($request);
            switch ($request_file) {
                case 'index.php':
                    echo "Übersicht";
                    break;
                case 'search.php':
                    echo "Suche";
                    break;
                case 'bans.php':
                    echo "Bans";
                    break;
                case 'mutes.php':
                    echo "Mutes";
                    break;
                case 'reports.php':
                    echo "Reports";
                    break;
                case 'accounts.php':
                    echo "Accounts";
                    break;
                case 'reasons.php':
                    echo "Bangründe";
                    break;
                case 'unbans.php':
                    echo "Entbannungsanträge";
                    break;
                default:
                    echo "ProfessionalBans";
                    break;
            }
            ?></title>
    <link rel="stylesheet" href="css/master.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/jquery.sweet-modal.min.css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.0/jquery.min.js"></script>
    <script src="js/jquery.sweet-modal.min.js"></script>
    <link rel="shortcut icon" type="image/x-icon" href="css/favicon.ico">
    <meta name="viewport" content="width=device-width,
            initial-scale=1.0,
            minimum-scale=1.0">
</head>
<?php

session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

require("./datamanager.php");
if (isInitialPassword($_SESSION['username'])) {
    header("Location: resetpassword.php?name=" . $_SESSION['username']);
    exit;
}

validateSession();

?>

<body>
    <div class="container">
        <div class="sidebar">
            <!-- START 
            Navbar for desktop devices -->
            <ul>
                <li <?php activeItem("index.php") ?>><a href="index.php"><i class="fas fa-home"></i> Übersicht</a></li>
                <li <?php activeItem("search.php") ?>><a href="search.php"><i class="fas fa-search"></i> Suche</a></li>
                <?php
                if (isMod($_SESSION["username"])) {
                    ?>
                    <li <?php activeItem("bans.php") ?>><a href="bans.php"><i class="fas fa-ban"></i> Bans</a></li>
                <?php
                }
                ?>
                <li <?php activeItem("mutes.php") ?>><a href="mutes.php"><i class="fas fa-volume-mute"></i> Mutes</a></li>
                <li <?php activeItem("livechat.php") ?>><a href="livechat.php"><i class="fas fa-comment"></i> Livechat</a></li>
                <?php
                if (isMod($_SESSION["username"])) {
                    ?>
                    <li <?php activeItem("reports.php") ?>><a href="reports.php"><i class="fas fa-flag"></i> Reports</a></li>
                <?php
                }
                ?>
                <?php
                if (isAdmin($_SESSION["username"])) {
                    ?>
                    <li <?php activeItem("accounts.php") ?>><a href="accounts.php"><i class="fas fa-users"></i> Accounts</a></li>
                    <li <?php activeItem("reasons.php") ?>><a href="reasons.php"><i class="fas fa-cogs"></i> Bangründe</a></li>
                <?php
                }
                ?>
                <?php
                if (isMod($_SESSION["username"])) {
                    ?>
                    <li <?php activeItem("unbans.php") ?>><a href="unbans.php"><i class="fas fa-envelope"></i> Entbannungsanträge</a></li>
                <?php
                }
                ?>
            </ul>
            <!-- END 
            Navbar for desktop devices -->
        </div>
        <div class="header">
            <!-- Trigger for mobile devices -->
            <i class="fas fa-bars fa-2x menu mobileicon"></i>
            <a href="logout.php"><i class="fas fa-sign-out-alt fa-2x headericon"></i></a>
        </div>
        <div class="content">
            <div class="mobilenavbar">
                <nav>
                    <!-- START 
                    Navbar for mobile devices -->
                    <ul class="navbar animated bounceInDown">
                        <li <?php activeItem("index.php") ?>><a href="index.php"><i class="fas fa-home"></i> Übersicht</a></li>
                        <li <?php activeItem("search.php") ?>><a href="search.php"><i class="fas fa-search"></i> Suche</a></li>
                        <?php
                        if (isMod($_SESSION["username"])) {
                            ?>
                            <li <?php activeItem("bans.php") ?>><a href="bans.php"><i class="fas fa-ban"></i> Bans</a></li>
                        <?php
                        }
                        ?>
                        <li><a href="mutes.php"><i class="fas fa-volume-mute"></i> Mutes</a></li>
                        <li><a href="livechat.php"><i class="fas fa-comment"></i> Livechat</a></li>
                        <?php
                        if (isMod($_SESSION["username"])) {
                            ?>
                            <li <?php activeItem("reports.php") ?>><a href="reports.php"><i class="fas fa-flag"></i> Reports</a></li>
                        <?php
                        }
                        ?>
                        <?php
                        if (isAdmin($_SESSION["username"])) {
                            ?>
                            <li <?php activeItem("accounts.php") ?>><a href="accounts.php"><i class="fas fa-users"></i> Accounts</a></li>
                            <li <?php activeItem("reasons.php") ?>><a href="reasons.php"><i class="fas fa-cogs"></i> Bangründe</a></li>
                        <?php
                        }
                        ?>
                        <?php
                        if (isMod($_SESSION["username"])) {
                            ?>
                            <li <?php activeItem("unbans.php") ?>><a href="unbans.php"><i class="fas fa-envelope"></i> Entbannungsanträge</a></li>
                        <?php
                        }
                        ?>
                    </ul>
                    <!-- END 
                    Navbar for mobile devices -->
                </nav>
            </div>
            <script type="text/javascript">
                $(document).ready(function() {
                    $('.menu').click(function() {
                        $('ul').toggleClass("navactive");
                    })
                })
            </script>

            <?php

            function activeItem($file)
            {
                $request = explode("/", $_SERVER['REQUEST_URI']);
                $request_file = end($request);
                if ($request_file == $file) {
                    echo 'class="active"';
                }
            }
