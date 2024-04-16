<?php

namespace App\Http\Controllers;

use App\Events\StoreNotificationEvent;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Dish;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Order_dish;
use App\Models\Table;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
{

    public function getOrders(Request $request)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        $query = $request->get('tab');
        $orders = Order::orderBy('created_at', 'desc')->get();
        if ($query && $query != 'view') {
            $orders = Order::where('status', $query)->orderBy('created_at', 'desc')->get();
        }

        $orders = $orders->map(function ($order, $key) use ($user, $root) {
            return [
                'id' => $order->id,
                'table' => $order->table_id,
                'status' => $order->status,
                'created' => $order->created_at,
                'notes' => $order->notes,
                'responsible' => [
                    'id' => $order->responsible()->first()->id,
                    'name' => $order->responsible()->first()->last_name . ' ' . $order->responsible()->first()->first_name,
                    'avatar' => $order->responsible()->first()->avatar
                ],
                'chef' => [
                    'id' => User::where('id', $order->chef)->first()->id,
                    'name' => User::where('id', $order->chef)->first()->last_name . ' ' . User::where('id', $order->chef)->first()->first_name,
                    'avatar' => User::where('id', $order->chef)->first()->avatar
                ],
                'canChangeStatus' => $this->checkChangeStatus($user, $this->getNextStatus($order->status), $root),
                'changeStatus' => $this->getNextStatus($order->status),
                'dateStart' => $order->updated_at,
            ];
        });
        return response()->json([
            'data' => [
                'orders' => $orders,
                'actions' => $root->canEditOrders,
                'tabs' => [
                    'active' => $query ? $query : 'view',
                    'items' => [
                        'view' => 'Обзор',
//                        'myOrder' => 'Мои заказы',
                        'accept' => 'Принятые',
                        'prepare' => 'Готовятся',
                        'ready' => 'Готовые',
                        'passed' => 'Завершенные',
                    ]
                ]
            ]
        ], 200);
    }

    private function checkChangeStatus($user, $status, $root)
    {
        $userCode = $user->role()->first()->code;
        if ($userCode == 'admin') {
            return true;
        } elseif ($userCode == 'waiter') {
            if ($status != 'prepare' && $status != 'ready' && $root->canEditOrders) {
                return true;
            } else false;
        } elseif ($userCode == 'chef') {
            if ($status == 'prepare' || $status == 'ready' && $root->canEditOrders) {
                return true;
            } else false;
        }
    }

    private function getNextStatus($status)
    {
        switch ($status) {
            case 'accept':
                return "prepare";
            case 'prepare':
                return 'ready';
            case 'ready':
                return 'passed';
            case 'passed':
                return 'finish';
        }
    }

    private function getStatus($status)
    {
        switch ($status) {
            case 'accept':
                return "Принят";
            case 'prepare':
                return 'Готовоится';
            case 'ready':
                return 'Готов';
            case 'passed':
                return 'Завершен';
        }
    }

    public function getFormEdit(string $id)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        $order = Order::where('id', $id)->first();

        if ($root->canEditOrders) {
            $usersResponse = User::where('roles', 2)->get()->map(function ($elem, $key) {
                return [
                    'id' => $elem->id,
                    'code' => 'responsible',
                    'type' => 'text',
                    'value' => $elem->first_name . " " . $elem->last_name,
                ];
            });
            $usersChef = User::where('roles', 3)->get()->map(function ($elem, $key) {
                return [
                    'id' => $elem->id,
                    'code' => 'chef',
                    'type' => 'text',
                    'value' => $elem->first_name . " " . $elem->last_name,
                ];
            });

            $dishes = Dish::all()->map(function ($elem, $key) {
                return [
                    'id' => $elem->id,
                    'code' => 'dish_id',
                    'type' => 'text',
                    'cost' => $elem->cost,
                    'value' => $elem->name,
                ];
            });

            $dishesOrder = Order_dish::where('order_id', $order->id)->get()->map(function ($elem, $key) use ($dishes) {
                return [
                    'dish' => [
                        'id' => $elem->dish()->first()->id,
                        'code' => 'id',
                        'type' => 'select',
                        'value' => $elem->dish()->first()->id,
                        'possibleValues' => $dishes,
                    ],
                    'count' => [
                        'id' => $elem->id,
                        'code' => 'count',
                        'type' => 'number',
                        'value' => $elem->count,
                    ],
                    'sum' => [
                        'id' => $elem->id,
                        'code' => 'sum',
                        'type' => 'number',
                        'value' => $elem->dish()->first()->cost,
                    ]
                ];
            });
            $tables = Table::all()->map(function ($elem, $key) {
                return [
                    'id' => $elem->id,
                    'code' => 'table_id',
                    'type' => 'text',
                    'value' => $elem->number,
                ];
            });
            return response()->json([
                'data' => [
                    'form' => [
                        [
                            'code' => 'status',
                            'type' => 'select',
                            'value' => $order->status,
                            'placeholder' => 'Статус заказа',
                            'canEdit' => $root->canStatusOrders,
                            'possibleValues' => [
                                [
                                    'id' => 'accept',
                                    'code' => 'status',
                                    'type' => 'text',
                                    'value' => 'Принят',
                                ],
                                [
                                    'id' => 'prepare',
                                    'code' => 'status',
                                    'type' => 'text',
                                    'value' => 'Готовится',
                                ],
                                [
                                    'id' => 'ready',
                                    'code' => 'status',
                                    'type' => 'text',
                                    'value' => 'Готов',
                                ],
                                [
                                    'id' => 'passed',
                                    'code' => 'status',
                                    'type' => 'text',
                                    'value' => 'Завершен',
                                ]
                            ],
                        ],
                        [
                            'code' => 'responsible',
                            'type' => 'select',
                            'value' => $order->responsible,
                            'placeholder' => 'Ответственный за заказ',
                            'canEdit' => $root->canResponsibleOrders,
                            'possibleValues' => $usersResponse,
                        ],
                        [
                            'code' => 'chef',
                            'type' => 'select',
                            'value' => $order->chef,
                            'placeholder' => 'Ответственный за приготовление',
                            'canEdit' => $root->canChefOrders,
                            'possibleValues' => $usersChef,
                        ],
                        [
                            'code' => 'table_id',
                            'type' => 'select',
                            'value' => $order->table()->first()->number,
                            'placeholder' => 'Стол',
                            'canEdit' => $root->canTableOrders,
                            'possibleValues' => $tables
                        ],
                        [
                            'code' => 'notes',
                            'type' => 'textarea',
                            'value' => $order->notes,
                            'placeholder' => 'Примечание к заказу',
                            'canEdit' => $root->canNotesOrders,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'dishes',
                            'type' => 'form',
                            'value' => count($dishesOrder) != 0 ? $dishesOrder : [['dish' => [
                                'id' => null,
                                'code' => 'id',
                                'type' => 'select',
                                'value' => null,
                                'possibleValues' => $dishes,
                            ],
                                'count' => [
                                    'id' => null,
                                    'code' => 'count',
                                    'type' => 'number',
                                    'value' => 0,
                                ],
                                'sum' => [
                                    'id' => null,
                                    'code' => 'sum',
                                    'type' => 'number',
                                    'value' => 0,
                                ]
                            ]],
                            'placeholder' => 'Блюда',
                            'canEdit' => $root->canNotesOrders,
                            'possibleValues' => null,
                        ],
                    ]
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для редактирования заказа'
                ]
            ], 403);
        }
    }

    public function getFormCreateOrder()
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        if ($root->canEditOrders) {

            $dishes = Dish::all()->map(function ($elem, $key) {
                return [
                    'id' => $elem->id,
                    'code' => 'dish_id',
                    'type' => 'text',
                    'value' => $elem->name,
                    'cost' => $elem->cost,
                ];
            });

            $usersForResponsible = User::all()->filter(function ($user) {
                return (bool) $user->role()->first()->root()->first()->canBeResponsibleOrder;
            });
            $usersResponse = $usersForResponsible->map(function ($elem, $key) {
                return [
                    'id' => $elem->id,
                    'code' => 'responsible',
                    'type' => 'text',
                    'value' => $elem->first_name . " " . $elem->last_name,
                ];
            });

            $usersForChef = User::all()->filter(function ($user) {
                return (bool) $user->role()->first()->root()->first()->canBeChefOrder;
            });
            $usersChef = $usersForChef->map(function ($elem, $key) {
                return [
                    'id' => $elem->id,
                    'code' => 'chef',
                    'type' => 'text',
                    'value' => $elem->first_name . " " . $elem->last_name,
                ];
            });
            $tables = Table::all()->map(function ($elem, $key) {
                return [
                    'id' => $elem->id,
                    'code' => 'table_id',
                    'type' => 'text',
                    'value' => $elem->number,
                ];
            });
            return response()->json([
                'data' => [
                    'form' => [
                        [
                            'code' => 'status',
                            'type' => 'select',
                            'value' => null,
                            'placeholder' => 'Статус заказа',
                            'canEdit' => $root->canStatusOrders,
                            'possibleValues' => [
                                [
                                    'id' => 'accept',
                                    'code' => 'status',
                                    'type' => 'text',
                                    'value' => 'Принят',
                                ],
                                [
                                    'id' => 'prepare',
                                    'code' => 'status',
                                    'type' => 'text',
                                    'value' => 'Готовится',
                                ],
                                [
                                    'id' => 'ready',
                                    'code' => 'status',
                                    'type' => 'text',
                                    'value' => 'Готов',
                                ],
                                [
                                    'id' => 'passed',
                                    'code' => 'status',
                                    'type' => 'text',
                                    'value' => 'Завершен',
                                ]
                            ],
                        ],
                        [
                            'code' => 'responsible',
                            'type' => 'select',
                            'value' => null,
                            'placeholder' => 'Ответственный за заказ',
                            'canEdit' => $root->canResponsibleOrders,
                            'possibleValues' => $usersResponse,
                        ],
                        [
                            'code' => 'chef',
                            'type' => 'select',
                            'value' => null,
                            'placeholder' => 'Ответственный за приготовление',
                            'canEdit' => $root->canChefOrders,
                            'possibleValues' => $usersChef,
                        ],
                        [
                            'code' => 'table_id',
                            'type' => 'select',
                            'value' => null,
                            'placeholder' => 'Стол',
                            'canEdit' => $root->canTableOrders,
                            'possibleValues' => $tables
                        ],
                        [
                            'code' => 'notes',
                            'type' => 'textarea',
                            'value' => null,
                            'placeholder' => 'Примечание к заказу',
                            'canEdit' => $root->canNotesOrders,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'dishes',
                            'type' => 'form',
                            'value' =>  [['dish' => [
                                'id' => null,
                                'code' => 'id',
                                'type' => 'select',
                                'value' => null,
                                'possibleValues' => $dishes,
                            ],
                                'count' => [
                                    'id' => null,
                                    'code' => 'count',
                                    'type' => 'number',
                                    'value' => 0,
                                ],
                                'sum' => [
                                    'id' => null,
                                    'code' => 'sum',
                                    'type' => 'number',
                                    'value' => 0,
                                ]
                            ]],
                            'placeholder' => 'Блюда',
                            'canEdit' => $root->canNotesOrders,
                            'possibleValues' => null,
                        ],
                    ]
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для редактирования заказа'
                ]
            ], 403);
        }
    }

    public function updateOrder(string $id, UpdateOrderRequest $request)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        $order = Order::where('id', $id)->first();
        if ($root->canEditOrders) {
            $newArr = array();
            $dishes = array_map(function ($elem) use (&$newArr) {
                return $newArr[$elem['dish_id']] = [
                    'count' => $elem['count'],
                    'sum' => $elem['count'] * Dish::where('id', $elem['dish_id'])->first()->cost,
                ];
            }, $request->dishes);
            $order->dishes()->sync($newArr);

            //УВЕДОМЛЕНИЯ
            $orderPath = $request->header('origin')."/orders#".$id;
            if($order->responsible != $request->responsible) {
                $newResponsible = User::where('id', $request->responsible)->first();
                $oldResponsible = User::where('id', $order->responsible)->first();
                $chef = User::where('id', $request->chef)->first();
                $message = "Ответственный за заказ №$id изменен. <a class='a-notify' href='$orderPath'>Перейти в заказ</a>";
                Notification::create([
                    "message" => $message,
                    "user" => $oldResponsible->id,
                    "read" => false,
                ]);
                event(new StoreNotificationEvent($message, $oldResponsible));
                Notification::create([
                    "message" => $message,
                    "user" => $newResponsible->id,
                    "read" => false,
                ]);
                event(new StoreNotificationEvent($message, $newResponsible));
                Notification::create([
                    "message" => $message,
                    "user" => $chef->id,
                    "read" => false,
                ]);
                event(new StoreNotificationEvent($message, $chef));
            }
            if($order->chef != $request->chef) {
                $newChef = User::where('id', $request->chef)->first();
                $oldChef = User::where('id', $order->chef)->first();
                $message = "Ответственный за приготовление заказа №$id изменен. <a class='a-notify' href='$orderPath'>Перейти в заказ</a>";
                Notification::create([
                    "message" => $message,
                    "user" => $oldChef->id,
                    "read" => false,
                ]);
                event(new StoreNotificationEvent($message, $oldChef));
                Notification::create([
                    "message" => $message,
                    "user" => $newChef->id,
                    "read" => false,
                ]);
                event(new StoreNotificationEvent($message, $newChef));
                Notification::create([
                    "message" => $message,
                    "user" => User::where('id', $request->chef)->first()->id,
                    "read" => false,
                ]);
                event(new StoreNotificationEvent($message, User::where('id', $request->responsible)->first()));
            }
            if($order->status != $request->status) {
                $newStatus = $this->getStatus($request->status);
                $oldStatus = $this->getStatus($order->status);
                $chef = User::where('id', $request->chef)->first();
                $responsible = User::where('id', $request->responsible)->first();
                $message = "Статус заказа №$id был изменен с <strong>«".$oldStatus."»</strong> на <strong>«".$newStatus."»</strong>. <a class='a-notify' href='$orderPath'>Перейти в заказ</a>";
                Notification::create([
                    "message" => $message,
                    "user" => $chef->id,
                    "read" => false,
                ]);
                event(new StoreNotificationEvent($message, $chef));
                Notification::create([
                    "message" => $message,
                    "user" => $responsible->id,
                    "read" => false,
                ]);
                event(new StoreNotificationEvent($message, $responsible));
            }
            $order->update($request->validated());
            return response()->json([
                'data' => [
                    'code' => '200',
                    'message' => 'Заказ успешно обновлен',
                    'leeee' => $request->header('origin')."/orders#".$id,
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для редактирования заказа'
                ]
            ], 403);
        }
    }

    public function getOrderDishes(string $id) {
        $user = Auth::user();
        $order = Order::where('id', $id)->first();
        $dishes = Order_dish::where('order_id', $order->id)->get()->map(function ($elem, $key) {
            return [
                'id' => $elem->dish()->first()->id,
                'name' => $elem->dish()->first()->name,
                'cost' => $elem->dish()->first()->cost,
                'count' => $elem->count,
            ];
        });
        return response()->json([
            'data' => $dishes
        ], 200);
    }

    public function createOrder(CreateOrderRequest $request)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        if ($root->canEditOrders) {
            $newOrder = Order::create($request->validated());
            $dishes = array_map(function ($elem) use (&$newArr) {
                return $newArr[$elem['dish_id']] = [
                    'count' => $elem['count'],
                    'sum' => $elem['count'] * Dish::where('id', $elem['dish_id'])->first()->cost,
                ];
            }, $request->dishes);
            $newOrder->dishes()->sync($newArr);
            return response()->json([
                'data' => [
                    'code' => '200',
                    'message' => 'Заказ успешно создан'
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для редактирования заказа'
                ]
            ], 403);
        }
    }

    public function updateStatus(string $id, Request $request)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        $order = Order::where('id', $id)->first();
        if ($root->canEditOrders) {
            $order->update(['status' => $request->get('status')]);
            return response()->json([
                'data' => [
                    'message' => 'Заказ успешно обновлен'
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для редактирования заказа'
                ]
            ], 403);
        }

    }

    private function getTranslateStatus($status)
    {
        switch ($status) {
            case 'accept':
                return "Принят";
            case 'prepare':
                return 'Готовится';
            case 'ready':
                return 'Готов';
            case 'passed':
                return 'Завершен';
        }
    }
}
