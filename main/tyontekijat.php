<?php 
session_start();
require("../includes/header.php");
require("../includes/token.php");
$formTokenInput = createFormTokenInput();
?>
<div class="container flex-container-2-col">
    <div class="main-section">
        <section class="main-content pos-relative text-left-effect">
            <h1>Kenttätyössä mu<span class="underline-effect">kana</span></h1>
            <p class="pos-relative"><img class="bg-illustration bg-blob-employee pos-absolute" src="Images/Icons/Blobs/blob_employee.svg"/>Yrityksen esihenkilöt ovat aktiivisesti mukana työnteossa myös kentällä. Näin olemme jatkuvasti ajantasalla myös siitä, millaisessa ympäristössä meidän hoitajamme työskentelevät. Kun ymmärrämme miten kentällä menee, osaamme johtaa paremmin. Työntekijöidemme hyvinvointi on <em>ykkösprioriteettimme</em>.</p>
            <h2>Vapaus vai<span class="underline-effect">kuttaa</span></h2>
            <p>Haluatko itse vaikuttaa siihen, milloin ja missä työskentelet?</p>
            <p>Oletpa sitten kokenut sosiaali- ja terveydenhuollon ammattilainen tai alan opiskelija, kerro meille toiveesi. Keikkatyössä Sinulla on vapaus valita ja <em>mahdollisuus keskittyä kaikista tärkeimpiin eli asiakkaisiin</em>! Meillä jokainen työntekijä on tärkeä, ja haluamme myös Sinun olevan yksi meidän kasvavassa parvessa, osa tätä kasvutarinaa. Saat kokeneet ja palveluhenkiset esihenkilöt tueksi, ja autamme Sinua erilaisissa työsuhteen aikana tai ennen työsudetta heräävissä kysymyksissä.</p>
            <p>Tulitpa sitten sijaiseksi yhteen työvuoroon tai pidemmäksi aikaa, <em>saat tukea ja opastusta työhösi</em>. Keikkatyö on palkitsevaa ja sopii erilaisiin elämäntilanteisiin.</p>
            <p>Meillä työskennellessäsi Sinulla on <em>vapaus vaikuttaa</em>. Keikkatyöskentely onkin mahtava vaihtoehto, mikäli etsit työtä esimerkiksi oman työn rinnalle, haluat jatkaa eläkkeelle jäätyäsi töitä omien menojen ja aikataulun mukaan tai opiskelijana haluat oman alasi työkokemusta.</p>
            <p>Ties vaikka löydät sattumalta tulevan vakityösi keikkaillessa!<span class="text-end-effect"></span></p>
        </section>
    </div>
    
    <div class="form-container">
        <section>
            <h2>Liity kasvavaan par<span class="underline-effect">veemme!</span></h2>
            <form method="POST" class="form employee-form" novalidate="novalidate">
                <?= $formTokenInput ?>
                <div id="name-group" class="form-section flex-column">
                    <label for="name">
                        <span>Nimi </span>
                        <abbr title="pakollinen" aria-label="pakollinen">*</abbr>
                    </label>
                    <input type="text" name="eName" id="name">
                </div>
                <div id="email-group" class="form-section flex-column">
                    <label for="email">
                        <span>Sähköposti </span>
                        <abbr title="pakollinen" aria-label="pakollinen">*</abbr>
                    </label>
                    <input type="email" name="eEmail" id="email">
                </div>
                <div id="phone-group" class="form-section flex-column">
                    <label for="phone">
                        <span>Puhelinnumero </span>
                        <abbr title="pakollinen" aria-label="pakollinen">*</abbr>
                    </label>
                    <input type="tel" name="ePhone" id="phone">
                </div>
                <div id="jobTitle-group" class="form-section flex-column">
                    <label for="jobTitle">
                        <span>Ammattinimike </span>
                        <abbr title="pakollinen" aria-label="pakollinen">*</abbr>
                    </label>
                    <input type="text" name="eJobTitle" id="jobTitle" list="jobTitles">
                    <datalist id="jobTitles">
                        <option>Lähihoitaja</option>
                        <option>Lähihoitajaopiskelija</option>
                        <option>Terveydenhoitaja</option>
                        <option>Terveydenhoitajaopiskelija</option>
                        <option>Sairaanhoitaja</option>
                        <option>Sairaanhoitajaopiskelija</option>
                        <option>Sosionomi</option>
                        <option>Geronomi</option>
                        <option>Hoiva-avustaja</option>
                    </datalist>
                </div>
                <div id="workTimes-group" class="form-section">
                    <fieldset>
                        <legend>Minkälaista työtä haet?</legend>
                        <ul>
                            <li>
                                <label for="dayWork">Päivätyö</label>
                                <input type="checkbox" id="dayWork" name="eWorkTime" value="Päivätyö">
                            </li>
                            <li>
                                <label for="eveningWork">Iltatyö</label>
                                <input type="checkbox" id="eveningWork" name="eWorkTime" value="Iltatyö">
                            </li>
                            <li>
                                <label for="nightWork">Yötyö</label>
                                <input type="checkbox" id="nightWork" name="eWorkTime" value="Yötyö">
                            </li>
                        </ul>
                    </fieldset>
                </div>
                <?php
                    include("../includes/employee_form.php");
                ?>
                <div id="love-group" class="form-section">
                    <fieldset>
                        <legend>Onko sinulla voimassa olevat LOVe-lääkeluvat?</legend>
                        <ul>
                            <li>
                                <label for="loveY">Kyllä</label>
                                <input type="radio" id="loveY" name="eLove" value="y">
                            </li>
                            <li>
                                <label for="loveN">Ei</label>
                                <input type="radio" id="loveN" name="eLove" value="n">
                            </li>
                        </ul>
                    </fieldset>
                </div>
                <div id="driverLicense-group" class="form-section">
                    <fieldset>
                        <legend>Onko sinulla voimassa oleva B-ajokortti?</legend>
                        <ul>
                            <li>
                                <label for="driverLicenseY">Kyllä</label>
                                <input type="radio" id="driverLicenseY" name="eDriverLicense" value="y">
                            </li>
                            <li>
                                <label for="driverLicenseN">Ei</label>
                                <input type="radio" id="driverLicenseN" name="eDriverLicense" value="n">
                            </li>
                        </ul>
                    </fieldset>
                </div>
                <div id="about-group" class="form-section flex-column">
                    <label for="about">
                        <span>Kerro itsestäsi </span>
                        <abbr title="pakollinen" aria-label="pakollinen">*</abbr>
                    </label>
                    <textarea name="eAbout" id="about"></textarea>
                    <small class="textarea-counter" id="aboutLen">0/2000</small>
                </div>
                <div id="gdprConsent-group" class="form-section">
                    <div class="form-gdpr">
                       <ul>
                            <li>
                                <label for="GDPR">
                                    <span>Olen lukenut ja hyväksyn <a href="tietosuojaseloste">tietosuojaselosteen</a> </span>
                                </label>
                            </li>
                            <li>
                                <input type="checkbox" id="GDPR" name="eGDPR" value="accepted">
                            </li>
                        </ul>
                    </div>
                </div>
                <button class="btn btn-main"><span id="formBtnText">Lähetä työhakemus</span></button>
            </form>
        </section>
    </div>
</div>
<?php include("../includes/footer.php") ?>