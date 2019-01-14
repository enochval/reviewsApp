<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Facades\App\Services\CommentsOrReviews;

class ReviewsCommentsController extends Controller
{
    public function __construct()
    {
    }

    public function review(Request $request)
    {
        $data = $request->validate([
            'url' => 'required'
        ]);

        $url = $data['url'];

        if (str_contains($url, 'youtube')) {
            $videoID = str_after($url, '?v=');
            try {
                $comments = CommentsOrReviews::get($videoID);
                return response()->json($comments);
            } catch (\Exception $exception) {
                return response()->json($exception->getMessage());
            }
        } elseif(str_contains($url, 'amazon')) {
            $asin = str_before(str_after($url, 'dp/'), '/');
            return response()->json($asin);
            try {
                $reviews = CommentsOrReviews::getAmazonReviews($asin);
                return response()->json($reviews);
            } catch (\Exception $exception) {
                return response()->json($exception->getMessage());
            }
        } else {
            return response()->json('Invalid URL');
        }
    }

    public function index()
    {
        return view('welcome');
    }

    public function amazon()
    {
        $asin = 'B00891PV0G';
        $response = CommentsOrReviews::getAmazonReviews($asin);
        return response()->json($response);
    }
}
