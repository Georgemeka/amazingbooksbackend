<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller {
    /**
    * Create User
    * @param Request $request
    * @return User
    */

    public function createUser( Request $request ) {
        if (!$this->phoneFormatValidation($request->phone)) {
            return response()->json( [
                'status' => false,
                'message' => 'Invalid Phone number provided.',
            ], 500 );
        }
        
        try {
            $validateUser = Validator::make( $request->all(),
            [
                'name' => 'required',
                'phone' => 'required|unique:users,phone',
                'password' => 'required'
            ] );

            if ( $validateUser->fails() ) {
                return response()->json( [
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401 );
            }

            $user = User::create( [
                'name' => $request->name,
                'phone' => $request->phone,
                'password' => Hash::make( $request->password )
            ] );

            return response()->json( [
                'status' => true,
                'message' => 'User Created Successfully',
                'token' => $user->createToken( 'API TOKEN' )->plainTextToken
            ], 200 );

        } catch ( \Throwable $th ) {
            //If there is any form of error ... return 500

            return response()->json( [
                'status' => false,
                'message' => $th->getMessage()
            ], 500 );
        }
    }

    /**
    * Login The User
    * @param Request $request
    * @return User
    */

    public function loginUser( Request $request ) {
        if (!$this->phoneFormatValidation($request->phone)) {
            return response()->json( [
                'status' => false,
                'message' => 'Invalid Phone number provided.',
            ], 500 );
        }
        
        try {
            $validateUser = Validator::make( $request->all(),
            [
                'phone' => 'required',
                'password' => 'required'
            ] );

            if ( $validateUser->fails() ) {
                return response()->json( [
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401 );
            }

            if ( !Auth::attempt( $request->only( [ 'phone', 'password' ] ) ) ) {
                return response()->json( [
                    'status' => false,
                    'message' => 'Phone & Password does not match with our record.',
                ], 401 );
            }

            $user = User::where( 'phone', $request->phone )->first();

            return response()->json( [
                'status' => true,
                'message' => 'User Logged In Successfully',
                'token' => $user->createToken( 'API TOKEN' )->plainTextToken
            ], 200 );

        } catch ( \Throwable $th ) {
            return response()->json( [
                'status' => false,
                'message' => $th->getMessage()
            ], 500 );
        }
    }
    
    private function phoneFormatValidation($phone) {
        if(preg_match('/^[0-9]{11}+$/', $phone)) {
            return true;
        } else {
            return false;
        }
    }
}