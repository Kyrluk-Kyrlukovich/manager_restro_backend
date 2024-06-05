<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::match(['post'], '/login', [\App\Http\Controllers\UsersController::class, 'login']);


Route::middleware('auth:sanctum')->group(function() {


    Route::post('/signup', [\App\Http\Controllers\UsersController::class, 'signup']);

    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index']);
    Route::get('/notifications/read', [\App\Http\Controllers\NotificationController::class, 'updateStatusRead']);
    Route::get('/notifications/delete', [\App\Http\Controllers\NotificationController::class, 'deleteNotifications']);
    Route::get('/login-info', [\App\Http\Controllers\UsersController::class, 'loginInfo']);
    Route::post('/update-user/{id}', [\App\Http\Controllers\UsersController::class, 'updateUserInfo']);
    Route::get('/logout', [\App\Http\Controllers\UsersController::class, 'logout']);
    Route::get('/get-users', [\App\Http\Controllers\UsersController::class, 'getUsers']);
    Route::get('/dismiss/{id}', [\App\Http\Controllers\UsersController::class, 'dismissUser']);
    Route::get('/rehire/{id}', [\App\Http\Controllers\UsersController::class, 'rehire']);

    Route::get('/tables', [\App\Http\Controllers\TablesController::class, 'getTables']);
    Route::post('/tables/{id}/update', [\App\Http\Controllers\TablesController::class, 'updateTable']);
    Route::post('/create-table', [\App\Http\Controllers\TablesController::class, 'createTable']);
    Route::delete('/delete-table/{id}', [\App\Http\Controllers\TablesController::class, 'deleteTable']);


    Route::get('/dishes', [\App\Http\Controllers\DishesController::class, 'getDishes']);
    Route::post('/edit-dish/{id}', [\App\Http\Controllers\DishesController::class, 'editDish']);
    Route::delete('/delete-dish/{id}', [\App\Http\Controllers\DishesController::class, 'deleteDish']);
    Route::post('/set-image-dish/{id}', [\App\Http\Controllers\DishesController::class, 'setImage']);
    Route::delete('/delete-image-dish/{id}', [\App\Http\Controllers\DishesController::class, 'deleteDishPhoto']);
    Route::get('/dish/{id}', [\App\Http\Controllers\DishesController::class, 'getDish']);
    Route::post('/dish-create', [\App\Http\Controllers\DishesController::class, 'createDish']);


    Route::get('/categories-dishes', [\App\Http\Controllers\CategoriesDishesController::class, 'getCategoriesDishes']);
    Route::post('/edit-category-dishes/{id}', [\App\Http\Controllers\CategoriesDishesController::class, 'editCategoryDishes']);
    Route::post('/create-category-dishes', [\App\Http\Controllers\CategoriesDishesController::class, 'createCategoryDishes']);
    Route::delete('/delete-category-dishes/{id}', [\App\Http\Controllers\CategoriesDishesController::class, 'deleteCategoryDishes']);


    Route::get('/get-orders', [\App\Http\Controllers\OrdersController::class, 'getOrders']);
    Route::get('/update-status-orders/{id}', [\App\Http\Controllers\OrdersController::class, 'updateStatus']);
    Route::put('/update-order/{id}', [\App\Http\Controllers\OrdersController::class, 'updateOrder']);
    Route::post('/create-order', [\App\Http\Controllers\OrdersController::class, 'createOrder']);
    Route::get('/get-order-dishes/{id}', [\App\Http\Controllers\OrdersController::class, 'getOrderDishes']);

    Route::get('/get-shifts', [\App\Http\Controllers\ShiftsController::class, 'getShifts']);
    Route::get('/take-shift/{id}', [\App\Http\Controllers\ShiftsController::class, 'takeShift']);
    Route::get('/reject-shift/{id}', [\App\Http\Controllers\ShiftsController::class, 'rejectShift']);
    Route::post('/create-shift', [\App\Http\Controllers\ShiftsController::class, 'createShift']);
    Route::get('/delete-shift/{id}',     [\App\Http\Controllers\ShiftsController::class, 'deleteShift']);
    Route::put('/update-shift/{id}',     [\App\Http\Controllers\ShiftsController::class, 'updateShift']);
    Route::get('/start-shift/{id}',     [\App\Http\Controllers\ShiftsController::class, 'startShift']);
    Route::get('/end-shift/{id}',     [\App\Http\Controllers\ShiftsController::class, 'endShift']);

    Route::get('/get-roles',     [\App\Http\Controllers\RolesController::class, 'getRoles']);
    Route::get('/get-roots/{id}',     [\App\Http\Controllers\RolesController::class, 'getRootsRole']);
    Route::put('/update-roots/{id}',     [\App\Http\Controllers\RolesController::class, 'updateRoot']);
    Route::post('/create-role',     [\App\Http\Controllers\RolesController::class, 'createRole']);
    Route::put('/update-role/{id}',     [\App\Http\Controllers\RolesController::class, 'updateRole']);
    Route::delete('/delete-role/{id}',     [\App\Http\Controllers\RolesController::class, 'deleteRole']);
//    ----------------------FORM---------------------
    Route::get('/get-form-users/{id}', [\App\Http\Controllers\UsersController::class, 'getFormUpdateUser']);
    Route::get('/get-form-create-user', [\App\Http\Controllers\UsersController::class, 'getFormCreateUser']);

    Route::get('/get-form-create-table', [\App\Http\Controllers\TablesController::class, 'getFormCreateTable']);
    Route::get('/get-form-edit-table/{id}', [\App\Http\Controllers\TablesController::class, 'getFormEditTable']);

    Route::get('/get-form-edit-dish/{id}', [\App\Http\Controllers\DishesController::class, 'getFormEditDishes']);
    Route::get('/get-form-create-dish', [\App\Http\Controllers\DishesController::class, 'getFormCreateDish']);

    Route::get('/get-form-edit-category/{id}', [\App\Http\Controllers\CategoriesDishesController::class, 'getFormEditCategoryDishes']);
    Route::get('/get-form-create-category', [\App\Http\Controllers\CategoriesDishesController::class, 'getFormCreateCategoryDishes']);

    Route::get('/get-form-edit-order/{id}', [\App\Http\Controllers\OrdersController::class, 'getFormEdit']);
    Route::get('/get-form-create-order', [\App\Http\Controllers\OrdersController::class, 'getFormCreateOrder']);

    Route::get('/get-form-create-shift', [\App\Http\Controllers\ShiftsController::class, 'getFormCreateShift']);
    Route::get('/get-form-edit-shift/{id}', [\App\Http\Controllers\ShiftsController::class, 'getFormEditShift']);

    Route::get('/get-form-create-role', [\App\Http\Controllers\RolesController::class, 'getFormCreateRole']);
    Route::get('/get-form-edit-role/{id}', [\App\Http\Controllers\RolesController::class, 'getFormUpdateRole']);

    //----------------------------CHARTS----------------------------------

    Route::get('/get-orders-costs-data', [\App\Http\Controllers\ApexChartController::class, 'dataOrdersAndCost']);
    Route::get('/get-dishes-data', [\App\Http\Controllers\ApexChartController::class, 'dataDishes']);

});
