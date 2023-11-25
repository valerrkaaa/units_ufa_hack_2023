<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'name' => 'required|string'
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
            'content' => ''
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
        $content = Storage::get($lesson->content);

        return response()->json(['status' => 'success', 'lesson' => [
            'id' => $lesson->id,
            'course_id' => $lesson->course_id,
            'name' => $lesson->name,
            'content' => $content
        ]]);
    }

    public function saveLessonData(Request $request){
        $validator = Validator::make($request->all(), [
            'lesson_id' => 'required|integer',
            'content' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => "validator error",
                'errors' => $validator->errors()->toArray(),
            ]);
        }

        $lesson = Lesson::findOrFail($request->lesson_id);
        $file_path = 'lessons/' . (string)($lesson->id) . '.lsn';
        Storage::put($file_path, $request->content);
        $lesson->update(['content' => $file_path]);
        $lesson->save();

        return response()->json(['status' => 'success']);
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
