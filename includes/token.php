<?php
function formToken() {
    return sha1(mt_rand());
}

function initFormToken() {
    if(isset($_SESSION['form_token'])) {
        return $_SESSION['form_token'];
    }

    $formToken = formToken();
    $_SESSION['form_token'] = $formToken;
    return $formToken;
}

function createFormTokenInput() {
    $formToken = initFormToken();
    return '<input type="hidden" id="formToken" name="formToken" value="'.$formToken.'">';
}
?>