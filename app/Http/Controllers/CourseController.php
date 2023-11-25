<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseTag;
use App\Models\Lesson;
use App\Models\PassedLesson;
use App\Models\Tag;
use App\Models\UserCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    public function getCourseList(Request $request)
    {
        $user_courses = UserCourse::where('user_id', auth()->id())->get();
        $all_courses = Course::all();

        $user_course_dict = [];
        foreach ($user_courses as $user_course){
            $user_course_dict[$user_course->course_id] = $user_course;
        }

        $response =[];
        foreach ($all_courses as $course){
            $tags = CourseTag::where('course_id', $course->id)->get();

            if (array_key_exists($course->id, $user_course_dict)){
                array_push($response, [
                    'id' => $course->id,
                    'name' => $course->name,
                    'description' => $course->description,
                    'avg_mark' => $user_course_dict[$course->id]->avg_mark,
                    'is_finished' => $user_course_dict[$course->id]->is_finished,
                    'user_feedback' => $user_course_dict[$course->id]->user_feedback,
                    'tags' => $tags
                ]);
            }
            else {
                array_push($response, [
                    'id' => $course->id,
                    'name' => $course->name,
                    'description' => $course->description,
                    'avg_mark' => '0',
                    'is_finished' => false,
                    'user_feedback' => 0,
                    'tags' => $tags
                ]);
            }
        }
        return response()->json(['status' => 'success', 'courses' => $response]);
    }

    public function getRecomendedCourselist(Request $request)
    {
        $response =[];

        // todo ml-запрос
        return response()->json(['status' => 'success', 'courses' => $response]);
    }

    public function getCourse(Request $request)
    {
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

    public function createCourse(Request $request)
    {
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

        // создание курса
        $course = Course::create([
            'owner_id' => auth()->id(),
            'name' => $request->name,
            'description' => $request->description
        ]);

        // теги
        $all_tags = Tag::all();
        $tags_response = Http::post(
            'http://26.46.215.75:2309/preference_mark/mark_description', 
            ['description' => $request->description, 
            'tags' => $all_tags
        ]);
        $tags = json_decode($tags_response->getBody()->getContents())->description;
        foreach ($tags as $tag){
            CourseTag::create([
                'course_id' => $course->id,
                'tag_id' => $tag
            ]);
        }

        // создание эмбеддингов
        $embedding_response = Http::post(
            'http://26.46.215.75:2309/course/encode_course',
            ['description' => $request->description]
        );
        $embedding_path = 'embeddings/' . $course->id . '.emb';
        $embedding = json_encode(json_decode($embedding_response->getBody()->getContents())->description);
        // dd($embedding);
        Storage::put($embedding_path, $embedding);
        $course->update(['embedding_path' => $embedding_path]);

        return response()->json(['status' => 'success']);
    }

    public function deleteCourse(Request $request)
    {
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
        Lesson::where('course_id', $course->id)->delete();
        $course->delete();

        return response()->json(['status' => 'success']);
    }
}
