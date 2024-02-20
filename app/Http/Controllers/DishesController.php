<?php

namespace App\Http\Controllers;

use App\Events\StoreNotificationEvent;
use App\Http\Requests\CreateDishRequest;
use App\Http\Requests\EditDishRequest;
use App\Http\Requests\PhotoRequest;
use App\Models\Category_dish;
use App\Models\Dish;
use App\Models\Dish_photo;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DishesController extends Controller
{
    public function getDishes(Request $request)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        $query = $request->get('tab');
        $categories = Category_dish::all()->map(function ($category, $key) {
            return [
                'id' => $category->id,
                'name' => $category->name,
            ];
        });
        $sortDish = Dish::all();
        if ($query && $query != 0) {
            $sortDish = Dish::where('category_id', $query)->get();
        }

        $dishes = $sortDish->map(function ($dish, $key) {
            return [
                'id' => $dish->id,
                'name' => $dish->name,
                'description' => $dish->description,
                'cost' => $dish->cost,
                'category' => $dish->category()->first()->name,
                'images' => Dish_photo::where('dish_id', $dish->id)->get(),
            ];
        });
        return response()->json([
            'data' => [
                'actions' => $root->canEditDish,
                'dishes' => $dishes,
                'tabs' => [
                    'active' => $query ? (int) $query : 0,
                    'items' => $categories->prepend([
                        'id' => 0,
                        'name' => 'Обзор'
                    ])
                ]
            ]
        ], 200);
    }

    public function getFormCreateDish()
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        $categories = Category_dish::all()->map(function ($category, $key) {
            return [
                'id' => $category->id,
                'code' => 'category_id',
                'type' => 'text',
                'value' => $category->name,
            ];
        });

        if ($root->canEditDish) {
            return response()->json([
                'data' => [
                    'editMode' => $root->canEditDish,
                    'form' => [
                        [
                            'code' => 'name',
                            'type' => 'text',
                            'value' => '',
                            'placeholder' => 'Введите название блюда',
                            'canEdit' => $root->canEditDish,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'description',
                            'type' => 'textarea',
                            'value' => '',
                            'placeholder' => 'Введите описание',
                            'canEdit' => $root->canEditDish,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'cost',
                            'type' => 'number',
                            'value' => '',
                            'placeholder' => 'Введите цену',
                            'canEdit' => $root->canEditDish,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'category_id',
                            'type' => 'select',
                            'value' => '',
                            'placeholder' => 'Выберите категорию',
                            'canEdit' => $root->canEditDish,
                            'possibleValues' => $categories,
                        ],
                    ]
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для редактирования блюда'
                ]
            ], 403);
        }
    }

    public function getFormEditDishes(string $id)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        $dish = Dish::where('id', $id)->first();
        $categories = Category_dish::all()->map(function ($category, $key) {
            return [
                'id' => $category->id,
                'code' => 'category_id',
                'type' => 'text',
                'value' => $category->name,
            ];
        });

        $photos = Dish_photo::where('dish_id', $dish->id)->get()->map(function ($image, $key) {
            return [
                'id' => $image->id,
                'name' => $image->name,
                'url' => $image->photo,
            ];
        });
        if ($root->canEditDish) {
            return response()->json([
                'data' => [
                    'editMode' => $root->canEditDish,
                    'form' => [
                        [
                            'code' => 'name',
                            'type' => 'text',
                            'value' => $dish->name,
                            'placeholder' => 'Введите название блюда',
                            'canEdit' => $root->canEditDish,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'description',
                            'type' => 'textarea',
                            'value' => $dish->description,
                            'placeholder' => 'Введите описание',
                            'canEdit' => $root->canEditDish,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'cost',
                            'type' => 'number',
                            'value' => $dish->cost,
                            'placeholder' => 'Введите цену',
                            'canEdit' => $root->canEditDish,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'category_id',
                            'type' => 'select',
                            'value' => $dish->category()->first()->id,
                            'placeholder' => 'Выберите категорию',
                            'canEdit' => $root->canEditDish,
                            'possibleValues' => $categories,
                        ],
                        [
                            'code' => 'image',
                            'type' => 'upload',
                            'value' => $photos,
                            'placeholder' => 'Выберите фото для загрузки',
                            'canEdit' => $root->canEditDish,
                            'possibleValues' => null,
                        ],
                    ]
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для редактирования блюда'
                ]
            ], 403);
        }
    }

    public function editDish(string $id, EditDishRequest $request)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        $dish = Dish::where('id', $id)->first();
        $dishPath = $request->header('origin')."/dish/".$id;
        if ($root->canEditDish) {
            $usersChef = User::where('roles', 3)->get();
            $dish->update($request->validated());
            foreach ($usersChef as $chef) {
                $message = "Блюдо «".$dish->name."» было изменено. <a class='a-notify' href='$dishPath'>Ознакомьтесь с новыми изменениями</a>";
                Notification::create([
                    "message" => $message,
                    "user" => $chef->id,
                    "read" => false,
                ]);
                event(new StoreNotificationEvent($message, $chef));
                Notification::create([
                    "message" => $message,
                    "user" => $chef->id,
                    "read" => false,
                ]);
            }
            return response()->json([
                'data' => [
                    'message' => 'Блюдо успешно изменено',
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для редактирования блюда'
                ]
            ], 403);
        }
    }

    public function deleteDish(string $id)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        $dish = Dish::where('id', $id)->first();
        if ($root->canEditDish) {
            $dish->delete();
            return response()->json([
                'data' => [
                    'message' => 'Блюдо успешно удалено'
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для удаления блюда'
                ]
            ], 403);
        }
    }

    public function setImage(string $id, PhotoRequest $request)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        $dish = Dish::where('id', $id)->first();
        if ($root->canEditDish) {
            $request->validated();
            $image = $request->file('image')->store('/public');
            $newPhoto = Dish_photo::create([
                'photo' => $image,
                'dish_id' => $dish->id,
                'name' => $request->file('image')->getClientOriginalName()
            ]);
            return response()->json([
                'data' => [
                    'message' => 'Фото успешно добавлено'
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для редактирования блюда'
                ]
            ], 403);
        }
    }

    public function deleteDishPhoto(string $id)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        if ($root->canEditDish) {
            $image = Dish_photo::where('id', $id)->first()->delete();
            return response()->json([
                'data' => [
                    'message' => 'Фото успешно удалено'
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для редактирования блюда'
                ]
            ], 403);
        }
    }

    public function getDish(string $id)
    {
        $user = Auth::user();
        $dish = Dish::where('id', $id)->first();
        $photos = Dish_photo::where('dish_id', $dish->id)->get()->map(function ($image, $key) {
            return [
                'id' => $image->id,
                'name' => $image->name,
                'url' => $image->photo,
            ];
        });
        return response()->json([
            'data' => [
                'dish' => [
                    'id' => $dish->id,
                    'name' => $dish->name,
                    'description' => $dish->description,
                    'cost' => $dish->cost,
                    'category' => Category_dish::where('id', $dish->category_id)->first()->name,
                    'images' => $photos,
                ],
            ]
        ], 200);
    }

    public function createDish(CreateDishRequest $request)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        if ($root->canEditDish) {
            $newDish = Dish::create($request->validated());
            return response()->json([
                'data' => [
                    'message' => 'Блюдо успешно создано'
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для создания блюда'
                ]
            ], 403);
        }
    }
}
