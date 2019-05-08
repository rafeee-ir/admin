<?phpnamespace App\Http\Controllers;use App\Category;use App\Media;use App\TaskMeter;use App\TaskOrderUser;use App\TaskUser;use App\User;use App\Comment;use App\Task;use App\Brand;//use http\Client\Curl\User as User;use DateTime;use Verta;use Illuminate\Http\Request;use Illuminate\Support\Carbon;use Illuminate\Support\Facades\Auth;class TaskController extends Controller{    public function __construct()    {        $this->middleware('auth');        $this->middleware('permission:task-list');        $this->middleware('permission:task-create', ['only' => ['create','store']]);        $this->middleware('permission:task-edit', ['only' => ['edit','update']]);        $this->middleware('permission:task-delete', ['only' => ['destroy']]);    }    /**     * Display a listing of the resource.     *     * @return \Illuminate\Http\Response     */    public function index()    {        $user = Auth::user();        $usersInTasks = TaskUser::all();        $users = User::all();        $taskMeter = TaskMeter::where('user_id', $user->id)->orderBy('created_at','DESC')->first();        if(isset($_GET['sort']) && $_GET['sort'] == 'pending') {            $tasks = $user->tasks()->where('pending', '1')->where('isDone', '0')->orderBy('updated_at', 'DESC')->paginate(20);        }elseif(isset($_GET['sort']) && $_GET['sort'] == 'done'){            $tasks = $user->tasks()->where('isDone', '1')->orderBy('pending','ASC')->orderBy('updated_at','DESC')->paginate(20);        }elseif(isset($_GET['sort']) && $_GET['sort'] == 'latest'){            $tasks = $user->tasks()->where('isDone', '0')->orderBy('pending','ASC')->orderBy('updated_at','DESC')->paginate(20);        }else{            $tasks = $user->tasks()->where('isDone', '0')->orderBy('pending','ASC')->orderBy('orderTask','ASC')->orderBy('updated_at','DESC')->orderBy('deadline','ASC')->paginate(10);           // $tasks = $user->taskOrder()->where('isDone', '0')->where('pending','0')->orderBy('updated_at','DESC')->paginate(20);        }//        $tasks = Task::orderBy('deadline','ASC')->paginate(9);//        $team = User::with('tasks');        $i = 1; $skipped = ($tasks->currentPage() - 1) * $tasks->perPage();        foreach ($tasks as $key => $loop) {            $v = new Verta($loop->startDate);            $loop->jStart = $v->year . "/" . $v->month . "/" . $v->day;            $v = new Verta($loop->deadline);            $loop->jEnd = $v->year . "/" . $v->month . "/" . $v->day;            $loop->i = $skipped + $i++;        }        foreach ($tasks as $key => $loop)        {            date_default_timezone_set("UTC");            $loop->pastOr = Carbon::now()->diffInSeconds($loop->startDate, false);            date_default_timezone_set("Asia/Tehran");//            $now = new Carbon();//            $dt = new Carbon($this->created_at);//            $dt->setLocale('es');//            return $dt->diffForHumans($now);            $loop->nowt = "The time is " . date("h:i:sa");            $loop->rightNow = Carbon::now()->diffInMinutes($loop->deadline, false);//            $diffDead = Carbon::now()->diffForHumans($loop->deadline, false);            $loop->diffDead = verta($loop->deadline)->formatDifference();            $loop->passNow = abs(Carbon::now()->diffInDays($loop->startDate, false));            $loop->passNowHours = abs(Carbon::now()->diffInMinutes($loop->startDate, false));            $loop->diffDate = abs(Carbon::parse($loop->startDate)->diffInMinutes($loop->deadline, false)) + 1;            $loop->diffdiff = (($loop->passNowHours) * 100) / ($loop->diffDate);            $loop->prog = floor($loop->diffdiff);        }        $titleOfPage = 'کارهای من';        return view('tasks.index', compact('tasks','user','taskMeter','usersInTasks','users','titleOfPage'));    }    public function allTasks()    {        $user = Auth::user();        $tasks = Task::where('isDone', '0')->where('pending', '0')->orderBy('orderTask','ASC')->orderBy('deadline','ASC')->paginate(20);        $i = 1; $skipped = ($tasks->currentPage() - 1) * $tasks->perPage();        foreach ($tasks as $key => $loop) {            $v = new Verta($loop->startDate);            $loop->jStart = $v->year . "/" . $v->month . "/" . $v->day;            $v = new Verta($loop->deadline);            $loop->jEnd = $v->year . "/" . $v->month . "/" . $v->day;            $loop->i = $skipped + $i++;        }        foreach ($tasks as $key => $loop)        {            date_default_timezone_set("UTC");            $loop->pastOr = Carbon::now()->diffInSeconds($loop->startDate, false);            date_default_timezone_set("Asia/Tehran");            $loop->rightNow = Carbon::now()->diffInDays($loop->deadline, false);            $loop->passNow = abs(Carbon::now()->diffInDays($loop->startDate, false));            $loop->passNowHours = abs(Carbon::now()->diffInMinutes($loop->startDate, false));            $loop->diffDate = abs(Carbon::parse($loop->startDate)->diffInMinutes($loop->deadline, false)) + 1;            $diffdiff = (($loop->passNowHours) * 100) / ($loop->diffDate);            $loop->prog = floor($diffdiff);        }        return view('tasks.index', compact('tasks','user'));    }    public function taskMeters()    {        $taskMeters = TaskMeter::orderBy('created_at','DESC')->get();        $users = User::all();        $tasks = Task::all();       return view('tasks.taskMeters', compact('taskMeters','users','tasks'));    }    public function isDone()    {        $user = Auth::user();        $tasks = $user->tasks()->where('isDone', '1')->orderBy('orderTask','ASC')->orderBy('deadline','ASC')->paginate(20);//        $tasks = Task::orderBy('deadline','ASC')->paginate(9);//        $team = User::with('tasks');        $i = 1; $skipped = ($tasks->currentPage() - 1) * $tasks->perPage();        foreach ($tasks as $key => $loop) {            $v = new Verta($loop->startDate);            $loop->jStart = $v->year . "/" . $v->month . "/" . $v->day;            $v = new Verta($loop->deadline);            $loop->jEnd = $v->year . "/" . $v->month . "/" . $v->day;            $loop->i = $skipped + $i++;        }        foreach ($tasks as $key => $loop)        {            date_default_timezone_set("UTC");            $loop->pastOr = Carbon::now()->diffInSeconds($loop->startDate, false);            date_default_timezone_set("Asia/Tehran");            $loop->rightNow = Carbon::now()->diffInDays($loop->deadline, false);            $loop->passNow = abs(Carbon::now()->diffInDays($loop->startDate, false));            $loop->passNowHours = abs(Carbon::now()->diffInHours($loop->startDate, false));            $loop->diffDate = abs(Carbon::parse($loop->startDate)->diffInHours($loop->deadline, false));            $diffdiff = (($loop->passNowHours) * 100) / ($loop->diffDate);            $loop->prog = floor($diffdiff);        }        return view('tasks.index', compact('tasks','user'));    }    public function userIsDone($id)    {        $user = User::find($id);        $tasks = $user->tasks()->where('isDone', '1')->orderBy('orderTask','ASC')->orderBy('deadline','ASC')->paginate(20);//        $tasks = Task::orderBy('deadline','ASC')->paginate(9);//        $team = User::with('tasks');//        $i = 1; $skipped = ($tasks->currentPage() - 1) * $tasks->perPage();        foreach ($tasks as $key => $loop) {            $v = new Verta($loop->startDate);            $loop->jStart = $v->year . "/" . $v->month . "/" . $v->day;            $v = new Verta($loop->deadline);            $loop->jEnd = $v->year . "/" . $v->month . "/" . $v->day;            $loop->i = $skipped + $i++;        }        foreach ($tasks as $key => $loop)        {            date_default_timezone_set("UTC");            $loop->pastOr = Carbon::now()->diffInSeconds($loop->startDate, false);            date_default_timezone_set("Asia/Tehran");            $loop->rightNow = Carbon::now()->diffInDays($loop->deadline, false);            $loop->passNow = abs(Carbon::now()->diffInDays($loop->startDate, false));            $loop->passNowHours = abs(Carbon::now()->diffInHours($loop->startDate, false));            $loop->diffDate = abs(Carbon::parse($loop->startDate)->diffInHours($loop->deadline, false));            $diffdiff = (($loop->passNowHours) * 100) / ($loop->diffDate);            $loop->prog = floor($diffdiff);        }        return view('tasks.index', compact('tasks','user'));    }    public function create()    {        $user = Auth::user();        $categories = Category::where('parent_id', '=' , '0')->get();        $materials = Category::where('isMaterial', '=' , '1')->get();        $dimensions = Category::where('isDimension', '=' , '1')->get();        $types = Category::where('isType', '=' , '1')->orderby('title','asc')->get();        $childCategories = Category::where('parent_id', '!=' , '0')->get();        $users = User::all();        $brands = Brand::all();        $user_id = Auth::user()->id;        $nowDate = new Verta();        $jNow = $nowDate->year . "/" . $nowDate->month . "/" . $nowDate->day;        $urlP = url()->previous();        $titleOfPage = 'کار جدید';        return view('tasks.create', compact('categories','users', 'childCategories','brands','materials','user_id','dimensions','types','jNow','urlP', 'titleOfPage','user'));    }    public function store(Request $request)    {        $input=$request->all();        $images=array();        $request->validate([            'pic' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',            'title'=>'required',            'startTime'=>'required',            'endTime'=>'required',            'content'=> 'required'        ]);        $taskTitle = "";        if($request->get('title') == "ندارد"){            $taskTitle = $request->get('isType') ." ". $request->get('forProduct') ." ". $request->get('brand');        }else{            $taskTitle = $request->get('title');        }        $date = new DateTime($request->get('endDate'));        $time = new DateTime($request->get('endTime'));        $mergeEnd = new DateTime($date->format('Y-m-d') .' ' .$time->format('H:i:s'));        $date = new DateTime($request->get('startDate'));        $time = new DateTime($request->get('startTime'));        $mergeStart = new DateTime($date->format('Y-m-d') .' ' .$time->format('H:i:s'));        $t = $request->input('isType2');        if(isset($t) && $t != ""){            $t = $request->input('isType2');        }else{            $t = $request->input('isType');        }        $task = new Task([            'title' => $taskTitle,            'content'=> $request->get('content'),            'deadline'=> $mergeEnd->format('Y-m-d H:i:s'),//$request->get('endDate'),            'startDate'=> $mergeStart->format('Y-m-d H:i:s'),//$request->get('startDate'),            'reTask'=> $request->get('reTask'),            'orderTask'=> $request->get('orderTask'),            'weight'=> $request->get('weight'),            'brand'=> $request->get('brand'),            'material'=> $request->get('material'),            'dx'=> $request->get('dx'),            'dy'=> $request->get('dy'),            'dz'=> $request->get('dz'),            'dDesc'=> $request->get('dDesc'),            'pending'=> $request->get('pending'),            'type'=> $t,            'forProduct'=> $request->get('forProduct'),            'user_id'=> Auth::user()->id        ]);        if(!empty($request->pic)) {            $picName = $task->id . '_avatar' . time() . '.' . request()->pic->getClientOriginalExtension();            $request->pic->storeAs('uploads', $picName);            $task->pic = $picName;        }        $task->save();        $task->categories()->attach($request->categories);        $task->categories()->attach($request->categorieschild);        if($request->get('pending') != 2){            $task->users()->attach($request->users);        }        $task->userOrder()->attach($request->users);//        if($files=$request->file('medias')){//            foreach($files as $file){//                $name=$file->getClientOriginalName();//                $file->move('imagex',$name);//                $images[]=$name;//            }//        }//        /*Insert your data*///        Media::insert( [//            'name'=>  implode("|",$images),//            'user_id' => Auth::user()->id,//            'task_id' => $task->id,//            //you can put other insertion here//        ]);//        $users = $request->input('users');//        $users = implode(',', $users);//        $input['users'] = $users;//        $task->users()->attach($input);////        $categories = $request->input('categories');//        $categories = implode(',', $categories);//        //$input = $request->except('categories');//        $input['categories'] = $categories;//        $task->categories()->attach($input);        //$task->categories()->attach($request->categories, false);        //        $user->roles()->attach([        //            1 => ['expires' => $expires],        //            2 => ['expires' => $expires]        //        ]);        $urlP = $request->get('urlP');        return redirect($urlP)->with('success', 'Task has been added');    }    public function show($id)    {        //$feeds = Feed::with('comments', 'user')->where('user_id', Sentinel::getUser()->id)->latest()->get();        //$commentd = Comment::latest();        //return view('action.index', compact('feeds', 'blogs'));//        $order = TaskUser::where('task_id', $id)->orderBy('order','DESC')->get();//        $orderUsers = 1;//$order->order()->where('task_id', $id)->orderBy('order','DESC')->get();        $user = Auth::user();        //$comments = Task::find($id)->comments;        //$user = User::with('user_id')->where('user_id', '=', $comments->user_id)->get();        //$comments = Comment::all()->where('task_id',$id);        //$user=User::all()->get();        $task = Task::find($id);        $comments = $task->comments()->with('user')->orderBy('created_at','DESC')->get();        $dateBefore = Carbon::now();        foreach ($comments as $key => $loop){            $loop->jCreated_at = new Verta($loop->created_at);            $loop->diff = verta($loop->created_at)->formatDifference();            $loop->diffM = abs(Carbon::parse($loop->created_at)->diffInMinutes($dateBefore, false));        }        //$comments = $task->comments;        $dead = Carbon::now()->diffInDays($task->deadline, false);        $task->increment('viewCount');        $task->jStartDate = new Verta($task->startDate);        $task->jDeadline = new Verta($task->deadline);        $taskMeter = TaskMeter::where('task_id', $id)->orderBy('created_at','DESC')->first();        $taskMeters = TaskMeter::where('task_id', $id)->orderBy('id','ASC')->get();        $dateBefore = Carbon::now();        foreach ($taskMeters as $key => $loop)        {            $loop->diffH = abs(Carbon::parse($loop->created_at)->diffInHours($dateBefore, false));            $loop->diffM = abs(Carbon::parse($loop->created_at)->diffInMinutes($dateBefore, false));            $loop->diffS = abs(Carbon::parse($loop->created_at)->diffInSeconds($dateBefore, false));            $v = new Verta($loop->created_at);            $loop->jDate = $v->year . "." . $v->month . "." . $v->day;            $dateBefore = $loop->created_at;        }        $admin = User::find($task->user_id);        $users = $task->users()->where('task_id',$id)->get()->pluck('id')->toArray();        $urlP = url()->previous();        $titleOfPage = $task->title;        if(in_array($user->id, $users)){            $users = $task->users()->where('task_id',$id)->get();            return view('tasks.show', compact('task','comments', 'dead','user','taskMeter','taskMeters','users','admin','urlP','titleOfPage'));        }elseif ($user->id == 1 || $user->id == 2 || $user->id == 14 || $task->id == 28){            $users = $task->users()->where('task_id',$id)->get();            return view('tasks.show', compact('task','comments', 'dead','user','taskMeter','taskMeters','users','admin','urlP','titleOfPage'));        }else{            return redirect()->back();        }    }    public function edit($id)    {        $task = Task::find($id);        $brands = Brand::all();        $users_old = $task->users()->where('task_id',$id)->get();        $users = User::all();        $nowDate = new Verta($task->startDate);        $jStart = $nowDate->year . "/" . $nowDate->month . "/" . $nowDate->day;        $nowDate = new Verta($task->deadline);        $jEnd = $nowDate->year . "/" . $nowDate->month . "/" . $nowDate->day;        $urlP = url()->previous();        $titleOfPage = 'ویرایش ' . $task->title;        return view('tasks.edit', compact('task', 'brands','users','users_old','jEnd','jStart','urlP','titleOfPage'));    }    public function update(Request $request, $id)    {        $request->validate([            'title'=>'required',            'content'=> 'required'        ]);        $date = new DateTime($request->get('endDate'));        $time = new DateTime($request->get('endTime'));        $mergeEnd = new DateTime($date->format('Y-m-d') .' ' .$time->format('H:i:s'));        $date = new DateTime($request->get('startDate'));        $time = new DateTime($request->get('startTime'));        $mergeStart = new DateTime($date->format('Y-m-d') .' ' .$time->format('H:i:s'));        $task = Task::find($id);        $task->deadline = $mergeEnd;        $task->startDate = $mergeStart;        $task->reTask = $request->get('reTask');        $task->orderTask = $request->get('orderTask');        $task->weight = $request->get('weight');        $task->brand = $request->get('brand');        $task->material = $request->get('material');        $task->dx = $request->get('dx');        $task->dy = $request->get('dy');        $task->dz = $request->get('dz');        $task->dDesc = $request->get('dDesc');        $task->pending = $request->get('pending');        $task->type = $request->get('isType');        $task->forProduct = $request->get('forProduct');        $task->title = $request->get('title');        $task->content = $request->get('content');        if(!empty($request->pic)) {            $picName = $task->id . '_avatar' . time() . '.' . request()->pic->getClientOriginalExtension();            $request->pic->storeAs('uploads', $picName);            $task->pic = $picName;        }        $task->save();        $isUser = $request->input('isUser');        if (isset($isUser) && $isUser == 1){        }else{                $task->users()->sync($request->get('users'));                    }        $urlP = $request->get('urlP');        return redirect($urlP)->with('success', 'Task has been updated');    }    public function done(Request $request)    {        $task = Task::find($request->id);        $task->isDone = $request->isDone;        $task->done_user_id = $request->done_user_id;        $task->done_date = Carbon::now();        $task->save();        return redirect()->back();    }    /**     * Remove the specified resource from storage.     *     * @param  \App\Task  $task     * @return \Illuminate\Http\Response     */    public function pending()    {        $user = Auth::user();        $users = User::all();        $comments = Comment::orderBy('created_at' , 'DESC');        $taskMeters = TaskMeter::orderBy('created_at' , 'DESC');        if(isset($_GET['nouser'])){            $tasks = Task::where('pending', '2')->orderBy('updated_at','DESC')->paginate(20);        }else{            $tasks = Task::where('pending', '1')->orderBy('updated_at','DESC')->paginate(20);        }        $usersInTasks = TaskUser::all();        $i = 1; $skipped = ($tasks->currentPage() - 1) * $tasks->perPage();        foreach ($tasks as $key => $loop) {            $v = new Verta($loop->startDate);            $loop->jStart = $v->year . "/" . $v->month . "/" . $v->day;            $v = new Verta($loop->deadline);            $loop->jEnd = $v->year . "/" . $v->month . "/" . $v->day;            $loop->i = $skipped + $i++;        }        foreach ($tasks as $key => $loop)        {            date_default_timezone_set("UTC");            $loop->pastOr = Carbon::now()->diffInSeconds($loop->startDate, false);            date_default_timezone_set("Asia/Tehran");            $loop->nowt = "The time is " . date("h:i:sa");            $loop->rightNow = Carbon::now()->diffInMinutes($loop->deadline, false);            $loop->diffDead = verta($loop->deadline)->formatDifference();            $loop->passNow = abs(Carbon::now()->diffInDays($loop->startDate, false));            $loop->passNowHours = abs(Carbon::now()->diffInMinutes($loop->startDate, false));            $loop->diffDate = abs(Carbon::parse($loop->startDate)->diffInMinutes($loop->deadline, false)) + 1;            $diffdiff = (($loop->passNowHours) * 100) / ($loop->diffDate);            $loop->prog = floor($diffdiff);        }$linked = 'pending';        $titleOfPage = 'کارهای در انتظار';    return view('tasks.pending', compact('tasks','users','user','taskMeters','comments','usersInTasks','linked','titleOfPage'));    }    public function pendingUser($id)    {        $user = User::find($id);        $users = User::all();        $comments = Comment::orderBy('created_at' , 'DESC');        $taskMeters = TaskMeter::orderBy('created_at' , 'DESC');        $tasks = $user->tasks()->where('pending', '1')->orderBy('updated_at','DESC')->paginate(20);        $usersInTasks = TaskUser::all();        $i = 1; $skipped = ($tasks->currentPage() - 1) * $tasks->perPage();        foreach ($tasks as $key => $loop) {            $v = new Verta($loop->startDate);            $loop->jStart = $v->year . "/" . $v->month . "/" . $v->day;            $v = new Verta($loop->deadline);            $loop->jEnd = $v->year . "/" . $v->month . "/" . $v->day;            $loop->i = $skipped + $i++;        }        foreach ($tasks as $key => $loop)        {            date_default_timezone_set("UTC");            $loop->pastOr = Carbon::now()->diffInSeconds($loop->startDate, false);            date_default_timezone_set("Asia/Tehran");            $loop->nowt = "The time is " . date("h:i:sa");            $loop->rightNow = Carbon::now()->diffInMinutes($loop->deadline, false);            $loop->diffDead = verta($loop->deadline)->formatDifference();            $loop->passNow = abs(Carbon::now()->diffInDays($loop->startDate, false));            $loop->passNowHours = abs(Carbon::now()->diffInMinutes($loop->startDate, false));            $loop->diffDate = abs(Carbon::parse($loop->startDate)->diffInMinutes($loop->deadline, false)) + 1;            $diffdiff = (($loop->passNowHours) * 100) / ($loop->diffDate);            $loop->prog = floor($diffdiff);        }$linked = 'pending';$titleOfPage = 'کارهای در انتظار'. " " .$user->name;        return view('tasks.pending', compact('tasks','users','user','taskMeters','comments','usersInTasks','linked','titleOfPage'));    }    public function modir()    {        $user = Auth::user();        $users = User::all();        $comments = Comment::orderBy('created_at' , 'DESC');        $taskMeters = TaskMeter::orderBy('created_at' , 'DESC');        $tasks = Task::where('isDone', '0')->orderBy('pending','ASC')->orderBy('orderTask','ASC')->orderBy('updated_at','DESC')->orderBy('deadline','ASC')->paginate(20);        $usersInTasks = TaskUser::all();        $i = 1; $skipped = ($tasks->currentPage() - 1) * $tasks->perPage();        foreach ($tasks as $key => $loop) {            $v = new Verta($loop->startDate);            $loop->jStart = $v->year . "/" . $v->month . "/" . $v->day;            $v = new Verta($loop->deadline);            $loop->jEnd = $v->year . "/" . $v->month . "/" . $v->day;            $loop->i = $skipped + $i++;        }        foreach ($tasks as $key => $loop)        {            date_default_timezone_set("UTC");            $loop->pastOr = Carbon::now()->diffInSeconds($loop->startDate, false);            date_default_timezone_set("Asia/Tehran");            $loop->nowt = "The time is " . date("h:i:sa");            $loop->rightNow = Carbon::now()->diffInMinutes($loop->deadline, false);            $loop->diffDead = verta($loop->deadline)->formatDifference();            $loop->passNow = abs(Carbon::now()->diffInDays($loop->startDate, false));            $loop->passNowHours = abs(Carbon::now()->diffInMinutes($loop->startDate, false));            $loop->diffDate = abs(Carbon::parse($loop->startDate)->diffInMinutes($loop->deadline, false)) + 1;            $diffdiff = (($loop->passNowHours) * 100) / ($loop->diffDate);            $loop->prog = floor($diffdiff);        }        $linked = 'jobs';        $titleOfPage = 'مشاهده کارها';        return view('tasks.modir', compact('tasks','users','user','taskMeters','comments','usersInTasks','linked','titleOfPage'));    }    /**     * @param $id     */    public function modirUser($id)    {        $usersInTasks = TaskUser::all();        $user = User::find($id);        $comments = Comment::orderBy('created_at' , 'DESC');        $taskMeters = TaskMeter::where('user_id',$id)->orderBy('created_at' , 'DESC');        $users = User::all();        $tasks = $user->taskOrder()->where('isDone', '0')->where('pending','0')->orderBy('updated_at','DESC')->paginate(200);        $order = TaskOrderUser::where('user_id',$id)->orderBy('order_column','asc')->get();        foreach($order as $k => $v) {            $a[] = $v['task_id'];        }        $tasks = Task::whereIn('id',$a)->where('isDone', '0')->where('pending','0')->orderBy('updated_at','DESC')->paginate(200);        $tasks = Task::all();//        dd($tasks);       // $tasks = $user->tasks()->where('isDone', '0')->orderBy('pending','ASC')->orderBy('orderTask','ASC')->orderBy('updated_at','DESC')->orderBy('deadline','ASC')->paginate(20);        //$i = 1; $skipped = ($tasks->currentPage() - 1) * $tasks->perPage();        foreach ($tasks as $key => $loop) {            $v = new Verta($loop->startDate);            $loop->jStart = $v->year . "/" . $v->month . "/" . $v->day;            $v = new Verta($loop->deadline);            $loop->jEnd = $v->year . "/" . $v->month . "/" . $v->day;            //$loop->i = $skipped + $i++;        }        foreach ($tasks as $key => $loop)        {            date_default_timezone_set("UTC");            $loop->pastOr = Carbon::now()->diffInSeconds($loop->startDate, false);            date_default_timezone_set("Asia/Tehran");            $loop->nowt = "The time is " . date("h:i:sa");            $loop->rightNow = Carbon::now()->diffInMinutes($loop->deadline, false);            $loop->diffDead = verta($loop->deadline)->formatDifference();            $loop->passNow = abs(Carbon::now()->diffInDays($loop->startDate, false));            $loop->passNowHours = abs(Carbon::now()->diffInMinutes($loop->startDate, false));            $loop->diffDate = abs(Carbon::parse($loop->startDate)->diffInMinutes($loop->deadline, false)) + 1;            $diffdiff = (($loop->passNowHours) * 100) / ($loop->diffDate);            $loop->prog = floor($diffdiff);        }        $title = $user->name;        $titleOfPage = 'کارهای در انتظار'. ' ' . $user->name;        $linked = 'jobs';        return view('tasks.modir', compact('tasks','users','user','taskMeters','comments','title','usersInTasks','linked','titleOfPage','linked','order'));    }    public function destroy(Request $request,$id)    {        $task = Task::find($id);        $task->delete();        $urlP = $request->get('urlP');        return redirect($urlP)->with('success', 'Task has been deleted Successfully');    }}