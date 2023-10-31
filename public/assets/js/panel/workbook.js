function editWorkbook( workbook_slug ) {
	"use strict";

	document.getElementById( "workbook_button" ).disabled = true;
	document.getElementById( "workbook_button" ).innerHTML = magicai_localize.please_wait;
	document.querySelector( '#app-loading-indicator' )?.classList?.remove( 'opacity-0' );
	tinyMCE.get( "workbook_text" ).save();

	var formData = new FormData();
	formData.append( 'workbook_slug', workbook_slug );
	formData.append( 'workbook_text', $( "#workbook_text" ).val() );
	formData.append( 'workbook_title', $( "#workbook_title" ).val() );

	$.ajax( {
		type: "post",
		url: "/dashboard/user/openai/documents/workbook-save",
		data: formData,
		contentType: false,
		processData: false,
		success: function ( data ) {
			toastr.success( 'Workbook Saved Succesfully.' );
			document.getElementById( "workbook_button" ).disabled = false;
			document.getElementById( "workbook_button" ).innerHTML = "Save";
			document.querySelector( '#app-loading-indicator' )?.classList?.add( 'opacity-0' );

		},
		error: function ( data ) {
			console.log(data)
			toastr.error( 'Workbook  Error' );
			document.getElementById( "workbook_button" ).disabled = false;
			document.getElementById( "workbook_button" ).innerHTML = "Save";
			document.querySelector( '#app-loading-indicator' )?.classList?.add( 'opacity-0' );
		}
	} );
	return false;
}

document.getElementById("publish-doc").addEventListener("click", function(event) {
    "use strict";
    
    event.preventDefault();
    var formData = new FormData();
	formData.append( 'content', $( "#workbook_text" ).val() );
	formData.append( 'title', $( "#workbook_title" ).val() );
    formData.append( 'status', 'publish' );
    formData.append( 'publishTo', 'WordPress');
    
	$.ajax( {
		type: "post",
		url: "/dashboard/user/openai/documents/publish",
		data: formData,
		contentType: false,
		processData: false,
		success: function ( data ) {
			toastr.success( 'Document published Succesfully.' );
		},
		error: function ( data ) {
			toastr.error( 'Something went wrong, please try again!' );
		}
	});
});

function UnlockFeature (e) {
	"use strict";
    
    e.preventDefault();
    const popup = document.getElementById("unlock_feature");
    popup.style.display = "flex";
    const closeButton = document.getElementById("close_feature_popup");
    closeButton.addEventListener("click", function() {
        popup.style.display = "none";
    });
}

function ScheduleDocument (e) {
    "use strict";
    
    e.preventDefault();
    const popup = document.getElementById("schedule_popup");
    popup.style.display = "flex";
    const closeButton = document.getElementById("close_popup");
    closeButton.addEventListener("click", function() {
        popup.style.display = "none";
    });
    flatpickr("#datetimepicker", {
        inline: true,
        enableTime: true,
        minDate: "today",
        onChange: function(selectedDates, dateStr, instance) {
            document.getElementById("scheduled-time").style.display = "block";
            document.getElementById("datetimevalue").innerText = dateStr;
        }
    });
    document.getElementById("schedule_confirm").addEventListener("click", ConfirmSchedule);
}

function ConfirmSchedule(e) {
    "use strict";
    
    e.preventDefault();
    const selectedAccount = document.querySelector('input[name="account"]:checked');
    const selectedDateTime = document.getElementById("datetimevalue").innerText;
    if(selectedAccount == null)
        alert("Please select account first");
    else if(selectedDateTime == "")
        alert("Please select date/time");
    else {
        let submitBtn = document.getElementById('schedule_confirm');
        document.querySelector('#app-loading-indicator').classList.remove('opacity-0');
        submitBtn.classList.add('lqd-form-submitting');
        submitBtn.disabled = true;
        var formData = new FormData();
	    formData.append( 'document', workbook.id );
    	formData.append( 'datetime', selectedDateTime );
        formData.append( 'account', selectedAccount.value );
    	$.ajax( {
    		type: "post",
    		url: "/dashboard/user/scheduler/add",
    		data: formData,
    		contentType: false,
    		processData: false,
    		success: function ( data ) {
    			toastr.success( data.success );
    			document.querySelector('#app-loading-indicator').classList.add('opacity-0');
                submitBtn.classList.remove('lqd-form-submitting');
                submitBtn.disabled = false;
    			const popup = document.getElementById("schedule_popup");
                popup.style.display = "none";
    		},
    		error: function ( data ) {
    			toastr.error( data.responseJSON.error );
    			document.querySelector('#app-loading-indicator').classList.add('opacity-0');
                submitBtn.classList.remove('lqd-form-submitting');
                submitBtn.disabled = false;
    		}
    	});
    }
    
}

let currentPage = 1;

async function fetchUnsplashImages(query, page = 1) {
	let response;
	if(query)
    	response = await fetch(`https://api.unsplash.com/search/photos?page=${page}&query=${query}&client_id=${unsplashKey}`);
    else
		response = await fetch(`https://api.unsplash.com/photos?page=${page}&client_id=${unsplashKey}`);
    let data = await response.json();
    return query ? data.results : data;
}

function searchUnsplash() {
    let query = document.getElementById('searchInput').value;
    fetchUnsplashImages(query, currentPage).then(images => {
        displayImages(images);
    });
}

function nextPage() {
    currentPage++;
    searchUnsplash();
}

function previousPage() {
    currentPage = Math.max(1, currentPage - 1); // Ensure page doesn't go below 1
    searchUnsplash();
}

function displayImages(images) {
    let imageResultsDiv = document.getElementById('imageResults');
    imageResultsDiv.innerHTML = '';
    images.forEach(image => {
        let imgElem = document.createElement('img');
        imgElem.src = image.urls.small;
		imgElem.alt = image.alt_description || 'An Unsplash Image';
		imgElem.style.cursor = 'pointer';
        imgElem.onclick = function() {
			let width = 800;
            let height = 500;
            tinymce.activeEditor.insertContent('<img src="' + image.urls.full + '" width="' + width + '" height="' + height + '" />');
            document.getElementById('unsplash_images').style.display = 'none';
        };
        imageResultsDiv.appendChild(imgElem);
    });
}

document.addEventListener( "DOMContentLoaded", function () {
	"use strict";

	const outputContainer = document.getElementById('workbook_text');
    const updatedOutput = marked.parse(workbook.output);
    outputContainer.innerHTML = updatedOutput;

	const scheduleButton = document.getElementById("schedule-doc");
    if(scheduleButton) {
		if(plan_type === 'regular')
			scheduleButton.addEventListener("click", UnlockFeature);
		else
			scheduleButton.addEventListener("click", ScheduleDocument);
	}
        

	const tinymceOptions = {
		selector: '.tinymce',
		height: 600,
		menubar: false,
		statusbar: false,
		plugins: [
			'advlist', 'link', 'autolink', 'lists', 'code', 'image'
		],
		toolbar: 'styles | forecolor backcolor emoticons | bold italic underline link bullist numlist alignleft aligncenter alignright | image unsplash',
		automatic_uploads: true,
		relative_urls: false,
		remove_script_host: false,
		images_upload_url: '/dashboard/user/openai/documents/upload_image',
		file_picker_callback: function (cb, value, meta) {
			var input = document.createElement('input');
			input.setAttribute('type', 'file');
			input.setAttribute('accept', 'image/*');
			input.onchange = function () {
				var file = this.files[0];
				var reader = new FileReader();
				reader.onload = function () {
					var id = 'blobid' + (new Date()).getTime();
					var blobCache =  tinymce.activeEditor.editorUpload.blobCache;
					var base64 = reader.result.split(',')[1];
					var blobInfo = blobCache.create(id, file, base64);
					blobCache.add(blobInfo);
					cb(blobInfo.blobUri(), { title: file.name });
				};
				reader.readAsDataURL(file);
			};
			
			input.click();
		},
		directionality: document.documentElement.dir === 'rtl' ? 'rtl' : 'ltr',
		setup: function(editor) {
			editor.ui.registry.addButton('unsplash', {
				text: 'Unsplash Images',
				onAction: function() {
					const popup = document.getElementById("unsplash_images");
					popup.style.display = "flex";
					const closeButton = document.getElementById("close_unsplash_popup");
					closeButton.addEventListener("click", function() {
						popup.style.display = "none";
					});
					fetchUnsplashImages().then(images => {
						displayImages(images);
					});
						// Display images and allow user to select one
						//let selectedImage = userSelectsImage(images); // Implement this function as per your UI
						// Insert selected image into TinyMCE editor
						//editor.insertContent('<img src="' + selectedImage.url + '" />');
					
				}
			});
		}
	};
	if ( localStorage.getItem( "tablerTheme" ) === 'dark' ) {
		tinymceOptions.skin = 'oxide-dark';
		tinymceOptions.content_css = 'dark';
	}

	tinyMCE.init( tinymceOptions );

	$( 'body' ).on( 'click', '#workbook_regenerate', () => {
		sendOpenaiGeneratorForm();
	} );
	$( 'body' ).on( 'click', '#workbook_undo', () => {
		tinymce.activeEditor.execCommand( 'Undo' );
	} );
	$( 'body' ).on( 'click', '#workbook_redo', () => {
		tinymce.activeEditor.execCommand( 'Redo' );
	} );
	$( 'body' ).on( 'click', '#workbook_copy', () => {
		const codeOutput = document.querySelector( '#code-output' );
		if ( codeOutput && window.codeRaw ) {
			navigator.clipboard.writeText( window.codeRaw );
			toastr.success( 'Code copied to clipboard' );
			return;
		}
		if ( tinymce?.activeEditor ) {
			let contentToCopy = tinymce.activeEditor.selection.getContent( { format: 'html' } );
			if(!contentToCopy)
				contentToCopy = document.getElementById('workbook_text').value;	
			let sanitizedContent = contentToCopy.replace(/<\/?[^>]+(>|$)/g, "");
			navigator.clipboard.writeText(sanitizedContent);
			toastr.success('Content copied to clipboard');
			return;
		}
	} );
	$( 'body' ).on( 'click', '.workbook_download', event => {
		const button = event.currentTarget;
		const docType = button.dataset.docType;
		const docName = button.dataset.docName || 'document';

		tinymce.activeEditor.execCommand( 'selectAll', true );
		const content = tinymce.activeEditor.selection.getContent( { format: 'html' } );

		const html = `
<html ${ this.doctype === 'doc' ? 'xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40"' : '' }>
<head>
	<meta charset="utf-8" />
	<title>${ docName }</title>
</head>
<body>
	${ content }
</body>
</html>`;

		const url = `${ docType === 'doc' ? 'data:application/vnd.ms-word;charset=utf-8' : 'data:text/plain;charset=utf-8' },${ encodeURIComponent( html ) }`;

		const downloadLink = document.createElement( "a" );
		document.body.appendChild( downloadLink );
		downloadLink.href = url;
		downloadLink.download = `${ docName }.${ docType }`;
		downloadLink.click();

		document.body.removeChild( downloadLink );

	} );
} )
