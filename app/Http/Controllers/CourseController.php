<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\PassedLesson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    public function getCourseList(Request $request){
        $all_courses = Course::paginate(15);

        $response = [];
        foreach ($all_courses as $course){
            $lessons = Lesson::all()->where('course_id', $course->id);
            $lessons_count = count($lessons);


            $passed_lessons = PassedLesson::whereHas('lesson', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })->get();  // TODO mark

            $passed_lessons_count = count($passed_lessons);
            array_push($response, [
                'course_data' => $course, 
                'passed_lessons' => $passed_lessons_count, 
                'lessons_count' => $lessons_count
            ]);
        }

        return response()->json(['status' => 'success', 'courses' => $response, 'pagination']);
    }

    public function getCourse(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => "validator error",
                'errors' => $validator->errors()->toArray(),
            ]);
        }

        $course = Course::findOrFail($request->id);

        $lessons = DB::table('lessons')
        ->leftJoin('passed_lessons', function ($join) {
            $join->on('lessons.id', '=', 'passed_lessons.lesson_id');
        })
        ->where('lessons.course_id', $course->id)
        ->select('lessons.id as lesson_id', 'lessons.name as lesson_name')
        ->addSelect(DB::raw('COALESCE(passed_lessons.mark, 0) as mark'))
        ->get();

        return response()->json([
            'status' => 'success', 
            'course_info' => $course, 
            'lessons' => $lessons
        ]);
    }

    public function createCourse(Request $request){
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => "validator error",
                'errors' => $validator->errors()->toArray(),
            ]);
        }

        Course::create([
            'owner_id' => auth()->id(),
            'name' => $request->name,
            'description' => $request->description
        ]);

        return response()->json(['status' => 'success']);
    }

    public function deleteCourse(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => "validator error",
                'errors' => $validator->errors()->toArray(),
            ]);
        }

        $course = Course::findOrFail($request->id);
        $course->delete();

        Lesson::where('course_id', $course->id)->delete();

        return response()->json(['status' => 'success']);
    }
}
