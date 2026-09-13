<?php
namespace App\Services;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiData{
    //get all previous session which is not active now
    //1/1,2/1,3/1,4/1,5/1 5 regular sessoin
   /* public static function getRegularSessions()
    {
        $response = Http::withHeaders([
            'X-API-KEY' => 'EXAMBILL_98745012'
        ])->get('https://ugr.duetbd.org/api/architecture/regular-sessions');

        if ($response->failed()) {
            Log::error('Session import failed from API.');
            return null; // Don't return redirect from a static method — handle in controller
        }

        $data = json_decode($response->body()); //for getting object not associative array

        return $data->sessions ?? null;
    }*/

    public static function getRegularSessions()
    {
        $response = Http::withHeaders([
            'X-API-KEY' => 'EXAMBILL_98745012'
        ])->get('https://ugr.duetbd.org/api/architecture/regular-sessions');

        if ($response->failed()) {
            Log::error('Session import failed from API.');
            return null; // Don't return redirect from a static method — handle in controller
        }

        $data = json_decode($response->body()); //for getting object not associative array

        return $data->sessions ?? null;
    }

    public static function getReviewSessions()
    {
        $response = Http::withHeaders([
            'X-API-KEY' => 'EXAMBILL_98745012'
        ])->get('https://ugr.duetbd.org/api/architecture/review-sessions');

        if ($response->failed()) {
            Log::error('Session import failed from API.');
            return null; // Don't return redirect from a static method — handle in controller
        }

        $data = json_decode($response->body()); //for getting object not associative array

        return $data->sessions ?? null;
    }

    public static function getReviewSessionExtra()
    {
        $response = Http::withHeaders([
            'X-API-KEY' => 'EXAMBILL_98745012'
        ])->get('https://ugr.duetbd.org/api/architecture/latest-inactive-session-architecture');

        if ($response->failed()) {
            Log::error('Session import failed from API.');
            return null;
        }

        $data = json_decode($response->body());

        // correct key is "session", not "sessions"
        return $data->session ?? null;
    }


    public static function getSpecialSession()
    {
        $response = Http::withHeaders([
            'X-API-KEY' => 'EXAMBILL_98745012'
        ])->get('https://ugr.duetbd.org/api/architecture/latest-special-session-architecture');

        if ($response->failed()) {
            Log::error('Session import failed from API.');
            return null;
        }

        $data = json_decode($response->body());

        // correct key is "session", not "sessions"
        return $data->session ?? null;
    }




    public static function getSessionWiseTheoryCoursesRegular($sid)
    {
        $response = Http::withHeaders([
            'X-API-KEY' => 'EXAMBILL_98745012'
        ])->get("https://ugr.duetbd.org/api/session-wise-theory-courses-regular/{$sid}");

        // Handle response
        if ($response->failed()) {
            Log::error('Theory course fetch failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'error' => 'Unable to fetch data',
                'status_code' => $response->status(),
            ];
        }

        return json_decode($response->body()); // Returns a stdClass object
    }

    public static function getSessionWiseTheoryCoursesReview($sid)
    {
        /*$response = Http::withHeaders([
            'X-API-KEY' => 'EXAMBILL_98745012'
        ])->get("https://ugr.duetbd.org/api/session-wise-theory-courses-review/{$sid}");*/

        $response = Http::withHeaders([
            'X-API-KEY' => 'EXAMBILL_98745012'
        ])->get("https://ugr.duetbd.org/api/session-wise-theory-courses-review-1");

        // Handle response
        if ($response->failed()) {
            Log::error('Theory course fetch failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'error' => 'Unable to fetch data',
                'status_code' => $response->status(),
            ];
        }

        return json_decode($response->body()); // Returns a stdClass object
    }

    //for special session
    public static function getSessionWiseTheoryCoursesSpecial()
    {
        $response = Http::withHeaders([
            'X-API-KEY' => 'EXAMBILL_98745012'
        ])->get("https://ugr.duetbd.org/api/session-wise-theory-courses-special-architecture");

        // Handle response
        if ($response->failed()) {
            Log::error('Theory course fetch failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'error' => 'Unable to fetch data',
                'status_code' => $response->status(),
            ];
        }

        return json_decode($response->body()); // Returns a stdClass object
    }



    public static function getPreviousReviewSession()
    {
        $authKey = 'OE3KFIE649MRECGQ';
        //$authKey = $request->authKey;
        $url = 'https://ugr.duetbd.org/get-architecture-previous-review-session-data';

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url . '?authKey=' . urlencode($authKey),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return $data['sessions'] ?? null;
        }

        return null;
    }

    public static function getSessionWiseSessionalCourses($sid)
    {
        $response = Http::withHeaders([
            'X-API-KEY' => 'EXAMBILL_98745012'
        ])->get("https://ugr.duetbd.org/api/session-wise-sessional-courses-regular/{$sid}");

        // Handle response
        if ($response->failed()) {
            Log::error('Theory course fetch failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'error' => 'Unable to fetch data',
                'status_code' => $response->status(),
            ];
        }

        return json_decode($response->body()); // Returns a stdClass object
    }

    public static function getSessionWiseTheorySessionalCourses($sid)
    {
        $response = Http::withHeaders([
            'X-API-KEY' => 'EXAMBILL_98745012'
        ])->get("https://ugr.duetbd.org/api/session-wise-theory-sessional-courses/{$sid}");

        // Handle response
        if ($response->failed()) {
            Log::error('Theory Sessional course fetch failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'error' => 'Unable to fetch data',
                'status_code' => $response->status(),
            ];
        }

        return json_decode($response->body()); // Returns a stdClass object
    }



    public static function  getSessionWiseStudentAdvisor($sid){
        $response = Http::withHeaders([
            'X-API-KEY' => 'EXAMBILL_98745012'
        ])->get("https://ugr.duetbd.org/api/teacher-student-architecture/{$sid}");

        // Handle response
        if ($response->failed()) {
            Log::error('Session Wise Advisor failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'error' => 'Unable to fetch data',
                'status_code' => $response->status(),
            ];
        }

        return json_decode($response->body()); // Returns a stdClass object
    }

    public static function  getCoOrdinator(){
        $response = Http::withHeaders([
            'X-API-KEY' => 'EXAMBILL_98745012'
        ])->get("https://ugr.duetbd.org/api/coordinator-architecture");

        // Handle response
        if ($response->failed()) {
            Log::error('Co-ordinator found failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'error' => 'Unable to fetch data',
                'status_code' => $response->status(),
            ];
        }

        return json_decode($response->body())->coordinator; // Returns a stdClass object
    }

    public static function  getHead(){
        $response = Http::withHeaders([
            'X-API-KEY' => 'EXAMBILL_98745012'
        ])->get("https://ugr.duetbd.org/api/head-of-department-architecture");

        // Handle response
        if ($response->failed()) {
            Log::error('Co-ordinator found failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'error' => 'Unable to fetch data',
                'status_code' => $response->status(),
            ];
        }

        return json_decode($response->body())->head; // Returns a stdClass object
    }

    public static function  getSessionInfo($sid){
        $authKey = 'OE3KFIE649MRECGQ';
        //$authKey = $request->authKey;
        // ✅ Properly embed $sid into the URL
        $url = "https://ugr.duetbd.org/get-seesion-info/{$sid}?authKey=" . urlencode($authKey);

        /*//debug
        return response()->json([
            'request' => $request->authKey,
            'sid' => $sid,
            'url'=>$url
        ]);*/

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode === 200) {
            $data = json_decode($response, true);
            return $data ?? null;
        }

        return ['error' => 'Unable to fetch data', 'status_code' => $httpCode];
    }

    public static function  getTotalStudentInSession($sid){
        $response = Http::withHeaders([
            'X-API-KEY' => 'EXAMBILL_98745012'
        ])->get("https://ugr.duetbd.org/api/total-student-in-a-session-architecture/{$sid}");

        // Handle response
        if ($response->failed()) {
            Log::error('Total Student found failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return [
                'error' => 'Unable to fetch data',
                'status_code' => $response->status(),
            ];
        }

        $json= json_decode($response->body()); // Returns a stdClass object
        return $json->studentCount ?? null; // Return only the count as an integer or null
    }





}