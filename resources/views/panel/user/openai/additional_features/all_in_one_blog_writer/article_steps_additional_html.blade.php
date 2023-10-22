<div class="mb-3 col-xs-12 col-md-6" id="maximum_length_div" style="display: none;">
    <label class="form-label">{{__('Maximum Length')}}</label>
    <input type="number" class="form-control" id="maximum_length" name="maximum_length" max="{{$setting->openai_max_output_length}}" value="400" placeholder="{{__('Maximum character length of text')}}" required>
</div>

<div class="mb-3 col-xs-12 col-md-6" id="number_of_results_div" style="display: none;">
    <label class="form-label">{{__('Number of Results')}}</label>
    <input type="number" class="form-control" id="number_of_results" name="number_of_results" value="1" placeholder="{{__('Number of results')}}" required>
</div>


<div class="mb-3 col-xs-12 col-md-6" id="creativity_div" style="display: none;">
    <label class="form-label">{{__('Creativity')}}</label>
    <select type="text" class="form-select" name="creativity" id="creativity" required>
        <option value="0.25" {{$setting->openai_default_creativity == 0.25 ? 'selected' : ''}}>{{__('Economic')}}</option>
        <option value="0.5" {{$setting->openai_default_creativity == 0.5 ? 'selected' : ''}}>{{__('Average')}}</option>
        <option value="0.75" {{$setting->openai_default_creativity == 0.75 ? 'selected' : ''}}>{{__('Good')}}</option>
        <option value="1" {{$setting->openai_default_creativity == 1 ? 'selected' : ''}}>{{__('Premium')}}</option>
    </select>
</div>

<div class="mb-3 col-xs-12 col-md-6" id="tone_of_voice_div" style="display: none;">
    <div class="form-label">{{__('Tone of Voice')}}</div>
    <select class="form-select" id="tone_of_voice" name="tone_of_voice" required>
    <option value="Professional" {{$setting->openai_default_tone_of_voice == 'Professional' ? 'selected' : null}}>{{__('Professional')}}</option>
        <option value="Funny" {{$setting->opena_default_tone_of_voice == 'Funny' ? 'selected' : null}}>{{__('Funny')}}</option>
        <option value="Casual" {{$setting->openai_default_tone_of_voice == 'Casual' ? 'selected' : null}}>{{__('Casual')}}</option>
        <option value="Excited" {{$setting->openai_default_tone_of_voice == 'Excited' ? 'selected' : null}}>{{__('Excited')}}</option>
        <option value="Witty" {{$setting->openai_default_tone_of_voice == 'Witty' ? 'selected' : null}}>{{__('Witty')}}</option>
        <option value="Sarcastic" {{$setting->openai_default_tone_of_voice == 'Sarcastic' ? 'selected' : null}}>{{__('Sarcastic')}}</option>
        <option value="Feminine" {{$setting->openai_default_tone_of_voice == 'Feminine' ? 'selected' : null}}>{{__('Feminine')}}</option>
        <option value="Masculine" {{$setting->openai_default_tone_of_voice == 'Masculine' ? 'selected' : null}}>{{__('Masculine')}}</option>
        <option value="Bold" {{$setting->openai_default_tone_of_voice == 'Bold' ? 'selected' : null}}>{{__('Bold')}}</option>
        <option value="Dramatic" {{$setting->openai_default_tone_of_voice == 'Dramatic' ? 'selected' : null}}>{{__('Dramatic')}}</option>
        <option value="Grumpy" {{$setting->openai_default_tone_of_voice == 'Grumpy' ? 'selected' : null}}>{{__('Grumpy')}}</option>
        <option value="Secretive" {{$setting->openai_default_tone_of_voice == 'Secretive' ? 'selected' : null}}>{{__('Secretive')}}</option>
    </select>
</div>

<div class="mb-3 col-xs-12"  id="language_div">
    <label class="form-label">{{__('Language')}}</label>
    <select type="text" class="form-select" name="language" id="language" required>
        @include('panel.user.openai.components.countries')
    </select>
</div>