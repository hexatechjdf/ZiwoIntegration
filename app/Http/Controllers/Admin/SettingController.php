<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;

class SettingController extends Controller
{

    //

    public function index(Request $req)
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $user = \Auth::user();
        return view('admin.setting.index', compact('settings','user'));
    }

    public function integration(Request $req)
    {
        $user = loginUser();
        $settings = Setting::where('user_id', $user->id)->pluck('value', 'key')->toArray();
        return view('admin.integration.index', compact('settings'));
    }

    public function save(Request $request)
    {
        $user = loginUser();
        foreach ($request->setting ?? [] as $key => $value) {
            save_settings($key, $value, $user->id);
        }

        return response()->json(['success' => true, 'message' => 'Data saved successfully']);

    }
    public function userProfile(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|email',
            'user_id' => 'required|integer',
            'password' => 'nullable|min:6',
        ]);

        try {
            $userId = $request->user_id;
            $username = $request->username;
            $email = $request->email;
            $password = $request->password;
            $user = User::findOrFail($userId);
            $userExist = User::where('email', $email)
                ->where('id', '<>', $userId)
                ->first();

            if ($userExist) {
                if (!empty($password)) {
                    $user->password = bcrypt($password);
                    $user->save();
                    return response()->json(['status' => 'Success', 'message' => 'Password updated successfully']);
                } else {
                    return response()->json(['status' => 'Error', 'message' => 'Password is required'], 400);
                }
            } else {
                $user->email = $email;
                if (!empty($password)) {
                    $user->password = bcrypt($password);
                }
                $user->name = $username;
                $user->save();
                return response()->json(['status' => 'Success', 'message' => 'User profile updated successfully']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'Error', 'message' => $e->getMessage()], 500);
        }
    }
}
