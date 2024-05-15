<?php

namespace App\Http\Controllers;

use App\Models\YoutubeComment;
use App\Http\Requests\StoreYoutubeCommentRequest;
use App\Http\Requests\UpdateYoutubeCommentRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class YoutubeCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $totalComments = YouTubeComment::count();
        $positiveCount = YouTubeComment::select('textOriginal', 'sentiment', 'publishedAt')->where('sentiment', 'positive')->count();
        $negativeCount = YouTubeComment::select('textOriginal', 'sentiment', 'publishedAt')->where('sentiment', 'negative')->count();
        $neutralCount = YouTubeComment::select('textOriginal', 'sentiment', 'publishedAt')->where('sentiment', 'neutral')->count();

        $positivePercentage = $totalComments ? ($positiveCount * 100) / $totalComments : 0;
        $negativePercentage = $totalComments ? ($negativeCount * 100) / $totalComments : 0;
        $neutralPercentage = $totalComments ? ($neutralCount * 100) / $totalComments : 0;

        $total = [
            'TotalComment' => $totalComments,
            'positiveCount' => $positiveCount,
            'negativeCount' => $negativeCount,
            'neutralCount' => $neutralCount,
            'positivePercentage' => number_format($positivePercentage, 2),
            'negativePercentage' => number_format($negativePercentage, 2),
            'neutralPercentage' => number_format($neutralPercentage, 2),
        ];

        $result = YouTubeComment::selectRaw('
YEAR(publishedat) AS Year,
MONTH(publishedat) AS Month,
COALESCE(SUM(CASE WHEN sentiment = "negative" THEN 1 ELSE 0 END), 0) AS negativeCount,
COALESCE(SUM(CASE WHEN sentiment = "positive" THEN 1 ELSE 0 END), 0) AS positiveCount,
COALESCE(SUM(CASE WHEN sentiment = "neutral" THEN 1 ELSE 0 END), 0) AS neutralCount,
COALESCE(COUNT(*), 0) AS TotalCommentCount')
            ->groupByRaw('YEAR(publishedat), MONTH(publishedat)')
            ->orderByRaw('YEAR(publishedat), MONTH(publishedat)')
            ->get();

        $videoId = YoutubeComment::select('videoId')->first();

        if (empty($videoId)) {
            $videoId = "";
        } else {
            $videoId = $videoId->videoId;
        }

        $top = YouTubeComment::select('textOriginal', 'sentiment', 'publishedAt', 'authorProfileImageUrl', 'authorChannelUrl')->orderBy('publishedAt', 'desc')->limit(5)->get();

        return view('youtube-comments.index', [
            'total' => $total,
            'result' => $result,
            'videoId' => $videoId,
            'top5' => $top,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreYoutubeCommentRequest $request)
    {
        dump(YoutubeComment::all());
        DB::table("youtubecomments")->delete();
        dump(YoutubeComment::all());
        Http::post("http://127.0.0.1:5000/get_video_comments", [
            "youtube_link" => $request->url,
        ]);
        dump(YoutubeComment::all());

        return redirect()->route("youtube-comments.index");
    }

    /**
     * Display the specified resource.
     */
    public function show(YoutubeComment $youtubeComment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(YoutubeComment $youtubeComment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateYoutubeCommentRequest $request, YoutubeComment $youtubeComment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(YoutubeComment $youtubeComment)
    {
        //
    }
}
