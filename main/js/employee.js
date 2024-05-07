// Max lengths
var maxNameLength = 40;
var maxEmailLength = 60;
var maxPhoneLength = 13;
var maxJobTitleLength = 40;
var maxAboutLength = 3000;

var municipalities = [];
var municipalityDatalist = [];
var workTimes = [];

// Regular expression
var preg_alpha = /^([ A-z\u00C0-\u00ff]([A-z\u00C0-\u00ff]-[A-z\u00C0-\u00ff])?)+$/;
var preg_email = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
var preg_phone = /^(040|041|042|043|044|045|046|049|050)\d{4,10}$/;

var errors = {};
var submitAllowed = true;

var navHeightFix = 100;

function addMunicipalityTag() {
    $("#municipality-group").removeClass("has-error");
    $("#municipality").removeClass("error");
    $("#municipality-group .error-notification").remove();

    var tag = $("#municipality").val().trim();

    if (tag) {
        tag = capitalizeEveryWord(tag);

        // Jos kunta on jo lisätty
        if (municipalities.length != 0 && jQuery.inArray(tag, municipalities) != -1) {
            $("#municipality").val("");
            $("#municipality-group").addClass("has-error");
            $("#municipality").addClass("error");
            $("#municipality-group").append('<div class="error-notification">' + tag + ' on jo valittuna.</div>');
            return;
        }

        if (tag.match(preg_alpha)) {

            // Onko kunta listassa
            if (jQuery.inArray(tag, municipalityDatalist) == -1) {
                $("#municipality-group").addClass("has-error");
                $("#municipality").addClass("error");
                $("#municipality-group").append('<div class="error-notification">' + tag + ' ei ole listassa.</div>');
                return;
            }

            municipalities.push(tag);
            $(".tags").append(
                $("<span/>", {
                    text: tag,
                    class: "tag"
                }).append(
                    $("<span/>", {
                        text: "x",
                        class: "tag-x"
                    })
                )
            );

            $("#municipality").val("");
        } else {
            $("#municipality-group").addClass("has-error");
            $("#municipality").addClass("error");
            $("#municipality-group").append('<div class="error-notification">Vain kirjaimia.</div>');
        }
    } else {
        $("#municipality").val("");
    }
}

$(document).ready(function () {
    updateTextareaLength("about", "aboutLen", maxAboutLength);
    var fromAdd = false;

    $(".tag-container").on('click', '.tag-x', function () {
        tagToRemoveIndex = municipalities.indexOf($(this).parent().text().slice(0, -1));
        municipalities.splice(tagToRemoveIndex, 1);
        $(this).parent().remove();
        validateTagBox($("#municipality").val(), "municipality", municipalities, "Kunta / kaupunki on pakollinen.");
    });

    // Input validations and cleaning
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

    $("#jobTitle").on("focusout keyup", function (e) {
        if(e.keyCode != 9) {
            validateTextInput($("#jobTitle").val(), "jobTitle", strings = ["Ammattinimike", "Ammattinimikkeeseen vain kirjaimia", "Ammattinimikkeen"], maxJobTitleLength, preg_alpha);
        }
    });

    $("#jobTitle").focusout(function () {
        $("#jobTitle").val(stripExtraSpaces(capitalizeFirstLetter($("#jobTitle").val())));
    });

    $("input[name='eWorkTime']").change(function () {
        validateCheckboxInput("eWorkTime", "workTimes", "Valitse minkälaista työtä haet.");
    });

    $("#add").mousedown(function(e) {
        fromAdd = true;
        if(!$("#municipality").is(":focus")) {
            $("#add").css({
                borderTopLeftRadius: "0",
                borderTopRightRadius: ".4rem",
                borderBottomLeftRadius: "0",
                borderBottomRightRadius: ".4rem"})
                .animate({
                borderTopLeftRadius: "0",
                borderTopRightRadius: ".1rem",
                borderBottomLeftRadius: "0",
                borderBottomRightRadius: ".1rem"}, 250);
                e.preventDefault();
        }
        e.preventDefault();
    });

    $("#add").click(function() {
        $("#municipality").blur();
        $("#municipality").focus();
        fromAdd = false;
        addMunicipalityTag();    
    });

    $("#municipality").focusin(function () {
        // Only animate if not added with plus
        if(!fromAdd) {
            $("#add").css({
                borderTopLeftRadius: "0",
                borderTopRightRadius: ".4rem",
                borderBottomLeftRadius: "0",
                borderBottomRightRadius: ".4rem"})
                .animate({
                borderTopLeftRadius: "0",
                borderTopRightRadius: ".1rem",
                borderBottomLeftRadius: "0",
                borderBottomRightRadius: ".1rem"}, 250);
        }
    });

    $("#municipality").focusout(function () {
        // Only animate if not added with plus
        if(!fromAdd) {
            $("#add").css({
                borderTopLeftRadius: "0",
                borderTopRightRadius: ".1rem",
                borderBottomLeftRadius: "0",
                borderBottomRightRadius: ".1rem"})
                .animate({
                borderTopLeftRadius: "0",
                borderTopRightRadius: ".4rem",
                borderBottomLeftRadius: "0",
                borderBottomRightRadius: ".4rem"}, 250);
            validateTagBox($("#municipality").val(), "municipality", municipalities, "Kunta / kaupunki on pakollinen.");
            $("#municipality").val($("#municipality").val().trim());
        }
        
        
    });

    $("input[name='eLove']").change(function () {
        validateRadioInput("eLove", "love", "Valitse kyllä tai ei.");
    });

    $("input[name='eDriverLicense']").change(function () {
        validateRadioInput("eDriverLicense", "driverLicense", "Valitse kyllä tai ei.");
    });

    $("#about").on("focusout keyup", function (e) {
        if(e.keyCode != 9) {
            updateTextareaLength("about", "aboutLen", maxAboutLength);
            validateTextInput($("#about").val(), "about", strings = ["Kerro itsestäsi", "Kerro itsestäsi"], maxAboutLength);
        }
    });

    $("#about").focusout(function () {
        $("#about").val(stripExtraSpaces($("#about").val()));
        updateTextareaLength("about", "aboutLen", maxAboutLength);
    });

    $("input[name='eGDPR']").change(function () {
        validateCheckboxInput("eGDPR", "gdprConsent", "Lue ja hyväksy tietosuojaseloste.");
    });

    $("#municipalityList option").each(function () {
        municipalityDatalist.push($(this).text());
    });

    $("#municipality").keydown(function (event) {
        if (event.keyCode === 13) {
            addMunicipalityTag();
            event.preventDefault();
            return false;
        }
    });

    $("#municipalityWarningY").click(function() {
        console.log("Y");
    });

    $("#municipalityWarningN").click(function() {
        console.log("N");
    });

    $("form").submit(function (event) {
        if(!submitAllowed) {
            return false;
        }

        submitAllowed = false;

        validateTextInput($("#name").val(), "name", strings = ["Nimi", "Anna kelvollinen nimi", "Nimen"], maxNameLength, preg_alpha);
        validateTextInput($("#email").val(), "email", strings = ["Sähköposti", "Anna kelvollinen sähköposti", "Sähköpostin"], maxEmailLength, preg_email);
        validateTextInput($("#phone").val().replace("+358", "0"), "phone", strings = ["Puhelinnumero", "Anna kelvollinen puhelinnumero", "Puhelinnumeron"], maxPhoneLength, preg_phone);
        validateTextInput($("#jobTitle").val(), "jobTitle", strings = ["Ammattinimike", "Ammattinimikkeeseen vain kirjaimia", "Ammattinimikkeen"], maxJobTitleLength, preg_alpha);
        validateCheckboxInput("eWorkTime", "workTimes", "Valitse minkälaista työtä haet.");
        validateTagBox($("#municipality").val(), "municipality", municipalities, "Kunta / kaupunki on pakollinen.");
        validateRadioInput("eLove", "love", "Valitse kyllä tai ei.");
        validateRadioInput("eDriverLicense", "driverLicense", "Valitse kyllä tai ei.");
        validateTextInput($("#about").val(), "about", strings = ["Kerro itsestäsi", "Kerro itsestäsi"], maxAboutLength);
        validateCheckboxInput("eGDPR", "gdprConsent", "Lue ja hyväksy tietosuojaseloste.");

        if(document.getElementById("formSuccess") === null) {
            $("form").append('<div id="formSuccess" class="success"></div>').fadeIn();
        }

        if (!jQuery.isEmptyObject(errors)) {
            $("html, body").animate({
                scrollTop: $(".has-error").first().offset().top - navHeightFix
            }, 250);

            $(".has-error").first().focus();

            $("#formSuccess").text('Korjaathan virheet!').addClass("error");
            submitAllowed = true;
            return false;
        }

        let municipalityTest = checkTagBoxUnselectedInput($("#municipality").val(), municipalities, municipalityDatalist);

        if(municipalityTest.warning) {
            $("#formSuccess").html('<p>Olet kirjoittanut Kuntaan / kaupunkiin <b>' + municipalityTest.tagInput + '</b>, mutta sitä ei ole lisätty valintoihin.</p><p>Haluatko lisätä sen?</p><div class="button-group"><div class="btn btn-success" id="municipalityWarningY">Kyllä</div><div class="btn btn-error" id="municipalityWarningN">Ei</div></div>').removeClass("error").addClass("warning");
            submitAllowed = true;
            return false;
        }

        $(".form-section").removeClass("has-error");
        $(".form-section input").removeClass("error");
        $(".form-section textarea").removeClass("error");
        $(".error-notification").remove();
        $("#formSuccess").hide().removeClass("warning");
        $("#formBtnText").text("Lähetetään").addClass("has-loader");

        workTimes = [];

        $("input[name='eWorkTime']:checked").each(function () {
            workTimes.push($(this).val());
        });

        if (typeof $("#GDPR:checked").val() !== "undefined") {
            var gdpr = "y";
        } else {
            var gdpr = "n";
        }

        var formData = {
            formToken: $("#formToken").val(),
            name: $("#name").val(),
            email: $("#email").val(),
            phone: $("#phone").val(),
            jobTitle: $("#jobTitle").val(),
            workTimes: workTimes,
            municipalities: municipalities,
            love: $("input[name='eLove']:checked").val(),
            driverLicense: $("input[name='eDriverLicense']:checked").val(),
            about: $("#about").val(),
            gdprConsent: gdpr,
        };

        $.ajax({
            type: "POST",
            url: "employee_form_handler.php",
            data: formData,
            dataType: "json",
            encode: true,
        }).done(function (data) {
            if (!data.success) {
                submitAllowed = true;
                $("#formSuccess").addClass("error").fadeIn();
                $("#formBtnText").text("Lähetä työhakemus").removeClass("has-loader");
                if (!$.isEmptyObject(data.inputErrors) || data.inputErrors.length !== 0) {
                    $("#formSuccess").text("Korjaathan virheet.");

                    for (let i = 0; i < Object.keys(data.inputErrors).length; ++i) {
                        $("#" + Object.keys(data.inputErrors)[i] + "-group").addClass("has-error");
                        if (Object.keys(data.inputErrors)[i] == "workTimes") {
                            $("input[name='eWorkTime']").addClass("error");
                        } else if (Object.keys(data.inputErrors)[i] == "love") {
                            $("input[name='eLove']").addClass("error");
                        } else if (Object.keys(data.inputErrors)[i] == "driverLicense") {
                            $("input[name='eDriverLicense']").addClass("error");
                        } else if (Object.keys(data.inputErrors)[i] == "gdprConsent") {
                            $("#GDPR").addClass("error");
                        } else {
                            $("#" + Object.keys(data.inputErrors)[i]).addClass("error");
                        }
                        $("#" + Object.keys(data.inputErrors)[i] + "-group").append('<div class="error-notification">' + Object.values(data.inputErrors)[i] + '</div>');
                    }

                    $("html, body").animate({
                        scrollTop: $(".has-error").first().offset().top - navHeightFix
                    }, 250);

                    $(".error").first().focus();
                }

                if (data.errors.db) {
                    $("#formSuccess").text(data.errors.db);
                }

            } else {
                submitAllowed = true;
                municipalities = [];
                workTimes = [];

                $("form").trigger("reset");
                $(".tags .tag").remove();

                if(!data.formResubmission) {
                    $("#formToken").val(data.form_token);
                }

                $("#formSuccess").text(data.success_msg).removeClass("error").fadeIn();
                $("#formBtnText").text("Lähetä työhakemus").removeClass("has-loader");
                
                setTimeout(function() {
                    $('#formSuccess').fadeOut("slow", function() {
                        $(this).remove();
                    });
                }, 8000);
            }

        }).fail(function () {
            submitAllowed = true;
            $("#formSuccess").text("Jokin meni vikaan, yritäthän myöhemmin uudelleen.").addClass("error").fadeIn();
            $("#formBtnText").text("Lähetä työhakemus").removeClass("has-loader");
        });

        event.preventDefault();
    });
});