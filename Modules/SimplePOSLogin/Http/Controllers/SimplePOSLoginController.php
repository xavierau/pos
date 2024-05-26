<?php

namespace Modules\SimplePOSLogin\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class SimplePOSLoginController extends Controller
{
    use AuthenticatesUsers;

    public function login(Request $request)
    {
        $this->validate($request, [
            'employee_code' => ['required']
        ]);

        $user = User::whereNull('deleted_at')
            ->where('email', $request->employee_code)
            ->first();

        $this->authorizeForUser($user, 'Sales_pos', Sale::class);

        $this->guard('api')->login($user);

        return $this->sendLoginResponse($request);

    }
}
