<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ApexChartController extends Controller
{
    public function dataOrdersAndCost()
    {

        $user = Auth::user();
        $root = $user->role()->first()->root()->first();


        $daysOfWeekStr = array();
        $countOrdersForDay = array();
        $costForDay = array();

        for ($i = 0; $i < 7; $i++) {
            $day = $this->getOnlyDate(Carbon::now()->subDays(7 - $i));
            $nextDay = $this->getOnlyDate(Carbon::now()->subDays(7 - $i - 1));
            $daysOfWeekStr[] = $this->getDayOfWeek($day);

            $countOrdersForDay[] = count(Order::where([['created_at', '>=',$day],['created_at', '<=', $nextDay]])->get());
            $ordersForDay = Order::where([['created_at', '>=',$day],['created_at', '<=', $nextDay]])->get();
            $sum = 0;
            foreach ($ordersForDay as $order) {
                $sumForOrder = 0;
                $orderDishes = $order->orderDishes()->get();
                foreach ($orderDishes as $orderDishes) {
                    $sumForOrder += ((int) $orderDishes->sum);
                }
                $sum += $sumForOrder;
            }

            $costForDay[] = $sum;
        }
        return response()->json([
            'data' => [
                'costs' => $costForDay,
                'daysOfWeek' => $daysOfWeekStr,
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
