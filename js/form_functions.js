function validateTextInput(input, element, strings, maxLength, preg = "") {
    $("#" + element + "-group").removeClass("has-error");
    $("#" + element).removeClass("error");
    $("#" + element + "-group .error-notification").remove();

    input = input.trim();

    if (input === "") {
        $("#" + element + "-group").addClass("has-error");
        $("#" + element).addClass("error");
        $("#" + element + "-group").append('<div class="error-notification">' + strings[0] + ' on pakollinen.</div>');
        errors[element] = true;
        return false;
    }

    if (preg !== "") {
        if (!preg.test(input)) {
            $("#" + element + "-group").addClass("has-error");
            $("#" + element).addClass("error");
            $("#" + element + "-group").append('<div class="error-notification">' + strings[1] + '</div>');
            errors[element] = true;
            return false;
        }
    }

    if (input.length > maxLength) {
        $("#" + element + "-group").addClass("has-error");
        $("#" + element).addClass("error");
        $("#" + element + "-group").append('<div class="error-notification">' + strings[2] + ' maksimipituus on ' + maxLength + ' kirjainta. Olet kirjoittanut ' + input.length + ' kirjainta.</div>');
        errors[element] = true;
        return false;
    }

    delete errors[element];
    return true;
}

function validateCheckboxInput(input, element, error) {
    $("#" + element + "-group").removeClass("has-error");
    $("input[name='" + input + "']").removeClass("error");
    $("#" + element + "-group .error-notification").remove();

    checkedValues = [];

    $("input[name='" + input + "']:checked").each(function () {
        checkedValues.push($(this).val());
    });

    if (checkedValues.length == 0) {
        $("#" + element + "-group").addClass("has-error");
        $("input[name='" + input + "']").addClass("error");
        $("#" + element + "-group").append('<div class="error-notification">' + error + '</div>');
        errors[element] = true;
        return false;
    }

    delete errors[element];
    return true;
}

function validateRadioInput(input, element, error) {
    $("#" + element + "-group").removeClass("has-error");
    $("input[name='" + input + "']").removeClass("error");
    $("#" + element + "-group .error-notification").remove();

    if ($("input[name='" + input + "']:checked").length <= 0) {
        $("#" + element + "-group").addClass("has-error");
        $("input[name='" + input + "']").addClass("error");
        $("#" + element + "-group").append('<div class="error-notification">' + error + '</div>');
        errors[element] = true;
        return false;
    }

    delete errors[element];
    return true;
}

function validateTagBox(input, element, tags, error) {
    $("#" + element + "-group").removeClass("has-error");
    $("#" + element).removeClass("error");
    $("#" + element + "-group .error-notification").remove();

    input = input.trim();

    if (tags.length == 0) {
        $("#" + element + "-group").addClass("has-error");
        $("#" + element).addClass("error");
        $("#" + element + "-group").append('<div class="error-notification">' + error + '</div>');
        errors[element] = true;
        return false;
    }

    delete errors[element];
    return true;
}

function checkTagBoxUnselectedInput(input, selectedInputsList, validInputsList) {
    if (input !== "" && input.match(preg_alpha)) {
        input = input[0].toUpperCase() + input.slice(1).toLowerCase();

        // Input is not selected already and it is found from valid inputs 
        if (jQuery.inArray(input, selectedInputsList) === -1 && jQuery.inArray(input, validInputsList) !== -1) {
            return {warning: true, tagInput: input};
        } else {
            return {warning: false};
        }
    } else {
        return {warning: false};
    }
}

// Input cleaning
function stripExtraSpaces(input) {
    input = input.replace(/\s+/g, " ").trim();
    input = input.replace(/\s*-\s*/g, "-");
    return input;
}

function capitalizeFirstLetter(input) {
    if (input !== "") {
        input = input.toLowerCase().replace(/(^[A-z\u00C0-\u00ff])/, match => match.toUpperCase());
    }

    return input;
}

function capitalizeEveryWord(input) {
    if (input !== "") {
        input = input.toLowerCase().replace(/(^[A-z\u00C0-\u00ff]|\s[A-z\u00C0-\u00ff]|-[A-z\u00C0-\u00ff])/g, match => match.toUpperCase());
    }
    
    return input;
}

// Textarea wordcounter
function updateTextareaLength(textarea, charCounter, maxLength) {
    textLength = $("#" + textarea).val().length;
    text = $("#" + textarea).val();

    if (textLength > maxLength) {
        text = text.substr(0, maxLength);
        $("#" + textarea).val(text);
    } else {
        $("#" + charCounter).text(textLength + "/" + maxLength);
    }
}