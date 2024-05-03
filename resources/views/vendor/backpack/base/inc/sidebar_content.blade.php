<li class="nav-item">
    <a class="nav-link" href="{{ backpack_url('dashboard') }}">
        <i class="la la-home nav-icon"></i>
        {{ trans('backpack::base.dashboard') }}
    </a>
</li>


<!-- Users, Roles, Permissions -->
@canany([
'create_user', 'list_user', 'update_user', 'delete_user',
'create_role', 'list_role', 'update_role', 'delete_role',
'create_permission', 'list_permission', 'update_permission', 'delete_permission'
])
<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#">
        <i class="nav-icon la la-users"></i>
        {{ __('sidebar.authentication') }}
    </a>
    <ul class="nav-dropdown-items">
        @canany(['create_user', 'list_user', 'update_user', 'delete_user'])
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('user') }}">
                <i class="nav-icon la la-user"></i>
                <span>{{ __('sidebar.administrators') }}</span>
            </a>
        </li>
        @endcanany

        @canany(['create_role', 'list_role', 'update_role', 'delete_role'])
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('role') }}">
                <i class="nav-icon la la-id-badge"></i>
                <span>{{ __('sidebar.roles') }}</span>
            </a>
        </li>
        @endcanany

        @canany(['create_permission', 'list_permission', 'update_permission', 'delete_permission'])
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('permission') }}">
                <i class="nav-icon la la-key"></i>
                <span>{{ __('sidebar.permissions') }}</span>
            </a>
        </li>
        @endcanany
    </ul>
</li>
@endcanany




@canany([
'create_translation', 'list_translation', 'update_translation', 'delete_translation', 'show_translation',
'create_language', 'list_language', 'update_language', 'delete_language', 'show_language',
'elfinder'
])
<li class="nav-item nav-dropdown">
    <a class="nav-link nav-dropdown-toggle" href="#">
        <i class="nav-icon la la-globe"></i>
        {{ __('sidebar.parameters') }}
    </a>

    <ul class="nav-dropdown-items">
        @canany(['create_email', 'list_email', 'update_email', 'delete_email', 'show_email'])
        <li class='nav-item'>
            <a class='nav-link' href='{{ backpack_url('email') }}'>
                <i class='nav-icon la la-envelope-open-text'></i>
                {{ __('sidebar.email') }}
            </a>
        </li>
    @endcanany
        @canany(['create_translation', 'list_translation', 'update_translation', 'delete_translation',
        'show_translation'])
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('language/texts/' . default_language() . '/auth') }}">
                <i class="nav-icon la la-language"></i>
                {{ __('sidebar.langues') }}
            </a>
        </li>
        @endcanany

        @canany(['create_language', 'list_language', 'update_language', 'delete_language', 'show_language'])
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('language') }}">
                <i class="nav-icon la la-flag-checkered"></i>
                {{ __('sidebar.site_text') }}
            </a>
        </li>
        @endcanany

        @canany(['elfinder'])
        <li class="nav-item">
            <a class="nav-link" href="{{ backpack_url('elfinder') }}">
                <i class="nav-icon la la-files-o"></i>
                <span>{{ trans('backpack::crud.file_manager') }}</span>
            </a>
        </li>
        @endcanany

    </ul>
</li>
@endcanany
