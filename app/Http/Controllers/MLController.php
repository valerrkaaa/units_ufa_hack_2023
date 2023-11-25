<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\UserCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MLController extends Controller
{
    public function getEmbeddings(Request $request){
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => "validator error",
                'errors' => $validator->errors()->toArray(),
            ]);
        }

        $user_courses = UserCourse::where('user_id', $request->id)->take(5)->get();
        $response = [];
        foreach ($user_courses as $user_course){
            $course = Course::find($user_course->course_id)->select('id', 'name', 'embedding_path')->first();
            $embedding = Storage::get($course->embedding_path);
            array_push($response, [
                'id' => $course->id,
                'name' => $course->name,
                'embedding' => $embedding
            ]);
        }

        return response()->json(['status' => 'success', 'data' => $response]);
    }

    public function getCourse(Request $request){
        $courses = Course::take(5)->get();
        $response = [];
        foreach ($courses as $course){
            $embedding = Storage::get($course->embedding_path);
            array_push($response, [
                'id' => $course->id,
                'embedding' => json_decode($embedding),
            ]);
        }

        return response()->json(['status' => 'success', 'data' => $response]);
    }
}
