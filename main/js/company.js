// Max lengths
var maxCompanyLength = 50;
var maxNameLength = 40;
var maxBusinessIdLength = 9;
var maxEmailLength = 60;
var maxPhoneLength = 13;
var maxNeedsLength = 2000;

// Regular expression
var preg_alpha = /^([ A-z\u00C0-\u00ff]([A-z\u00C0-\u00ff]-[A-z\u00C0-\u00ff])?)+$/;
var preg_email = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
var preg_phone = /^(040|041|042|043|044|045|046|049|050)\d{4,10}$/;
var preg_businessId = /^\d{7}\-\d{1}$/;

var errors = {};
var submitAllowed = true;

$(document).ready(function () {
    updateTextareaLength("needs", "needsLen", maxNeedsLength);

    // Input validations and cleaning
    $("#company").on("focusout keyup", function (e) {
        if(e.keyCode != 9) {
            validateTextInput($("#company").val(), "company", strings = ["Yritys / kunta", "Yritykseen / kuntaan vain kirjaimia ja väliviivoja (-)", "Yrityksen / kunnan"], maxCompanyLength, preg_alpha);
        }
    });

    $("#company").focusout(function () {
        $("#company").val(stripExtraSpaces($("#company").val()));
    });

    $("#businessId").on("focusout keyup", function (e) {
        if(e.keyCode != 9) {
            validateTextInput($("#businessId").val(), "businessId", strings = ["Y-tunnus", "Anna kelvollinen Y-tunnus", "Y-tunnukseen"], maxBusinessIdLength, preg_businessId);
        }
    });

    $("#businessId").focusout(function () {
        $("#businessId").val($("#businessId").val().trim());
    });

    $("#name").on("focusout keyup", function (e) {
        if(e.keyCode != 9) {
            validateTextInput($("#name").val(), "name", strings = ["Nimi", "Anna kelvollinen nimi", "Nimen"], maxNameLength, preg_alpha);
        }
    });

    $("#name").focusout(function () {
        $("#name").val(stripExtraSpaces(capitalizeEveryWord($("#name").val())));
    });

    $("#email").on("focusout keyup", function (e) {
        if(e.keyCode != 9) {
            validateTextInput($("#email").val(), "email", strings = ["Sähköposti", "Anna kelvollinen sähköposti", "Sähköpostin"], maxEmailLength, preg_email);
        }
    });

    $("#email").focusout(function () {
        $("#email").val($("#email").val().trim());
    });

    $("#phone").on("focusout keyup", function (e) {
        if(e.keyCode != 9) {
            validateTextInput($("#phone").val().replace("+358", "0"), "phone", strings = ["Puhelinnumero", "Anna kelvollinen puhelinnumero", "Puhelinnumeron"], maxPhoneLength, preg_phone);
        }
    });

    $("#phone").focusout(function () {
        $("#phone").val($("#phone").val().trim());
    });

    $("#needs").on("focusout keyup", function (e) {
        if(e.keyCode != 9) {
            updateTextareaLength("needs", "needsLen", maxNeedsLength);
            validateTextInput($("#needs").val(), "needs", strings = ["Palvelutarve", "Palvelutarve"], maxNeedsLength);
        }
    });

    $("#needs").focusout(function () {
        $("#needs").val(stripExtraSpaces($("#needs").val()));
        updateTextareaLength("needs", "needsLen", maxNeedsLength);
    });

    $("input[name='cGDPR']").change(function () {
        validateCheckboxInput("cGDPR", "gdprConsent", "Lue ja hyväksy tietosuojaseloste.");
    });

    $("form").submit(function (event) {
        if(!submitAllowed) {
            return false;
        }

        submitAllowed = false;

        validateTextInput($("#company").val(), "company", strings = ["Yritys / kunta", "Yritykseen / kuntaan vain kirjaimia ja väliviivoja (-)", "Yrityksen / kunnan"], maxCompanyLength, preg_alpha);
        validateTextInput($("#businessId").val(), "businessId", strings = ["Y-tunnus", "Anna kelvollinen Y-tunnus", "Y-tunnukseen"], maxBusinessIdLength, preg_businessId);
        validateTextInput($("#name").val(), "name", strings = ["Nimi", "Anna kelvollinen nimi", "Nimen"], maxNameLength, preg_alpha);
        validateTextInput($("#email").val(), "email", strings = ["Sähköposti", "Anna kelvollinen sähköposti", "Sähköpostin"], maxEmailLength, preg_email);
        validateTextInput($("#phone").val().replace("+358", "0"), "phone", strings = ["Puhelinnumero", "Anna kelvollinen puhelinnumero", "Puhelinnumeron"], maxPhoneLength, preg_phone);
        validateTextInput($("#needs").val(), "needs", strings = ["Palvelutarve", "Palvelutarve"], maxNeedsLength);
        validateCheckboxInput("cGDPR", "gdprConsent", "Lue ja hyväksy tietosuojaseloste.");

        if(document.getElementById("formSuccess") === null) {
            $("form").append('<div id="formSuccess" class="success"></div>').fadeIn();
        }

        if (!jQuery.isEmptyObject(errors)) {
            $("html, body").animate({
                scrollTop: $(".has-error").first().offset().top
            }, 250);

            $(".has-error").first().focus();

            $("#formSuccess").text('Korjaathan virheet!').addClass("error");
            submitAllowed = true;
            return false;
        }

        $(".form-section").removeClass("has-error error");
        $(".form-section input").removeClass("error");
        $(".form-section textarea").removeClass("error");
        $(".error-notification").remove();
        $("#formSuccess").hide();
        $("#formBtnText").text("Lähetetään").addClass("has-loader");

        if (typeof $("#GDPR:checked").val() !== "undefined") {
            var gdpr = "y";
        } else {
            var gdpr = "n";
        }

        var formData = {
            formToken: $("#formToken").val(),
            company: $("#company").val(),
            businessId: $("#businessId").val(),
            name: $("#name").val(),
            email: $("#email").val(),
            phone: $("#phone").val(),
            needs: $("#needs").val(),
            gdprConsent: gdpr,
        };

        $.ajax({
            type: "POST",
            url: "company_form_handler.php",
            data: formData,
            dataType: "json",
            encode: true,
        }).done(function (data) {
            if (!data.success) {
                submitAllowed = true;
                $("#formSuccess").addClass("error").fadeIn();
                $("#formBtnText").text("Lähetä tilaushakemus").removeClass("has-loader");
                if (!$.isEmptyObject(data.inputErrors) || data.inputErrors.length !== 0) {
                    $("#formSuccess").text("Korjaathan virheet.");

                    for (let i = 0; i < Object.keys(data.inputErrors).length; ++i) {
                        $("#" + Object.keys(data.inputErrors)[i] + "-group").addClass("has-error");
                        if (Object.keys(data.inputErrors)[i] == "gdprConsent") {
                            $("#GDPR").addClass("error");
                        } else {
                            $("#" + Object.keys(data.inputErrors)[i]).addClass("error");
                        }
                        $("#" + Object.keys(data.inputErrors)[i] + "-group").append('<div class="error-notification">' + Object.values(data.inputErrors)[i] + '</div>');
                    }

                    $("html, body").animate({
                        scrollTop: $(".has-error").first().offset().top
                    }, 250);

                    $(".error").first().focus();
                }

                if (data.errors.db) {
                    $("#formSuccess").text(data.errors.db);
                }

            } else {
                submitAllowed = true;

                $("form").trigger("reset");

                if(!data.formResubmission) {
                    $("#formToken").val(data.form_token);
                }

                $("#formSuccess").text(data.success_msg).removeClass("error").fadeIn();
                $("#formBtnText").text("Lähetä tilaushakemus").removeClass("has-loader");
                
                setTimeout(function() {
                    $('#formSuccess').fadeOut("slow", function() {
                        $(this).remove();
                    });
                }, 8000);
            }

        }).fail(function () {
            submitAllowed = true;
            $("#formSuccess").text("Jokin meni vikaan, yritäthän myöhemmin uudelleen.").addClass("error").fadeIn();
            $("#formBtnText").text("Lähetä tilaushakemus").removeClass("has-loader");
        });

        event.preventDefault();
    });
});