<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('account_type')) {
            $query->where('account_type', $request->string('account_type'));
        }

        if ($request->filled('q')) {
            $query->where(function ($q) use ($request) {
                $term = '%' . $request->string('q') . '%';
                $q->where('name', 'like', $term)->orWhere('email', 'like', $term);
            });
        }

        return UserResource::collection($query->latest()->paginate($request->integer('per_page', 20)));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'account_type' => ['required', Rule::in(['user', 'business', 'admin'])],
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['email_verified_at'] = now();

        $user = User::query()->create($data);

        return new UserResource($user);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'account_type' => ['sometimes', Rule::in(['user', 'business', 'admin'])],
        ]);

        if (($data['account_type'] ?? null) !== 'admin' && $user->isAdmin() && $this->isLastAdmin($user)) {
            abort(422, 'Cannot demote the last remaining admin.');
        }

        $user->update($data);

        return new UserResource($user);
    }

    public function destroy(User $user)
    {
        if ($user->isAdmin() && $this->isLastAdmin($user)) {
            abort(422, 'Cannot delete the last remaining admin.');
        }

        $user->delete();

        return response()->json(['message' => 'User deleted.']);
    }

    private function isLastAdmin(User $user): bool
    {
        return User::query()->where('account_type', 'admin')->where('id', '!=', $user->id)->doesntExist();
    }
}
