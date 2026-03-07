<?php

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\TwilioService;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
class Settings
{

    public static function getSetting($setting_key)
    {
        $settingData = Setting::Select('setting_value')->where("setting_key", $setting_key)->first();
        return $settingData->setting_value;
    }
    public static function downloadpdf($pdf)
    {
        $pdf->output();
        $domPdf = $pdf->getDomPDF();
        $canvas = $domPdf->get_canvas();
        $canvas->page_text(500, 10, "Page {PAGE_NUM} of {PAGE_COUNT}", 'sans-serif', 8, [90/255, 62/255, 43/255]);
        $pdf->setPaper('L', 'landscape');
        return $pdf;
    }
    public static function downloadlandscapepdf($pdf)
    {
        $pdf->setPaper('a4', 'landscape');
        $pdf->output();
        $domPdf = $pdf->getDomPDF();
        $canvas = $domPdf->get_canvas();
        $canvas->page_text(760, 10, "Page {PAGE_NUM} of {PAGE_COUNT}", 'sans-serif', 8, [90/255, 62/255, 43/255]);
        return $pdf;
    }
	
	public static function getPunchIn($attendanceData,$staffId,$monthdate, $csvornot = 1){
        $punchInDate = ''; 
        $br  = '<br>';  
        if($csvornot == 0){
            $br  = "\n";
        }     
        foreach($attendanceData as $atttendanceRecord){
            if(($atttendanceRecord->staff_id ==$staffId) && (date('Y-m-d',strtotime($atttendanceRecord->punch_in_date)) ==  date('Y-m-d',strtotime($monthdate))) ){
                $punchInDate = 'Punch-In '.$br.'('.date(\Config::get('constants.dateformat.dmy'),strtotime($atttendanceRecord->punch_in_date)).')';
            }
        }
		return $punchInDate;
	}

    public static function getPunchOut($attendanceData,$staffId,$monthdate, $csvornot = 1){
        $punchOutDate = ''; 
        $br  = '<br>';  
        if($csvornot == 0){
            $br  = "\n";
        }          
        foreach($attendanceData as $atttendanceRecord){
            if(($atttendanceRecord->staff_id ==$staffId) && (date('Y-m-d',strtotime($atttendanceRecord->punch_out_date)) ==  date('Y-m-d',strtotime($monthdate))) ){
                $punchOutDate = 'Punch-Out '.$br.'('.date(\Config::get('constants.dateformat.dmy'),strtotime($atttendanceRecord->punch_out_date)).')';
            }
        }
		return $punchOutDate;
	}
	public static function formatDate($date,$format){
		if (strpos($date, '/') !== false) {
			return date($format, strtotime(str_replace('/', '-', $date)));
		} else {
			return date($format, strtotime($date));
		}	
		
	}
	public static function getEncodeCode($data){
            return  (!empty($data))? substr(str_shuffle("123456789"), 0, 5).$data:'';
    }
    public static function getDecodeCode($encodedCode){
        return  (!empty($encodedCode))? substr($encodedCode,5):'';
    }
	public static function getFormattedDate($date){		
		return (!empty($date)) ? date(\Config::get('constants.dateformat.slashdmyonly'),strtotime($date)):'';
	}
	public static function getFormattedDatetime($date){		
		return (!empty($date)) ? date(\Config::get('constants.dateformat.slashdmy'),strtotime($date)):'';
	}

	public static function uploadimageold($request, $fieldname, $pathname, $oldFilename = null){
		$filename = '';
		if ($request->hasFile($fieldname)) {
			$ds = DIRECTORY_SEPARATOR;
			$folderpath = 'uploads' . $ds . $pathname . $ds;
			$image = $request->file($fieldname);
			$filename = time() . '.' . $image->getClientOriginalExtension();

			// Ensure directories exist
			$folders = ['original', 'small', 'medium', 'large'];
			foreach ($folders as $folder) {
				$dir = public_path($folderpath . $folder . $ds);
				if (!File::exists($dir)) {
					File::makeDirectory($dir, 0755, true);
				}
			}
			
			// Delete old files if old filename is provided
			if ($oldFilename) {
				foreach ($folders as $folder) {
					$oldFilePath = public_path($folderpath . $folder . $ds . $oldFilename);
					if (File::exists($oldFilePath)) {
						File::delete($oldFilePath);
					}
				}
			}

			// Save original and resized images
			Image::make($image)->save(public_path($folderpath . 'original' . $ds . $filename));

			$sizes = [
				'small' => [100, 100],
				'medium' => [300, 300],
				'large' => [600, 600],
			];
			foreach ($sizes as $folder => $size) {
				Image::make($image)
					->fit($size[0], $size[1])
					->save(public_path($folderpath . $folder . $ds . $filename));
			}
		}

		return $filename;
	}
    public static function uploadimage($request, $fieldname, $pathname, $oldFilename = null)
    {
        $filename = '';

        if ($request->hasFile($fieldname)) {

            $ds = DIRECTORY_SEPARATOR;
            $folderpath = 'uploads' . $ds . $pathname . $ds;

            $imageFile = $request->file($fieldname);
            $filename  = time() . '.' . $imageFile->getClientOriginalExtension();

            // Ensure directories exist
            $folders = ['original', 'small', 'medium', 'large'];

            foreach ($folders as $folder) {
                $dir = public_path($folderpath . $folder . $ds);
                if (!File::exists($dir)) {
                    File::makeDirectory($dir, 0755, true);
                }
            }

            // Delete old files
            if ($oldFilename) {
                foreach ($folders as $folder) {
                    $oldPath = public_path($folderpath . $folder . $ds . $oldFilename);
                    if (File::exists($oldPath)) {
                        File::delete($oldPath);
                    }
                }
            }

            /* -------- ORIGINAL IMAGE -------- */
            Image::read($imageFile)
                ->save(public_path($folderpath . 'original' . $ds . $filename));

            /* -------- RESIZED IMAGES -------- */
            $sizes = [
                'small'  => [100, 100],
                'medium' => [300, 300],
                'large'  => [600, 600],
            ];

            foreach ($sizes as $folder => [$width, $height]) {
                Image::read($imageFile)
                    ->cover($width, $height)
                    ->save(public_path($folderpath . $folder . $ds . $filename));
            }
        }

        return $filename;
    }


    public static function isFileExists($foldername,$imagename){	
                $isExist = 0;
                $ds = DIRECTORY_SEPARATOR;
                $folderpath = 'uploads' . $ds . $foldername . $ds . 'original'. $ds;	
                $imagePath = $folderpath . $imagename;
                if (file_exists(public_path($imagePath))){
                    $isExist = 1;
                }
                return $isExist;
	}
	
	public static function  getcustomnumberformat($amount) {
		 return number_format($amount, 2);
        $parts = explode('.', number_format($amount, 2, '.', ''));
        $intPart = $parts[0];
        
        if (strlen($intPart) > 4) {
            $firstPart = substr($intPart, 0, 2);
            $lastPart = substr($intPart, 2);
            $formatted = $firstPart . ',' . $lastPart;
        } else {
            $formatted = $intPart;
        }
    
        return $formatted . '.' . $parts[1];
    }

    public static function applyDateRange($query, Request $request, $column = 'date',$defaultdate = false)
    { 
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $fromDate = Settings::formatDate($request->get('from_date'), 'Y-m-d');
            $toDate   = Settings::formatDate($request->get('to_date'), 'Y-m-d');
            $query->whereDate($column, '>=', $fromDate)->whereDate($column, '<=', $toDate);
        } elseif ($request->filled('from_date')) {
            $fromDate = Settings::formatDate($request->get('from_date'), 'Y-m-d');
            $query->whereDate($column, '>=', $fromDate);
        } elseif ($request->filled('to_date')) {
            $toDate = Settings::formatDate($request->get('to_date'), 'Y-m-d');
            $query->whereDate($column, '<=', $toDate);
        }elseif($defaultdate){
            $query->whereDate($column, '>=', date('Y-m-d'))->whereDate($column, '<=', date('Y-m-d'));
        }
        return $query;
    }



    public static function downloadcsvfile($data, $fileName)
        {
            header('Content-Type: text/csv; charset=utf-8');
            header("Content-Disposition: attachment; filename=$fileName");
            $fp = fopen('php://output', 'w');
            foreach ($data as $row) {
                fputcsv($fp, $row);
                ob_flush();
                flush(); // Push output to browser
            }
            fclose($fp);
            exit;
        }  


	public static function sendSms($to, $message)
	{
		try {
			$sid = config('services.twilio.sid');
			$token = config('services.twilio.token');
			$from = config('services.twilio.from');

			$client = new Client($sid, $token);

			$client->messages->create($to, [
				'from' => $from,
				'body' => $message,
			]);

			Log::info("✅ SMS sent to {$to}: {$message}");
			return ['success' => true, 'message' => 'SMS sent successfully'];
		} catch (\Exception $e) {
			Log::error("❌ Twilio SMS Error: " . $e->getMessage());
			return ['success' => false, 'message' => $e->getMessage()];
		}
	}
    public static function route_exists(string $name): bool
    {
        return Route::has($name);
    }


}

//Settings::sendSms('+2348012345678', 'Hello! Your order has been confirmed.');
