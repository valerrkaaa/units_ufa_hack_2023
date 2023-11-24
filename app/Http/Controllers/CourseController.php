<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\PassedLesson;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }
    public function getLessonList(Request $request){
        $all_courses = Course::paginate(15);
    
        dd($all_courses);

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
}
