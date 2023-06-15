<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\User;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Support\Str;


class AdminController extends Controller
{
    //
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['username', 'password']);

        if (!$token = auth('admin')->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth('admin')->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {

        // dd(auth('admin'));
        auth('admin')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('admin')->refresh());
        // return Auth::user()->id;
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('admin')->factory()->getTTL() * 60,
            'user' => auth('admin')->user()
        ]);
    }

    public function deleteService($adminId, $serviceId)
    {
        // Check if the admin exists
        $admin = Admin::find($adminId);
        if (!$admin) {
            return response()->json(['message' => 'Admin not found'], 404);
        }

        // Check if the service exists for the admin
        $service = Service::where('admin_id', $adminId)->find($serviceId);
        if (!$service) {
            return response()->json(['message' => 'Service not found'], 404);
        }

        // Delete the service
        $service->delete();

        return response()->json(['message' => 'Service deleted successfully']);
    }

    public function dailySales($adminId)
    {

        $admin = Admin::find($adminId);
        if (!$admin) {
            return response()->json(['message' => 'Admin not found'], 404);
        }

        $today = Carbon::today();
        $dailySales = Sale::select('user_id', Sale::raw('SUM(total_price) as total_sales'))
            ->whereDate('sale_date', $today)
            ->groupBy('user_id')
            ->get();

        $users = User::whereIn('id', $dailySales->pluck('user_id'))->get();

        $result = [];
        foreach ($users as $user) {
            $totalSales = $dailySales->where('user_id', $user->id)->first()->total_sales;
            $result[] = [
                'user_id' => $user->id,
                'username' => $user->username,
                'total_sales' => $totalSales,
            ];
        }

        return response()->json($result);
    }

    public function dailySalesByDate($adminId, $date = null)
    {
        $admin = Admin::find($adminId);
        if (!$admin) {
            return response()->json(['message' => 'Admin not found'], 404);
        }

        if ($date === null) {
            $date = Carbon::yesterday()->toDateString();
        }

        $dailySales = Sale::select('user_id', Sale::raw('SUM(total_price) as total_sales'))
            ->whereDate('sale_date', $date)
            ->groupBy('user_id')
            ->get();

        $users = User::whereIn('id', $dailySales->pluck('user_id'))->get();

        $result = [];
        foreach ($users as $user) {
            $totalSales = $dailySales->where('user_id', $user->id)->first()->total_sales;

            $cashSales = Sale::where('user_id', $user->id)
                ->whereDate('sale_date', $date)
                ->where('payment_method', 'Cash')
                ->sum('total_price');

            $cardSales = Sale::where('user_id', $user->id)
                ->whereDate('sale_date', $date)
                ->where('payment_method', 'Card')
                ->sum('total_price');

            $upiSales = Sale::where('user_id', $user->id)
                ->whereDate('sale_date', $date)
                ->where('payment_method', 'UPI')
                ->sum('total_price');

            $result[] = [
                'user_id' => $user->id,
                'username' => $user->username,
                'total_sales' => $totalSales,
                'cash_sales' => $cashSales,
                'card_sales' => $cardSales,
                'upi_sales' => $upiSales,
            ];
        }

        return response()->json($result);
    }

    public function pastMonthSales($adminId)
    {
        $admin = Admin::find($adminId);
        if (!$admin) {
            return response()->json(['message' => 'Admin not found'], 404);
        }

        $currentDate = Carbon::now();
        $currentMonth = $currentDate->month;

        // If current month is January, the previous month will be December of the previous year
        if ($currentMonth == 1) {
            $prevMonthYear = $currentDate->year - 1;
            $prevMonth = 12;
        } else {
            $prevMonthYear = $currentDate->year;
            $prevMonth = $currentMonth - 1;
        }

        $startDate = Carbon::create($prevMonthYear, $prevMonth, 1)->startOfMonth();
        $endDate = Carbon::create($prevMonthYear, $prevMonth, 1)->endOfMonth();

        $sales = Sale::select('user_id', Sale::raw('SUM(total_price) as total_sales'))
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->groupBy('user_id')
            ->get();

        $users = User::whereIn('id', $sales->pluck('user_id'))->get();

        $result = [];
        foreach ($users as $user) {
            $totalSales = $sales->where('user_id', $user->id)->first()->total_sales;
            $result[] = [
                'user_id' => $user->id,
                'username' => $user->username,
                'total_sales' => $totalSales,
            ];
        }

        return response()->json($result);
    }

    public function getEmployees($adminId)
    {
        $admin = Admin::find($adminId);
        if (!$admin) {
            return response()->json(['message' => 'Admin not found'], 404);
        }

        $employees = $admin->employees;

        return response()->json($employees);
    }

    public function getServices($adminId)
    {
        $admin = Admin::find($adminId);
        if (!$admin) {
            return response()->json(['message' => 'Admin not found'], 404);
        }

        $services = $admin->services;

        return response()->json($services);
    }

    public function userStats($adminId)
    {
        $admin = Admin::find($adminId);
        if (!$admin) {
            return response()->json(['message' => 'Admin not found'], 404);
        }

        $users = User::whereHas('admin', function ($query) use ($adminId) {
            $query->where('id', $adminId);
        })
            ->with('customer', 'employee')
            ->get();

        $stats = [];

        foreach ($users as $user) {
            $customerCount = $user->customer ? 1 : 0;
            $employeeCount = $user->employee ? 1 : 0;
            $salesCount = Sale::where('user_id', $user->id)->count();

            $stats[] = [
                'user_id' => $user->id,
                'username' => $user->username,
                'customer_count' => $customerCount,
                'employee_count' => $employeeCount,
                'sales_count' => $salesCount,
            ];
        }

        return response()->json($stats);
    }
}
