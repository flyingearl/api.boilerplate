<?php

namespace App\Http\Controllers;

use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        (new UpdateUserProfileInformation())->update($request->user(), $request->all());

        if ($request->has('password') && $request->filled('password')) {
            (new UpdateUserPassword())->update($request->user(), $request->all());
        }
    }
}
