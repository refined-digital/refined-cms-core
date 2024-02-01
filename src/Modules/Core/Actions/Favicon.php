<?php

namespace RefinedDigital\CMS\Modules\Core\Actions;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class Favicon
{
    public function __invoke()
    {
        $url = request()->segment(1);

        $filePath = $this->findIcon($url);

        if (!$filePath) {
            if (help()->isMultiTenancy()) {
                $url = 'icons/'.$url;
            }
            $filePath = public_path($url);
        }

        return response()->file($filePath);
    }

    protected function findIcon(string $url) : string
    {
        $file = settings()->getByKeyCode('[settings:site-settings:favicon]', true);

        if (!$file) {
            $path = $url;
            if (help()->isMultiTenancy()) {
                $path = 'icons/'.$path;
            }
            return public_path($path);
        }

        // find the directory
        $dir = storage_path('app/public/uploads/');
        if (help()->isMultiTenancy()) {
            $dir = storage_path('uploads/');
        }
        $dir .= $file->id.'/icons/';

        if (file_exists($dir)) {
            return $dir.$url;
        }

        // todo: update this to send base64_encoded image, rather than the url
        // $path = $dir.$file->file;
        $path = $file->link->original;
        $this->generateIcons($path, $dir);


        if (file_exists($dir)) {
            return $dir.$url;
        }
        return '';
    }

    protected function generateIcons(string $path, string $dir) : void
    {
        // todo: update this to use inline mode
        /*$masterPicture = [
            'type' => 'inline',
            'content' => base64_encode($path),
        ];*/

        $masterPicture = [
            'type' => 'url',
            'url' => env('APP_ENV') === 'local' ? 'https://placekitten.com/300/300' : $path,
        ];

        $apiKey = '0a633d8e5b8e2498eab9e4d5c1a3dff4ea7e48bb';
        $body = [
            'favicon_generation' => [
                'api_key' => $apiKey,
                'master_picture' => $masterPicture,
                'files_location' => [
                    'type' => 'root',
                ],
                'favicon_design' => [
                    'desktop_browser' => [],
                    'ios' => [
                        'picture_aspect' => 'background_and_margin',
                        'margin' => '4',
                        'background_color' => '#ffffff',
                        'assets' => [
                            'ios6_and_prior_icons' => false,
                            'ios7_and_later_icons' => false,
                            'precomposed_icons' => false,
                            'declare_only_default_icon' => true,
                        ],
                    ],
                    'android_chrome' => [
                        'picture_aspect' => 'background_and_margin',
                        'manifest' => [
                            'name' => config('app.name'),
                            'display' => 'standalone',
                        ],
                        'assets' => [
                            'legacy_icon' => false,
                            'low_resolution_icons' => false,
                        ],
                        'theme_color' => '#ffffff',
                    ],
                    'windows' => [
                        'picture_aspect' => 'no_change',
                        'background_color' => '#ffffff',
                        'app_name' => config('app.name'),
                        'assets' => [
                            'windows_10_ie_11_edge_tiles' => [
                                'small' => false,
                                'medium' => true,
                                'big' => false,
                                'rectangle' => false,
                            ],
                        ],
                    ],
                    'safari_pinned_tab' => [
                        'picture_aspect' => 'silhouette',
                        'threshold' => 60,
                        'theme_color' => '#5bbad5',
                    ],
                ],
                'settings' => [
                    'compression' => '0',
                    'scaling_algorithm' => 'Mitchell',
                    'error_on_image_too_small' => false,
                    'readme_file' => false,
                    'html_code_file' => false,
                    'use_path_as_is' => false,
                ],
                'versioning' => [
                    'param_name' => 'ver',
                    'param_value' => '15Zd8',
                ],
            ],
        ];

        $client = new Client();
        try {
            $response = $client->post('https://realfavicongenerator.net/api/favicon', [
                'json' => $body,
            ]);

            $data = json_decode($response->getBody()->getContents());

            if (isset($data->favicon_generation_result->favicon->package_url)) {
                $id = uniqid().'.zip';
                $fileName = storage_path($id);
                file_put_contents($fileName, file_get_contents($data->favicon_generation_result->favicon->package_url));

                $zip = new \ZipArchive();
                $res = $zip->open($fileName);
                if ($res === TRUE) {
                    if (!is_dir($dir)) {
                        mkdir($dir);
                    }
                    $zip->extractTo($dir);
                    $zip->close();
                }

                unlink($fileName);
            }
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
        }
    }
}