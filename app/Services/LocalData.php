<?php

namespace App\Services;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LocalData
{

    public static function getOrCreateRegularSession($sid, $exam_type)
    {
        /* this is local Session table data showing in extra review session list, where ugr_id is NULL because we added those session manually
         * {
           "id": 36,
           "ugr_id": null,
           "session": "2023-2024",
           "year": "1",
           "semester": "1",
           "status": 1,
           "created_at": "2025-11-11T07:14:37.000000Z",
           "updated_at": "2025-11-11T07:14:37.000000Z",
           "exam_type_id": 2
       },*/

        //here for review extra session it send $sid=36 which is local Session table id=36
        // 1) Try to find session where id = sid and ugr_id is NULL
        //this is for review extra session find;
        //for review extra session it return this
        $session_info = Session::whereNull('ugr_id')
            ->where('exam_type_id', $exam_type)
            ->where('id', $sid)//id=36 , $sid=36 both are local table session table id.
            ->where('status', 1)
            ->first();

        // 2) If not found, try to find session where ugr_id = sid
        //data send from view; session list is :   "id": 364,
        //this is ugr database session table 'id'
        //we try to find session where (ebill database)ugr_id = sid (ugr database)
        if (!$session_info) {
            $session_info = Session::where('ugr_id', $sid)
                ->where('exam_type_id', $exam_type)
                ->where('status', 1)
                ->first();
        }





        // 3) If still not found, pull from API and create new
        if (!$session_info) {
            $session_info_data = ApiData::getSessionInfo($sid);

            if ($session_info_data && isset($session_info_data['session'])) {
                $session_info = new Session();
                $session_info->ugr_id       = $sid;
                $session_info->session      = $session_info_data['session'];
                $session_info->year         = $session_info_data['year'];
                $session_info->semester     = $session_info_data['semester'];
                $session_info->exam_type_id = $exam_type;
                $session_info->status = 1;
                $session_info->created_at   = now();
                $session_info->updated_at   = now();
                $session_info->save();

                Log::info('✅ New review session created from API', $session_info->toArray());
            } else {
                Log::warning('⚠️ Session info missing or invalid in API response for sid: ' . $sid);
                return null; // or throw exception
            }
        }

        return $session_info;
    }
    /*public static function getOrCreateRegularSession($sessionId,$exam_type)
    {
        $session_info = Session::where('ugr_id', $sessionId)
            ->where('exam_type_id',$exam_type)->first();

        if (!$session_info) {
            $session_info_data = ApiData::getSessionInfo($sessionId);

            if ($session_info_data && isset($session_info_data['session'])) {
                $session_info = new Session();
                $session_info->ugr_id = $sessionId;
                $session_info->session = $session_info_data['session'];
                $session_info->year = $session_info_data['year'];
                $session_info->semester = $session_info_data['semester'];
                $session_info->exam_type_id = $exam_type;
                $session_info->created_at = now();
                $session_info->updated_at = now();
                $session_info->save();

                Log::info('✅ New session created from API', $session_info->toArray());
            } else {
                Log::warning('Session info missing or invalid in API response for sessionId: ' . $sessionId);
                return null; // Or throw exception
            }
        }

        return $session_info;
    }*/


    /*public static function getOrCreateRegularSession($sessionId,$exam_type)
    {
        $session_info = Session::where('ugr_id', $sessionId)
            ->where('exam_type_id',$exam_type)->first();

        if (!$session_info) {
            $session_info_data = ApiData::getSessionInfo($sessionId);

            if ($session_info_data && isset($session_info_data['session'])) {
                $session_info = new Session();
                $session_info->ugr_id = $sessionId;
                $session_info->session = $session_info_data['session'];
                $session_info->year = $session_info_data['year'];
                $session_info->semester = $session_info_data['semester'];
                $session_info->exam_type_id = $exam_type;
                $session_info->created_at = now();
                $session_info->updated_at = now();
                $session_info->save();

                Log::info('✅ New session created from API', $session_info->toArray());
            } else {
                Log::warning('Session info missing or invalid in API response for sessionId: ' . $sessionId);
                return null; // Or throw exception
            }
        }

        return $session_info;
    }*/
    public static function getOrCreateReviewSession($sid, $exam_type)
    {

        /* this is local Session table data showing in extra review session list, where ugr_id is NULL because we added those session manually
        * {
          "id": 36,
          "ugr_id": null,
          "session": "2023-2024",
          "year": "1",
          "semester": "1",
          "status": 1,
          "created_at": "2025-11-11T07:14:37.000000Z",
          "updated_at": "2025-11-11T07:14:37.000000Z",
          "exam_type_id": 2
      },*/

        //here for review extra session it send $sid=36 which is local Session table id=36
        // 1) Try to find session where id = sid and ugr_id is NULL
        //this is for review extra session find;
        //for review extra session it return this
        $session_info = Session::whereNull('ugr_id')
            ->where('exam_type_id', $exam_type)
            ->where('id', $sid)
            ->where('status', 1)
            ->first();

        // 2) If not found, try to find session where ugr_id = sid
        if (!$session_info) {
            $session_info = Session::where('ugr_id', $sid)
                ->where('exam_type_id', $exam_type)
                ->where('status', 1)
                ->first();
        }

        // 3) If still not found, pull from API and create new
        if (!$session_info) {
            $session_info_data = ApiData::getSessionInfo($sid);

            if ($session_info_data && isset($session_info_data['session'])) {
                $session_info = new Session();
                $session_info->ugr_id       = $sid;
                $session_info->session      = $session_info_data['session'];
                $session_info->year         = $session_info_data['year'];
                $session_info->semester     = $session_info_data['semester'];
                $session_info->exam_type_id = $exam_type;
                $session_info->status = 1;
                $session_info->created_at   = now();
                $session_info->updated_at   = now();
                $session_info->save();

                Log::info('✅ New review session created from API', $session_info->toArray());
            } else {
                Log::warning('⚠️ Session info missing or invalid in API response for sid: ' . $sid);
                return null; // or throw exception
            }
        }

        return $session_info;
    }

}
