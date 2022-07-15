<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
  use AuthenticatesUsers;
  
  public function username()
  {
      return 'name';
  }
  public function login($request)
  {
    
    $credentials = $this->credentials($request);

    if (!$token = Auth::guard('api')->attempt($credentials)) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }

    $user = User::where('name', $request->input('name'))->first();
    $now  = Carbon::now();

    if ($user->access_token && $now->lessThan((new Carbon($user->time_access))->addHours(12)) && $user->id != 1) {
      return response()->json(['message' => 'Usuário já logado!', 'user_logged' => true], 401);
    } elseif ($user->access_token && $user->id != 1) {
      try {
        JWTAuth::setToken($user->access_token);
        JWTAuth::invalidate($user->access_token);
      } catch (JWTException $ex) {
        $user->access_token = null;
      }
    }

    $user->update(['access_token' => $token, 'time_access' => $now]);
    $this->authenticated($request, $user);

    return $this->createNewToken($token);
  }

  public function refresh()
  {
    return $this->createNewToken(Auth::guard('api')->refresh());
  }

  private function createNewToken($token)
  {
    return response()->json([
      'access_token' => $token,
      'token_type'   => 'bearer',
      'expires_in'   => Auth::guard('api')->factory()->getTTL() * 60
    ]);
  }

  public function logout()
  {
    $user = User::find(auth('api')->user()->id);
    $user->update(['access_token' => null, 'time_access' => null]);
    Auth::guard('api')->logout();

    return response()->json([], 204);
  }

  public function loginForce($request)
  {
    $credentials = $this->credentials($request);

    if (!$token = auth('api')->attempt($credentials)) {
      return response()->json(['error' => 'Unauthorized'], 401);
    }

    $user = User::where('name', $request->input('name'))->first();

    try {
      JWTAuth::setToken($user->access_token);
      JWTAuth::invalidate($user->access_token);
    } catch (JWTException $ex) {
      $user->access_token = null;
    }

    //$now = Carbon::now();
    //$user->update(['access_token' => null, 'time_access' => null]);
    //$user->update(['access_token' => $token, 'time_access' => $now]);
    //$this->authenticated($request, $user);

    return $this->createNewToken($token);
  }
}
