<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserViewController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->integer('per_page', 10);

        $filters = $request->only(['search', 'role_id', 'is_active']);

        
        $users = $this->userService->paginate($filters, $perPage);

        return Inertia::render('Users/Index', [
            'users' => UserResource::collection($users), 
            'filters' => $filters,
        ]);
    }

    
    public function toggleStatus(Request $request, User $user)
    {
        $this->userService->toggleStatus($user);

        return redirect()->back();
    }


    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->back();
    }


    public function store(Request $request)
    {
       $this->userService->create($request->all());

        return redirect()->back();
    }
}
