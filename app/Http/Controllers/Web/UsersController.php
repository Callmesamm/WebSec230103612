<?php
namespace App\Http\Controllers\Web;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Validation\Rules\Password as PasswordRule; // Alias for validation rule
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Log;
use DB;
use Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationEmail;
use Laravel\Socialite\Facades\Socialite;
use Carbon\Carbon;
use Illuminate\Support\Facades\Password; // Facade for password reset
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

use App\Http\Controllers\Controller;
use App\Models\User;

class UsersController extends Controller {

    use ValidatesRequests;

    public function list(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('show_users')) abort(401);
        $query = User::select('*'); // Only customers
        if (auth()->user()->hasRole('Employee')) {
            $query->role('Customer');
        }
        $query->when($request->keywords, fn($q) => $q->where("name", "like", "%$request->keywords%"));
        $users = $query->get();
        return view('users.list', compact('users'));
    }

    public function register(Request $request) {
        return view('users.register');
    }

    public function verify(Request $request) {
        $decryptedData = json_decode(Crypt::decryptString($request->token), true); 
        $user = User::find($decryptedData['id']);
        if (!$user) abort(401);
        $user->email_verified_at = Carbon::now();
        $user->save();
    
        // Clear any existing session before logging in
        Auth::logout();
    
        // Log in the user after verification
        Auth::login($user);
    
        return view('users.verified', compact('user'));
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback() {
        try {
            $googleUser = Socialite::driver('google')->user();
            $user = User::updateOrCreate([
                'google_id' => $googleUser->id,
            ], [
                'name' => $googleUser->name,
                'email' => $googleUser->email,
                'google_token' => $googleUser->token,
                'google_refresh_token' => $googleUser->refreshToken,
            ]);
            Auth::login($user);
            return redirect('/');
        } catch (\Exception $e) {
            return redirect('login')->with('error', 'Google login failed.');
        }
    }

    public function redirectToLinkedin()
    {
        return Socialite::driver('linkedin')->redirect();
    }

    public function handleLinkedinCallback() {
        try {
            $linkedInUser = Socialite::driver('linkedin')->user();
            $user = User::updateOrCreate([
                'linkedin_id' => $linkedInUser->id,
            ], [
                'name' => $linkedInUser->name,
                'email' => $linkedInUser->email,
                'linkedin_token' => $linkedInUser->token,
                'linkedin_refresh_token' => $linkedInUser->refreshToken,
            ]);
            Auth::login($user);
            return redirect('/');
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'LinkedIn login failed.');
        }
    }

    public function doRegister(Request $request) {
        try {
            $this->validate($request, [
                'name' => ['required', 'string', 'min:5'],
                'email' => ['required', 'email', 'unique:users'],
                'password' => ['required', 'confirmed', PasswordRule::min(8)->numbers()->letters()->mixedCase()->symbols()],
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withInput($request->input())->withErrors('Invalid registration information.');
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->credit = 0.00;
        $user->save();

        $user->assignRole('Customer');
       
        $title = "Verification Link";
        $token = Crypt::encryptString(json_encode(['id' => $user->id, 'email' => $user->email]));
        $link = route("verify", ['token' => $token]);
        Mail::to($user->email)->send(new VerificationEmail($link, $user->name));
       
        // Clear any existing session before logging in
        Auth::logout();
        
        // Log in the new user
        Auth::login($user);
        return redirect('/');
    }

    public function login(Request $request) {
        return view('users.login');
    }

    public function doLogin(Request $request) {
        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return redirect()->back()->withInput($request->input())->withErrors('Invalid login information.');
        }

        $user = User::where('email', $request->email)->first();
        if (!$user->email_verified_at) {
            return redirect()->back()->withInput($request->input())->withErrors('Your email is not verified');
        }
        Auth::setUser($user);

        return redirect('/');
    }

    public function doLogout(Request $request) {
        Auth::logout();
        return redirect('/');
    }

    public function createEmployee(Request $request)
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized');
        }

        $this->validate($request, [
            'name' => ['required', 'string', 'min:5'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', PasswordRule::min(8)],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'credit' => 0.00,
        ]);
        $user->assignRole('Employee');

        return redirect()->route('users');
    }

    public function profile(Request $request, User $user = null)
    {
        $user = $user ?? auth()->user();
        if (auth()->id() != $user->id && !auth()->user()->hasPermissionTo('show_users')) {
            abort(401);
        }
    
        $purchases = $user->purchases()->with('product')->get();
    
        \Log::info('Purchases retrieved for user ' . $user->id . ':', [
            'purchases' => $purchases->toArray(),
        ]);
    
        \Log::info('Direct query test for purchases:', [
            'purchases' => \App\Models\Purchase::where('user_id', $user->id)->with('product')->get()->toArray(),
        ]);
    
        $permissions = [];
        foreach ($user->permissions as $permission) {
            $permissions[] = $permission;
        }
        foreach ($user->roles as $role) {
            foreach ($role->permissions as $permission) {
                $permissions[] = $permission;
            }
        }
    
        return view('users.profile', compact('user', 'permissions', 'purchases'));
    }

    public function edit(Request $request, User $user = null) {
        $user = $user ?? auth()->user();
        if (auth()->id() != $user?->id) {
            if (!auth()->user()->hasPermissionTo('edit_users')) abort(401);
        }
    
        $roles = [];
        foreach (Role::all() as $role) {
            $role->taken = ($user->hasRole($role->name));
            $roles[] = $role;
        }

        $permissions = [];
        $directPermissionsIds = $user->permissions()->pluck('id')->toArray();
        foreach (Permission::all() as $permission) {
            $permission->taken = in_array($permission->id, $directPermissionsIds);
            $permissions[] = $permission;
        }      

        return view('users.edit', compact('user', 'roles', 'permissions'));
    }

    public function save(Request $request, User $user) {
        if (auth()->id() != $user->id) {
            if (!auth()->user()->hasPermissionTo('show_users')) abort(401);
        }

        $user->name = $request->name;
        $user->save();

        if (auth()->user()->hasPermissionTo('admin_users')) {
            $user->syncRoles($request->roles);
            $user->syncPermissions($request->permissions);
            Artisan::call('cache:clear');
        }

        return redirect(route('profile', ['user' => $user->id]));
    }

    public function delete(Request $request, User $user) {
        if (!auth()->user()->hasPermissionTo('delete_users')) abort(401);
        return redirect()->route('users');
    }

    public function editPassword(Request $request, User $user = null) {
        $user = $user ?? auth()->user();
        if (auth()->id() != $user?->id) {
            if (!auth()->user()->hasPermissionTo('edit_users')) abort(401);
        }
        return view('users.edit_password', compact('user'));
    }

    public function savePassword(Request $request, User $user) {
        if (auth()->id() == $user?->id) {
            $this->validate($request, [
                'password' => ['required', 'confirmed', PasswordRule::min(8)->numbers()->letters()->mixedCase()->symbols()],
            ]);

            if (!Auth::attempt(['email' => $user->email, 'password' => $request->old_password])) {
                Auth::logout();
                return redirect('/');
            }
        } else if (!auth()->user()->hasPermissionTo('edit_users')) {
            abort(401);
        }

        $user->password = bcrypt($request->password);
        $user->save();

        return redirect(route('profile', ['user' => $user->id]));
    }

    public function addCredit(Request $request, User $user)
    {
        if (!auth()->user()->hasRole('Employee') || !$user->hasRole('Customer')) {
            abort(403);
        }

        $this->validate($request, [
            'credit' => ['required', 'numeric', 'min:1'],
        ]);

        $user->credit += $request->credit;
        $user->save();

        return redirect()->route('users')->with('success', 'Credit added successfully.');
    }

    public function forgotPassword(Request $request)
    {
        return view('users.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|exists:users,email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetForm(Request $request, $token)
    {
        return view('users.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)->numbers()->letters()->mixedCase()->symbols()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => bcrypt($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}