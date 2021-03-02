<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Task;

class TasksController extends Controller
{
    public function index()
    {
        $data = [];
        if (\Auth::check()) { // 認証済みの場合
            // 認証済みユーザを取得
            $user = \Auth::user();
            // ユーザのタスクの一覧を取得
            // （後のChapterで他ユーザの投稿も取得するように変更しますが、現時点ではこのユーザの投稿のみ取得します）
            $tasks = $user->tasks()->orderBy('id', 'desc')->paginate(10);

            $data = [
                // 'user' => $user,
                'tasks' => $tasks,
            ];
        }

        // Welcomeビューでそれらを表示
        return view('welcome', $data);
    }

    public function create()
    {
        $task = new Task;

        return view('tasks.create', [
            'task' => $task,
        ]);
    }

    public function store(Request $request)
    {
        // バリデーション
        $request->validate([
            'status' => 'required|max:10',
            'content' => 'required|max:255',
        ]);
        
        // 認証済みユーザ（閲覧者）の投稿として作成（リクエストされた値をもとに作成）
        $request->user()->tasks()->create([
            'status' => $request->status,
            'content' => $request->content,
        ]);

        // 前のURLへリダイレクトさせる
        return redirect('/');
    }

    public function show($id)
    {
        $task = Task::findOrFail($id);
        if (\Auth::id() === $task->user_id) {
            return view('tasks.show', [
            'task' => $task,
        ]);
        }

        // 前のURLへリダイレクトさせる
        return redirect('/');
    }

    public function edit($id)
    {
        $task = Task::findOrFail($id);
        
        if (\Auth::id() === $task->user_id) {
            return view('tasks.edit', [
            'task' => $task,
        ]);
        }

        // 前のURLへリダイレクトさせる
        return redirect('/');
    }

    public function update(Request $request, $id)
    {
        // バリデーション
        $request->validate([
            'status' => 'required|max:10',
            'content' => 'required|max:255',
        ]);
        
        $task = Task::findOrFail($id);
         if (\Auth::id() === $task->user_id) {
             $task->status = $request->status;
            $task->content = $request->content;
            $task->save();
         }
        
        // 前のURLへリダイレクトさせる
        return redirect('/');
    }

    public function destroy($id)
    {
        $task = \App\Task::findOrFail($id);
        
        // 認証済みユーザ（閲覧者）がそのタスクの所有者である場合は、投稿を削除
        if (\Auth::id() === $task->user_id) {
            $task->delete();
        }

        // 前のURLへリダイレクトさせる
        return redirect('/');
    }
    
    public function __construct(){
    $this->middleware('auth');
  }

}