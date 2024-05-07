<?php 
session_start();
require("../includes/header.php");
require("../includes/token.php");
$formTokenInput = createFormTokenInput();
?>
<div class="container flex-container-2-col">
    <div class="main-section">
        <section class="main-content pos-relative text-left-effect">
            <h1>Osaavia sotealan ammattilaisia yksikkönne tar<span class="underline-effect">peisiin</span></h1>
            <p class="pos-relative"><img class="bg-illustration bg-blob-company" src="./Images/Icons/Blobs/blob_company.svg"/>Oli kyse sitten lyhytaikaisesta tarpeesta taikka pitkäkestoisemmasta sijaisuudesta.</p>
            <p><em>Voit turvautua meihin</em> esimerkiksi äkillisen sairastapauksen tai yllättävän henkilöstötarpeen kasvun ilmetessä. Henkilöstövuokrauspalveluidemme avulla löydät vaivattomasti <em>luotettavat sijaiset</em> niin lomien kuin pidempikestoistenkin poissaolojen ajaksi.</p>
            <p>Kuka tietää, saatat jopa löytää meiltä välitettyjen hoitajien joukosta ammattilaisen, jonka haluat palkata vakituiseksi työntekijäksi!</p>
            <p><em>Sopimukset katsotaan aina yhdessä</em> vastavuoroisesti ja tavoitteena on molempia osapuolia tyydyttävä lopputulos.</p>
            <p>Tavoitteenamme on tarjota luotettavaa ja vaivatonta palvelua ja että <em>yhteistyö on tyydyttävää koko matkan ajan</em>!</p>
            <p>Laita siis rohkeasti lomakkeita käyttäen tilausta tarpeistanne jotta voimme lähteä etsimään sinun tarpeitasi vastaavaa ammattilaista.<span class="text-end-effect"></span></p>
            <img class="mobile-hidden main-content-illustration" src="./Images/Stock/customer_support/customer_support_transparent.png" alt="Iloinen asiakaspalvelija puhuu puhelimeen"/>
        </section>
    </div>
    <div class="form-container">
        <section>
            <form class="form company-form" novalidate="novalidate">
                <?= $formTokenInput ?>
                <div id="company-group" class="form-section flex-column">
                    <label for="company">
                        <span>Yritys / kunta </span>
                        <abbr title="pakollinen" aria-label="pakollinen">*</abbr>
                    </label>
                    <input type="text" name="cCompany" id="company">
                </div>
                <div id="businessId-group" class="form-section flex-column">
                    <label for="businessId">
                        <span>Y-tunnus </span>
                        <abbr title="pakollinen" aria-label="pakollinen">*</abbr>
                    </label>
                    <input type="text" name="cBusinessId" id="businessId">
                </div>
                <div id="name-group" class="form-section flex-column">
                    <label for="name">
                        <span>Yhteyshenkilön nimi</span>
                        <abbr title="pakollinen" aria-label="pakollinen">*</abbr>
                    </label>
                    <input type="text" name="cName" id="name">
                </div>
                <div id="email-group" class="form-section flex-column">
                    <label for="email">
                        <span>Yhteyshenkilön sähköposti </span>
                        <abbr title="pakollinen" aria-label="pakollinen">*</abbr>
                    </label>
                    <input type="email" name="cEmail" id="email">
                </div>
                <div id="phone-group" class="form-section flex-column">
                    <label for="phone">
                        <span>Yhteyshenkilön puhelinnumero </span>
                        <abbr title="pakollinen" aria-label="pakollinen">*</abbr>
                    </label>
                    <input type="tel" name="cPhone" id="phone">
                </div>
                <div id="needs-group" class="form-section flex-column">
                    <label for="needs">
                        <span>Kerro henkilöstö- / palvelutarpeistanne </span>
                        <abbr title="pakollinen" aria-label="pakollinen">*</abbr>
                    </label>
                    <textarea name="cNeeds" id="needs"></textarea>
                    <small class="textarea-counter" id="needsLen">0/3000</small>
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
                                <input type="checkbox" id="GDPR" name="cGDPR" value="accepted">
                            </li>
                        </ul>
                    </div>
                </div>
                <button class="btn btn-main"><span id="formBtnText">Lähetä tilaushakemus</span></button>
            </form>
        </section>
    </div>
</div>

<?php include("../includes/footer.php") ?>