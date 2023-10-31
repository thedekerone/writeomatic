document.addEventListener("DOMContentLoaded", function() {
    "use strict";
    
    const openButton = document.getElementById("open-popup");
    const popup = document.getElementById("wordpress-popup");
    if(openButton)
        openButton.addEventListener("click", function(e) {
            e.preventDefault();
            popup.style.display = "flex";
            const closeButton = document.getElementById("close-popup");
            closeButton.addEventListener("click", function() {
                popup.style.display = "none";
            });
            const wpIntegrateBtn = document.getElementById("integration_button");
            wpIntegrateBtn.addEventListener("click", integrateWordpress);
        });
    const disconnectWPBtn = document.getElementById("disconnect-button");
    if(disconnectWPBtn)
        disconnectWPBtn.addEventListener("click", removeWordPress);
});

function integrateWordpress(ev) {
    "use strict";
    
    ev.preventDefault();
    
    const submitBtn = document.getElementById("integration_button");
    document.querySelector('#app-loading-indicator').classList.remove('opacity-0');
    submitBtn.classList.add('lqd-form-submitting');
    submitBtn.disabled = true;
    
    var formData = new FormData();
    formData.append('name', 'WordPress');
    formData.append('url', $("#url").val());
    formData.append('username', $("#username").val());
    formData.append('password', $("#password").val());
     
    $.ajax({
        type: "post",
        url: "/dashboard/user/integrations/add",
        data: formData,
        contentType: false,
        processData: false,
        success: function (data) {
            toastr.success('WordPress integrated Successfully!');
            submitBtn.classList.remove('lqd-form-submitting');
            document.querySelector('#app-loading-indicator').classList.add('opacity-0');
            submitBtn.disabled = false;
            const popup = document.getElementById("wordpress-popup");
            popup.style.display = "none";
            location.reload();
        },
        error: function (data) {
			toastr.error('Something went wrong, Please try again!');
            submitBtn.classList.remove('lqd-form-submitting');
			document.querySelector('#app-loading-indicator').classList.add('opacity-0');
            submitBtn.disabled = false;
        }
    });
    return false;
}

function removeWordPress(ev) {
    "use strict";
    
    ev.preventDefault();
    
    if (confirm('Are you sure you want to proceed?')) {
        $.ajax({
            type: "GET",
            url: "/dashboard/user/integrations/remove/WordPress",
            dataType: 'json',
            success: function (data) {
                toastr.success('WordPress disconnected Successfully!');
                location.reload();
            },
            error: function (data) {
    			toastr.error('Something went wrong, Please try again!');
            }
        });
        return false;
    } else {
        return false;
    }
}