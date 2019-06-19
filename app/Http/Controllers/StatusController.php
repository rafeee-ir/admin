<?php

namespace App\Http\Controllers;

use App\Notifications\messageSent;
use App\Notifications\RepliedToTask;
use App\Status;
use App\Task;
use App\TaskOrderUser;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use phpDocumentor\Reflection\Types\Null_;
use Verta;

class StatusController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        $myTasksStatus = $user->taskOrder()->get();
        $usersStatus = User::all();
        $statusesToMe = Status::with('user')->where('to_user',$user->id)->orderBy('created_at','DESC')->paginate(5);
        $dateBefore = Carbon::now();

        foreach ($statusesToMe as $key => $loop){
            $loop->jCreated_at = new Verta($loop->created_at);
            $loop->diff = verta($loop->created_at)->formatDifference();
            $loop->diffM = abs(Carbon::parse($loop->created_at)->diffInMinutes($dateBefore, false));


        }

        $statuses = Status::with('task','user')->whereNull('to_user')->orderBy('updated_at','DESC')->paginate(20);
        //$tasks = Task::all();
        return view('statuses.index', compact('statuses','myTasksStatus','usersStatus','statusesToMe'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'content'=>'required'
        ]);
        $status = new Status([
            'status'    => $request->get('status'),
            'content'   => $request->get('content'),
            'to_user'   => $request->get('to_user'),
            'task_id'   => $request->get('task_id'),
            'user_id'   => $request->get('user_id'),
            'post_id'   => $request->get('post_id'),
        ]);
        $status->save();

        $taskOrderUser = TaskOrderUser::where('user_id',$request->get('user_id'))->where('task_id',$request->get('task_id'))->first();
        if($request->get('status') == 'comment'){
            $task_id = $request->get('task_id');
            $task = Task::find($task_id);
            $task->increment('commentCount');
        }elseif($request->get('status') == 'end'){
            $taskOrderUser->isDone = 1;
            $taskOrderUser->lastStatus = 3;
            $taskOrderUser->save();
        }elseif($request->get('status') == 'end-back'){
            $taskOrderUser->isDone = 0;
            $taskOrderUser->lastStatus = 1;
            $taskOrderUser->save();
        }elseif($request->get('status') == 'start'){

            $lastStatus = TaskOrderUser::where('lastStatus','2')->where('user_id',$user->id)->first();

            if(!empty($lastStatus)){
                $lastStatusOther = TaskOrderUser::where('lastStatus','2')->where('user_id',$user->id)->get();
                foreach ($lastStatusOther as $o){
                    $o->lastStatus = 1;
                    $o->save();
                }
                $lastStatus->lastStatus = 1;
                $lastStatus->save();
                $taskOrderUser->lastStatus = 2;
                $taskOrderUser->save();
            }else{
                $lastStatusOther = TaskOrderUser::where('lastStatus','2')->where('user_id',$user->id)->get();
                foreach ($lastStatusOther as $o){
                    $o->lastStatus = 1;
                    $o->save();
                }
                $taskOrderUser->lastStatus = 2;
                $taskOrderUser->save();
            }

        }

        $inputStatus = $request->get('status');
        $user_id = $request->get('user_id');
        if($inputStatus == 'on' || $inputStatus == 'off' || $inputStatus == 'in' || $inputStatus == 'out' || $inputStatus == 'lunch-start' || $inputStatus == 'lunch-end'){
            $user = User::find($user_id);
            $user->lastStatus = $inputStatus;
            $user->save();
        }

//lastStatus 0 Not yet
//lastStatus 1 workerd not Done
//lastStatus 2 working
//lastStatus 3 Done
//        $user->notify(new RepliedToTask($status));
//        Auth::user()->notify(new messageSent($status));
        return back()->with('success', 'Done');


    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Status  $status
     * @return \Illuminate\Http\Response
     */
    public function show(Status $status)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Status  $status
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
//        Must Be in everywhere Start
        $user = Auth::user();
        $myTasksStatus = $user->taskOrder()->get();
        $usersStatus = User::all();
        $statusesToMe = Status::with('user')->where('to_user',$user->id)->orderBy('created_at','DESC')->paginate(5);
        $dateBefore = Carbon::now();
        foreach ($statusesToMe as $key => $loop){
            $loop->jCreated_at = new Verta($loop->created_at);
            $loop->diff = verta($loop->created_at)->formatDifference();
            $loop->diffM = abs(Carbon::parse($loop->created_at)->diffInMinutes($dateBefore, false));


        }
//        Must Be in everywhere End

        $user = Auth::user();
        $status = Status::find($id);
        $dateBefore = Carbon::now();
        $diffM = abs(Carbon::parse($status->created_at)->diffInMinutes($dateBefore, false));

        if($status->user_id == $user->id && $diffM <= 5){
            return view('statuses.edit', compact('status','user','myTasksStatus','usersStatus','statusesToMe'));
        }else{
            return redirect()->back();
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Status  $status
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {

        $user = Auth::user();
        $status = Status::find($id);

        if($request->input('user_id') == $user->id){
            $status->content = $request->input('content');
            $status->save();

        }







        return redirect()->back()->with('success');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Status  $status
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {
        $task = Status::find($id);
        $task->delete();

        $task = Task::find($request->get('task_id'));
        $task->decrement('commentCount');

        return redirect()->back()->with('success');
    }


//    public function homeStatusToMe(){
//
//       return $messages = Status::with('user','toUser')->where('to_user', 1)->orWhere('user_id', 1)->orderBy('created_at','DESC')->get();
////        foreach ($messages as $key => $loop) {
////            date_default_timezone_set("Asia/Tehran");
////            $loop->diff = verta($loop->created_at)->formatDifference();
////        }
////
////     return $messages;
//
//    }


    public function addStatusToBox(Request $request){

        $status = Status::create($request->all());
//        $status->notify(new messageSent($status));
        if (!empty($status->task_id)){
            $task_id = $request->get('task_id');
            $user_id = $request->get('user_id');

            if ($request->get('status') == 'comment'){
                $task = Task::find($task_id);
                $task->increment('commentCount');
            }
            $x = TaskOrderUser::where('user_id',$user_id)->where('task_id',$task_id)->first();
            $x->lastStatus = 2;
            $x->save();
            $orderTaskOther = TaskOrderUser::where('user_id',$user_id)->where('lastStatus',2)->where('task_id','!=',$task_id)->get();
            foreach ($orderTaskOther as $o){
                $o->lastStatus = 1;
                $o->save();
            }
        }




//            $user = Auth::user();
//
//        $orderTask = TaskOrderUser::where('user_id',$user)->where('task_id',$task_id)->first();
//        $orderTask->lastStatus = 2;
//        $orderTask->save();
//

        return $status;
    }
    public function statusListBox(){
        if (isset($_GET['ID'])) {
            $status = Status::where('status', 'box')->where('user_id', $_GET['ID'])->latest()->get();
        }else{
            $status = Status::where('status', 'box')->where('user_id', 0)->latest()->get();
        }
        return $status;
    }
    public function commentList(){

        $data = Status::with('user','toUser')->where('to_user',$_GET['ID'])->orWhere('user_id',$_GET['ID'])->whereNotNull('to_user')->orderBy('created_at','DESC')->get();
        foreach ($data as $key => $loop) {
            date_default_timezone_set("Asia/Tehran");
            $loop->diff = verta($loop->created_at)->formatDifference();
        }

        return $data;
    }
//    public function statusUpdate(Request $request, Status $status, $id){
//        $status->status = 'boxed';
//        $status->save();
//    }
    public function statusUpdate($id, Request $request)
    {
        $post = Status::find($id);

        $post->update($request->all());

        return response()->json('successfully updated');
    }

    public function userStatusCommentsCount(){
        $data = Status::where('status', 'comment')->where('user_id', $_GET['ID'])->get()->count();
        return $data;

    }
    public function userStatusCommentsToUserCount(){
        $data = Status::where('status', 'status')->where('user_id', $_GET['ID'])->get()->count();
        return $data;

    }
    public function userTasksCount(){
        $data= Task::where('user_id', $_GET['ID'])->get()->count();
        return $data;

    }
    public function userTasksSelf(){
        $data= TaskOrderUser::where('user_id', $_GET['ID'])->get()->count();
        return $data;

    }
    public function userPostVerified(){
        $data= Status::where('user_id', $_GET['ID'])->where('status','verifyPost')->get()->count();
        return $data;

    }
    public function userOffCount(){
        $data= Status::where('user_id', $_GET['ID'])->where('status','off')->get()->count();
        return $data;

    }
    public function userBoxCount(){
        $data= Status::where('user_id', $_GET['ID'])->where('status','box')->get()->count();
        return $data;

    }
    public function userLunchCount(){
        $data= Status::where('user_id', $_GET['ID'])->where('status','lunch-start')->get()->count();
        return $data;
    }
    public function userDaysCount(){
        $days= Status::where('user_id', $_GET['ID'])->where('status','in')->get()->count();
        return $days;

    }
    public function userEndCount(){
        $data= Status::where('user_id', $_GET['ID'])->where('status','end')->get()->count();
        return $data;
    }
    public function allStatics(){
        $userEndCount= Status::where('user_id', $_GET['ID'])->where('status','end')->get()->count();
        $userDaysCount= Status::where('user_id', $_GET['ID'])->where('status','in')->get()->count();
        $userLunchCount= Status::where('user_id', $_GET['ID'])->where('status','lunch-start')->get()->count();
        $userBoxCount= Status::where('user_id', $_GET['ID'])->where('status','box')->get()->count();
        $userOffCount= Status::where('user_id', $_GET['ID'])->where('status','off')->get()->count();
        $userPostVerified= Status::where('user_id', $_GET['ID'])->where('status','verifyPost')->get()->count();
        $userTasksSelf= TaskOrderUser::where('user_id', $_GET['ID'])->get()->count();
        $userTasksCount= Task::where('user_id', $_GET['ID'])->get()->count();
        $userStatusCommentsToUserCount = Status::where('status', 'status')->where('user_id', $_GET['ID'])->get()->count();
        $userStatusCommentsCount = Status::where('status', 'comment')->where('user_id', $_GET['ID'])->get()->count();
        return response()->json([
            'userEndCount' => $userEndCount,
            'userDaysCount' => $userDaysCount,
            'userLunchCount' => $userLunchCount,
            'userBoxCount' => $userBoxCount,
            'userOffCount' => $userOffCount,
            'userPostVerified' => $userPostVerified,
            'userTasksSelf' => $userTasksSelf,
            'userTasksCount' => $userTasksCount,
            'userStatusCommentsToUserCount' => $userStatusCommentsToUserCount,
            'userStatusCommentsCount' => $userStatusCommentsCount
        ]);
    }

    public function allStaticsBoxes(){
        $data= Status::where('user_id', $_GET['ID'])->where('status','box')->get();
        return $data;
    }

//statics

public function statics(){
    $user = Auth::user();
    $myTasksStatus = $user->taskOrder()->get();
    $usersStatus = User::all();
    $statusesToMe = Status::with('user')->where('to_user',$user->id)->orderBy('created_at','DESC')->paginate(5);
    $dateBefore = Carbon::now();

    foreach ($statusesToMe as $key => $loop){
        $loop->jCreated_at = new Verta($loop->created_at);
        $loop->diff = verta($loop->created_at)->formatDifference();
        $loop->diffM = abs(Carbon::parse($loop->created_at)->diffInMinutes($dateBefore, false));


    }
    $users = User::all();
    return view('users.statics', compact('users','myTasksStatus','usersStatus','statusesToMe'));

}
    public function searchTasks(){
        $s = $_GET['s'];

        if(!empty($s) && strlen($s) > 5){
            $searchValues = preg_split('/\s+/', $s, -1, PREG_SPLIT_NO_EMPTY);
            $tasks = Task::where(function ($q) use ($searchValues) {
                foreach ($searchValues as $value) {
                    $q->where('title', 'like', "%{$value}%");
                }
            })->get();
//            $tasks = Task::where('title', 'like', '%' . $s . '%')->get();
            return $tasks;
        }

    }
    public function fetchTasks(){
            $u = $_GET['u'];
            $el = $_GET['el'];
            $op = $_GET['op'];
            $val = $_GET['val'];
            $ord = $_GET['ord'];
            $ordOp = $_GET['ordOp'];

//        if ($lastStatusOp == 'le'){
//            $lastStatusOp = '<=';
//        }elseif($lastStatusOp == 'be'){
//            $lastStatusOp = '>=';
//        }
        if ($val == 2){
            $tasks = TaskOrderUser::with('task','user')->whereHas('user')->whereHas('task')->where('user_id', $u)->where('lastStatus', 1)->where('routine', 0)->orWhere('lastStatus', 2)->where('user_id', $u)->where('routine', 0)->orderBy('lastStatus','desc')->orderBy($ord , $ordOp)->get();
        }else{
            $tasks = TaskOrderUser::with('task','user')->whereHas('user')->whereHas('task')->where('user_id', $u)->where($el, $op, $val)->orderBy($ord , $ordOp)->get();
        }
        foreach ($tasks as $key => $loop){
            $users = TaskOrderUser::with('user')->whereHas('user')->where('task_id',$loop->task_id)->pluck('user_id')->toArray();
            $users = User::whereIn('id',$users)->get();
            $loop->users = $users;
        }
            return $tasks;

    }
    public function commentFetch(){

        $comments = Status::where( 'created_at', '>', Carbon::now()->subDays(15))->with('user')->whereHas('user')->where('status','comment')->orderBy('updated_at','desc')->get();
        $dateBefore = Carbon::now();

        foreach ($comments as $key => $loop){
            $loop->jCreated_at = new Verta($loop->created_at);
            $loop->diff = verta($loop->created_at)->formatDifference();
            $loop->diffM = abs(Carbon::parse($loop->created_at)->diffInMinutes($dateBefore, false));
        }
        return $comments;
    }
}
