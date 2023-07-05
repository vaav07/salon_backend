<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\User;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'refresh', 'register']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['username', 'password']);

        if (!$token = auth()->attempt($credentials)) {
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
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
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
            'expires_in' => auth()->factory()->getTTL() * 60, //1 hour = 60  1440 = 1 day
            'user' => auth()->user()
        ]);
    }

    public function register(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'admin_id' => 'required',
            'username' => 'required',
            'name' => 'required|string|min:2|max:100',
            // 'email' => 'string|email|max:100|unique:users',
            'password' => 'required|string|min:5'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'admin_id' => $req->admin_id,
            'username' => $req->username,
            'name' => $req->name,
            'email' => $req->email,
            'password' => Hash::make($req->password),
        ]);

        return response()->json([
            'message' => 'user register successfully',
            'user' => $user
        ]);
    }

    public function countStatistics($id)
    {


        $employeeCount = Employee::where('user_id', $id)->count();
        $customerCount = Customer::where('user_id', $id)->count();
        $serviceCount = Service::count();
        $saleCount = Sale::where('user_id', $id)->count();

        // $overallSalesAmount = Sale::where('user_id', $id)->sum('total_price');
        // $cashSalesAmount = Sale::where('user_id', $id)->where('payment_method', 'cash')->sum('total_price');
        // $upiSalesAmount = Sale::where('user_id', $id)->where('payment_method', 'upi')->sum('total_price');
        // $cardSalesAmount = Sale::where('user_id', $id)->where('payment_method', 'card')->sum('total_price');

        $today = Carbon::today();
        $todaysSales = Sale::where('user_id', $id)
            ->whereDate('sale_date', $today)
            // ->sum('total_price')
            ->get();
        $todaysSalesCount = $todaysSales->count();
        $todaysOverallSalesAmount = $todaysSales->sum('total_price');
        $todaysCashSalesAmount = $todaysSales->where('payment_method', 'cash')->sum('total_price');
        $todaysCardSalesAmount = $todaysSales->where('payment_method', 'card')->sum('total_price');
        $todaysUpiSalesAmount = $todaysSales->where('payment_method', 'upi')->sum('total_price');

        $employeeSalesAmounts = $todaysSales->groupBy('employee_id')
            ->map(function ($sales, $employeeId) {
                $employee = Employee::find($employeeId);
                $employeeName = $employee ? $employee->fullname : 'Unknown Employee';
                $salesAmount = $sales->sum('total_price');
                // return [$employeeName => $salesAmount];
                return  ['employee_name' => Str::title($employeeName), 'salesAmount' => $salesAmount];
            })
            ->values()
            ->toArray();


        $statistics = [
            'employee_count' => $employeeCount,
            'customer_count' => $customerCount,
            'service_count' => $serviceCount,
            'sale_count' => $saleCount,
            // 'overallSalesAmount' => $overallSalesAmount,
            // 'cashSalesAmount' => $cashSalesAmount,
            // 'upiSalesAmount' => $upiSalesAmount,
            // 'cardSalesAmount' => $cardSalesAmount,
            'todays_sale_count' => $todaysSalesCount,
            'todaysOverallSalesAmount' => $todaysOverallSalesAmount,
            'todaysCashSalesAmount' => $todaysCashSalesAmount,
            'todaysCardSalesAmount' => $todaysCardSalesAmount,
            'todaysUpiSalesAmount' => $todaysUpiSalesAmount,
            'employeeSalesAmounts' => $employeeSalesAmounts,
        ];

        return response()->json($statistics);
    }


    public function getCustomer($id)
    {
        $customers = Customer::where('user_id', $id)
            // ->with(['latestSale' => function ($query) {
            //     $query
            //         ->orderBy('sale_date', 'desc')
            //         ->limit(1);
            // }])
            ->get();

        return response()->json($customers);
    }

    public function getSpecificCustomer($id)
    {
        $specificCustomer = Customer::find($id);

        return ["result" => $specificCustomer];
    }

    public function addCustomer(Request $req)
    {
        $customer = new Customer();
        $customer->admin_id = $req->admin_id;
        $customer->user_id = $req->user_id;
        $customer->fullname = $req->fullname;
        $customer->email = $req->email;
        $customer->phone_no = $req->phone_no;
        // $customer->alt_phone_no = $req->alt_phone_no;
        // $customer->address = $req->address;
        // $customer->state = $req->state;
        // $customer->city = $req->city;
        // $customer->pincode = $req->pincode;
        // $customer->dob = $req->dob;
        $customer->gender = $req->gender;
        $result = $customer->save();
        if ($result) {

            return ["Result" => "Data has been saved", "Customer_ID" => $customer->id];
        } else {
            return ["Result" => "Operation failed"];
        }
    }

    public function updateCustomer(Request $req, $id)
    {
        // Find the resource
        $resource = Customer::findOrFail($id);

        // Update the resource
        $resource->update($req->all());

        // Return a response
        return response()->json(['message' => 'Resource updated successfully']);
    }

    //Search
    public function search(Request $request)
    {
        // Get the search query from the request
        // $query = $request->query('q');
        $searchTerm = $request->query('q');
        $userId = $request->query('user_id');

        // Perform the search query
        // for searching single column
        // $results = Customer::where('customer_fullname', 'like', "%$query%")->get();   

        //multiple columns
        $results = Customer::where(function ($searchQuery) use ($searchTerm, $userId) {
            $searchQuery->where('fullname', 'like', "%$searchTerm%")
                ->orWhere('phone_no', 'like', "%$searchTerm%")
                ->where('user_id', 'like', "%$userId%");
        })->limit(8)->get();

        // Return the search results
        return ["users" => $results];
        // return response()->json($results);
    }

    public function getEmployee($id)
    {
        $getEmployee = Employee::where("user_id", $id)->get();

        return ["result" => $getEmployee];
    }

    public function addEmployee(Request $req)
    {
        $employee = new Employee();
        $employee->admin_id = $req->admin_id;
        $employee->user_id = $req->user_id;
        $employee->fullname = $req->fullname;
        $employee->email = $req->email;
        $employee->phone_no = $req->phone_no;
        // $employee->alt_phone_no = $req->alt_phone_no;
        // $employee->address = $req->address;
        // $employee->state = $req->state;
        // $employee->city = $req->city;
        // $employee->pincode = $req->pincode;
        // $employee->dob = $req->dob;
        $employee->gender = $req->gender;
        $employee->date_of_joining = $req->date_of_joining;
        $result = $employee->save();
        if ($result) {

            return ["Result" => "Data has been saved"];
        } else {
            return ["Result" => "Operation failed"];
        }
    }

    public function getSpecificEmployee($id)
    {
        $specificCEmployee = Employee::find($id);

        return ["result" => $specificCEmployee];
    }

    public function updateEmpolyee(Request $req, $id)
    {
        // Find the resource
        $resource = Employee::findOrFail($id);

        // Update the resource
        $resource->update($req->all());

        // Return a response
        return response()->json(['message' => 'Resource updated successfully']);
    }

    public function getService()
    {
        $getService = Service::all();

        return ["result" => $getService];
    }

    public function getServicesName()
    {


        $getService = Service::pluck('service_name');

        return ["result" => $getService];
    }

    public function addService(Request $req)
    {
        $service = new Service();

        $service->fill($req->all());

        $result = $service->save();
        if ($result) {

            return ["Result" => "Data has been saved"];
        } else {
            return ["Result" => "Operation failed"];
        }
    }

    public function allReports($id, Request $request)
    {
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        // echo "satrtDate" . $startDate;
        // echo "endDate" . $endDate;


        $invoices = Sale::with(['customer', 'services', 'employee'])
            ->where('user_id', $id);

        if ($startDate && $endDate) {
            $invoices->whereBetween('sale_date', [$startDate, $endDate]);
        }

        $invoices = $invoices->get();


        $formattedInvoices = $invoices->map(function ($invoice) {
            return [
                'customer_name' => $invoice->customer->fullname,
                'employee_name' => $invoice->employee->fullname,
                'services' => $invoice->services->pluck('service_name')->implode(", "),
                'invoice_date' => $invoice->sale_date,
                'invoice_time' => $invoice->sale_time,
                'total_amount' => floatval($invoice->total_price),
            ];
        });

        return response()->json($formattedInvoices);
    }

    public function addSale(Request $req)
    {

        $invoice = Sale::create([
            'admin_id' => $req->admin_id,
            'user_id' => $req->user_id,
            'employee_id' => $req->employee_id,
            'customer_id' => $req->customer_id,
            'sale_date' => $req->sale_date,
            'sale_time' => $req->sale_time,
            'payment_method' => $req->payment_method,
            'total_price' => $req->total_price,
        ]);


        $invoice->services()->attach($req->services);
        return ["Succeess"];
    }

    //not using this
    // public function lastVisited($id)
    // {
    //     $inactiveCustomers = Customer::where('user_id', $id)
    //         ->inactive()
    //         ->with(['latestSale' => function ($query) {
    //             $query->select('customer_id', 'sale_date');
    //         }])
    //         ->get()
    //         ->map(function ($customer) {
    //             return [
    //                 'fullname' => $customer->fullname,
    //                 'phone_no' => $customer->phone_no,
    //                 'last_visited' => $customer->latestSale ? $customer->latestSale->sale_date : null
    //             ];
    //         });

    //     return response()->json($inactiveCustomers);
    // }

    public function inactiveCustomers($id, $duration)
    {
        $cutOffDate = now()->subMonths($duration); // Get the date/time based on the month cutoff

        $inactiveCustomers = Customer::where('user_id', $id)
            ->inactive()
            ->whereDoesntHave('latestSale', function ($query) use ($cutOffDate) {
                $query->where('sale_date', '>=', $cutOffDate);
            })
            // ->select('fullname', 'phone_no', 'sale_date')
            ->get()
            ->map(function ($customer) {
                return [
                    'fullname' => $customer->fullname,
                    'phone_no' => $customer->phone_no,
                    'last_visited' => $customer->latestSale ? $customer->latestSale->sale_date : null
                ];
            });

        return response()->json($inactiveCustomers);
    }

    public function customerVisitsInMonth($id)
    {
        $startDate = null;
        $endDate = null;

        // Check if the "month" query parameter is provided
        if (request()->has('month')) {
            $startDate = Carbon::parse(request('month'))->startOfMonth();
            $endDate = Carbon::parse(request('month'))->endOfMonth();
        }

        $customerVisits = Customer::select('fullname')
            ->withCount(['sales' => function ($query) use ($startDate, $endDate) {
                if ($startDate && $endDate) {
                    $query->whereBetween('sale_date', [$startDate, $endDate]);
                }
            }])
            ->where('user_id', $id)
            ->get();

        return response()->json($customerVisits);
    }
}
