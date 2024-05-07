<?php
$currentSite = basename($_SERVER['SCRIPT_NAME']);

$linksAndTitles = array(
    "index.php" => array(
        "title" => "Korppi HR",
        "link" => "Etusivu"
    ),
    "tyontekijat.php" => array(
        "title" => "Työnhakijoille - Korppi HR",
        "link" => "Työnhakijoille",
        "formValidatorJs" => "employee.js"
    ),
    "yrityksille.php" => array(
        "title" => "Yrityksille - Korppi HR",
        "link" => "Yrityksille",
        "formValidatorJs" => "company.js"
    ),
    "yhteydenotto.php" => array(
        "title" => "Yhteydenotto - Korppi HR",
        "link" => "Yhteydenotto",
        "formValidatorJs" => "contact.js"
    ),
    "tietosuojaseloste.php" => array(
        "title" => "Tietosuojaseloste - Korppi HR"
    )
);

$linksHTML = "";
$validatorJsHTML = "";
foreach($linksAndTitles as $href => $data) {
    
    if(array_key_exists("link", $data)) {
        if($currentSite == $href) {
            $titleHTML = "<title>".$data['title']."</title>";
            $linksHTML .= '<li class="active"><a class="nav-link active" href="'.$href.'">'.$data['link'].'</a></li>';
        } else {
            $linksHTML .= '<li><a class="nav-link" href="'.$href.'">'.$data['link'].'</a></li>';
        }
    }

    if(array_key_exists("formValidatorJs", $data)) {
        if($currentSite == $href) {
            $validatorJsHTML = '<script src="./js/form_functions.js"></script>
            <script src="./js/'.$data['formValidatorJs'].'"></script>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="./Images/Logos/Favicons/favicon.ico" sizes="16x16 32x32 48x48 64x64" type="image/vnd.microsoft.icon">
    <link rel="stylesheet" href="./main.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="./js/nav_mobile.js"></script>
    <?php
    if($validatorJsHTML !== "") {
        echo $validatorJsHTML;
    }
    echo $titleHTML
    ?>
</head>
<body>
<header id="#nav" class="container-fluid bg-white scrolled">
    <div class="navbar container">
        <a class="nav-link" href="index.php"><img class="nav-logo" src="./Images/Logos/korppihr_logo_no_margin.png" alt="Korppi HR logo"/></a>
        <nav class="nav-content flex-container">
            <ul id="nav-items" class="nav-items">
            <?php
                echo $linksHTML;
            ?>
            </ul>
            <button id="menu-icon" class="nav-menu">
            <span></span>
            <span></span>
            <span></span>
            </button>
        </nav>
    </div>
</header>
<div class="overflow-container container-fluid">