<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group([
    'middleware' => 'api'
], function ($router) {
    Route::post('register', 'AuthController@register');
    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');
});

Route::group([
    'middleware' => ['api']
], function ($router){
    Route::get('courses', 'CourseController@getCourseList');
    Route::get('recomended_courses', 'CourseController@getRecomendedCourselist');
    Route::get('course', 'CourseController@getCourse');
    Route::get('lesson', 'LessonController@getLesson');
    Route::get('tags', 'TagsController@getTags');
    Route::post('save_user_tags', 'TagsController@saveUserTags');
});

Route::group([
    'middleware' => ['api', 'role:admin']
], function ($router){
    Route::post('create_course', 'CourseController@createCourse');
    Route::delete('delete_course', 'CourseController@deleteCourse');
    Route::post('create_lesson', 'LessonController@createLesson');
    Route::post('save_lesson', 'LessonController@saveLessonData');
    Route::delete('delete_lesson', 'LessonController@deleteLesson');
});

Route::group(['prefix' => 'ml'], function ($router){
    Route::get('embeddings', 'MLController@getEmbeddings');
    Route::get('courses', 'MLController@getCourse');
});

Route::group(['prefix' => 'game'], function ($router){
    Route::get('test', 'GameController@test');
});
