<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LessonController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    public function createLesson(Request $request){
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|integer',
            'name' => 'required|string',
            'content' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => "validator error",
                'errors' => $validator->errors()->toArray(),
            ]);
        }

        // проверка на то, он ли владелец курса
        $course = Course::where('id', $request->course_id)->first();
        if ($course == null) return response()->json(['status' => 'course not found']);
        if ($course->owner_id != auth()->id()) return response()->json(['status' => 'permission denied']);

        Lesson::create([
            'course_id' => $request->course_id,
            'name' => $request->name,
            'content' => $request->content
        ]);

        return response()->json(['status' => 'success']);
    }

    public function getLesson(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => "validator error",
                'errors' => $validator->errors()->toArray(),
            ]);
        }

        $lesson = Lesson::findOrFail($request->id);

        return response()->json(['status' => 'success', 'lesson' => $lesson]);
    }

    public function deleteLesson(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => "validator error",
                'errors' => $validator->errors()->toArray(),
            ]);
        }

        $lesson = Lesson::findOrFail($request->id);
        $course = Course::find($lesson->course_id);
        if ($course->owner_id != auth()->id()) return response()->json(['status' => 'permission denied']);

        $lesson->delete();

        return response()->json(['status' => 'success']);
    }
}
