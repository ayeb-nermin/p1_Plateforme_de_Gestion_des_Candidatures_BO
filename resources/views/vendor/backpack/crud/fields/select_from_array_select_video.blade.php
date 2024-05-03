@php
    $field['allows_null'] = $field['allows_null'] ?? $crud->model::isColumnNullable($field['name']);
@endphp
<!-- select from array -->
@include('crud::fields.inc.wrapper_start')
    <label>{!! $field['label'] !!}</label>
    @include('crud::fields.inc.translatable_icon')
    <select
        id="video_type"
        onchange="changeOption(this)"
        name="{{ $field['name'] }}@if (isset($field['allows_multiple']) && $field['allows_multiple']==true)[]@endif"
        @include('crud::fields.inc.attributes')
        @if (isset($field['allows_multiple']) && $field['allows_multiple']==true)multiple @endif
        >

        @if ($field['allows_null'])
            <option value="">-</option>
        @endif

        @if (count($field['options']))
            @foreach ($field['options'] as $key => $value)
                @if((old(square_brackets_to_dots($field['name'])) !== null && (
                        $key == old(square_brackets_to_dots($field['name'])) ||
                        (is_array(old(square_brackets_to_dots($field['name']))) &&
                        in_array($key, old(square_brackets_to_dots($field['name'])))))) ||
                        (null === old(square_brackets_to_dots($field['name'])) &&
                            ((isset($field['value']) && (
                                        $key == $field['value'] || (
                                                is_array($field['value']) &&
                                                in_array($key, $field['value'])
                                                )
                                        )) ||
                                (!isset($field['value']) && isset($field['default']) &&
                                ($key == $field['default'] || (
                                                is_array($field['default']) &&
                                                in_array($key, $field['default'])
                                            )
                                        )
                                ))
                        ))
                    <option value="{{ $key }}" selected>{{ $value }}</option>
                @else
                    <option value="{{ $key }}">{{ $value }}</option>
                @endif
            @endforeach
        @endif
    </select>

    {{-- HINT --}}
    @if (isset($field['hint']))
        <p class="help-block">{!! $field['hint'] !!}</p>
    @endif
@include('crud::fields.inc.wrapper_end')
<!-- include field specific select2 js-->
@push('after_scripts') @if ($crud->getRequest()->ajax()) @endpush @endif
    <script>
        $(function() {
            $('input[name=video_link]').closest('.form-group').hide();
            $('input[name=video]').closest('.form-group').hide();

            {{--$('input[name={{ $field['name'] }}]').trigger('change');--}}
        });
        if (typeof changeOption != 'function') {
            function changeOption(elem) {
                $('input[name=video_link]').closest('.form-group').hide();
                $('input[name=video]').closest('.form-group').hide();
                if($(elem).val() == 1) {
                    $('input[name=video_link]').closest('.form-group').show();
                }
                if($(elem).val() == 2) {
                    $('input[name=video]').closest('.form-group').show();
                }
            }
        }

        $( document ).ready(function() {
            elem = $('#video_type');
            if ($(elem).val() == 1) {
                $('input[name=video_link]').closest('.form-group').show();
            }
            if ($(elem).val() == 2) {
                $('input[name=video]').closest('.form-group').show();
            }
        });
    </script>
@if (!$crud->getRequest()->ajax()) @endpush @endif
{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
