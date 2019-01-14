<?php
/**
 * Created by PhpStorm.
 * User: enochval
 * Date: 1/11/19
 * Time: 4:00 PM
 */

namespace App\Services;

use GuzzleHttp\Client;

class CommentsOrReviews
{
    private $client;

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    private function setClient(): void
    {
        $this->client = new Client([
            'base_uri' => 'https://www.googleapis.com/youtube/v3/',
//            'timeout'  => 2.0,
        ]);
    }

    public function __construct()
    {
    }

    private function getCommentThreads($videoID, $pageToken)
    {
        $params = array_filter([
            'key' => config('youtube.key'),
            'channelId' => null,
            'id' => null,
            'videoId' => $videoID,
            'maxResults' => 100,
            'part' => implode(', ', ['snippet']),
            'order' => null,
            'pageToken' => $pageToken
        ]);

        $response = $this->client->request('GET', 'commentThreads', [
            'query' => $params
        ]);
        return json_decode($response->getBody()->getContents(), true);
    }

    private function getProcessedCommentThreads($videoID)
    {
        $response = [];
        $count = 0;
        $pageToken = '';
        do {
            $comments = $this->getCommentThreads($videoID, $pageToken);
            foreach ($comments['items'] as $comment) {
                $response[$count]['username'] = array_get($comment, 'snippet.topLevelComment.snippet.authorDisplayName');
                $response[$count]['comment'] = array_get($comment, 'snippet.topLevelComment.snippet.textDisplay');
                $response[$count]['starRating'] = array_get($comment, 'snippet.topLevelComment.snippet.viewerRating');
                $response[$count]['date'] = array_get($comment, 'snippet.topLevelComment.snippet.publishedAt');
                $response[$count]['link'] = null;
                $count += 1;
            }

            if (isset($comments['nextPageToken']))
                $pageToken = $comments['nextPageToken'];

        } while (isset($comments['nextPageToken']));

        return $response;
    }

    public function get($videoID)
    {
        $this->setClient();
        return $this->getProcessedCommentThreads($videoID);
    }

    // AMAZON

    public function getAmazonReviews($asin)
    {
//        set_time_limit(300);
        include(base_path('app/Services/simple_html_dom.php'));

        $response = [];

        $html = new \simple_html_dom();
        $html->load_file('http://www.amazon.com/product-reviews/'.$asin);

        dd('it got here');

        $totalPageNumber = $html->find('li[class=page-button] a');
        $length = sizeof($totalPageNumber);
        $totalPages = $length ? $totalPageNumber[$length-1]->plaintext : false;

        if (!$totalPages || $totalPages <= 1) {
            $allReviewDiv = $html->find('div[id=cm_cr-review_list] div[class=a-section review]');
            $response = $this->processAmazonReviews($allReviewDiv);
        } elseif($totalPages > 1) {
            for($i=1; $i <= $totalPages; $i++) {
                $url='http://www.amazon.com/product-reviews/'.$asin.'/?sortBy=recent&pageNumber='.$i;
                $html->load_file($url);
                $allReviewDiv = $html->find('div[id=cm_cr-review_list] div[class=a-section review]');
                $tempResp = $this->processAmazonReviews($allReviewDiv);
                $response = array_merge($response, $tempResp);
            }
        }
        return $response;

    }

    private function processAmazonReviews($allReviewDiv)
    {
        $username = $rating = $reviewText = $date = '';
        $tempResponse = [];

        foreach ($allReviewDiv as $reviewDiv) {
            if ($userDiv = $reviewDiv->find('span[class=a-profile-name]'))
                $username = $userDiv[0]->plaintext;

            //REVIEW RATING
            if ($reviewRatingIcon = $reviewDiv->find('a[class=a-link-normal] i')) {
                $reviewRating = $reviewRatingIcon[0]->plaintext;
                $reviewRating = explode(" ", $reviewRating);
                $rating = $reviewRating[0];

            }
            //REVIEW DATE
            if ($reviewDateSpan = $reviewDiv->find('span[class=a-size-base a-color-secondary review-date]')) {
                $date = $reviewDateSpan[0]->plaintext;
//                $date=format_date($date);
            }
            //text of the review
            if ($reviewTextDiv = $reviewDiv->find('span[class=a-size-base review-text]')) {
                $patterns = array();
                $patterns[0] = '#<br\s*/?>#i';
                $patterns[1] = '#<p\s*/?>#i';
                $patterns[2] = '#<li\s*/?>#i';
                $text = preg_replace($patterns, "\n", $reviewTextDiv[0]);
                $reviewText = strip_tags($text);
            }
            $reviews = [
                'username' => $username,
                'comment' => $reviewText,
                'starRating' => $rating,
                'date' => $date,
                'link' => null,
            ];
            array_push($tempResponse, $reviews);
        }
        return $tempResponse;
    }

}
