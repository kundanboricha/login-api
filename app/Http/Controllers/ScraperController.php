<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
// use App\HouseMigrateData;
use App\Models\HouseMigrateData;


class ScraperController extends Controller
{

    public function viewdata()
    {
        return view('data.app');
    }

    public function index(Request $request)
    {
        // Get the first 5 houses
        $houses = HouseMigrateData::take(5)->get();
    
        foreach ($houses as $house) {
            // Fetch the HTML content from the URL
            $response = Http::get($house->URL);
    
            if ($response->failed()) {
                return response()->json(['error' => 'Failed to fetch URL'], 500);
            }
    
            // Parse the HTML content to extract img tags
            $htmlContent = $response->body();
            $this->parseImgTags($htmlContent, $house->Ref_ID);
        }
    
        // Optionally, you can return a response when all houses are processed.
        return response()->json(['message' => 'Images fetched and stored successfully.']);
    }
    
    private function parseImgTags($htmlContent, $Ref_ID)
    {
        // Use a DOMDocument to parse the HTML
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($htmlContent);
        libxml_clear_errors();
    
        // Find all img tags and extract their src attributes
        $imgElements = $doc->getElementsByTagName('img');
    
        foreach ($imgElements as $imgElement) {
            $src = $imgElement->getAttribute('src');
    
            // Check if $src is an absolute URL
            if (filter_var($src, FILTER_VALIDATE_URL)) {
                // Download and save the image

                if (basename($src) === 'logo-2018.jpg') {
                    continue;
                }
                
                $imageContents = file_get_contents($src);
    
                if ($imageContents !== false) {
                    // Define the directory path
                    $directory = storage_path('app/public/images/' . $Ref_ID . '/');
    
                    // Create the directory if it doesn't exist
                    if (!file_exists($directory)) {
                        mkdir($directory, 777, true);
                    }
    
                    // Set the image path
                    $imagePath = $directory . basename($src);
    
                    // Save the image to the specified path
                    file_put_contents($imagePath, $imageContents);
    
                    // Insert image details into the database
                    DB::table('house_images')->insert([
                        'image' => $imagePath,
                        'house_ref_id' => $Ref_ID,
                        'original_image' => $src,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

        }   
