<?php

namespace App\Http\Controllers;

use App\Todo;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    public function index()
    {
        return response()->json(['todos' => Todo::all()]);

    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'title' => 'required',
        ]);
        if ($validate) {
            $todo = Todo::create([
                'title' => $request->title,
                'is_complete' => false
            ]);
        }
        return response()->json(['todo' => $todo]);
    }

    public function update(Request $request, $id)
    {
        return Todo::whereId($id)->update($request->except('_method'));
    }

    public function destroy($id)
    {
        return Todo::whereIn('id', explode(',', $id))->delete();
    }
}
