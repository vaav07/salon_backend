<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\User;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);

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
            'expires_in' => auth()->factory()->getTTL() * 60, //1 hour  1440 = 1 day
            'user' => auth()->user()
        ]);
    }

    public function getCustomer($id)
    {
        $getCustomer = Customer::where("user_id", $id)->get();

        return ["result" => $getCustomer];
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
        $customer->customer_fullname = $req->customer_fullname;
        $customer->email = $req->email;
        $customer->phone_no = $req->phone_no;
        $customer->alt_phone_no = $req->alt_phone_no;
        $customer->address = $req->address;
        $customer->state = $req->state;
        $customer->city = $req->city;
        $customer->pincode = $req->pincode;
        $customer->dob = $req->dob;
        $customer->gender = $req->gender;
        $result = $customer->save();
        if ($result) {

            return ["Result" => "Data has been saved"];
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
            $searchQuery->where('customer_fullname', 'like', "%$searchTerm%")
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
        $employee->employee_fullname = $req->employee_fullname;
        $employee->email = $req->email;
        $employee->phone_no = $req->phone_no;
        $employee->alt_phone_no = $req->alt_phone_no;
        $employee->address = $req->address;
        $employee->state = $req->state;
        $employee->city = $req->city;
        $employee->pincode = $req->pincode;
        $employee->dob = $req->dob;
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
        // $service->admin_id = $req->admin_id;
        // $service->user_id = $req->user_id;
        // $service->service_name = $req->service_name;
        // $service->description = $req->description;
        // $service->price = $req->price;
        $service->fill($req->all());

        $result = $service->save();
        if ($result) {

            return ["Result" => "Data has been saved"];
        } else {
            return ["Result" => "Operation failed"];
        }
    }

    public function allReports($id)
    {
        // $getSales = Sale::where("user_id", $id)->get();

        // return ["result" => $getSales];
        $reports = DB::table('sales')
            ->where('sales.user_id', '=', $id)
            ->join('customers', 'sales.customer_id', '=', 'customers.id')
            ->join('employees', 'sales.employee_id', '=', 'employees.id')
            ->join('services', 'sales.service_id', '=', 'services.id')
            ->select('customers.customer_fullname as customer_name', 'customers.email', 'customers.phone_no', 'customers.alt_phone_no', 'customers.address', 'services.service_name as service_name', 'employees.employee_fullname as employee_name', 'sale_date', 'sale_time', 'total_price')
            ->get();

        return ["result" => $reports];
    }

    public function addSale(Request $req)
    {
        $sale = new Sale();
        $sale->admin_id = $req->admin_id;
        $sale->user_id = $req->user_id;
        $sale->employee_id = $req->employee_id;
        $sale->customer_id = $req->customer_id;
        $sale->service_id = $req->service_id;
        $sale->sale_date = $req->sale_date;
        $sale->sale_time = $req->sale_time;
        $sale->payment_method = $req->payment_method;
        $sale->total_price = $req->total_price;

        //chatgpt
        // $sale->fill($req->only([
        //     'admin_id',
        //     'user_id',
        //     'employee_id',
        //     'customer_id',
        //     'service_id',
        //     'sale_date',
        //     'sale_time',
        //     'payment_method',
        //     'total_price'
        // ]));

        //bard
        // $sale->fill($req->all());

        $result = $sale->save();
        if ($result) {

            return ["Result" => "Data has been saved"];
        } else {
            return ["Result" => "Operation failed"];
        }
    }
}
