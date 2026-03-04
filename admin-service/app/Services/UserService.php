<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    
    public function paginate(array $filters, int $perPage = 15)
    {
        $query = User::query()->with('role');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'ILIKE', "%{$search}%")
                  ->orWhere('last_name',  'ILIKE', "%{$search}%")
                  ->orWhere('email',      'ILIKE', "%{$search}%")
                  ->orWhere('phone',      'ILIKE', "%{$search}%");
            });
        }

        if (!empty($filters['role_id'])) {
            $query->where('role_id', $filters['role_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }


    public function create(array $data): User
    {
        $payload = [
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'] ?? null,
            'role_id'    => $data['role_id'],
            'is_active'  => $data['is_active'] ?? true,
            'password'   => Hash::make($data['password']),
        ];

        $user = User::create($payload);
        return $user->load('role');
    }


    public function update(User $user, array $data): User
    {
        $payload = collect($data)->only([
            'first_name', 'last_name', 'email', 'phone', 'role_id', 'is_active'
        ])->toArray();

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);
        return $user->load('role');
    }


    public function delete(User $user): void
    {
        $user->delete();
    }

    public function toggleStatus(User $user): User
    {
        $user->update(['is_active' => !$user->is_active]);
        return $user->fresh()->load('role');
    }
}