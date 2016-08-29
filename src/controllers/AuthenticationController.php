<?php

namespace Haematoma\Authentication\Controllers;

use App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Requests;
use Haematoma\Authentication\Models\User;
use Haematoma\Authentication\Models\Role;
use Illuminate\Support\Facades\Auth;


class AuthenticationController extends Controller
{
    public function getAccessLevel()
	{
		$users = User::all();
		
        return view('admin.access', ['users' => $users]);
	}
	
	public function postAssignAccess(Request $request)
	{
		// Auth::user() teljesen egyenértékű $request->user(), 
		// csak az Auth-hoz kell: use Illuminate\Support\Facades\Auth;
		
		$admin = $request->user(); 
		
		// User kikeresése email alapján
		$user = User::where('email', $request['email'])->first();
        // manyToMany kapcsolat köztes kapcsolótáblából eltávolítja az összes rekordot.
		$user->roles()->detach();
        // Ha van a formban kijelölt role_user checkbox, akkor összekapcsolja
		if ($request['role_user']) {
            $user->roles()->attach(Role::where('name', 'User')->first(), array(
					'created_by' => $admin->id,
					'created_at' => date("Y-m-d H:i:s")
			));
        }
        if ($request['role_trainer']) {
            $user->roles()->attach(Role::where('name', 'Trainer')->first(), array(
					'created_by' => $admin->id,
					'created_at' => date("Y-m-d H:i:s")
			));
        }
        if ($request['role_admin']) {
            $user->roles()->attach(Role::where('name', 'Admin')->first(), array(
					'created_by' => $admin->id,
					'created_at' => date("Y-m-d H:i:s")
			));
        }
		$messages = array('Első üzenetem!', 'Második üzenetem', 'Harmadik üzenetem');
		$info = array('Első üzenetem!', 'Második üzenetem', 'Harmadik üzenetem');
        return redirect()->back()
				->with('messages', $messages)
				->with('informations', $info);
	}
	
	public function getSignUpPage()
    {
        return view('signup');
    }
	
    public function getSignInPage()
    {
        return view('signin');
    }
	
    public function postSignUp(Request $request)
    {
        $admin = Auth::user();
		
		$user = new User();
        $user->name = $request['name'];
        $user->email = $request['email'];
        $user->password = bcrypt($request['password']);
		$user->birth = $request['birth'];
		$user->genus = $request['genus'];
		$user->address = $request['address'];
		$user->phone = $request['phone'];
		$user->region = $request['region'];
		$user->created_by = $admin->id;
		$user->updated_by = $admin->id;
		$user->created_at = date("Y-m-d H:i:s");
		$user->updated_at = date("Y-m-d H:i:s");
		
        $user->save();
        $user->roles()->attach(Role::where('name', 'User')->first());
        
		// Auth::login($user);
        
		return redirect()->back();
    }
	
    public function postSignIn(Request $request)
    {
        if (Auth::attempt(['email' => $request['email'], 'password' => $request['password']])) {
            return redirect()->route('admin.access');
        }
        
		return redirect()->back();
    }
	
    public function getLogout()
    {
        Auth::logout();
        return redirect()->route('kezdolap');
    }
}
