<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserSearchRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\PostSeedService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;

use App\Models\User;

class UserController extends Controller
{
    public function __construct(
        protected UserRepositoryInterface $userRepository,
        protected PostSeedService $postSeedService
    )
    {
        //
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return response()->json('Permission denied', 403);
        }

        $users = User::all();
        return response()->json(['data' => $users]);
    }

    /**
     * @param UserSearchRequest $request
     * @return JsonResponse
     */
    public function search(UserSearchRequest $request): JsonResponse
    {
        if (!Auth::check()) {
            return response()->json('Permission denied', 403);
        }

        $users = $this->userRepository->searchByEmail($request->getEmail(), [Auth::user()->email]);

        return response()->json(['data' => $users]);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $user = $this->userRepository->getById($id);;

        if (!$user) {
            throw new ModelNotFoundException(
                'User not found'
            );
        }

        return response()->json(['data' => $user]);
    }

    /**
     * @param UserUpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UserUpdateRequest $request, int $id): JsonResponse
    {
        $user = $this->userRepository->getById(Auth::id());

        if (!$user) {
            throw new ModelNotFoundException(
                'User not found'
            );
        }

        $response = Gate::inspect('update', $user);

        if (!$response->allowed()) {
            return response()->json($response->message(), 403);
        }

        $user->update($request->validated());

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
            throw new ModelNotFoundException(
                'User not found'
            );
        }

        $this->authorize('delete', User::class);

        return response()->json('User was deleted.');
    }
}
