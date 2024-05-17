<?php
// Tarkistetaan onko pyyntö tullut lomakkeelta (ei vain url kautta)
if($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location:./yhteydenotto.php");
    exit();
}

session_start();

$formResubmission = false;

$errors = [];
$inputErrors = [];
$data = [];

// Regular expression
$preg_alpha = "/^([ \p{Latin}]([\p{Latin}]-[\p{Latin}])?)+$/u";
$preg_email = '/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/';
$preg_phone = "/^(040|041|042|043|044|045|046|049|050)\d{4,10}$/";

// Input max lengths and additional sanitazion vars
$MAX_NAME_LENGTH = 40;
$MAX_EMAIL_LENGTH = 60;
$MAX_PHONE_LENGTH = 13;
$MAX_TEXT_LENGTH = 2000;

// Db config
// Gets $connection variable
$dbStatus = include("config.php");
require("token.php");

// If database connection is OK
if($dbStatus !== "OK") {
    $errors['db'] = "Palvelimeen ei juuri nyt saada yhteyttä, yritä myöhemmin uudelleen. Virhekoodi C1";
} else {
    if(!isset($_POST['formToken']) || $_POST['formToken'] === "") {
        $errors['db'] = "Palvelimeen ei juuri nyt saada yhteyttä, yritä myöhemmin uudelleen. Virhekoodi T1";
    } else if($_POST['formToken'] !== $_SESSION['form_token']) {
        $formResubmission = true;
    } else {
        if(!isset($_POST['name']) || $_POST['name'] === "") {
            $inputErrors['name'] = "Yritys / kunta on pakollinen.";
        } else if(!preg_match($preg_alpha, str_replace(' ', '', $_POST['name']))) {
            $inputErrors['name'] = "Yritykseen / kuntaan vain kirjaimia.";
        } else if(strlen($_POST['name']) > $MAX_NAME_LENGTH) {
            $inputErrors['name'] = "Yrityksen / kunnan maksimipituus on $MAX_NAME_LENGTH kirjainta. Olet kirjoittanut ".strlen($_POST['name'])." kirjainta.";
        } else {
            $name = clean_input(capitalizeEveryWord($_POST['name']));
        }
    
        if(!isset($_POST['email']) || $_POST['email'] === "") {
            $inputErrors['email'] = "Sähköposti on pakollinen.";
        } else if(!preg_match($preg_email, str_replace(' ', '', $_POST['email']))) {
            $inputErrors['email'] = "Syötä kelvollinen sähköposti.";
        } else if(strlen($_POST['email']) > $MAX_EMAIL_LENGTH) {
            $inputErrors['email'] = "Sähköpostin maksimipituus on $MAX_EMAIL_LENGTH kirjainta. Olet kirjoittanut ".strlen($_POST['email'])." kirjainta.";
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
    
        if(!isset($_POST['text']) || $_POST['text'] === "") {
            $inputErrors['text'] = "Palvelutarve on pakollinen.";
        } else if(strlen($_POST['text']) > $MAX_TEXT_LENGTH) {
            $inputErrors['text'] = "Palvelutarpeen maksimipituus on $MAX_TEXT_LENGTH kirjainta. Olet kirjoittanut ".strlen($_POST['text'])." kirjainta.";
        } else {
            $text = clean_input($_POST['text']);
        }
        
        if(!isset($_POST['gdprConsent']) || $_POST['gdprConsent'] === "n" || empty($_POST['gdprConsent'])) {
            $inputErrors['gdprConsent'] = "Lue ja hyväksy tietosuojaseloste.";
        } else {
            $gdpr = 1;
        }
    }
}

if(empty($errors) && empty($inputErrors)) {
    $stmt_contact = $connection -> 
    prepare("INSERT INTO contact 
    (name, email, phone, text, gdpr) 
    VALUES (?, ?, ?, ?, ?)");

    $stmt_contact -> bind_param("ssssi", $name, $email, $phone, $text, $gdpr);

    $connection -> begin_transaction();

    if($stmt_contact -> execute()) {
        $saved = true;
    } else {
        $saved = false;
        $errors['db'] = "Palvelimeen ei juuri nyt saada yhteyttä, yritä myöhemmin uudelleen. Virhekoodi: I1";
    }

    // If the SQL queries are succesful
    if($saved) {
        if($connection -> commit()) {
            $to = "email@address.com";

            $subject = "Yhteydenotto - ".$name."";
            $subjectConfirmation = "Yhteydenottosi on vastaanotettu!";

            $message = compose_email($name, $phone, $email, $text);
            $messageConfirmation = composeConfirmationEmail();

            $from = $email;
            $fromConfirmation = "email@no-reply.fi";

            $headers = array(
                'From' => $from,
                'MIME-version' => "1.0",
                'Content-Type' => "text/html;charset=utf-8"
            );

            $headersConfirmation = array(
                'From' => $fromConfirmation,
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
    $data['success_msg'] = "Yhteydenottosi on vastaanottettu!";
} else if (!empty($errors) || !empty($inputErrors)) {
    $data['success'] = false;
    $data['errors'] = $errors;
    $data['inputErrors'] = $inputErrors;
} else {
    $data['form_token'] = formToken();
    $_SESSION['form_token'] = $data['form_token'];

    $data['success'] = true;
    $data['success_msg'] = "Yhteydenottosi on vastaanottettu!";
}

// Respond with JSON
echo json_encode($data);

function clean_input($input) {
    $input = trim($input);
    $input = preg_replace("/\s\s+/", " ", $input);
    $input = str_replace(['- ', ' -', ' - '], '-', $input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);

    return $input;
}

function capitalizeEveryWord($string) {
    $string = implode('-', array_map('ucwords', explode('-', strtolower($string))));
    return $string;
}

function compose_email($name, $email, $phone, $text) {
    $email_html =
    '<!DOCTYPE html>
    <html lang="fi" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Yhteydenotto - '.$name.'</title>
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
                <td width="1em"></td>
                <td style="padding:0;word-wrap:break-word;">
                    <h1 style="margin:0;padding:1em 0 .5em 0;font-size:1.1em;font-weight:300;">Yhteydenotto</h1>
                </td>
                <tr>
                    <td style="padding:0 0 1em 0;font-size:0;line-height:0;">&nbsp;</td>
                </tr>
                <td style="padding:0;word-wrap:break-word;">
                    <h2 style="margin:0;padding:0 0 .1em 0;font-size:1.8em;">'.$name.'</h2>
                </td>
                <td width="1em"></td>
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
                    <h2 style="margin:0;padding:1em 0 .5em 0;font-size: 1.5em;font-weight:100;">Vapaa teksti</h1>
                </td>
            </tr>
            <tr>
                <td style="padding:0 0 .3em 0;font-size:0;line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td style="padding:0;word-wrap:break-word;">
                    <p style="font-size:.9em;margin:0;padding:0;">'.$text.'</p>
                </td>
            </tr>
        </table>
    </body>
    </html>';

    return $email_html;
}

function composeConfirmationEmail() {
    $email_html = '
    <!DOCTYPE html>
    <html lang="fi" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Yhteydenottosi on vastaanotettu!</title>
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
                <h1 style="margin:0;padding:1em 0 .5em 0;font-size:1.1em;font-weight:300;">Yhteydenottosi on vastaanotettu! Vastaamme mahdollisimman pian.</h1>
            </td>
        </tr>
        </table>
    </body>
    </html>';

    return $email_html;
}
?>
