<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;

use App\Models\User;

class UserController extends Controller
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected PostService $postService
    )
    {
        //
    }

    public function index(): JsonResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return response()->json('Permission denied', 403);
        }

        $users = User::all();
        return response()->json(['data' => $users]);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $user = $this->userRepository->getById($id);;

        if ($user === null) {
            return response()->json('User not found', 404);
        }

        return response()->json(['data' => $user]);
    }

    /**
     * @param UserStoreRequest $request
     * @return JsonResponse
     */
    public function store(UserStoreRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        if ($this->userRepository->getByEmail($request->getEmail())) {
            return response()->json('Email is already in use.', 400);
        }

        $user = User::query()->create([
            'name' => $request->getName(),
            'email' => $request->getEmail(),
            'password' => Hash::make($request->getPassword()),
            'permission' => 7
        ]);

        $this->postService->seedIntroData($user);

        return response()->json(['data' => $user], 201);
    }

    /**
     * @param UserUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UserUpdateRequest $request, int $id): JsonResponse
    {
        if (!Auth::user()->isAdmin()) {
            $id = Auth::id();
        }

        if ($request->getEmail()) {
            $emailUser = $this->userRepository->getByEmail($request->getEmail());

            if ($emailUser->id !== $id) {
                return response()->json('Email is already in use.', 400);
            }
        }

        $user = $this->userRepository->getById($id);

        if (!$user) {
            return response()->json('User not found.', 404);
        }

        $response = Gate::inspect('update', $user);

        if (!$response->allowed()) {
            return response()->json($response->message(), 403);
        }

        $user->update($request->only('name', 'email', 'theme'));

        if (Hash::check($request->getPasswordOld(), $user->password)) {
            $user->update([
                'password' => Hash::make($request->getPasswordNew())
            ]);
        }

        return response()->json(['data' => $user]);
    }

    /**
     * @param $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $user = $this->userRepository->getById($id);;

        if (!$user) {
            return response()->json('User not found.', 404);
        }

        $this->authorize('delete', User::class);

        return response()->json('User was deleted.');
    }
}
