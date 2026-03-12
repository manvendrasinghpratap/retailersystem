<?php

namespace App\Http\Controllers\Administrator;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubscriptionRequest;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Helpers\Settings;
use DB;
use PDF;

class SubscriptionController extends Controller
{
    protected $breadcrumbAddNew;
    protected $breadcrumbListing;
    public function __construct()
    {
        $this->middleware('auth'); 
        $this->breadcrumbAddNew = ['title'=>__('translation.subscription_plan'),'route1'=>'administrator.subscription','route1Title'=>__('translation.subscription').' '.__('translation.listing'),'route2'=>'administrator.subscription.store','route2Title'=>__('translation.add_new_subscription'),'reset_route'=>'administrator.subscription','reset_route_title'=>__('translation.cancel')];

        $this->breadcrumbListing = ['title'=>__('translation.subscription').' '.__('translation.listing'),'route1'=>'administrator.subscription.add','route1Title'=>__('translation.add_new_subscription'),'route2'=>'administrator.subscription.store','route2Title'=>__('translation.add_new_subscription'),'reset_route'=>'administrator.subscription','reset_route_title'=>__('translation.cancel')];

    }

    public function index(Request $request)
    {
        $breadcrumb = $this->breadcrumbListing;
        $subscription = SubscriptionPlan::select('*');

        if (!empty(request()->get('subscription_id'))) {
            $subscription = $subscription->where('id', request()->get('subscription_id'));
        }
        if (request()->get('is_active') !==null) {
            $subscription = $subscription->where('status', request()->get('is_active'));
        }

        $subscriptionList = $subscription->orderBy('id', 'desc')->paginate(\Config::get('constants.pagination'));
        $subscriptionDropdown = SubscriptionPlan::pluck('name', 'id')->toArray();
         $account_status  = \Config::get('constants.accountstatus');
        return view('backend.administrator.subscription.index', compact("subscriptionList","subscriptionDropdown",'breadcrumb','account_status'));

    }

    public function create(Request $request)
    {
        $breadcrumb = $this->breadcrumbAddNew;
        $route = 'add';
        $subscriptions = SubscriptionPlan::where('status', 'active')->get();
        return view('backend.administrator.subscription.form', compact(["subscriptions", "route",'breadcrumb']));
    }

    public function store(SubscriptionRequest $request)
        {
            try {
                $data = $request->validated();
                SubscriptionPlan::create([
                    'name'        => ucwords($data['name']),
                    'price'       => $data['price'],
                    'duration'    => $data['duration'],
                    'description' => $data['description'] ?? null,
                ]);
                return Settings::roleRedirect('subscription','Subscription Added Successfully.');
            } catch (\Exception $e) {
                 return Settings::roleRedirect('subscription','Something went wrong!','error');
            }
        }

    public function edit(Request $request, $id)
    {
        $breadcrumb = Settings::updateBreadcrumbRoute($this->breadcrumbAddNew,['route2'],['administrator.subscription.update']);
        $route = 'edit';        
        $id = Settings::getDecodeCode($id);  
        $subscription = SubscriptionPlan::where('id', $id)->first();
        return view('backend.administrator.subscription.form', compact(["subscription", "route",'breadcrumb']));
    }

   public function update(SubscriptionRequest $request)
    {
        try {
            $data = $request->validated();

            $subscription = SubscriptionPlan::findOrFail($request->subscription_id);

            $subscription->name        = ucwords($data['name']);
            $subscription->price       = $data['price'];
            $subscription->duration    = $data['duration'];
            $subscription->description = $data['description'] ?? null;
            $subscription->save();
            return Settings::roleRedirect('subscription','Subscription Updated Successfully.');
        } catch (\Exception $e) {
            return Settings::roleRedirect('subscription','Something went wrong!','error');
        }
    }

    public function statusUpdate(Request $request)
    {
        try {
            $id = $request->input('id');
            $status = $request->input('status');
            SubscriptionPlan::where("id", $id)->update(["status" => $status]);
            return response()->json(['message' => 'Record updated successfully!']);
            return redirect()->route('subscription')->with('success', 'Status Updated Successfully.');
            Session::flash('success', 'Status update successfully.');
        } catch (\Exception $e) {
            Session::flash('error', 'something went wrong!');
            return redirect()->back()->withInput();
        }
    }

    public function delete(Request $request)
    {
        try {
            $id = $request->input('id');
            SubscriptionPlan::where("id", $id)->update(["status" => 0]);
            SubscriptionPlan::where("id", $id)->delete();
            Session::flash('success', 'Subscription deleted successfully.');
        } catch (\Exception $e) {
            Session::flash('error', 'something went wrong!');
            return redirect()->back()->withInput();
        }
    }

    public function downloadsubscriptionpdf(Request $request)
    {
        $updatedAt = '';
        $subscriptionList = SubscriptionPlan::whereNot('status','suspended')->orderBy('id', 'desc')->get();
		$pdfHeaderdata = \Config::get('constants.downloadsubscriptionpdf');
        $pdf = PDF::loadView('backend.subscription.downloadpdf', compact("subscriptionList", "updatedAt",'pdfHeaderdata'))->setOptions(['defaultFont' => 'sans-serif']);
        return $pdf->stream('subscription-list.pdf');
    }
}
