<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Http\Requests\UpdateRootsRequest;
use App\Models\Role;
use App\Models\Root;
use Illuminate\Support\Facades\Auth;

class RolesController extends Controller
{
    public function getRoles()
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();

        if ($root->canWatchSettingsRolesTab) {
            $roles = Role::all();
            return response()->json([
                'data' => [
                    "roles" => $roles,
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для просмотра ролей'
                ]
            ], 403);
        }
    }

    public function getRootsRole(string $id)
    {
        $user = Auth::user();
        $role = $user->role()->first();
        $root = $role->root()->first();
        if ($root->canWatchSettingsRolesTab) {
            $roots = Role::where('id', $id)->first()->root()->first();
            return response()->json([
                'data' => [
                    "roleId" => $role->id,
                    "rootId" => $roots->id,
                    "roots" => $roots->makeHidden(['id', 'created_at', 'updated_at']),
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для просмотра ролей'
                ]
            ], 403);
        }
    }

    public function updateRoot(string $id, UpdateRootsRequest $request)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        if ($root->canWatchSettingsRolesTab) {
            $rootUser = Root::where('id', $id)->first();
            $rootUser->update($request->validated());
            return response()->json([
                'data' => [
                    'code' => '200',
                    'message' => 'Права обновлены'
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для изменения прав'
                ]
            ], 403);
        }
    }

    public function getFormCreateRole()
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        if ($root->canWatchSettingsRolesTab) {
            return response()->json([
                'data' => [
                    'form' => [
                        [
                            'code' => 'code',
                            'type' => 'text',
                            'value' => '',
                            'placeholder' => 'Придумайте уникальный код для роли',
                            'canEdit' => $root->canWatchSettingsRolesTab,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'role',
                            'type' => 'text',
                            'value' => '',
                            'placeholder' => 'Название роли',
                            'canEdit' => $root->canWatchSettingsRolesTab,
                            'possibleValues' => null,
                        ],
                    ]
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для создания роли'
                ]
            ], 403);
        }
    }

    public function getFormUpdateRole(string $id)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        if ($root->canWatchSettingsRolesTab) {
            $role = Role::where('id', $id)->first();
            return response()->json([
                'data' => [
                    'form' => [
                        [
                            'code' => 'code',
                            'type' => 'text',
                            'value' => $role->code,
                            'placeholder' => 'Придумайте уникальный код для роли',
                            'canEdit' => $root->canWatchSettingsRolesTab,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'role',
                            'type' => 'text',
                            'value' => $role->role,
                            'placeholder' => 'Название роли',
                            'canEdit' => $root->canWatchSettingsRolesTab,
                            'possibleValues' => null,
                        ],
                    ]
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для редактирования роли'
                ]
            ], 403);
        }
    }

    public function createRole(CreateRoleRequest $request)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        if ($root->canWatchSettingsRolesTab) {
            $newRoot = Root::create();
            $dataValidate = $request->validated();
            Role::create(array_merge($dataValidate, ["root_id" => $newRoot->id]));
            return response()->json([
                'data' => [
                    'code' => '200',
                    "rootId" => $newRoot->id,
                    'message' => 'Роль усешно создана'
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для создания роли'
                ]
            ], 403);
        }
    }
    public function updateRole(string $id, UpdateRoleRequest $request)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        if ($root->canWatchSettingsRolesTab) {
            $checkRole = Role::where([["id", "!=", $id], ["code", $request->code]])->first();
            if(!$checkRole) {
                $role = Role::where("id", $id)->first();
                $role->update($request->validated());
                return response()->json([
                    'data' => [
                        'code' => '200',
                        'message' => 'Роль усешно изменена'
                    ]
                ], 200);
            } else {
                return response()->json([
                    'error' => [
                        'code' => '403',
                        'message' => 'Роль с таким кодом уже существует'
                    ]
                ], 403);
            }
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для создания роли'
                ]
            ], 403);
        }
    }
}
