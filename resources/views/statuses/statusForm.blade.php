
<form  method="post" action="{{ route('status.store') }}">
    @csrf
    <div class="row form-group advanced w-100">
           {{--<div class="col-md-6">--}}
            {{--<select name="task_id" class="form-control form-control-sm selectTaskStatus" disabled>--}}
                {{--<option selected="selected" value="">به کار</option>--}}
                {{--@foreach($myTasksStatus as $myTask)--}}
                    {{--<option value="{{ $myTask->id }}">{{ $myTask->title }}</option>--}}

                {{--@endforeach--}}


            {{--</select>--}}
        {{--</div>--}}
        <div class="col-md-6">
            <select name="to_user" class="form-control form-control-sm selectUserStatus">
                <option selected="selected" value="">به شخص</option>
                @foreach($usersStatus as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>

                @endforeach


            </select>
        </div>
    </div>
    <div class="input-group">
        <input type="text" class="form-control InputToFocus inputUserStatus" name="content" autocomplete="off" placeholder=".....">
        <div class="input-group-append">
            <button class="btn btn-dark btn-add" type="submit"><i class="fa fa-check"></i></button>
        </div>
    </div>
    <input type="hidden" name="status" value="status" class="statusStatus">
    <input type="hidden" name="user_id" value="{{Auth::id()}}">
    @if(isset($lastStartedStatus) && $lastStartedStatus->status == 'start')
    <input type="hidden" name="task_id" value="{{$lastStartedStatus->task_id}}" class="endTaskId" disabled="disabled">
        @endif

</form>


    {{--<button class="btn btn-link float-right mt-2" data-toggle="collapse" data-target=".advanced" type="button"><i class="fa fa-bars"></i></button>--}}
{{--@if(isset($lastStartedStatus) && $lastStartedStatus->status == 'start')--}}
    {{--<button class="btn btn-link float-right mt-2 endTask" type="button" data-toggle="collapse" data-target=".advanced"><i class="fa fa-stop"></i></button>--}}
{{--@elseif(isset($lastStartedStatus) && $lastStartedStatus->status == 'end')--}}
    {{--<button class="btn btn-link float-right mt-2 playTask" type="button" data-toggle="collapse" data-target=".advanced"><i class="fa fa-play"></i></button>--}}
{{--@else--}}
    {{--<button class="btn btn-link float-right mt-2 playTask" type="button" data-toggle="collapse" data-target=".advanced"><i class="fa fa-play"></i></button>--}}
{{--@endif--}}


{{--<form  method="post" action="{{ route('status.store') }}">--}}
    {{--@csrf--}}
    {{--<input type="hidden" name="user_id" value="{{Auth::id()}}">--}}
    {{--<input type="hidden" name="status" value="off">--}}
    {{--<input type="hidden" name="content" value="توقف زمان برای  {{Auth::user()->name}}">--}}

    {{--<button class="btn btn-link mt-2" title="استراحت" type="submit"><i class="fa fa-power-off"></i></button>--}}

{{--</form>--}}
