<?php

namespace App\Http\Controllers;

use App\Events\StoreNotificationEvent;
use App\Http\Requests\CreateShiftRequest;
use App\Http\Requests\UpdateShiftRequest;
use App\Models\Notification;
use App\Models\Role;
use App\Models\Shift;
use App\Models\UserShift;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftsController extends Controller
{
    public function getShifts()
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        $shifts = Shift::orderBy('date_start', 'asc')->get()->map(function ($shift, $key) use ($user) {
            return [
                'id' => $shift->id,
                'name' => $shift->name,
                'date_start' => $shift->date_start,
                'date_end' => $shift->date_end,
                'hours' => $shift->hours,
                'count_staff' => $shift->count_staff,
                'users' => $shift->users->map(function ($user, $key) {
                    $user->roleName = Role::where('id', $user->roles)->first()->role;
                    return $user;
                }),
                'shift' => [
                    'active' => $shift->userShifts()->where('user', $user->id)->first() == null ? false : (bool)$shift->userShifts()->where('user', $user->id)->first()->active,
                    'freely' => count($shift->userShifts()->get()) < (int)$shift->count_staff,
                    'include' => $shift->userShifts()->where('user', $user->id)->first() != null,
                ],
                'active' => $shift->userShifts()->where('user', $user->id)->first() == null ? false : (bool)$shift->userShifts()->where('user', $user->id)->first()->active,
            ];
        });


        return response()->json([
            'shifts' => $shifts,
            'canCreateShift' => $root->canCreateShift,
            'canEditShift' => (bool) $root->canEditShift,
        ], 200);
    }

    public function takeShift(string $id)
    {
        $user = Auth::user();
        $shift = Shift::where('id', $id)->first();
        if ($shift) {
            $includeUser = $user->shifts()->where('shift', $shift->id)->first();
            if (!$includeUser) {
                $shift->users()->attach($user->id, [
                    'active' => false,
                    'force' => false,
                    'orders' => 0,
                ]);
                return response()->json([
                    'data' => [
                        'code' => '200',
                        'message' => 'Вы успешно взяли смену',
                    ]
                ], 200);
            } else {
                return response()->json([
                    'error' => [
                        'code' => '403',
                        'message' => 'Вы уже учавствуете в этой смене',
                    ]
                ], 403);
            }
        } else {
            return response()->json([
                'error' => [
                    'code' => '404',
                    'message' => 'Смена не найдена',
                ]
            ], 404);
        }
    }

    public function rejectShift(string $id)
    {
        $user = Auth::user();
        $shift = Shift::where('id', $id)->first();
        if ($shift) {
            $includeUser = $user->shifts()->where('shift', $shift->id)->first();
            if ($includeUser) {
                $shift->users()->detach($user->id);
                return response()->json([
                    'data' => [
                        'code' => '200',
                        'message' => 'Вы отказались от участия в этой смене',
                    ]
                ], 200);
            } else {
                return response()->json([
                    'error' => [
                        'code' => '403',
                        'message' => 'Вы не учавствуете в этой смене',
                    ]
                ], 403);
            }
        } else {
            return response()->json([
                'error' => [
                    'code' => '404',
                    'message' => 'Смена не найдена',
                ]
            ], 404);
        }
    }

    public function getFormCreateShift()
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();

        if($root->canCreateShift) {
            return response()->json([
                'data' => [
                    'form' => [
                        [
                            'code' => 'name',
                            'type' => 'text',
                            'value' => '',
                            'placeholder' => 'Введите название смены',
                            'canEdit' => $root->canCreateShift,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'date_start',
                            'type' => 'datetime',
                            'value' => '',
                            'placeholder' => 'Выберите дату и время начала смены',
                            'canEdit' => $root->canCreateShift,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'date_end',
                            'type' => 'datetime',
                            'value' => '',
                            'placeholder' => 'Выберите дату и время окончания смены',
                            'canEdit' => $root->canCreateShift,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'count_staff',
                            'type' => 'number',
                            'value' => '',
                            'placeholder' => 'Укажите количество человек необходимое на эту смену',
                            'canEdit' => $root->canCreateShift,
                            'possibleValues' => null,
                        ],
                    ]
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для создания смены',
                ]
            ], 403);
        }
    }

    public function getFormEditShift(string $id)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();

        if($root->canCreateShift) {
            $shift = Shift::where('id', $id)->first();
            return response()->json([
                'data' => [
                    'form' => [
                        [
                            'code' => 'name',
                            'type' => 'text',
                            'value' => $shift->name,
                            'placeholder' => 'Введите название смены',
                            'canEdit' => $root->canEditShift,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'date_start',
                            'type' => 'datetime',
                            'value' => $shift->date_start,
                            'placeholder' => 'Выберите дату и время начала смены',
                            'canEdit' => $root->canEditShift,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'date_end',
                            'type' => 'datetime',
                            'value' => $shift->date_end,
                            'placeholder' => 'Выберите дату и время окончания смены',
                            'canEdit' => $root->canEditShift,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'count_staff',
                            'type' => 'number',
                            'value' => $shift->count_staff,
                            'placeholder' => 'Укажите количество человек необходимое на эту смену',
                            'canEdit' => $root->canEditShift,
                            'possibleValues' => null,
                        ],
                    ]
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для создания смены',
                ]
            ], 403);
        }
    }

    public function createShift(CreateShiftRequest $request) {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        if($root->canCreateShift) {
            $validateData = $request->validated();
            $shiftCheckDateStart = Shift::all()->every(function ($value, $key) use ($request) {
                return !Carbon::parse($request->date_start)->between(Carbon::parse($value->date_start), Carbon::parse($value->date_end));
            });

            $shiftCheckDateEnd = Shift::all()->every(function ($value, $key) use ($request) {
                return !Carbon::parse($request->date_end)->between(Carbon::parse($value->date_start), Carbon::parse($value->date_end));
            });
            if(!$shiftCheckDateEnd && !$shiftCheckDateStart) {
                return response()->json([
                    'error' => [
                        'code' => '403',
                        'message' => "Вы не можете создать смену на эту дату, так она пересекается с другой сменой",
                    ]
                ], 403);
            }

            $dateStart = Carbon::parse($request->date_start);
            $dateEnd = Carbon::parse($request->date_end);
            $hours = $dateEnd->diffInHours($dateStart);
            Shift::create(array_merge($validateData, ['hours' => $hours]));
            return response()->json([
                'data' => [
                    'code' => '200',
                    'message' => $hours,
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для создания смены',
                ]
            ], 403);
        }
    }

    public function deleteShift(string $id) {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        if($root->canEditShift) {
            $shift = Shift::where('id', $id)->first();
            $shiftCheckActive = $shift->userShifts()->get()->every(function ($value, $key) {
                return !$value->active;
            });
            if($shiftCheckActive) {
                $shift->delete();
                return response()->json([
                    'data' => [
                        'code' => '200',
                        'message' => 'Вы успешно удалили смену',
                    ]
                ], 200);
            } else {
                return response()->json([
                    'error' => [
                        'code' => '403',
                        'message' => 'Вы не можете удалить смену так как она уже началась',
                    ]
                ], 403);
            }
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для удаления смены смены',
                ]
            ], 403);
        }
    }

    public function updateShift(string $id, UpdateShiftRequest $request) {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        if($root->canEditShift) {
            $shift = Shift::where('id', $id)->first();
            $shiftCheckActive = $shift->userShifts()->get()->every(function ($value, $key) {
                return !$value->active;
            });
            if($shiftCheckActive) {
                $validateData = $request->validated();
                $shiftCheckDateStart = Shift::where('id', '!=', $id)->get()->every(function ($value, $key) use ($request) {
                    return !Carbon::parse($request->date_start)->between(Carbon::parse($value->date_start), Carbon::parse($value->date_end));
                });

                $shiftCheckDateEnd = Shift::where('id', '!=', $id)->get()->every(function ($value, $key) use ($request) {
                    return !Carbon::parse($request->date_end)->between(Carbon::parse($value->date_start), Carbon::parse($value->date_end));
                });
                if(!$shiftCheckDateEnd && !$shiftCheckDateStart) {
                    return response()->json([
                        'error' => [
                            'code' => '403',
                            'message' => "Вы не можете изменить смену на эту дату, так она пересекается с другой сменой",
                        ]
                    ], 403);
                }

                $dateStart = Carbon::parse($request->date_start);
                $dateEnd = Carbon::parse($request->date_end);
                $hours = $dateEnd->diffInHours($dateStart);
                $shift->update($validateData, ['hours' => $hours]);
                $shiftPath = $request->header('origin')."/shifts#".$id;
                $usersShift = $shift->users()->get();
                $message = "Смена «".$shift->name."» была изменена. <a class='a-notify' href='$shiftPath'>Ознакомьтесь с новыми изменения</a>";
                foreach ($usersShift as $user) {
                    Notification::create([
                        "message" => $message,
                        "user" => $user->id,
                        "read" => false,
                    ]);
                    event(new StoreNotificationEvent($message, $user));
                }

                return response()->json([
                    'data' => [
                        'code' => '200',
                        'message' => 'Вы успешно изменили смену',
                    ]
                ], 200);
            } else {
                return response()->json([
                    'error' => [
                        'code' => '403',
                        'message' => 'Вы не можете редактировать смену так как она уже началась',
                    ]
                ], 403);
            }
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для редактирования смены',
                ]
            ], 403);
        }
    }

    public function startShift(string $id, Request $request) {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        if($root->canEditShift) {
            $shift = Shift::where('id', $id)->first();
            $usersShift = $shift->userShifts()->get();
            foreach ($usersShift as $user) {
                $user->active = true;
                $user->save();
            }

            $users = $shift->users()->get();
            $shiftPath = $request->header('origin')."/shifts#".$id;
            $message = "Смена «".$shift->name."» началась. <a class='a-notify' href='$shiftPath'>Просмотреть</a>";
            foreach ($users as $user) {
                Notification::create([
                    "message" => $message,
                    "user" => $user->id,
                    "read" => false,
                ]);
                event(new StoreNotificationEvent($message, $user));
            }

            return response()->json([
                'data' => [
                    'code' => '200',
                    'message' => 'Смена успешно активирована',
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для того чтобы активировать смену',
                ]
            ], 403);
        }
    }

    public function endShift(string $id) {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        if($root->canEditShift) {
            $shift = Shift::where('id', $id)->first();
            $usersShift = $shift->userShifts()->get();
            foreach ($usersShift as $user) {
                $user->active = false;
                $user->save();
            }

            return response()->json([
                'data' => [
                    'code' => '200',
                    'message' => 'Смена успешно закончена',
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для того чтобы закончить смену',
                ]
            ], 403);
        }
    }
}
