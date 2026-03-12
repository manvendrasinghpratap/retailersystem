<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Image;


class UserController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }


    public function index() {
        $userList = User::select('*')
            ->where('is_active','1')
            ->where('is_deleted','0')
            ->orderBy('id','desc')
            ->paginate(5);
        return view('backend.users.index',compact("userList"));
    }

    public function statusUpdate(Request $request) {
        try {
            $id = $request->input('id');
            $status = $request->input('status');
            $Menu = User::where("id",$id)->update(["is_active"=>$status]);
            Session::flash('success', 'Data update successfully.');
        } catch (\Exception $e) {
            Session::flash('error', 'something went wrong!');
            return redirect()->back()->withInput();
        }
    }


        public function delete(Request $request)
    {
        try{
            $id = $request->input('id');
            $is_deleted = $request->input('is_deleted');
            $Menu = User::where("id",$id)->update(["is_deleted"=>$is_deleted]);
            Session::flash('success', 'Data update successfully.');
        } catch (\Exception $e) {
            Session::flash('error', 'something went wrong!');
            return redirect()->back()->withInput();
        }
    }



}
