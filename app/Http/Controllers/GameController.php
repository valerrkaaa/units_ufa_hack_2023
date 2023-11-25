<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GameController extends Controller
{
    //

    public function test(Request $request){
        return response()->json([
            [
                'question' => 'test',
                'answer 1' => 'test 1',
                'answer 2' => 'test 2',
                'right_answer' => 'test 1'
            ]
        ]);
    }
}
