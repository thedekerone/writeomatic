<script>
let titleGenerated = false;
let introGenerated = false;
let headingsGenerated = false;
let articleGenerated = false;

function addAdditionalFormData(formData) {
    let contentType = getActiveStepType();

    switch(contentType) {
        case 'introduction':
            const generatedTitleIntro = localStorage.getItem("generatedTitle");
            formData.append('chosen_title', generatedTitleIntro);
            formData.append('iteration_type', 'iterative_introduction');
            break;
        
        case 'headings':
            const generatedTitleHeadings = localStorage.getItem("generatedTitle");
            const generatedIntroductionHeadings = localStorage.getItem("generatedIntroduction");
            formData.append('chosen_title', generatedTitleHeadings);
            formData.append('chosen_intro', generatedIntroductionHeadings);
            formData.append('iteration_type', 'iterative_headings');
            break;

        case 'article':
            const generatedTitleArticle = localStorage.getItem("generatedTitle");
            const generatedIntroductionArticle = localStorage.getItem("generatedIntroduction");
            const generatedHeadingsArticle = localStorage.getItem("generatedHeadings");
            formData.append('chosen_title', generatedTitleArticle);
            formData.append('chosen_intro', generatedIntroductionArticle);
            formData.append('chosen_headings', generatedHeadingsArticle);
            formData.append('iteration_type', 'iterative_article');
            break;

        default:
            formData.append('iteration_type', 'iterative_title');
    }
}

function onTitleGenerated(title) {
    localStorage.setItem("generatedTitle", title);
    titleGenerated = true;
    updateStepAvailability();
    updateButtonVisibility();
}

function onIntroductionGenerated(introduction) {
    localStorage.setItem("generatedIntroduction", introduction);
    introGenerated = true;
    updateStepAvailability();
    updateButtonVisibility();

}

function onHeadingsGenerated(headings) {
    localStorage.setItem("generatedHeadings", headings);
    headingsGenerated = true;
    updateStepAvailability();
    updateButtonVisibility();

}

function onArticleGenerated(article) {
    localStorage.setItem("generatedArticle", article);
    articleGenerated = true;
    updateStepAvailability();
    updateButtonVisibility();

}

function loadContextToEditor() {

    let contentType = getActiveStepType();

    if(contentType === 'title' && titleGenerated) {
        return localStorage.getItem("generatedTitle");
    }
    else if(contentType === 'introduction' && introGenerated) {
        return localStorage.getItem("generatedIntroduction");
    }
    else if (contentType === 'headings' && headingsGenerated) {
        return localStorage.getItem("generatedHeadings");
    }
    else if (articleGenerated) {
        return localStorage.getItem("generatedArticle");
    }

    return "";
}

function saveContextFromEditor(data, contentType) {

    if(contentType === 'step_title') {
        localStorage.setItem("generatedTitle", data);
    }
    else if(contentType === 'step_intro') {
        localStorage.setItem("generatedIntroduction", data);
    }
    else if (contentType === 'step_headings') {
        localStorage.setItem("generatedHeadings", data);
    }
    else {
        localStorage.setItem("generatedArticle", data);
    }
}

function saveContext(responseData) {
    let contentType = getActiveStepType();

    if(contentType === 'title') {
        onTitleGenerated(responseData);
    }
    else if(contentType === 'introduction') {
        onIntroductionGenerated(responseData);
    }
    else if(contentType === 'headings') {
        onHeadingsGenerated(responseData);
    }
    else {
        onArticleGenerated(responseData);
    }

}

function getActiveStepType() {
    // Check the steps and determine which one is active
    if ($('#step_title').hasClass('active')) {
        return 'title';
    } else if ($('#step_intro').hasClass('active')) {
        return 'introduction';
    } else if ($('#step_headings').hasClass('active')) {
        return 'headings';
    } else {
        return 'article';
    }
}

function updateStepAvailability() {
    // If the title has been generated, allow access to introduction
    if (titleGenerated) {
        $('#step_title').addClass('step-completed');
        $('#step_intro').removeClass('disabled-step');
    } else {
        $('#step_intro').addClass('disabled-step');
    }
    
    // If the title and intro have been generated, allow access to headings
    if (titleGenerated && introGenerated) {
        $('#step_intro').addClass('step-completed');
        $('#step_headings').removeClass('disabled-step');
    } else {
        $('#step_headings').addClass('disabled-step');
    }
    
    // If the title, intro, and headings have been generated, allow access to article
    if (titleGenerated && introGenerated && headingsGenerated) {
        $('#step_headings').addClass('step-completed');
        $('#step_article').removeClass('disabled-step');
    } else {
        $('#step_article').addClass('disabled-step');
    }

    if (titleGenerated && introGenerated && headingsGenerated && articleGenerated) {
        $('#step_article').addClass('step-completed');
    }
}
function changeActiveStep(elem) {
    if ($(elem).hasClass('disabled-step')) {
        return;
    }
    const previousStep = $('.clickable-step.active').attr('id');

    $('.clickable-step').removeClass('active');
    $(elem).addClass('active');
    let type = getActiveStepType();
    // Logic for showing/hiding divs
    if (type == 'article') {
        $('#tone_of_voice_div').show();
        $('#number_of_results_div, #creativity_div, #maximum_length_div').hide();
    } else if (type == 'title') {
        $('#language_div').show();
        $('#number_of_results_div, #tone_of_voice_div, #creativity_div, #maximum_length_div').hide();
    } else {
        $('#number_of_results_div, #tone_of_voice_div, #creativity_div, #maximum_length_div, #language_div').hide();
    }

    // Logic to handle content
    const editor = tinyMCE.activeEditor;
    if (editor) {
        // toastr.info(previousStep);
        let myContent = editor.getContent();
        saveContextFromEditor(myContent, previousStep);

        editor.setContent(loadContextToEditor());
    }

    // Update UI based on selected value
    $('#step_title span:first-child, #step_intro span:first-child, #step_headings span:first-child, #step_article span:first-child').css('background-color', '#e0e0e0');
    if (type == 'title') {
        $('#step_title span:first-child').css('background-color', '#330582');
    } else if (type == 'introduction') {
        $('#step_title span:first-child, #step_intro span:first-child').css('background-color', '#330582');
    } else if (type == 'headings') {
        $('#step_title span:first-child, #step_intro span:first-child, #step_headings span:first-child').css('background-color', '#330582');
    } else if (type == 'article') {
        $('#step_title span:first-child, #step_intro span:first-child, #step_headings span:first-child, #step_article span:first-child').css('background-color', '#330582');
    }
    updateButtonVisibility();
}

function updateButtonVisibility() {
    const activeStep = getActiveStepType();
    if (activeStep === 'title' && titleGenerated == false) {
        $("#previous_button").hide();
        $("#next_button").hide();
        $("#openai_generator_button").show();
    } else if (activeStep === 'title' && titleGenerated == true){
        $("#previous_button").hide();
        $("#next_button").show();
        $("#openai_generator_button").hide();
    } else if (activeStep === 'introduction' && introGenerated == false) {
        $("#previous_button").show();
        $("#next_button").hide();
        $("#openai_generator_button").show();
    } else if (activeStep === 'introduction' && introGenerated == true){
        $("#previous_button").show();
        $("#next_button").show();
        $("#openai_generator_button").hide();
    } else if (activeStep === 'headings' && headingsGenerated == false) {
        $("#previous_button").show();
        $("#next_button").hide();
        $("#openai_generator_button").show();
    } else if (activeStep === 'headings' && headingsGenerated == true){
        $("#previous_button").show();
        $("#next_button").show();
        $("#openai_generator_button").hide();
    } else if (activeStep === 'article' && articleGenerated == false) {
        $("#previous_button").show();
        $("#next_button").hide();
        $("#openai_generator_button").show();
    } else if (activeStep === 'article' && articleGenerated == true){
        $("#previous_button").show();
        $("#next_button").hide();
        $("#openai_generator_button").hide();
    }      
}

$(document).ready(function() {

    // Initial setup
    $('#number_of_results_div, #tone_of_voice_div, #creativity_div, #maximum_length_div').hide();
    let previousStep = null; // Variable to track the previous step

    // Clickable steps logic
    initializePopover('keywords');

    $(".clickable-step").click(function() {
        changeActiveStep(this);

    });
    $("#next_button").click(function(event) {
        event.preventDefault(); // Prevent form submission
        const nextStep = $('.clickable-step.active').next('.clickable-step');
        if (nextStep.length) {
            changeActiveStep(nextStep);
        }
    });

    $("#previous_button").click(function(event) {
        event.preventDefault(); // Prevent form submission
        const prevStep = $('.clickable-step.active').prev('.clickable-step');
        if (prevStep.length) {
            changeActiveStep(prevStep);
        }
    });
    updateButtonVisibility();



});
</script>
