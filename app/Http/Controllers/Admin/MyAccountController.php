<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Requests\ChangePasswordRequest;
use Backpack\CRUD\app\Http\Requests\AccountInfoRequest;
use App\Http\Requests\UpdateAdminLocale;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controller;
use App\Traits\CrudPermissions;
use Alert;

class MyAccountController extends Controller
{
    use CrudPermissions;

    protected $data = [];

    public function __construct()
    {
        $this->middleware(backpack_middleware());
    }

    public function updateLocale(UpdateAdminLocale $request)
    {
        $settings = json_decode(backpack_user()->settings) ?? new \stdClass();
        $settings->locale = $request->locale;
        backpack_user()->update(['settings' => json_encode($settings)])
            ? Alert::success(trans('backpack::base.account_updated'))->flash()
            : Alert::error(trans('backpack::base.error_saving'))->flash();

        return redirect()->back();
    }

    /**
     * Show the user a form to change his personal information & password.
     */
    public function getAccountInfoForm()
    {
        $this->data['title'] = trans('backpack::base.my_account');
        $this->data['user'] = $this->guard()->user();

        return view(backpack_view('my_account'), $this->data);
    }

    /**
     * Save the modified personal information for a user.
     */
    public function postAccountInfoForm(AccountInfoRequest $request)
    {
        $result = $this->guard()->user()->update($request->except(['_token']));

        if ($result) {
            Alert::success(trans('backpack::base.account_updated'))->flash();
        } else {
            Alert::error(trans('backpack::base.error_saving'))->flash();
        }

        return redirect()->back();
    }

    /**
     * Save the new password for a user.
     */
    public function postChangePasswordForm(ChangePasswordRequest $request)
    {
        $user = $this->guard()->user();
        $user->password = Hash::make($request->new_password);

        if ($user->save()) {
            Alert::success(trans('backpack::base.account_updated'))->flash();
        } else {
            Alert::error(trans('backpack::base.error_saving'))->flash();
        }

        return redirect()->back();
    }

    /**
     * Get the guard to be used for account manipulation.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return backpack_auth();
    }
}
