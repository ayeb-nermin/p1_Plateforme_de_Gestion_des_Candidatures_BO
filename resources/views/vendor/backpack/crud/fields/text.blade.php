<!-- text input -->
@if(isset($field['display']) && ! $field['display'])
<div style="display: none">
@endif
@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')

    @if(isset($field['prefix']) || isset($field['suffix'])) <div class="input-group"> @endif
        @if(isset($field['prefix'])) <div class="input-group-prepend"><span class="input-group-text">{!! $field['prefix'] !!}</span></div> @endif
        <input
            type="text"
            @if(isset($field['slug']) && $field['slug'] === true) onkeyup="bpFieldSlugElement(event)" onblur="bpFieldSlugElement(event)" @elseif(isset($field['slug_class']) && ! empty($field['slug_class'])) onkeyup="bpFieldSlugClassElement(event, '{{ $field['slug_class'] }}')" onblur="bpFieldSlugClassElement(event, '{{ $field['slug_class'] }}')" @endif
            name="{{ $field['name'] }}"
            value="{{ old(square_brackets_to_dots($field['name'])) ?? $field['value'] ?? $field['default'] ?? '' }}"
            @include('crud::fields.inc.attributes')
        >
        @if(isset($field['suffix'])) <div class="input-group-append"><span class="input-group-text">{!! $field['suffix'] !!}</span></div> @endif
    @if(isset($field['prefix']) || isset($field['suffix'])) </div> @endif

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')
@if(isset($field['display']) && ! $field['display'])
    </div>
@endif
@if(isset($field['slug']) && $field['slug'] === true)
    @push('crud_fields_scripts')
    <script>
        /*  $('input[type=text]').on('click', function() {
         $(this).closest('.tab-pane').find('.slug').val(slugMe($(this).val()));
         });*/
        function slugMe(value) {
            var pattern = /[\u0600-\u06FF\u0750-\u077F]/;
            if (!pattern.test(value)) {
                var rExps = [
                    {re: /[\xC0-\xC6]/g, ch: 'A'},
                    {re: /[\xE0-\xE6]/g, ch: 'a'},
                    {re: /[\xC8-\xCB]/g, ch: 'E'},
                    {re: /[\xE8-\xEB]/g, ch: 'e'},
                    {re: /[\xCC-\xCF]/g, ch: 'I'},
                    {re: /[\xEC-\xEF]/g, ch: 'i'},
                    {re: /[\xD2-\xD6]/g, ch: 'O'},
                    {re: /[\xF2-\xF6]/g, ch: 'o'},
                    {re: /[\xD9-\xDC]/g, ch: 'U'},
                    {re: /[\xF9-\xFC]/g, ch: 'u'},
                    {re: /[\xC7-\xE7]/g, ch: 'c'},
                    {re: /[\xD1]/g, ch: 'N'},
                    {re: /[\xF1]/g, ch: 'n'}];


                // converti les caractères accentués en leurs équivalent alpha
                for (var i = 0, len = rExps.length; i < len; i++)
                    value = value.replace(rExps[i].re, rExps[i].ch);

                // 1) met en bas de casse
                // 2) remplace les espace par des tirets
                // 3) enleve tout les caratères non alphanumeriques
                // 4) enlève les doubles tirets
                return value.toLowerCase()
                        .replace(/\s+/g, '-')
                        .replace(/[^a-z0-9-]/g, '')
                        .replace(/\-{2,}/g, '-');
            }
            else {
                // arabe
                return value.replace(/\s+/g, '-')
                        .replace(/\-{2,}/g, '-');
            }
        }

        function bpFieldSlugElement(element) {
            element.target.value = slugMe(element.target.value);
        }

        function bpFieldSlugClassElement(element, target) {
            var target = document.getElementsByClassName(target)[0].value = slugMe(element.target.value);
        }
    </script>
    @endpush
@endif
