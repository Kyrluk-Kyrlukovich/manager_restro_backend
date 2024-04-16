<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApexChartController extends Controller
{
    public function dataOrdersAndCost(Request $request)
    {

        $user = Auth::user();
        $root = $user->role()->first()->root()->first();


        $daysOfWeekStr = array();
        $daysOfWeekCost = array();
        $daysOfWeekCountOrders = array();
        $countOrdersForDay = array();
        $costForDay = array();
        $periodIncome = 7;
        $periodOrders = 7;

        if ($request->get('periodIncome') && $request->get('periodOrders')) {
            $periodIncome = (int) $request->get('periodIncome');
            $periodOrders = (int) $request->get('periodOrders');
        }

        for ($i = 0; $i < $periodOrders; $i++) {
            $day = $this->getOnlyDate(Carbon::now()->subDays($periodOrders - $i));
            $nextDay = $this->getOnlyDate(Carbon::now()->subDays($periodOrders - $i - 1));
            if($periodOrders > 7) {
                $daysOfWeekCost[] = $day->format('d.m.y');
            } else {
                $daysOfWeekCost[] = $this->getDayOfWeek($day);
            }

            $countOrdersForDay[] = count(Order::where([['created_at', '>=',$day],['created_at', '<=', $nextDay]])->get());
        }

        for($i = 0; $i < $periodIncome; $i++) {
            $day = $this->getOnlyDate(Carbon::now()->subDays($periodIncome - $i));
            $nextDay = $this->getOnlyDate(Carbon::now()->subDays($periodIncome - $i - 1));
            if($periodIncome > 7) {
                $daysOfWeekCountOrders[] = $day->format('d.m.y');
            } else {
                $daysOfWeekCountOrders[] = $this->getDayOfWeek($day);
            }


            $ordersForDay = Order::where([['created_at', '>=',$day],['created_at', '<=', $nextDay]])->get();
            $sum = 0;
            if(count($ordersForDay) != 0) {
                foreach ($ordersForDay as $order) {
                    $sumForOrder = 0;
                    $orderDishes = $order->orderDishes()->get();
                    foreach ($orderDishes as $orderDishes) {
                        $sumForOrder += ((int) $orderDishes->sum);
                    }
                    $sum += $sumForOrder;
                }
            } else {
                $sum = 0;
            }


            $costForDay[] = $sum;
        }
        return response()->json([
            'data' => [
                'costs' => $costForDay,
                'daysOfWeekCost' => $daysOfWeekCost,
                'daysOfWeekCountOrders' => $daysOfWeekCountOrders,
                'countOrdersDate' => $periodOrders > 7,
                'countIncomeDate' => $periodIncome > 7,
                'countOrders' => $countOrdersForDay,
            ]
        ], 200);
    }

    private function getDayOfWeek($date)
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        switch ($dayOfWeek) {
            case 0:
                return "Воскресенье";
            case 1:
                return "Понедельник";
            case 2:
                return "Вторник";
            case 3:
                return "Среда";
            case 4:
                return "Четверг";
            case 5:
                return "Пятница";
            case 6:
                return "Суббота";
        }
    }

    private function getOnlyDate($date) {
       return Carbon::parse(Carbon::parse($date)->toDateString());
    }
}
