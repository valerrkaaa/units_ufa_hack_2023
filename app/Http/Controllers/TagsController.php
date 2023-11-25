<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\UserTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TagsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['getTags']]);
    }

    public function getTags(Request $request){
        $tags = Tag::all();

        return response()->json(['status' => 'success', 'tags' => $tags]);
    }

    public function saveUserTags(Request $request){
        $validator = Validator::make($request->all(), [
            'tags' => 'required|array',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => "validator error",
                'errors' => $validator->errors()->toArray(),
            ]);
        }

        foreach ($request->tags as $tagId){
            $existedTag = UserTag::where('tag_id', $tagId)->where('user_id', auth()->id())->get();
            if (count($existedTag) != 0) continue;

            UserTag::create([
                'user_id' => auth()->id(),
                'tag_id' => $tagId
            ]);
        }

        return response()->json(['status' => 'success']);
    }
}
