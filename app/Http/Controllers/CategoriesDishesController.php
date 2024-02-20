<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCategoryDishesRequest;
use App\Http\Requests\EditCategoryDishesRequest;
use App\Models\Category_dish;
use Illuminate\Support\Facades\Auth;

class CategoriesDishesController extends Controller
{
    public function getCategoriesDishes()
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        $categories = Category_dish::all()->map(function ($category, $key) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'count_dishes' => count($category->dishes()->get()),
            ];
        });
        return response()->json([
            'data' => [
                'actions' => $root->canEditCategories,
                'categories' => $categories,
            ]
        ], 200);
    }

    public function getFormEditCategoryDishes(string $id)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        $category = Category_dish::where('id', $id)->first();
        if ($root->canEditCategories) {
            return response()->json([
                'data' => [
                    'editMode' => $root->canEditCategories,
                    'form' => [
                        [
                            'code' => 'name',
                            'type' => 'text',
                            'value' => $category->name,
                            'placeholder' => '',
                            'canEdit' => $root->canEditCategories,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'code',
                            'type' => 'text',
                            'value' => $category->code,
                            'placeholder' => 'Введите категорию(на латинице без пробелов)',
                            'canEdit' => $root->canEditCategories,
                            'possibleValues' => null,
                        ],
                    ]
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для редактирования категории'
                ]
            ], 403);
        }
    }

    public function getFormCreateCategoryDishes()
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        if ($root->canEditCategories) {
            return response()->json([
                'data' => [
                    'editMode' => $root->canEditCategories,
                    'form' => [
                        [
                            'code' => 'name',
                            'type' => 'text',
                            'value' => '',
                            'placeholder' => 'Введите название категории',
                            'canEdit' => $root->canEditCategories,
                            'possibleValues' => null,
                        ],
                        [
                            'code' => 'code',
                            'type' => 'text',
                            'value' => '',
                            'placeholder' => 'Введите категорию(на латинице без пробелов)',
                            'canEdit' => $root->canEditCategories,
                            'possibleValues' => null,
                        ],
                    ]
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для создания категории'
                ]
            ], 403);
        }
    }

    public function editCategoryDishes(string $id, EditCategoryDishesRequest $request)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        $category = Category_dish::where('id', $id)->first();
        $check = Category_dish::where([['id', '!=', $id], ['code', $request->code]])->first();
        if ($root->canEditCategories) {
            if (!$check) {
                $category->update($request->validated());
                return response()->json([
                    'data' => [
                        'message' => 'Вы успешно изменили объект'
                    ]
                ], 200);
            } else {
                return response()->json([
                    'error' => [
                        'code' => '403',
                        'message' => 'Такой код уже используется в другой категории',
                    ]
                ], 403);
            }
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для редактирования категории'
                ]
            ], 403);
        }
    }

    public function createCategoryDishes(CreateCategoryDishesRequest $request)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        $check = Category_dish::where([['code', $request->code]])->first();
        if ($root->canEditCategories) {
            if (!$check) {
                Category_dish::create($request->validated());
                return response()->json([
                    'data' => [
                        'message' => 'Вы успешно создали объект'
                    ]
                ], 200);
            } else {
                return response()->json([
                    'error' => [
                        'code' => '403',
                        'message' => 'Такой код уже используется в другой категории',
                    ]
                ], 403);
            }
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для создания категории'
                ]
            ], 403);
        }
    }

    public function deleteCategoryDishes(string $id)
    {
        $user = Auth::user();
        $root = $user->role()->first()->root()->first();
        $category = Category_dish::where('id', $id)->first();
        if ($root->canEditCategories) {
            $category->delete();
            return response()->json([
                'data' => [
                    'message' => 'Вы успешно удалили категорию'
                ]
            ], 200);
        } else {
            return response()->json([
                'error' => [
                    'code' => '403',
                    'message' => 'У вас недостаточно прав для редактирования категории'
                ]
            ], 403);
        }
    }
}
