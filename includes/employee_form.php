<?php
// Db config
// Gets $connection variable
$dbStatus = include("config.php");

echo '<div id="municipality-group" class="form-section flex-column">
        <label for="municipality">
            <span>Kunta / kaupunki </span>
            <abbr title="pakollinen" aria-label="pakollinen">*</abbr>
        </label>
        <small class="municipality-help">Kirjoita haluamasi kunta tai kaupunki kenttään ja paina Enter-nappia tai klikkaa Plus (+) -painiketta lisätäksesi sen.</small>

        <div class="tag-container">
            <div class="tags">
            </div>
        </div>';

// If database connection is OK
if($dbStatus === "OK") {
    echo '<div class="input-with-add-wrapper">
    <input class="input-with-add" type="text" name="eMunicipality" id="municipality" list="municipalityList">
    <div id="add" class="btn-add">+</div>
    </div>
    <datalist id="municipalityList">';

    $sql = "SELECT municipality FROM municipalities";

    $stmt = $connection->prepare($sql);
    $stmt->execute();

    $stmt->bind_result($municipality);

    while($stmt->fetch()) {
        echo "<option>".$municipality."</option>";
    }

    echo "</datalist></div>";
} else {
    echo '<input type="text" name="eMunicipality" id="municipality">
    </div>';
}
?>