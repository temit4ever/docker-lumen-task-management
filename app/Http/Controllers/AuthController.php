<?php


namespace App\Http\Controllers;


use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
  /**
   * Constructor
   */
  public function __construct()
  {
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function register(Request $request)
  {
    $this->validate(
      $request,
      [
        'name' => 'required| max:50',
        'username' => 'required| max:50',
        'email' => 'required|email|unique:users',
        'password' => [
          'required',
          'string',
          'min:10',
          'regex:/[a-z]/',      // must contain at least one lowercase letter
          'regex:/[A-Z]/',      // must contain at least one uppercase letter
          'regex:/[0-9]/',      // must contain at least one digit
          'regex:/[@$!%*#?&]/', // must contain a special character
          ],
        ]
    );
     $user = User::create([
      'name' => $request->name,
      'username' => $request->username,
      'email' => $request->email,
      'password' => password_hash($request->password, PASSWORD_DEFAULT),
      'api_token' => app('hash')->make($request->api_token),
      'login_at' => time(),
    ]);

     //$user = User::create($request->all());

    return response()->json(['user' => $user, 'message' => "{$user->name} you have successfully registered"], '201');
  }

  public function loginUser(Request $request)
  {
    $this->validate(
      $request,
      [
        'email' => 'required|',
        'password' => 'required',
      ]
    );

    $loginUser = User::where('email', $request->email)->first();

    if (!$loginUser) {
      return response()->json(['status' => 'error', 'message' => 'This user does not exist'], '401');
    }
    // Check if the password user is using to log in is the same as the one they registered with
    if (Hash::check($request->password, $loginUser->password)) {
      $loginUser->api_token  = app('hash')->make('api_token');
      $loginUser->save();
      return response()->json(['login_user' => $loginUser, 'message' => "{$loginUser->name} you have successfully log in"], '200');
    }
    else {
      return response()->json(['status' => 'error', 'message' => 'The password does not match'], '401');
    }
  }

  public function logoutUser(Request $request) {
    $logoutUser = User::where('api_token', trim($request->api_token))->first();

    if ($logoutUser) {
      $logoutUser->api_token = null;
      $logoutUser->save();
      return response()->json(['logout_user' => $logoutUser, 'message' => "{$logoutUser->name} you have successfully log out"], '200');
    }
  }


}
