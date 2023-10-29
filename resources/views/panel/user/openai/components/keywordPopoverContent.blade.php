
<style>
.popover-container {
    background-color: #fff;
    border: 1px solid #eee;
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    padding: 30px;
    border-radius: 10px;
    width: 300px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.popover-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.close-btn {
    background: none;
    border: none;
    cursor: pointer;
}

.keyword {
    cursor: pointer;
    transition: background-color 0.2s;
}

.keyword.selected,
.keyword:hover {
    background-color: #d1cDFF;
}

.keyword:hover {
    transform: translateY(-2px);
}

.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 10;
    display: none;
}

.popover-input {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: #F1EDFF;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #eee;
    z-index: 20;
    box-shadow: 0 3px 15px rgba(0, 0, 0, 0.2);
    width: 400px;
}

.keyword-common {
    border: 1px solid #F3F3F3;
    border-radius: 20px;
    padding: 8px 10px;
    margin: 0px 5px 5px 5px;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #F1EDFF;
    transition: 0.3s;
    height: 30px;
    line-height: 1;
}

.add-keyword {
    width: 30px;
    height: 30px;
    display: flex;  
    align-items: center;  
    justify-content: center;
    font-size: 20px;  
    cursor: pointer;  
    line-height: 1;
}

input[type="text"]#newKeyword {
    padding: 5px 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    width: 100%;
    box-sizing: border-box;
    margin-bottom: 10px;
}

button#addKeyword {
    padding: 5px 10px;
    background-color: #330582;
    color: #FFF;
    border: none;
    border-radius: 20px;
    width: 100%;
    cursor: pointer;
    margin-top: 5px;
    transition: background-color 0.3s;
}

#select_keywords {
    margin-left: 0px;
}

button#addKeyword:hover {
    background-color: #5D389C;
}

</style>


<div class="mb-3 col-xs-12">
    <div class="popover-header">
        <label class="form-label">{{__('Select Keywords')}}</label>
    </div>
    <!-- The popover for new keyword input -->
    <div class="overlay" id="overlay"></div>
    <div class="popover-input">
        <p class="popover__message"><label class="text-base">Add Keyword</label></p>
        <input type="text" id="newKeyword" placeholder="Add new keyword" />
        <button id="addKeyword" type="button">Add</button>
    </div>

    <div class="row col-xs-12 my-[10px]" id="select_keywords">
        <label class="keyword w-fit keyword-common add-keyword">+</label>

    </div>
</div>

<script>

function initializePopover(keywordsTextboxId) {

    document.getElementById(keywordsTextboxId).readOnly = true;
    const keywordContainer = document.getElementById('select_keywords');
    const addKeywordButton = document.getElementById('addKeyword');
    const newKeywordInput = document.getElementById('newKeyword');
    const addBtn = document.querySelector('.add-keyword');
    const popover = document.querySelector('.popover-input');
    const overlay = document.getElementById('overlay');

    function togglePopover() {
        if (popover.style.display === 'none' || popover.style.display === '') {
            popover.style.display = 'block';
            overlay.style.display = 'block';
        } else {
            popover.style.display = 'none';
            overlay.style.display = 'none';
        }
    }

    overlay.addEventListener('click', function() {
        popover.style.display = 'none';
        overlay.style.display = 'none';
    });

    newKeywordInput.addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevent form submission
            saveKeyWord();
        }
    });

    function updateKeywordsTextbox() {
        const keywordsTextbox = document.getElementById(keywordsTextboxId);
        if (keywordsTextbox) {
            const selectedKeywords = document.querySelectorAll('.keyword.selected:not(.add-keyword)');
            const selectedKeywordsArray = Array.from(selectedKeywords).map(kw => {
                // Here, we get the text content of the keyword, then trim any trailing spaces, and finally replace the delete icon
                return kw.textContent.replace('🞫', '').trim();
            });
            keywordsTextbox.value = selectedKeywordsArray.join(', ');
        }
    }

    addKeywordButton.addEventListener('click', function() {
        saveKeyWord();
    });

    function saveKeyWord() {
        const newKeywordValue = newKeywordInput.value.trim();
        if (newKeywordValue) {
            const newKeywordElem = document.createElement('label');
            newKeywordElem.className = 'keyword w-fit keyword-common';
            newKeywordElem.innerText = newKeywordValue + "\u00A0\u00A0";

            const deleteIcon = document.createElement('span');
            deleteIcon.innerText = '🞫';
            deleteIcon.style.cursor = 'pointer';
            deleteIcon.addEventListener('click', function(e) {
                keywordContainer.removeChild(newKeywordElem);
                e.stopPropagation(); // Prevents the click event on the keyword label from firing
                updateKeywordsTextbox();
            });

            newKeywordElem.appendChild(deleteIcon);
            keywordContainer.insertBefore(newKeywordElem, addBtn);
            newKeywordInput.value = '';
        }
    }
    // Using event delegation for clicking on keywords
    keywordContainer.addEventListener('click', function(e) {
        const keyword = e.target.closest('.keyword');
        if (keyword && !keyword.classList.contains('add-keyword')) {
            keyword.classList.toggle('selected');
            updateKeywordsTextbox();
        }
    });

    addBtn.addEventListener('click', function() {
        togglePopover();
    });
}



</script>