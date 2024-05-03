@php($model = $crud->getModel())
@canany(['enable_'.strtolower(basename(get_class($model), 'App\Models\\'))])
<input type="checkbox" data-size="mini" data-toggle="toggle" {{ (intval($entry->is_accept) === 1)?'checked':'' }}
data-on="{{ __('form.commun.etat.accepter') }}" onchange="changeEtatRequest(this, '{{ $entry->getKey() }}')"
       data-off="{{ __('form.commun.etat.refuser') }}"
       data-onstyle="outline-success" data-offstyle="outline-danger" class="chkToggle">
{{-- Button Javascript --}}
{{-- - used right away in AJAX operations (ex: List) --}}
{{-- - pushed to the end of the page, after jQuery is loaded, for non-AJAX operations (ex: Show) --}}
@push('after_scripts') @if ($crud->getRequest()->ajax()) @endpush @endif
<style>
    .swal-icon img {
        width: 25%;
    }
</style>
<script>
    $(function(){ $('.chkToggle').bootstrapToggle() });
    if (typeof changeEtatRequest != 'function') {

        function changeEtatRequest(elem, id, state) {
            state = (typeof state !== 'undefined')?state:(($(elem).is(':checked')) ? 1 : 0);

            if($(elem).closest('.btn-group').find('.loading-status').length) {
                $('.loading-status').show();
                $(elem).closest('.btn-group').find('button').hide();
            }

            swal({
                title              : "{!! $button->name !!}",
                text               : '{{ __('form.commun.msg_enable') }}',
                closeOnClickOutside: false,
                closeOnEsc         : false,
                icon               : "warning",
                buttons            : {
                    cancel: {
                        text      : "{!! trans('backpack::crud.cancel') !!}",
                        value     : null,
                        visible   : true,
                        className : "bg-secondary",
                        closeModal: true,
                    },
                    submit: {
                        text     : "{!! trans('form.commun.continue') !!}",
                        value    : true,
                        visible  : true,
                        className: "bg-success",
                    }
                }
            }).then((value) => {
                if(value==true) {
                    $.ajax({
                        url        : (window.location.href)+'/NotifActeur',
                        type       : "Post",
                        data : { state : state,
                                 id    : id
                               },
                        success : function (response) {
                            if (response==200) {
                                // Show a success message
                                swal({
                                    title  : 'Opération terminée avec succès!',
                                    text   : 'Demande approuvée. Something went wrong!',
                                    icon   : "success",
                                    timer  : 4000,
                                    buttons: false,
                                });
                                
                            }
                            else {
                                console.log(response);
                                // Show a error message
                                swal({
                                    title  : "Oops...",
                                    text   :  "Something went wrong!",
                                    icon   : "error",
                                    timer  : 4000,
                                    buttons: false,
                                });
                            }

                            // Reload the table
                            $('#crudTable').DataTable().ajax.reload();

                            // Hide the modal, if any
                            $('.modal').modal('hide');

                        },
                        error      : function (result) {
                            // Show an alert with the result
                            swal({
                                title  : "{{ __('form.commun.title_error_operation_popup') }}",
                                text   : "{{ __('form.commun.text_error_operation_popup') }}",
                                icon   : "error",
                                timer  : 4000,
                                buttons: false,
                            });
                        }
                    });
                } else {
                    // Reload the table
                    $('#crudTable').DataTable().ajax.reload();
        }
        });
        }
    }

    // make it so that the function above is run after each DataTable draw event
    // crud.addFunctionToDataTablesDrawEventQueue('showImportModel');
</script>
@if (!$crud->getRequest()->ajax()) @endpush @endif
@endcanany
