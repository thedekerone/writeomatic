<div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
    <div id="step_title" class="clickable-step active" style="position: relative; flex-grow: 1; text-align: center;">
        <span style="display: inline-block; width: 30px; height: 30px; border-radius: 50%; background-color: #330582; line-height: 30px; text-align: center; color: #fff;">1</span>
        <span style="display: block; margin-top: 5px;">{{__('Title')}}</span>
    </div>
    <div id="step_intro" class="clickable-step disabled-step" style="position: relative; flex-grow: 1; text-align: center;">
        <span style="display: inline-block; width: 30px; height: 30px; border-radius: 50%; background-color: #e0e0e0; line-height: 30px; text-align: center; color: #fff;">2</span>
        <span style="display: block; margin-top: 5px;">{{__('Introduction')}}</span>
    </div>
    <div id="step_headings" class="clickable-step disabled-step" style="position: relative; flex-grow: 1; text-align: center;">
        <span style="display: inline-block; width: 30px; height: 30px; border-radius: 50%; background-color: #e0e0e0; line-height: 30px; text-align: center; color: #fff;">3</span>
        <span style="display: block; margin-top: 5px;">{{__('Headings')}}</span>
    </div>
    <div id="step_article" class="clickable-step disabled-step" style="position: relative; flex-grow: 1; text-align: center;">
        <span style="display: inline-block; width: 30px; height: 30px; border-radius: 50%; background-color: #e0e0e0; line-height: 30px; text-align: center; color: #fff;">4</span>
        <span style="display: block; margin-top: 5px;">{{__('Article')}}</span>
    </div>
</div>

<div class="mb-3 col-xs-12" style="display: none;">
    <label class="form-label">{{__('What would you like to generate?')}}</label>
    <select class="form-select" id="generation_type" name="generation_type" required>
        <option value="title">{{__('Title')}}</option>
        <option value="introduction" disabled>{{__('Introduction')}}</option>
        <option value="headings" disabled>{{__('Headings')}}</option>
        <option value="article" disabled>{{__('Article')}}</option>
    </select>
</div>





