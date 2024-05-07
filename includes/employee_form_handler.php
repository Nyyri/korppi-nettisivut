<?php
// Tarkistetaan onko pyyntö tullut lomakkeelta (ei vain url kautta)
if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location:./index.php");
    exit();
}

session_start();

$formResubmission = false;

$errors = [];
$inputErrors = [];
$data = [];

$preg_alpha = "/^([ \p{Latin}]([\p{Latin}]-[\p{Latin}])?)+$/";
$preg_email = '/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/';
$preg_phone = "/^(040|041|042|043|044|045|046|049|050)\d{4,10}$/";

// Input max lengths and additional sanitazion vars
$MAX_WORKTYPES = 3;
$MAX_NAME_LENGTH = 40;
$MAX_EMAIL_LENGTH = 60;
$MAX_PHONE_LENGTH = 13;
$MAX_JOB_TITLE_LENGTH = 40;
$MAX_MUNICIPALITY_LENGTH = 40;
$MAX_ABOUT_LENGTH = 2000;

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
        
        if(!isset($_POST['jobTitle']) || $_POST['jobTitle'] === "") {
            $inputErrors['jobTitle'] = "Ammattinimike on pakollinen.";
        } else if(!preg_match($preg_alpha, str_replace(' ', '', $_POST['jobTitle']))) {
            $inputErrors['jobTitle'] = "Ammattinimikkeeseen vain kirjaimia.";
        } else if(mb_strlen($_POST['jobTitle'], "UTF-8") > $MAX_JOB_TITLE_LENGTH) {
            $inputErrors['jobTitle'] = "Ammattinimikkeen maksimipituus on $MAX_JOB_TITLE_LENGTH kirjainta. Olet kirjoittanut ".mb_strlen($_POST['jobTitle'], "UTF-8")." kirjainta.";
        } else {
            $jobTitle = clean_input(ucfirst(strtolower($_POST['jobTitle'])));
        }
        
        if(!isset($_POST['workTimes']) || empty($_POST['workTimes'])) {
            $inputErrors['workTimes'] = "Valitse minkälaista työtä haet.";
        } else if(count($_POST['workTimes']) > $MAX_WORKTYPES) {
            $data['success'] = false;
            return;
        } else {
            $worktimes = $_POST['workTimes'];
    
            $stmt = $connection ->
            prepare("SELECT COUNT(1), worktimeID FROM worktimes WHERE worktime = ?");
    
            $worktimeIds = [];
            $i = 0;
            foreach($worktimes as $worktime) {
                $stmt -> bind_param("s", $worktime);
                $stmt -> execute();
                $stmt -> bind_result($count, $worktimeID);
                $stmt -> fetch();
                if($count === 1) {
                    $worktimeIds[] = $worktimeID;
                } else {
                    $stmt -> close();
                    return;
                }
    
                // last iteration -> close statement
                if(++$i === count($worktimes)) {
                    $stmt -> close();
                }
            }
        }
        
        if(!isset($_POST['municipalities']) || $_POST['municipalities'] === "") {
            $inputErrors['municipality'] = "Kunta on pakollinen.";
        } else {
            // Remove duplicates
            $municipalities = array_values(array_unique($_POST['municipalities']));
    
            $stmt = $connection ->
            prepare("SELECT COUNT(1), municipalityID FROM municipalities WHERE municipality = ?");
    
            $municipalityIds = [];
            $i = 0;
            foreach($municipalities as $municipality) {
                $stmt -> bind_param("s", $municipality);
                $stmt -> execute();
                $stmt -> bind_result($count, $municipalityID);
                $stmt -> fetch();
                if($count === 1) {
                    $municipalityIds[] = $municipalityID;
                } else {
                    $stmt -> close();
                    $inputErrors['municipality'] = $municipality." ei ole listassa.";
                    break;
                }
    
                // last iteration -> close statement
                if(++$i === count($municipalities)) {
                    $stmt -> close();
                }
            }
        }
        
        if(!isset($_POST['love']) || empty($_POST['love'])) {
            $inputErrors['love'] = "Valitse kyllä tai ei.";
        } else if($_POST['love'] !== "y" && $_POST['love'] !== "n") {
            return;
        } else if($_POST['love'] === "y") {
            $love = 1;
        } else {
            $love = 0;
        }
        
        if(!isset($_POST['driverLicense']) || empty($_POST['driverLicense'])) {
            $inputErrors['driverLicense'] = "Valitse kyllä tai ei.";
        } else if($_POST['driverLicense'] !== "n" && $_POST['driverLicense'] !== "y") {
            return;
        } else if($_POST['driverLicense'] === "y") {
            $driverLicense = 1;
        } else {
            $driverLicense = 0;
        }
        
        if(!isset($_POST['about']) || $_POST['about'] === "") {
            $inputErrors['about'] = "Kerro itsestäsi on pakollinen.";
        } else if(mb_strlen($_POST['about'], "UTF-8") > $MAX_ABOUT_LENGTH) {
            $inputErrors['about'] = "Kerro itsestäsi maksimipituus on $MAX_ABOUT_LENGTH kirjainta. Olet kirjoittanut ".mb_strlen($_POST['about'], "UTF-8")." kirjainta.";
        } else {
            $about = clean_input($_POST['about']);
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
    prepare("INSERT INTO e_application 
    (name, email, phone, job_title, love, driver_license, about, gdpr) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt_worktimes = $connection ->
    prepare("INSERT INTO e_worktimes (eID, worktimeID) VALUES (?, ?)");

    $stmt_municipalities = $connection ->
    prepare("INSERT INTO e_municipalities (eID, municipalityID) VALUES (?, ?)");

    $stmt_application -> bind_param("ssssiisi", $name, $email, $phone, $jobTitle, $love, $driverLicense, $about, $gdpr);
    $stmt_worktimes -> bind_param("ii", $eID, $selection);
    $stmt_municipalities -> bind_param("ii", $eID, $municipality);

    $connection -> begin_transaction();

    if($stmt_application -> execute()) {
        $saved = true;

        $eID = $connection -> insert_id;

        // Loop through each selected worktime and try SQL insertion
        // with latest inserted employee application ID
        foreach($worktimeIds as $selection) {
            if($stmt_worktimes -> execute()) {
                $saved = true;
            } else {
                $saved = false;
                $errors['db'] = "Palvelimeen ei juuri nyt saada yhteyttä, yritä myöhemmin uudelleen. Virhekoodi: W1";
                break;
            }
        }

        // Loop through each municipality and try SQL insertion
        // with latest inserted employee application ID
        foreach($municipalityIds as $municipality) {
            if($stmt_municipalities -> execute()) {
                $saved = true;
            } else {
                $saved = false;
                $errors['db'] = "Palvelimeen ei juuri nyt saada yhteyttä, yritä myöhemmin uudelleen. Virhekoodi: M1";
                break;
            }
        }

    } else {
        $saved = false;
        $errors['db'] = "Palvelimeen ei juuri nyt saada yhteyttä, yritä myöhemmin uudelleen. Virhekoodi: I1";
    }

    // If the SQL queries are succesful
    if($saved) {
        if($connection -> commit()) {
            $to = "email@address.com";
            $subject = "Työhakemus - ".$name."";
            $message = compose_email($name, $phone, $email, $jobTitle, $worktimes, $municipalities, $love, $driverLicense, $about);
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
    $data['success_msg'] = "Työhakemuksesi on vastaanottettu!";
} else if(!empty($errors) || !empty($inputErrors)) {
    $data['success'] = false;
    $data['errors'] = $errors;
    $data['inputErrors'] = $inputErrors;
} else {
    $data['form_token'] = formToken();
    $_SESSION['form_token'] = $data['form_token'];

    $data['success'] = true;
    $data['success_msg'] = "Työhakemuksesi on vastaanottettu!";
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

function compose_email($name, $email, $phone, $title, $worktimes, $municipalities, $love, $license, $about) {
    $worktimes_html = "";
    $municipalities_html = "";
    $permissions_html = "";
    
    foreach($worktimes as $worktime) {
        $worktimes_html .= '<tr><td style="padding:0;vertical-align: top;">
        <p style="font-size:.85em;margin: 0;padding: 0;box-sizing: border-box;padding-bottom: .25rem;">
        '.$worktime.'
        </p></td></tr>
        <tr>
            <td style="padding:0 0 .3em 0;font-size:0;line-height:0;">&nbsp;</td>
        </tr>';
    }

    foreach($municipalities as $municipality) {
        $municipalities_html .= '<tr><td style="padding:0;vertical-align: top;">
        <p style="font-size:.85em;margin: 0;padding: 0;box-sizing: border-box;padding-bottom: .25rem;">
        '.$municipality.'
        </p></td></tr>
        <tr>
            <td style="padding:0 0 .3em 0;font-size:0;line-height:0;">&nbsp;</td>
        </tr>';
    }

    if($love === 1) {
        $permissions_html .= '<tr><td style="padding:0;vertical-align: top;">
        <p style="font-size:.85em;margin: 0;padding: 0;box-sizing: border-box;padding-bottom: .25rem;">LOVE-lääkeluvat
        </p></td></tr>
        <tr>
            <td style="padding:0 0 .3em 0;font-size:0;line-height:0;">&nbsp;</td>
        </tr>';
    }

    if($license === 1) {
        $permissions_html .= '<tr><td style="padding:0;vertical-align: top;">
        <p style="font-size:.85em;margin: 0;padding: 0;box-sizing: border-box;padding-bottom: .25rem;">B-ajokortti
        </p></td></tr>
        <tr>
            <td style="padding:0 0 .3em 0;font-size:0;line-height:0;">&nbsp;</td>
        </tr>';
    }

    if($love !== 1 && $license !== 1) {
        $permissions_html .= '<tr><td style="padding:0;vertical-align: top;">
        <p style="font-size:.85em;margin: 0;padding: 0;box-sizing: border-box;padding-bottom: .25rem;">-
        </p></td></tr>
        <tr>
            <td style="padding:0 0 .3em 0;font-size:0;line-height:0;">&nbsp;</td>
        </tr>';
    }

    $email_html =
    '<!DOCTYPE html>
    <html lang="fi" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Työhakemus - '.$name.'</title>
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
                    <h1 style="margin:0;padding:1em 0 .5em 0;font-size:1.1em;font-weight:300;">Työhakemus</h1>
                </td>
                <tr>
                    <td style="padding:0 0 1em 0;font-size:0;line-height:0;">&nbsp;</td>
                </tr>
                <td style="padding:0;word-wrap:break-word;">
                    <h2 style="margin:0;padding:0 0 .1em 0;font-size:1.8em;">'.$name.', '.$title.'</h2>
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
        <table role="presentation" style="width:100%;table-layout:fixed;border-collapse:collapse;border:0;border-spacing:0;">
            <tr>
                <td style="padding:0;vertical-align: top;">
                    <table role="presentation" style="width:100%;table-layout:fixed;border-collapse:collapse;border:0;border-spacing:0;">
                        <tr>
                            <td style="padding:0;vertical-align: top;">
                                <h3 style="margin:0;padding:0 0 .25em 0;font-weight:400;font-size:1.1em;">Kunnat</h3>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 0 .3em 0;font-size:0;line-height:0;">&nbsp;</td>
                        </tr>
                        '.$municipalities_html.'
                    </table>
                </td>
                <td style="padding:0;vertical-align: top;">
                    <table role="presentation" style="width:100%;table-layout:fixed;border-collapse:collapse;border:0;border-spacing:0;">
                        <tr>
                            <td style="padding:0;vertical-align: top;">
                                <h3 style="margin:0;padding:0 0 .25em 0;font-weight:400;font-size:1.1em;">Luvat</h3>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 0 .3em 0;font-size:0;line-height:0;">&nbsp;</td>
                        </tr>
                        '.$permissions_html.'
                    </table>
                </td>
                <td style="padding:0;vertical-align: top;">
                    <table role="presentation" style="width:100%;table-layout:fixed;border-collapse:collapse;border:0;border-spacing:0;">
                        <tr>
                            <td style="padding:0;vertical-align: top;">
                                <h3 style="margin:0;padding:0 0 .25em 0;font-weight:400;font-size:1.1em;">Työajat</h3>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:0 0 .3em 0;font-size:0;line-height:0;">&nbsp;</td>
                        </tr>
                        '.$worktimes_html.'
                    </table>
                </td>
            </tr>
            <tr>
                <td style="padding:0 0 1.5em 0;font-size:0;line-height:0;">&nbsp;</td>
            </tr>
        </table>
        <table role="presentation" style="table-layout:fixed;width:100%;border-collapse:collapse;border:0;border-spacing:0;">
            <tr>
                <td style="padding:0;word-wrap:break-word;">
                    <h2 style="margin:0;padding:1em 0 .5em 0;font-size: 1.5em;font-weight:100;">Hieman minusta</h1>
                </td>
            </tr>
            <tr>
                <td style="padding:0 0 .3em 0;font-size:0;line-height:0;">&nbsp;</td>
            </tr>
            <tr>
                <td style="padding:0;word-wrap:break-word;">
                    <p style="font-size:.9em;margin:0;padding:0;">'.$about.'</p>
                </td>
            </tr>
        </table>
    </body>
    </html>';

    return $email_html;
}
?>