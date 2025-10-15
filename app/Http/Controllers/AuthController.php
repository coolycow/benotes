<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginCodeRequest;
use App\Http\Requests\LoginPasswordRequest;
use App\Http\Requests\ResetRequest;
use App\Http\Requests\SendCodeRequest;
use App\Http\Requests\SendResetRequest;
use App\Mail\CodeMail;
use App\Models\User;
use App\Services\ConfirmationCodeService;
use App\Services\PostService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Psr\SimpleCache\InvalidArgumentException;
use Random\RandomException;

class AuthController extends Controller
{
    public function __construct(
        protected PostService $postService,
        protected ConfirmationCodeService $confirmationCodeService
    )
    {
        //
    }

    /**
     * @param SendCodeRequest $request
     * @return JsonResponse
     * @throws RandomException|InvalidArgumentException
     */
    public function sendCode(SendCodeRequest $request): JsonResponse
    {
        $email = $request->getEmail();

        if (Cache::has("auth:code:{$email}")) {
            return response()->json(['message' => 'Email already sent code'], 400);
        }
        $code = $this->confirmationCodeService->generate();

        Cache::put("auth:code:{$email}", $code, now()->addMinutes(15));

        // Отправляем письмо
        Mail::to($email)->queue(new CodeMail($code));

        return response()->json(['message' => 'Code sent to your email']);
    }

    /**
     * @param LoginPasswordRequest $request
     * @return JsonResponse
     */
    public function loginPassword(LoginPasswordRequest $request): JsonResponse
    {
        $token = Auth::attempt($request->only('email', 'password'));

        if (!$token) {
            return response()->json('Invalid login credentials', 400);
        }

        $data = [
            "token" => [
                "access_token" => $token,
                "token_type" => 'Bearer',
                "expire" => (int) config('jwt.ttl')
            ]
        ];

        return response()->json(compact('data'));
    }

    /**
     * @param LoginCodeRequest $request
     * @return JsonResponse
     */
    public function loginCode(LoginCodeRequest $request): JsonResponse
    {
        $email = $request->getEmail();
        $code = $request->getCode();

        // 2. Получаем email из Redis по ключу вида: "auth:code:{email}"
        $storedCode = Cache::get("auth:code:{$email}");

        if (!$storedCode || $storedCode !== $code) {
            throw ValidationException::withMessages([
                'code' => 'Invalid or expired confirmation code.',
            ])->errorBag('login');
        }

        try {
            // 3. Находим пользователя по email
            /**
             * @var Authenticatable|User $user
             */
            $user = User::query()->where('email', $email)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            $user = User::query()->create([
                'email' => $email,
                'name' => strstr($email, '@', true),
                'password' => Str::random(),
            ]);

            $this->postService->seedIntroData($user);
        }

        Auth::setUser($user);

        // 4. Авторизуем пользователя (через Sanctum или JWT)
        if (!Auth::check()) {
            return response()->json(['error' => 'Failed to authenticate user'], 401);
        }

        // 5. Генерируем токен (если используешь Sanctum)
        $token = auth()->tokenById($user->getKey());

        // 6. Удаляем использованный код из кэша
        Cache::forget("auth:code:{$email}");

        // 7. Формируем ответ
        $data = [
            'token' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                "expire" => (int) config('jwt.ttl')
            ],
        ];

        return response()->json(compact('data'), 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json(['data' => Auth::user()]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function refresh(Request $request): JsonResponse
    {
        if ($request->bearerToken() === 'null') {
            return response()->json('', Response::HTTP_BAD_REQUEST);
        }

        try {
            $data = [
                "token" => [
                    "access_token" => Auth::refresh(),
                    "token_type" => 'Bearer',
                    "expire" => (int) config('jwt.ttl')
                ]
            ];
            return response()->json(compact('data'));
        } catch (JWTException $e) {
            return response()->json('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        Auth::logout();
        return response()->json('', 204);
    }

    /**
     * @param SendResetRequest $request
     * @return JsonResponse
     */
    public function sendReset(SendResetRequest $request): JsonResponse
    {
        $response = Password::broker()->sendResetLink(
            $request->only('email')
        );

        if ($response === Password::RESET_LINK_SENT) {
            return response()->json(trans('passwords.sent'));
        } else if (trans($response)) {
            return response()->json(['error' => trans($response)], 500);
        } else {
            return response()->json(['error' => $response], 500);
        }
    }

    /**
     * @param ResetRequest $request
     * @return JsonResponse
     */
    public function reset(ResetRequest $request): JsonResponse
    {
        $response = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->password = Hash::make($password);
                $user->save();
            }
        );

        if ($response === Password::PASSWORD_RESET) {
            return response()->json(trans('passwords.reset'));
        } else if (trans($response)) {
            return response()->json(['error' => trans($response)], 500);
        } else {
            return response()->json(['error' => $response], 500);
        }
    }
}
