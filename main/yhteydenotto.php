<?php 
session_start();
require("../includes/header.php");
require("../includes/token.php");
$formTokenInput = createFormTokenInput();
?>
<div class="container flex-container-2-col">
    <div class="main-section">
        <section class="main-content pos-relative text-left-effect">
            <h1>Mitä miel<span class="underline-effect">essä?</span></h1>
            <p class="pos-relative"><img class="bg-illustration bg-blob-contact" src="./Images/Icons/Blobs/blob_contact.svg"/>Heräsikö Sinulle kysymyksiä tai ajatuksia? <em>Selvitetään se yhdessä</em>!</p>
            <img class="mobile-hidden main-content-illustration"src="./Images/Logos/korvu_questioning_the_universe.png" alt="Korvu ihmettelemässä maailman menoa"/>
        </section>
    </div>
    <div class="form-container">
        <section>
            <h2>Ota yht<span class="underline-effect">eyttä!</span></h2>
            <form class="form company-form" novalidate="novalidate">
                <?= $formTokenInput ?>
                <div id="name-group" class="form-section flex-column">
                    <label for="name">
                        <span>Nimi </span>
                        <abbr title="pakollinen" aria-label="pakollinen">*</abbr>
                    </label>
                    <input type="text" name="name" id="name">
                </div>
                <div id="email-group" class="form-section flex-column">
                    <label for="email">
                        <span>Sähköposti </span>
                        <abbr title="pakollinen" aria-label="pakollinen">*</abbr>
                    </label>
                    <input type="email" name="email" id="email">
                </div>
                <div id="phone-group" class="form-section flex-column">
                    <label for="phone">
                        <span>Puhelinnumero </span>
                    </label>
                    <input type="tel" name="phone" id="phone">
                </div>
                <div id="text-group" class="form-section flex-column">
                    <label for="text">
                        <span>Vapaa teksti </span>
                        <abbr title="pakollinen" aria-label="pakollinen">*</abbr>
                    </label>
                    <textarea name="text" id="text"></textarea>
                    <small class="textarea-counter" id="textLen">0/2000</small>
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
                                <input type="checkbox" id="GDPR" name="GDPR" value="accepted">
                            </li>
                        </ul>
                    </div>
                </div>
                <button class="btn btn-main"><span id="formBtnText">Ota yhteyttä</span></button>
            </form>
        </section>
    </div>
</div>
            

<?php include("../includes/footer.php") ?>