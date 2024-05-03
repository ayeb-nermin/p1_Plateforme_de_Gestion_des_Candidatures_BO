<!-- PAGE OR LINK field -->
<!-- Used in Backpack\MenuCRUD -->
@php
$menus = get_menus([], true);
$except = isset($field['except']) ? (is_array($field['except']) ? $field['except'] : [$field['except']]) : [];
@endphp
@include('crud::fields.inc.wrapper_start')
<label>{!! $field['label'] !!}</label>
<div class="row" data-init-function="bpFieldInitPageOrLinkElement">
    <div class="col-sm-3">
        <select data-identifier="page_or_link_select" name="{{ $field['name'] }}"
            @include('crud::fields.inc.attributes')>
            <option value=""></option>
            @foreach (config('cms.menu_type') as $key => $value)
                @if (!in_array($key, $except))
                    <option {{ isset($entry) && $entry->{$field['name']} == $key ? 'selected' : '' }}
                        value="{{ $key }}">{{ __('form.menu.menu_type.' . $value) }}</option>
                @endif
            @endforeach
        </select>
    </div>
    <div class="col-sm-9">
        <!-- external link input -->
        <div
            class="page_or_link_value page_or_link_external_link {{ !isset($entry) || (isset($entry) && $entry->type != 2) ? 'd-none' : '' }}">
            <input type="url" class="form-control" name="external_link"
                placeholder="{{ __('backpack::crud.page_link_placeholder') }}"
                {{ !isset($entry) || (isset($entry) && $entry->type != 2) ? 'disabled' : '' }}
                value="{{ isset($entry) ? $entry->external_link : '' }}">
        </div>

        <!-- internal link input -->
        <div
            class="page_or_link_value page_or_link_internal_link {{ !isset($entry) || (isset($entry) && $entry->type != 1) ? 'd-none' : '' }}">
            <select name="internal_link" data-init-function="bpFieldInitInternalLinkOtherOption"
                data-identifier="internal_link_select"
                {{ !isset($entry) || (isset($entry) && $entry->type != 1) ? 'disabled' : '' }}
                @include('crud::fields.inc.attributes')>
                <option value="">--{{ __('form.menu.plural') }}--</option>
                @if (!empty($menus))
                    @foreach ($menus as $menu_id => $title)
                        @if (!isset($entry) || (isset($entry) && $menu_id != $entry->menu_id))
                            <option {{ isset($entry) && $entry->internal_link == $menu_id ? 'selected' : '' }}
                                value="{{ $menu_id }}">{{ $title }}</option>
                        @endif
                    @endforeach
                    <option {{ isset($entry) && $entry->internal_link == -1 ? 'selected' : '' }} value="-1">
                        {{ __('form.menu.menu_type.other') }}</option>
                @endif
            </select>
            <div
                class="input-group {{ !isset($entry) || ((isset($entry) && $entry->type == 1 && $entry->internal_link != -1) || (isset($entry) && $entry->type != 1)) ? 'd-none' : '' }}">
                <span style="padding-top: 8px;">{{ url(locale()) }}/</span>
                <input class="form-control" type="text" name="internal_link_text"
                    value="{{ $entry->internal_link_text ?? '' }}">
            </div>
        </div>

        <select name="target"
            class="form-control page_or_link_target col-sm-3 {{ !isset($entry) || (isset($entry) && $entry->type != 1 && $entry->type != 2) ? 'd-none' : '' }}
                "
            {{ !isset($entry) || (isset($entry) && $entry->type != 1 && $entry->type != 2) ? 'disabled' : '' }}>
            <option value="">--{{ __('form.commun.target') }}--</option>
            <option {{ isset($entry) && $entry->target == '_blank' ? 'selected' : '' }} value="_blank">_blank</option>
            <option {{ isset($entry) && $entry->target == '_self' ? 'selected' : '' }} value="_self">_self</option>
        </select>

        <!-- template -->
        <div
            class="page_or_link_value page_or_link_template {{ !isset($entry) || (isset($entry) && $entry->type != 3) ? 'd-none' : '' }}">
            <select class="form-control" name="module_reference"
                {{ !isset($entry) || (isset($entry) && $entry->type != 3) ? 'disabled' : '' }}>
                <option value="">--{{ __('form.menu.content_type') }}--</option>
                @foreach (get_modules_that_can_be_menus() as $moduleReference => $moduleName)
                    <option {{ isset($entry) && $entry->module_reference == $moduleReference ? 'selected' : '' }}
                        value="{{ $moduleReference }}">{{ __('module.' . $moduleReference . '.module_name') }}
                    </option>
                @endforeach
            </select>
            <!-- template -->
           {{-- <div class="page_or_link_value page_or_link_template {{ (! isset($entry) || (isset($entry) && $entry->type != 3))?'d-none':'' }}">
                <select class="form-control" name="module_id"
                    {{ (! isset($entry) || (isset($entry) && $entry->type != 3))?'disabled':'' }}>
                    <option value="">--{{ __('form.content_type') }}--</option>
                    @foreach(config('cms.modules') as $moduleIdas =>$moduleName)
                        <option {{ (isset($entry) && $entry->module_id == $moduleIdas)?'selected':'' }}
                                value="{{ $moduleIdas }}">{{ __('form.module.'.$moduleName['reference']) }}</option>
                    @endforeach
                </select>
            </div>--}}
        </div>
    </div>
</div>
{{-- HINT --}}
@if (isset($field['hint']))
    <p class="help-block">{!! $field['hint'] !!}</p>
@endif
@include('crud::fields.inc.wrapper_end')


{{-- ########################################## --}}
{{-- Extra CSS and JS for this particular field --}}
{{-- If a field type is shown multiple times on a form, the CSS and JS will only be loaded once --}}

{{-- End of Extra CSS and JS --}}
{{-- ########################################## --}}
@if ($crud->fieldTypeNotLoaded($field))
    @php
        $crud->markFieldTypeAsLoaded($field);
    @endphp

    {{-- FIELD CSS - will be loaded in the after_styles section --}}
    @push('crud_fields_styles')
    @endpush

    {{-- FIELD JS - will be loaded in the after_scripts section --}}
    @push('crud_fields_scripts')
        <script>
            $(function() {
                $('select[name=internal_link]').on('change', function(e) {
                    $('input[name=internal_link_text]').closest('div').addClass('d-none');
                    switch (parseInt($(this).val())) {
                        case -1:
                            $('input[name=internal_link_text]').closest('div').removeClass('d-none');
                            break;
                    }
                });
            })

            function bpFieldInitPageOrLinkElement(element) {
                $wrapper = element;

                $wrapper.find('[data-identifier=page_or_link_select]').on('change', function(e) {
                    $wrapper.find(".page_or_link_external_link input").attr('disabled', 'disabled');
                    $wrapper.find(".page_or_link_internal_link select").attr('disabled', 'disabled');
                    $wrapper.find(".page_or_link_template select").attr('disabled', 'disabled');
                    $wrapper.find(".page_or_link_value").removeClass("d-none").addClass("d-none");
                    $wrapper.find(".page_or_link_target").attr('disabled', 'disabled');
                    $wrapper.find(".page_or_link_target").addClass('d-none');

                    switch (parseInt($(this).val())) {
                        case 2:
                            $wrapper.find(".page_or_link_external_link input").removeAttr('disabled');
                            $wrapper.find(".page_or_link_external_link").removeClass('d-none');
                            $wrapper.find(".page_or_link_target").removeAttr('disabled');
                            $wrapper.find(".page_or_link_target").removeClass('d-none');
                            break;

                        case 1:
                            $wrapper.find(".page_or_link_internal_link select").removeAttr('disabled');
                            $wrapper.find(".page_or_link_internal_link").removeClass('d-none');
                            $wrapper.find(".page_or_link_target").removeAttr('disabled');
                            $wrapper.find(".page_or_link_target").removeClass('d-none');
                            break;

                        case 3:
                            $wrapper.find(".page_or_link_template select").removeAttr('disabled');
                            $wrapper.find(".page_or_link_template").removeClass('d-none');
                            $wrapper.find(".page_or_link_target").attr('disabled', 'disabled');
                            $wrapper.find(".page_or_link_target").addClass('d-none');
                            break;
                    }
                });
            }
        </script>
    @endpush

@endif
