<?php

namespace App\Http\Controllers;

use App\Events\StoreNotificationEvent;
use App\Http\Requests\CreateTableRequest;
use App\Http\Requests\UpdateTableRequest;
use App\Http\Resources\IndexUsers;
use App\Models\Notification;
use App\Models\Table;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TablesController extends Controller
{
    public function getTables(Request $request)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        $tb = Table::all();
        $query = $request->get('tab');
        if ($query && $query != 'view') {
            $tb = Table::where('status', $query)->get();
        }
        $tables = $tb->map(function ($table, $key) {
            return [
                'id' => $table->id,
                'number' => $table->number,
                'status' => $table->status,
                'status_name' => $table->status == 'emty' ? 'Свободен' : ($table->status == 'reserved' ? 'Забронирован' : 'Занят'),
                'placements' => $table->placements,
                'served' => $table->served ? new IndexUsers(User::where('id', $table->served)->first()) : null,
                'actions' => [
                    'emty' => 'Свободен',
                    'reserved' => 'Забронирован',
                    'taken' => 'Занят',
                ],
            ];
        });
        return response()->json([
            'data' => [
                'tables' => $tables,
                'actions' => $root->canEditTables,
                'tabs' => [
                    'active' => $query ? $query : 'view',
                    'items' => [
                        'view' => 'Обзор',
                        'emty' => 'Свободен',
                        'reserved' => 'Забронирован',
                        'taken' => 'Занят',
                    ]
                ]
            ]
        ], 200);
    }

    public function updateTable(string $id, UpdateTableRequest $request)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        if ($root->canEditTables) {
            $table = Table::where('id', $id)->first();
            $checkTableNumber = Table::where([['id', '!=', $id], ['number', $request->number]])->first();
            if (!$checkTableNumber) {
                $table->update($request->validated());
                if ($request->status == 'emty') {
                    $tablesPath = $request->header('origin') . "/tables?tab=emty";
                    $usersWaiter = User::where('roles', 2)->get();
                    foreach ($usersWaiter as $waiter) {
                        $message = "Появились свободные столы. <a class='a-notify' href='$tablesPath'>Ознакомьтесь с освободившимися столами</a>";
                        Notification::create([
                            "message" => $message,
                            "user" => $waiter->id,
                            "read" => false,
                        ]);
                        event(new StoreNotificationEvent($message, $waiter));
                        Notification::create([
                            "message" => $message,
                            "user" => $waiter->id,
                            "read" => false,
                        ]);
                    }
                }
            } else {
                return response()->json([
                    'error' => [
                        'code' => '403',
                        'message' => 'Стол с таким номер уже существует',
                    ]
                ], 403);
            }
            return response()->json([
                'data' => [
                    'message' => $table,
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для редактирования стола'
                ]
            ], 403);
        }
    }

    public function getFormCreateTable()
    {
        $user = Auth::user();
        $root = $user->role->first()->root()->first();
        if ($root->canEditTables) {
            return response()->json([
                'data' => [
                    'editMode' => $root->canEditUsers,
                    'form' => [
                        [
                            'code' => 'number',
                            'type' => 'number',
                            'value' => '',
                            'placeholder' => 'Введите номер стола',
                            'canEdit' => $root->canEditTables,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'placements',
                            'type' => 'number',
                            'value' => '',
                            'placeholder' => 'Введите количество посадочных мест',
                            'canEdit' => $root->canEditTables,
                            'possibleValues' => null,
                        ]
                    ]
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для создания стола'
                ]
            ], 403);
        }
    }

    public function createTable(CreateTableRequest $request)
    {
        $user = Auth::user();
        $root = $user->role->first()->root()->first();
        if ($root->canEditTables) {
            $table = Table::create($request->validated());
            return response()->json([
                'data' => [
                    'message' => 'Стол успешно создан'
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для создания стола'
                ]
            ], 403);
        }
    }

    public function getFormEditTable(string $id)
    {
        $user = Auth::user();
        $table = Table::where('id', $id)->first();
        $root = $user->role->first()->root()->first();
        if ($root->canEditTables) {
            return response()->json([
                'data' => [
                    'editMode' => $root->canEditTables,
                    'form' => [
                        [
                            'code' => 'number',
                            'type' => 'number',
                            'value' => $table->number,
                            'placeholder' => 'Введите номер стола',
                            'canEdit' => $root->canEditTables,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'placements',
                            'type' => 'number',
                            'value' => $table->placements,
                            'placeholder' => 'Введите количество посадочных мест',
                            'canEdit' => $root->canEditTables,
                            'possibleValues' => null,
                        ]
                    ]
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для создания стола'
                ]
            ], 403);
        }
    }

    public function deleteTable(string $id)
    {
        $user = Auth::user();
        $root = $user->role->first()->root()->first();
        if ($root->canEditTables) {
            $table = Table::where('id', $id)->first()->delete();
            return response()->json([
                'data' => [
                    'message' => 'Стол успешно удален'
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для удаления стола'
                ]
            ], 403);
        }
    }
}
