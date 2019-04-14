<?php


namespace App\Http\Controllers;


use App\Task;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use DB;
use Hash;


class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('permission:user-list');
        $this->middleware('permission:user-create', ['only' => ['create','store']]);
        $this->middleware('permission:user-edit', ['only' => ['edit','update']]);
        $this->middleware('permission:user-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
$tasks = Task::all();
        $data = User::orderBy('created_at','ASC')->paginate(25);
        return view('users.index',compact('data','tasks'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::pluck('name','name')->all();
        return view('users.create',compact('roles'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles' => 'required'
        ]);


        $input = $request->all();
        $input['password'] = Hash::make($input['password']);


        $user = User::create($input);
        $user->assignRole($request->input('roles'));


        return redirect()->route('users.index')
            ->with('success','User created successfully');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::find($id);
        return view('users.show',compact('user'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name','name')->all();


        return view('users.edit',compact('user','roles','userRole'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
//            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'same:confirm-password',
//            'roles' => 'required'
        ]);


        $input = $request->all();
        if(!empty($input['password'])){
            $input['password'] = Hash::make($input['password']);
        }else{
            $input = array_except($input,array('password'));
        }


        $user = User::find($id);
        $user->update($input);
        DB::table('model_has_roles')->where('model_id',$id)->delete();


        $user->assignRole($request->input('roles'));


        return redirect()->route('users.index')
            ->with('success','User updated successfully');
    }
public function profileUpdate(Request $request, $id){
    $this->validate($request, [
        'name' => 'required',
//            'email' => 'required|email|unique:users,email,'.$id,
        'password' => 'same:confirm-password',
//            'roles' => 'required'
    ]);


    $input = $request->all();
    if(!empty($input['password'])){
        $input['password'] = Hash::make($input['password']);
    }else{
        $input = array_except($input,array('password'));
    }


    $user = User::find($id);
    $user->update($input);

    return redirect(back())->with('success', 'User has been updated');
}

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        User::find($id)->delete();
        return redirect()->route('users.index')
            ->with('success','User deleted successfully');
    }

    public function profile()
    {


        $user = Auth::user();
        $tasks = $user->tasks()->paginate(5);
        $myTasks = Task::where('user_id', $user);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name','name')->all();
        return view('users.profile',compact('user','roles','userRole','tasks','myTasks'));

    }
    public function update_avatar(Request $request){















        $request->validate([
            'avatar' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'name' => 'required',
            'password' => 'same:confirm-password'
        ]);



        $user = Auth::user();
        $user->name = $request->get('name');
        $user->experience = $request->get('experience');
        if(!empty($request->avatar)) {
            $avatarName = $user->id . '_avatar' . time() . '.' . request()->avatar->getClientOriginalExtension();
            $request->avatar->storeAs('avatars', $avatarName);
            $user->avatar = $avatarName;
        }
        if(!empty($request->password)){
            $request->password = Hash::make($request->password);
            $user->password = $request->password;
        }


        $user->save();




//        $input = $request->all();
//        if(!empty($input['password'])){
//            $input['password'] = Hash::make($input['password']);
//        }else{
//            $input = array_except($input,array('password'));
//        }



//        $user = User::find($id);
//        $user->update($input);



        //$request->file('avatar')->move(public_path("/uploads/avatars"), $avatarName);

//        $user->save();

//        $user->update($input);




        return redirect()->back()->with('success', 'User has been updated');

    }


}
