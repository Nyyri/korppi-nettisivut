<?php
// Tarkistetaan onko pyyntö tullut lomakkeelta (ei vain url kautta)
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location:./yrityksille.php");
    exit();
}

session_start();

$formResubmission = false;

$errors = [];
$inputErrors = [];
$data = [];

// Regular expression
$preg_alpha = "/^([ \p{Latin}]([\p{Latin}]-[\p{Latin}])?)+$/u";
$preg_phone = "/^(040|041|042|043|044|045|046|049|050)\d{4,10}$/";
$preg_businessId = "/^\d{7}\-\d{1}$/";

// Input max lengths and additional sanitazion vars
$MAX_NAME_LENGTH = 50;
$MAX_BUSINESS_ID_LENGTH = 9;
$MAX_EMAIL_LENGTH = 60;
$MAX_PHONE_LENGTH = 13;
$MAX_NEEDS_LENGTH = 2000;

// Db config
// Gets $connection variable
$dbStatus = include("config.php");
require("token.php");

// If database connection is OK
if($dbStatus != "OK") {
    $errors['db'] = "Palvelimeen ei juuri nyt saada yhteyttä, yritä myöhemmin uudelleen. Virhekoodi C1";
} else {
    if(!isset($_POST['formToken']) || $_POST['formToken'] === "") {
        $errors['db'] = "Palvelimeen ei juuri nyt saada yhteyttä, yritä myöhemmin uudelleen. Virhekoodi T1";
    } else if($_POST['formToken'] !== $_SESSION['form_token']) {
        $formResubmission = true;
    } else {
        if(!isset($_POST['company']) || $_POST['company'] === "") {
            $inputErrors['company'] = "Yritys / kunta on pakollinen.";
        } else if(!preg_match($preg_alpha, str_replace(' ', '', $_POST['company']))) {
            $inputErrors['company'] = "Yritykseen / kuntaan vain kirjaimia.";
        } else if(mb_strlen($_POST['company'], "UTF-8") > $MAX_COMPANY_LENGTH) {
            $inputErrors['company'] = "Yrityksen / kunnan maksimipituus on $MAX_COMPANY_LENGTH kirjainta. Olet kirjoittanut ".mb_strlen($_POST['company'], "UTF-8")." kirjainta.";
        } else {
            $company = clean_input($_POST['company']);
        }
    
        if(!isset($_POST['businessId']) || $_POST['businessId'] === "") {
            $inputErrors['businessId'] = "Y-tunnus on pakollinen.";
        } else if(!preg_match($preg_businessId, $_POST['businessId']) || mb_strlen($_POST['businessId'], "UTF-8") > $MAX_BUSINESS_ID_LENGTH) {
            $inputErrors['businessId'] = "Anna kelvollinen Y-tunnus.";
        } else {
            $businessId = clean_input($_POST['businessId']);
        }
    
        if(!isset($_POST['name']) || $_POST['name'] === "") {
            $inputErrors['name'] = "Nimi on pakollinen.";
        } else if(!preg_match($preg_alpha, str_replace(' ', '', $_POST['name']))) {
            $inputErrors['name'] = "Nimeen vain kirjaimia.";
        } else if(mb_strlen($_POST['name'], "UTF-8") > $MAX_NAME_LENGTH) {
            $inputErrors['name'] = "Nimen maksimipituus on $MAX_NAME_LENGTH kirjainta. Olet kirjoittanut ".mb_strlen($_POST['name'], "UTF-8")." kirjainta.";
        } else {
            $name = clean_input(capitalizeEveryWord($_POST['name']));
        }
    
        if(!isset($_POST['email']) || $_POST['email'] === "") {
            $inputErrors['email'] = "Sähköposti on pakollinen.";
        } else if(!preg_match($preg_email, str_replace(' ', '', $_POST['email']))) {
            $inputErrors['email'] = "Syötä kelvollinen sähköposti.";
        } else if(mb_strlen($_POST['email'], "UTF-8") > $MAX_EMAIL_LENGTH) {
            $inputErrors['email'] = "Sähköpostin maksimipituus on $MAX_EMAIL_LENGTH kirjainta. Olet kirjoittanut ".mb_strlen($_POST['email'], "UTF-8")." kirjainta.";
        } else {
            $email = clean_input($_POST['email']);
        }
        
        if(isset($_POST['phone']) && $_POST['phone'] !== "") {
            $phone = $_POST['phone'];
        
            if(strpos($phone, "+358") !== false) {
                $phone = str_replace("+358", "0", $phone);
            }
        
            if(!preg_match($preg_phone, $phone) || strlen($phone) > $MAX_PHONE_LENGTH) {
                $inputErrors['phone'] = "Anna kelvollinen puhelinnumero.";
            }
        
        } else {
            $inputErrors['phone'] = "Puhelinnumero on pakollinen.";
        }
    
        if(!isset($_POST['needs']) || $_POST['needs'] === "") {
            $inputErrors['needs'] = "Palvelutarve on pakollinen.";
        } else if(mb_strlen($_POST['needs'], "UTF-8") > $MAX_NEEDS_LENGTH) {
            $inputErrors['needs'] = "Palvelutarpeen maksimipituus on $MAX_NEEDS_LENGTH kirjainta. Olet kirjoittanut ".mb_strlen($_POST['needs'], "UTF-8")." kirjainta.";
        } else {
            $needs = clean_input($_POST['needs']);
        }
        
        if(!isset($_POST['gdprConsent']) || $_POST['gdprConsent'] === "n" || empty($_POST['gdprConsent'])) {
            $inputErrors['gdprConsent'] = "Lue ja hyväksy tietosuojaseloste.";
        } else {
            $gdpr = 1;
        }
    }
}

if(empty($errors) && empty($inputErrors)) {
    $stmt_application = $connection -> 
    prepare("INSERT INTO c_application 
    (name, businessID, email, phone, needs, gdpr) 
    VALUES (?, ?, ?, ?, ?, ?)");

    $stmt_application -> bind_param("sssssi", $name, $businessId, $email, $phone, $needs, $gdpr);

    $connection -> begin_transaction();

    if($stmt_application -> execute()) {
        $saved = true;
    } else {
        $saved = false;
        $errors['db'] = "Palvelimeen ei juuri nyt saada yhteyttä, yritä myöhemmin uudelleen. Virhekoodi: I1";
    }

    // If the SQL queries are succesful
    if($saved) {
        if($connection -> commit()) {
            $to = "email@address.com";
            $subject = "Yrityshakemus - ".$name."";
            $message = compose_email($name, $businessId, $phone, $email, $needs);
            $from = "email@address.com";
            $headers = array(
                'From' => "email@address.com",
                'MIME-version' => "1.0",
                'Content-Type' => "text/html;charset=utf-8"
            );
    
            if(!mail($to, $subject, $message, $headers)) {
                $errors['db'] = "Sähköpostin lähettäminen epäonnistui.";
            }
        } else {
            $errors['db'] = "Palvelimeen ei juuri nyt saada yhteyttä, yritä myöhemmin uudelleen. Virhekoodi: T1";
            $connection -> rollBack();
            $connection -> close();
        }
    } else {
        $connection -> rollBack();
    }
}

if($formResubmission) {
    $data['success'] = true;
    $data['formResubmission'] = true;
    $data['success_msg'] = "Tilaushakemuksesi on vastaanotettu.";
} else if(!empty($errors) || !empty($inputErrors)) {
    $data['success'] = false;
    $data['errors'] = $errors;
    $data['inputErrors'] = $inputErrors;
} else {
    $data['form_token'] = formToken();
    $_SESSION['form_token'] = $data['form_token'];

    $data['success'] = true;
    $data['success_msg'] = "Tilaushakemuksesi on vastaanottettu!";
}

// Respond with JSON
echo json_encode($data);

function clean_input($input) {
    $input = trim($input);
    $input = preg_replace("/\s\s+/", " ", $input);
    $input = preg_replace("/\s*-\s*/", '-', $input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);

    return $input;
}

function capitalizeEveryWord($string) {
    $string = implode('-', array_map('ucwords', explode('-', strtolower($string))));
    return $string;
}

function compose_email($name, $businessId, $email, $phone, $needs) {
    $email_html =
    '<!DOCTYPE html>
    <html lang="fi" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Yrityshakemus - '.$name.'</title>
        <!--[if mso]>
        <noscript>
            <xml>
                <o:OfficeDocumentSettings>
                    <o:PixelsPerInch>96</o:PixelsPerInch>
                </o:OfficeDocumentSettings>
            </xml>
        </noscript>
        <![endif]-->
        <style>
            table, td, div, h1, p {
                font-family: Arial, Helvetica, sans-serif;
            }
        </style>
    </head>
    <body style="margin:0 2rem 0 2rem;padding:0;background-color:#f5f1ff;">
        <table role="presentation" style="width:100%;table-layout:fixed;border-collapse:collapse;border:0;border-spacing:0;">
            <tr>
                <td style="padding:0;word-wrap:break-word;">
                    <h1 style="margin:0;padding:1em 0 .5em 0;font-size:1.1em;font-weight:300;">Yrityshakemus</h1>
                </td>
                <tr>
                    <td style="padding:0 0 1em 0;font-size:0;line-height:0;">&nbsp;</td>
                </tr>
                <td style="padding:0;word-wrap:break-word;">
                    <h2 style="margin:0;padding:0 0 .1em 0;font-size:1.8em;">'.$name.', '.$businessId.'</h2>
                </td>
            </tr>
            <tr>
                <td style="padding:0 0 .8em 0;font-size:0;line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td style="padding:0;word-wrap:break-word;">
                    <p style="list-style:none;font-size:.85em;margin:0;padding:0 0 .2em 0;box-sizing: border-box;padding-bottom: .25rem;">'.$phone.' </p>
                </td>
            </tr>
            <tr>
                <td style="padding:0 0 .3em 0;font-size:0;line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td style="padding:0;word-wrap:break-word;">
                    <p style="font-size:.85em;margin:0;padding:0 0 1.5em 0;box-sizing: border-box;">'.$email.'</p>
                </td>
            </tr>
            <tr>
                <td style="padding:0 0 1.5em 0;font-size:0;line-height:0;">&nbsp;</td>
            </tr>
        </table>
        <table role="presentation" style="table-layout:fixed;width:100%;border-collapse:collapse;border:0;border-spacing:0;">
            <tr>
                <td style="padding:0;word-wrap:break-word;">
                    <h2 style="margin:0;padding:1em 0 .5em 0;font-size: 1.5em;font-weight:100;">Palvelutarpeemme</h1>
                </td>
            </tr>
            <tr>
                <td style="padding:0 0 .3em 0;font-size:0;line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td style="padding:0;word-wrap:break-word;">
                    <p style="font-size:.9em;margin:0;padding:0;">'.$needs.'</p>
                </td>
            </tr>
        </table>
    </body>
    </html>';

    return $email_html;
}
?>
