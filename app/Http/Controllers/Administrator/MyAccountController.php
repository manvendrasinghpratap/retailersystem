<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Designation;
use App\Models\SubscriptionPlan;
use App\Models\State;
use App\Models\Countries;
use App\Models\LocalGovernment;
use App\Models\Account;
use App\Models\UserDetail;
use App\Models\AccountSubscription;
use App\Models\SubscriptionPayment;
use App\Helpers\Settings;
use App\Models\DefaultSiteConfig;
use App\Models\UserAccountSubscription;
use App\Models\SiteConfig;
use DB;
use PDF;
use App\Services\UserService;
class MyAccountController extends Controller
{
    protected $breadcrumbAddNew;
    protected $breadcrumbListing;
    protected $breadcrumbSubscribeListing;
    protected $breadcrumbChangePassword;
    protected $userService;
    
    public function __construct(UserService $userService){
        $this->userService = $userService;
        $this->middleware('auth');
        $this->breadcrumbAddNew = ['title' => __('translation.accounts'), 'route1' => 'administrator.account.add', 'route1Title' => __('translation.add_new_account'), 'route2' => 'administrator.account.add', 'route2Title' => __('translation.add_new_account'), 'reset_route' => 'administrator.accounts', 'reset_route_title' => __('translation.cancel')];

        $this->breadcrumbListing = ['title' => __('translation.add_new_account'), 'route1' => 'administrator.accounts', 'route1Title' => __('translation.accounts'), 'route2' => 'account.add', 'route2Title' => __('translation.add_new_account'), 'reset_route' => 'administrator.accounts', 'reset_route_title' => __('translation.cancel')];
        $this->breadcrumbSubscribeListing = ['title' => __('translation.assign_subscription_to_account'), 'route1' => 'administrator.accounts', 'route1Title' => __('translation.assign_subscription_to_account').' '.__('translation.detail'), 'route2' => 'administrator.accounts', 'route2Title' => __('translation.accounts'), 'reset_route' => 'administrator.accounts', 'reset_route_title' => __('translation.cancel')];

        $this->breadcrumbChangePassword = ['title' => 'Change Password', 'route1' => 'administrator.dashboard', 'route1Title' => 'Dashboard', 'route2' => 'administrator.updatepassword', 'route2Title' => 'Profile', 'reset_route' => 'administrator.dashboard', 'reset_route_title' => __('translation.cancel')];
    }

    public function index(Request $request)
    {
        $breadcrumb                  = $this->breadcrumbAddNew;
        $account_status              = \Config::get('constants.accountstatus');
        $updatedAt = '';
        $accountList = Account::with(['user','subscription'])->where('is_deleted',0);

        if (!empty(request()->get('accountname'))) {
            $accountList = $accountList->where('name', 'LIKE', '%' . trim(request()->get('accountname')) . '%');
        }
        if (!empty(request()->get('subscription_id'))) {
            $accountList = $accountList->whereHas('subscription', function ($query) {
            $query->where('subscription_id', request()->get('subscription_id'));
            });
        }
        if (request()->get('is_active') !==null) {
            $accountList = $accountList->where('status', request()->get('is_active')==1?'active':'inactive');
        }
        $accountList = $accountList->orderBy('id', 'desc')->paginate(\Config::get('constants.pagination'));
        $subscriptionPlan = SubscriptionPlan::select('name','id')->where('status',1)->whereNull('deleted_at')->pluck('name','id')->toArray();
        return view('backend.administrator.account.index', compact("accountList", "updatedAt",'breadcrumb','account_status','subscriptionPlan'));
    }

    public function create(Request $request)
    {
        $route                      = 'add';
        $submitText                 = 'Save';
        $breadcrumb                 = $this->breadcrumbListing;
        $suffix                     = \Config::get( 'constants.suffix' );
        $account_status             = \Config::get('constants.accountstatus');
        $designation                = Designation::where('id', '!=', 1)->where('id', '!=', 3)->get();
        $localGovernment            = LocalGovernment::pluck('name','name')->toArray();
        $countries                  = Countries::pluck('country_name','country_name')->toArray();
        $state                      = State::pluck('name','name')->toArray();
        return view('backend.administrator.account.form', compact(["designation", "route",'breadcrumb','suffix','localGovernment','countries','state','account_status','submitText']));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'first_name' => 'required',
                'last_name' => 'required',
                'office_phone' => 'required',
                'cell_phone' => 'required',
                'whatsapp_number' => 'required',
                'nin' => 'required',
                'local_government' => 'required',
                'country_of_origin' => 'required',
                'state_of_origin' => 'required',
                'email' => 'required|unique:users|max:50',
                'username' => 'required|unique:users|max:20',
                'password' => 'required|confirmed|min:8',
                'password_confirmation' => 'required',
            ]);
            $filename = 'default.png';
            if ($request->hasFile('avatar')) {
                $filename = Settings::uploadimage($request, 'avatar', 'staff');
            }
            $user = $this->userService->createAdmin($request);
            $account = Account::createAccount($user, $request);
            $user->account_id           = $account->id;
            $user->save();
            $userDetail = UserDetail::updateOrCreateDetail($user->id, $request->all());
            return Settings::roleRedirect('accounts','Account Added Successfully.');
            } catch (\Exception $e) {
                return Settings::roleRedirect('accounts','Something went wrong!','error');
            }
    }

    public function edit(Request $request, $id)
    {
        $id                         = Settings::getDecodeCode($id);
        $route                      = 'edit';
        $submitText                 = 'Update';
        $breadcrumb = Settings::updateBreadcrumbRoute($this->breadcrumbListing,['title'],[__('translation.update_account')]);
        $accountdetails             = Account::find($id);
        $suffix                     = \Config::get( 'constants.suffix' );
        $account_status             = \Config::get('constants.accountstatus');
        $designation                = Designation::where('id', '!=', 1)->where('id', '!=', 3)->get();
        $localGovernment            = LocalGovernment::pluck('name','name')->toArray();
        $countries                  = Countries::pluck('country_name','country_name')->toArray();
        $state                      = State::pluck('name','name')->toArray();
        return view('backend.administrator.account.form', compact(["designation", "route",'breadcrumb','suffix','localGovernment','countries','state','account_status','accountdetails','submitText']));
    }

    public function statusUpdate(Request $request)
    {
        try {
            $id = $request->input('id');
            $status = $request->input('status');
            User::where("hotel_id", $id)->update(["is_active" => $status]);
            Hotel::where("id", $id)->update(["status" => $status==0?'inactive':'active']);
            Session::flash('success', 'Data update successfully.');
        } catch (\Exception $e) {
            Session::flash('error', 'something went wrong!');
            return redirect()->back()->withInput();
        }
    }

    public function delete(Request $request)
    {
        try {
            $id = $request->input('id');
            $is_deleted = $request->input('is_deleted');
            Account::where("id", $id)->update(["is_deleted" => 1,'status' => 0]);
            Session::flash('success', 'Data update successfully.');
        } catch (\Exception $e) {
            Session::flash('error', 'something went wrong!');
            return redirect()->back()->withInput();
        }
    }

    public function update(Request $request)
        {
            try {
                $id = Settings::getDecodeCode($request->account);
                $account = Account::findOrFail($id);
                $user    = User::findOrFail($account->user_id);
                $request->validate([
                'first_name' => 'required',
                'last_name' => 'required',
                'office_phone' => 'required',
                'cell_phone' => 'required',
                'whatsapp_number' => 'required',
                'nin' => 'required',
                'local_government' => 'required',
                'country_of_origin' => 'required',
                'state_of_origin' => 'required',
                'email'             => 'required|email|max:50|unique:users,email,' . $user->id,
            ]);

                $fullName = $request->suffix . ' ' . ucwords($request->first_name) . ' ' .ucwords($request->last_name);

                /*
                |--------------------------------------------------------------------------
                | Update User
                |--------------------------------------------------------------------------
                */

                $user->update([
                    'name'       => $fullName,
                    'email'      => $request->email,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Update Account
                |--------------------------------------------------------------------------
                */

                $account->update([
                    'name'   => $fullName,
                    'status' => $request->is_active
                ]);

                /*
                |--------------------------------------------------------------------------
                | Update User Details
                |--------------------------------------------------------------------------
                */
                $userDetail = UserDetail::updateOrCreateDetail($user->id, $request->all());
                return Settings::roleRedirect('accounts', 'Account Updated Successfully.');

            } catch (\Exception $e) {

                return Settings::roleRedirect('accounts', 'Something went wrong!', 'error');
            }
        }





    public function downloadHotelpdf(Request $request)
    {
        $updatedAt = '';
        $hotelList = Hotel::whereNot('status','suspended')->orderBy('id', 'desc')->get();
        $subscriptionList = SubscriptionPlan::pluck('name', 'id')->toArray();
		$pdfHeaderdata = \Config::get('constants.downloadhotelpdf');
        $pdf = PDF::loadView('backend.hotel.downloadpdf', compact("hotelList", "subscriptionList", "updatedAt",'pdfHeaderdata'))->setOptions(['defaultFont' => 'sans-serif']);
        return $pdf->stream('hotel-list.pdf');
    }

    public function subscribe(Request $request,$accountId){
        $id                         = Settings::getDecodeCode($accountId);
        $route                      = 'add';
        $submitText                 = 'Save';
        $breadcrumb                 = $this->breadcrumbSubscribeListing;
        $suffix                     = \Config::get( 'constants.suffix' );
        $account_status             = \Config::get('constants.accountstatus');
        $subscriptionplan           = SubscriptionPlan::where('status',1)->whereNull('deleted_at')->pluck('name','id')->toArray();
        return view('backend.administrator.account.subscribe', compact(["route",'breadcrumb','subscriptionplan','account_status','submitText','accountId']));
    }

    public function storesubscribe(Request $request)
        {
            $request->validate([
                'account' => 'required',
                'subscription_id' => 'required',
                'start_date' => 'required',
            ]);

            try {

                $accountId = Settings::getDecodeCode($request->account);

                $subscriptionDetails = SubscriptionPlan::findOrFail($request->subscription_id);

                $duration  = $subscriptionDetails->duration ?? 1;
                $startDate = Settings::formatDate($request->start_date, 'Y-m-d');
                $endDate   = date('Y-m-d', strtotime("+{$duration} month", strtotime($startDate)));

                $status = ($startDate == date('Y-m-d')) ? 1 : 0;

                $pos      = $request->pos ?? 0;
                $transfer = $request->transfer ?? 0;

                $accountSubscription = new AccountSubscription();
                $accountSubscription->account_id          = $accountId;
                $accountSubscription->subscription_id     = $request->subscription_id;
                $accountSubscription->subscription_name   = ucwords($subscriptionDetails->name);
                $accountSubscription->start_date          = $startDate;
                $accountSubscription->end_date            = $endDate;
                $accountSubscription->status              = $status;
                $accountSubscription->discount            = $request->discount ?? 0;
                $accountSubscription->amount_paid         = $pos + $transfer;
                $accountSubscription->subscription_price  = $subscriptionDetails->price;
                $accountSubscription->created_by          = Auth::id();
                $accountSubscription->save();

                /* POS Payment */
                if ($pos > 0) {
                    SubscriptionPayment::create([
                        'account_id'               => $accountId,
                        'account_subscription_id'  => $accountSubscription->id,
                        'payment_method'           => 1,
                        'amount'                   => $pos,
                        'created_by'               => Auth::id(),
                    ]);
                }

                /* Bank Transfer Payment */
                if ($transfer > 0) {
                    SubscriptionPayment::create([
                        'account_id'               => $accountId,
                        'account_subscription_id'  => $accountSubscription->id,
                        'payment_method'           => 2,
                        'amount'                   => $transfer,
                        'created_by'               => Auth::id(),
                    ]);
                }

                /* Get user id from account */
                $userId = Account::where('id', $accountId)->value('user_id');

                /* Create or Update user subscription */
                UserAccountSubscription::updateOrCreate(
                    ['user_id' => $userId],
                    [
                        'account_subscription_id' => $accountSubscription->id,
                        'status' => $status
                    ]
                );

                /* Update default site config */
                DefaultSiteConfig::where('id', 1)->update(['account_id' => $accountId]);

                return Settings::roleRedirect('accounts', 'Subscription Plan Added Successfully.');

            } catch (\Exception $e) {

                return Settings::roleRedirect('accounts', 'Something went wrong!', 'error');
            }
        }

    

    public function updateadministrationpassword(Request $request){
        
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required',
                'password' => ['required', 'min:' . $request->passwordlength],
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'iserror' => true,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
    
            // Check current password
            if (!Hash::check($request->current_password, Auth::user()->password)) {
                return response()->json([
                    'iserror' => true,
                    'message' => 'Current password is incorrect'
                ], 422);
            }
    
            // Proceed with password update
            $request->merge(['staff_id' => Auth::user()->id]);
            $this->updatepassword($request);  
            Auth::logout();

            // Optionally, invalidate the session
            $request->session()->invalidate();
            $request->session()->regenerateToken();  
            return response()->json([
                'iserror' => false,
                'message' => 'Password changed successfully! You will be logged out.',
                'redirect' => route('login')
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'iserror' => true,
                'message' => 'Something went wrong!',
                'debug' => $e->getMessage()
            ], 500);
        }

    }

    public function updatepassword(Request $request){

        $request->validate([
            'staff_id' => 'required'
        ]); 
        try {            
            $user           = User::find($request->staff_id);
            $user->password = Hash::make($request->input('password'));
            $user->update();
            return response()->json(['message' => 'Password Changed Successfully!']);
        } catch (\Exception $e) {
            Session::flash('error', 'something went wrong!');
            return redirect()->back()->withInput();
        }
    }
    public function getsubscriptionprice(Request $request){
        $request->validate([
            'subscriptionid' => 'required'
        ]); 
        try {   
            $subscriptionDetails = SubscriptionPlan::find($request->subscriptionid);                  
            return response()->json(['price'=>$subscriptionDetails->price]);
        } catch (\Exception $e) {
            Session::flash('error', 'something went wrong!');
            return redirect()->back()->withInput();
        }
    }

    public function accountsubscriptionpaymentdetails(Request $request)
    {
        $request->validate([
            'accountSubscriptionId' => 'required'
        ]);
        try {
            $subscriptionPaymentDetails = SubscriptionPayment::where('account_subscription_id', $request->accountSubscriptionId)->get();    
            $renderPage = view('backend.administrator.account.accountsubscriptionpaymentdetails', compact('subscriptionPaymentDetails'))->render();    
            return response()->json([
                'status' => 'success',
                'html' => $renderPage
            ]);
        } catch (\Exception $e) {  
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong!'
            ], 500);
        }
    }

    public function editPassword()
    {
        $breadcrumb = $this->breadcrumbChangePassword;
        return view('backend.administrator.account.password',compact('breadcrumb'));
    }
   
    

}
