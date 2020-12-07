<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\API\UserCollection;
use Carbon\Carbon;
use App\Http\Requests\API\UserRegisterRequest;
use Auth;

class AuthController extends Controller
{
    /**
     * @var successResponse
     * @var failResponse
     * @var createResponse
     * @var notFoundResponse
     * @var errorResponse
     */
    protected $successResponse,$failResponse,$createResponse,$notFoundResponse,$errorResponse;

    /**
     * AuthController constructor.
     *
     */
    public function __construct () {
        $this->successResponse = 200;
        $this->createResponse = 201;
        $this->notFoundResponse = 404;
        $this->failResponse = 400;
        $this->errorResponse = 500;
    }

    public function register(UserRegisterRequest $request)
    {
        $request['password'] = bcrypt($request->password);

        $user = User::create($request->all());
        
        $accessToken = $user->createToken('authToken')->accessToken;

        return response([
            'status' => true,
            'message' => 'Registered Successfully'
        ], $this->successResponse);
    }

    public function login(Request $request)
    {
        $loginData = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if (!auth()->attempt($loginData)) {
            return response([
                'status' => false,
                'message' => 'Invalid Credentials',
            ], $this->failResponse);
        }

        $accessToken = auth()->user()->createToken('authToken')->accessToken;

        $user = auth()->user();

        $user['token'] = $accessToken;

        return response([
            'status' => true,
            'message' => 'Welcome ...',
            'user' => new UserCollection(auth()->user()),
        ], $this->successResponse);

    }

    public function logout()
    { 
        $user = Auth::user()->token();
        $user->revoke();
        //return data
        return response()->json([
            'status' => true,
            'message' => " looged out Successfully",
        ], $this->successResponse);
    }
}
