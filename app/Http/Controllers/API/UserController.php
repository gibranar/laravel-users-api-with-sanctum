<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends BaseController
{
    public function index(): JsonResponse
    {
        $users = User::all();

        return $this->sendResponse(UserResource::collection($users), 'Users retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): JsonResponse
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'username' => 'required',
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $product = User::create($input);

        return $this->sendResponse(new UserResource($product), 'User created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id): JsonResponse
    {
        $user = User::find($id);

        if (is_null($user)) {
            return $this->sendError('User not found.');
        }

        return $this->sendResponse(new UserResource($user), 'User retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'username' => 'nullable',
            'name' => 'nullable',
            'email' => 'nullable|email',
            'password' => 'nullable',
            'confirm_password' => 'required_with:password|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error.',
                'errors' => $validator->errors()
            ], 422);
        }

        if (isset($input['username']) && User::where('username', $input['username'])->exists() && $input['username'] !== $user->username) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error.',
                'errors' => ['username' => ['Username already exists']]
            ], 422);
        } elseif (isset($input['email']) && User::where('email', $input['email'])->exists() && $input['email'] !== $user->email) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error.',
                'errors' => ['email' => ['Email already exists']]
            ], 422);
        }

        $user->username = $input['username'];
        $user->name = $input['name'];
        $user->email = $input['email'];
        if (isset($input['password'])) {
            $user->password = $input['password'];
        }
        $user->save();

        return $this->sendResponse(new UserResource($user), 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error.',
                'errors' => ['user' => ['You cannot delete yourself']]
            ], 422);
        }

        $user->delete();

        return $this->sendResponse([], 'User deleted successfully.');
    }
}
