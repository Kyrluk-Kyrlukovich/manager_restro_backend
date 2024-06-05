<?php

namespace App\Http\Controllers;


use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignupRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\IndexUsers;
use App\Models\Nav_tab;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public function signup(SignupRequest $request)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        if($root->canCreateUser) {
            User::create(['password' => Hash::make($request->password)] + $request->validated());
            return response()->json([
                "data" => [
                    "code" => "200",
                    "message" => "Пользователь создан успешно"
                ]
            ], 200);
        }
        return response()->json([
            "error" => [
                "code" => "403",
                "message" => "У вас недостаточно прав для создания пользователя"
            ]
        ], 403);
    }

    public function login(LoginRequest $request)
    {
        if (Auth::attempt($request->only(['email', 'password']))) {
            $user = Auth::user();
            if($user->status == 'dismissed') {
                return response()->json([
                    'error' => [
                        'code' => '403',
                        'message' => 'Ваш аккаунт не активен, так как вы были уволены'
                    ]
                ], 403);
            }
            $user->tokens()->delete();
            $token = $user->createToken('api');
            return response()->json([
                'data' => [
                    'token' => $token->plainTextToken
                ]
            ]);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'Неверный логин или пароль'
                ]
            ], 403);
        }
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        $user->tokens()->delete();
        $request->session()->flush();
        return response()->json([
            'data' => [
                'message' => 'Вы успешно вышли зи аккаунта'
            ]
        ], 200);
    }

    public function loginInfo()
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        if($user->status == 'dismissed') {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'Ваш аккаунт не активен, так как вы были уволены'
                ]
            ], 403);
        }
        $role = Role::where([['id', $user->roles]])->first();
        $tabs = Nav_tab::all()->filter(function ($tab) use ($root) {
            if($tab->code == "settings-roles") {
                return $root->canWatchSettingsRolesTab;
            }
            return  true;
        });
        return response()->json([
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'patronymic' => $user->patronymic,
                    'roles' => $role,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'salary' => $user->salary,
                    'avatar' => $user->avatar,
                ],
                'tabs' => $tabs,
            ]
        ], 200);
    }

    public function getFormCreateUser()
    {
        $userAuth = Auth::user();

        $root = $userAuth->role()->first()->root()->first();
        if ($root->canCreateUser) {
            $roles = Role::all()->map(function ($elem, $key) {
                return [
                    'id' => $elem->id,
                    'code' => 'role',
                    'type' => 'text',
                    'value' => $elem->role,
                ];
            });
            return response()->json([
                'data' => [
                    'form' => [
                        [
                            'code' => 'first_name',
                            'type' => 'text',
                            'value' => null,
                            'placeholder' => 'Ваше имя',
                            'canEdit' => true,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'last_name',
                            'type' => 'text',
                            'value' => null,
                            'placeholder' => 'Ваша фамилия',
                            'canEdit' => true,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'patronymic',
                            'type' => 'text',
                            'value' => null,
                            'placeholder' => 'Ваше отчество',
                            'canEdit' => true,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'roles',
                            'type' => 'select',
                            'value' => 3,
                            'placeholder' => 'Роль',
                            'canEdit' => $root->canEditUsers,
                            'possibleValues' => $roles
                        ],
                        [
                            'code' => 'email',
                            'type' => 'text',
                            'value' => null,
                            'placeholder' => 'Email',
                            'canEdit' => true,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'phone',
                            'type' => 'text',
                            'value' => null,
                            'placeholder' => 'Ваш телефон',
                            'canEdit' => true,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'salary',
                            'type' => 'text',
                            'value' => null,
                            'placeholder' => 'Зарплата',
                            'canEdit' => true,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'password',
                            'type' => 'password',
                            'value' => null,
                            'placeholder' => 'Пароль',
                            'canEdit' => true,
                            'possibleValues' => null,
                        ],
                    ]
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав'
                ]
            ], 403);
        }
    }

    public function getFormUpdateUser(string $id, UpdateUserRequest $request)
    {
        $userAuth = Auth::user();
        $roles = Role::all()->map(function ($elem, $key) {
            return [
                'id' => $elem->id,
                'code' => 'role',
                'type' => 'text',
                'value' => $elem->role,
            ];
        });

        if ($id == $userAuth->id) {
            $role = $userAuth->role()->first();
            return response()->json([
                'data' => [
                    'editMode' => true,
                    'form' => [
                        [
                            'code' => 'avatar',
                            'type' => 'image',
                            'value' => $userAuth->avatar,
                            'placeholder' => '',
                            'canEdit' => true,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'first_name',
                            'type' => 'text',
                            'value' => $userAuth->first_name,
                            'placeholder' => 'Ваше имя',
                            'canEdit' => true,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'last_name',
                            'type' => 'text',
                            'value' => $userAuth->last_name,
                            'placeholder' => 'Ваша фамилия',
                            'canEdit' => true,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'patronymic',
                            'type' => 'text',
                            'value' => !$userAuth->patronymic ? '' : $userAuth->patronymic,
                            'placeholder' => 'Ваше отчество',
                            'canEdit' => true,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'roles',
                            'type' => 'select',
                            'value' => $role->id,
                            'placeholder' => 'Роль',
                            'canEdit' => $userAuth->roles == 1,
                            'possibleValues' => $roles,
                        ],
                        [
                            'code' => 'email',
                            'type' => 'text',
                            'value' => $userAuth->email,
                            'placeholder' => 'Email',
                            'canEdit' => true,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'phone',
                            'type' => 'text',
                            'value' => $userAuth->phone,
                            'placeholder' => 'Ваш телефон',
                            'canEdit' => true,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'salary',
                            'type' => 'text',
                            'value' => $userAuth->salary,
                            'placeholder' => 'Зарплата',
                            'canEdit' => true,
                            'possibleValues' => null,
                        ],
                    ]
                ]
            ], 200);
        } else {
            $user = User::where('id', $id)->first();
            $role = $user->role()->first();
            $authRoot = $userAuth->role()->first()->root()->first();
            $root = $role->root()->first();
            return response()->json([
                'data' => [
                    'editMode' => $authRoot->canEditUsers,
                    'form' => [
                        [
                            'code' => 'avatar',
                            'type' => 'image',
                            'value' => $user->avatar,
                            'placeholder' => '',
                            'canEdit' => $authRoot->canEditUsers,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'first_name',
                            'type' => 'text',
                            'value' => $user->first_name,
                            'placeholder' => 'Ваше имя',
                            'canEdit' => $authRoot->canEditUsers,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'last_name',
                            'type' => 'text',
                            'value' => $user->last_name,
                            'placeholder' => 'Ваша фамилия',
                            'canEdit' => $authRoot->canEditUsers,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'patronymic',
                            'type' => 'text',
                            'value' => !$user->patronymic ? '' : $user->patronymic,
                            'placeholder' => 'Ваше отчество',
                            'canEdit' => $authRoot->canEditUsers,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'roles',
                            'type' => 'select',
                            'value' => $role->id,
                            'placeholder' => 'Роль',
                            'canEdit' => $userAuth->roles == 1,
                            'possibleValues' => $roles,
                        ],
                        [
                            'code' => 'email',
                            'type' => 'text',
                            'value' => $user->email,
                            'placeholder' => 'Email',
                            'canEdit' => $authRoot->canEditUsers,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'phone',
                            'type' => 'text',
                            'value' => $user->phone,
                            'placeholder' => 'Ваш телефон',
                            'canEdit' => $authRoot->canEditUsers,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'salary',
                            'type' => 'text',
                            'value' => $user->salary,
                            'placeholder' => 'Зарплата',
                            'canEdit' => $authRoot->canEditUsers,
                            'possibleValues' => null,
                        ],
                    ]
                ]
            ], 200);
        }

    }


    public function updateUserInfo(string $id, UpdateUserRequest $request)
    {
        $authUser = Auth::user();
        $user = User::where('id', $id)->first();
        $emailCheck = User::where([['email', $request->email], ['id', '!=', $user->id]])->first();
        $phoneCheck = User::where([['phone', $request->phone], ['id', '!=', $user->id]])->first();
        $root = $authUser->id == $user->id ? true : $authUser->role()->first()->root()->first()->canEditUsers;
        if (!$emailCheck && !$phoneCheck) {
            if ($root) {
                if($request->file('avatar')) {
                    $avatar = $request->file('avatar')->store('/public');
                    $user->update(['avatar' => $avatar] + $request->validated());
                } else {
                    $user->update($request->validated());
                }

                return response()->json([
                    'data' => [
                        'message' => 'Данные изменены',
                        'user' => $request->roles
                    ]
                ], 200);
            } else {
                return response()->json([
                    'error' => [
                        'code' => '403',
                        'message' => 'У вас недостаточно прав для редактирования пользователя'
                    ]
                ], 403);
            }
        } else if ($phoneCheck) {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'Этот телефон уже используется'
                ]
            ], 403);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'Этот email  уже используется'
                ]
            ], 403);
        }
    }

    public function getUsers()
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        $users = IndexUsers::collection(User::all());
        return response()->json([
            'data' => [
                'canCreateUser' => $root->canEditUsers,
                'canDismiss' => $root->canEditDismissUser,
                'users' => $users,
            ]
        ], 200);
    }

    public function dismissUser(string $id)
    {
        $authUser = Auth::user();
        $root = $authUser->role()->first()->root()->first();
       if($root->canEditDismissUser) {
           $user = User::where('id', $id)->first();
           if($user->status != 'dismissed') {
               $user->status = 'dismissed';
               $user->save();
               return response()->json([
                   'data' => [
                       'message' => 'Сотрдуник уволен'
                   ]
               ], 200);
           } else {
               return response()->json([
                   'error' => [
                       'code' => '403',
                       'message' => 'Сотрдуник уже уволен'
                   ]
               ], 403);
           }
       } else {
           return response()->json([
               'error' => [
                   'code' => '403',
                   'message' => 'У вас недостаточно прав для увольнения пользователя'
               ]
           ], 403);
       }
    }

    public function rehire(string $id)
    {
        $authUser = Auth::user();
        $root = $authUser->role()->first()->root()->first();
        if($root->canEditDismissUser) {
            $user = User::where('id', $id)->first();
            if($user->status != 'work') {
                $user->status = 'work';
                $user->save();
            } else {
                return response()->json([
                    'error' => [
                        'code' => '403',
                        'message' => 'Сотрдуник уже работает'
                    ]
                ], 403);
            }

        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                        'message' => 'У вас недостаточно прав для восстановления пользователяв в статус "Работает"'
                ]
            ], 403);
        }
    }
}
