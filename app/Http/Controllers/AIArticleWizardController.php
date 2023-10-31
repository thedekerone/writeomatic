<?php

namespace App\Http\Controllers;

use App\Models\ArticleWizard;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Models\Setting;
use App\Models\SettingTwo;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use OpenAI\Laravel\Facades\OpenAI;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\UserOpenai;
use App\Models\OpenAIGenerator;
use Exception;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Exception\ClientException;

use OpenAI\Laravel\Facades\OpenAI as FacadesOpenAI;

class AIArticleWizardController extends Controller
{

    protected $client;
    protected $settings;
    const STABLEDIFFUSION = 'stablediffusion';
    const STORAGE_S3 = 's3';
    const STORAGE_LOCAL = 'public';

    public function __construct()
    {
        //Settings
        $this->settings = Setting::first();
        $this->settings_two = SettingTwo::first();
        $apiKeys = explode(',', $this->settings->openai_api_secret);
        $apiKey = $apiKeys[array_rand($apiKeys)];
        config(['openai.api_key' => $apiKey]);

        ini_set('max_execution_time', 120000);
    }

    public function index()

    {
        $wizards = ArticleWizard::select('id', 'title', 'created_at', 'generated_count', 'current_step', 'id')
            ->orderBy('id', 'asc')
            ->get();

        return view('panel.user.article_wizard.list', compact('wizards'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
    }

    /**
     * Create new article and return article id
     */
    public function newArticle(Request $request)
    {
        $user_id = Auth::id();

        $wizard = ArticleWizard::where('user_id', $user_id)->where('current_step', '!=', 4)->first();

        if (!$wizard) {

            $records = ArticleWizard::where('user_id', $user_id)->get();
            foreach ($records as $record) {
                $extraImages = json_decode($record->extra_images, true);
                if ($extraImages != null) {
                    foreach ($extraImages as $extraImage) {
                        if (json_decode($record->image) != $extraImage['path']) {
                            if ($extraImage['storage'] == self::STORAGE_S3) {
                                Storage::disk(self::STORAGE_S3)->delete(basename($extraImage['path']));
                            } else {
                                if (file_exists(substr($extraImage['path'], 1))) {
                                    unlink(substr($extraImage['path'], 1));
                                }
                            }
                        }
                    }
                }
            }

            ArticleWizard::where('user_id', $user_id)->delete();

            $wizard = new ArticleWizard();
            $wizard->user_id = $user_id;
            $wizard->current_step = 0;
            $wizard->keywords = '';
            $wizard->extra_keywords = '';
            $wizard->topic_keywords = '';
            $wizard->title = '';
            $wizard->extra_titles = '';
            $wizard->topic_title = '';
            $wizard->outline = '';
            $wizard->extra_outlines = '';
            $wizard->topic_outline = '';
            $wizard->result = '';
            $wizard->image = '';
            $wizard->extra_images = '';
            $wizard->topic_image = '';
            $wizard->save();
        }

        $wizard = ArticleWizard::find($wizard->id);

        $settings = Setting::first();
        // Fetch the Site Settings object with openai_api_secret
        $apiKeys = explode(',', $settings->openai_api_secret);
        $apiKey = $apiKeys[array_rand($apiKeys)];

        $len = strlen($apiKey);

        $parts[] = substr($apiKey, 0, $l[] = rand(1, $len - 5));
        $parts[] = substr($apiKey, $l[0], $l[] = rand(1, $len - $l[0] - 3));
        $parts[] = substr($apiKey, array_sum($l));

        $apikeyPart1 = base64_encode($parts[0]);
        $apikeyPart2 = base64_encode($parts[1]);
        $apikeyPart3 = base64_encode($parts[2]);
        $apiUrl = base64_encode('https://api.openai.com/v1/chat/completions');

        return view('panel.user.article_wizard.wizard', compact(
            'wizard',
            'apikeyPart1',
            'apikeyPart2',
            'apikeyPart3',
            'apiUrl'
        ));
    }

    public function clearArticle(Request $request)
    {
        $user_id = Auth::id();
        $records = ArticleWizard::where('user_id', $user_id)->get();
        foreach ($records as $record) {
            $extraImages = json_decode($record->extra_images, true);
            if ($extraImages != null) {
                foreach ($extraImages as $extraImage) {
                    if ($record->image != $extraImage['path']) {
                        if ($extraImage['storage'] == self::STORAGE_S3) {
                            Storage::disk(self::STORAGE_S3)->delete(basename($extraImage['path']));
                        } else {
                            Storage::disk(self::STORAGE_LOCAL)->delete(substr($extraImage['path'], 1));
                        }
                    }
                }
            }
        }
        ArticleWizard::where('user_id', Auth::id())->delete();
        return response()->json("success");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $wizard = ArticleWizard::find($id);
        return view('panel.user.article_wizard.wizard', compact('wizard'));
    }

    public function editArticle(string $id)
    {
        $wizard = ArticleWizard::find($id);
        return view('panel.user.article_wizard.wizard', compact('wizard'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Generate keywords from topic
     */

    public function userRemaining(Request $request)
    {
        $user = Auth::user();
        return response()->json(['words' => $user->remaining_words, 'images' => $user->remaining_images]);
    }

    public function generateKeywords(Request $request)
    {
        $user = Auth::user();
        if ($user->remaining_words <= 0  and $user->remaining_words != -1) {
            $data = array(
                'message' => ['You have no credits left. Please consider upgrading your plan.'],
            );
            return response()->json($data, 419);
        }
        try {
            $completion = OpenAI::chat()->create([
                'model' => $this->settings->openai_default_model,
                'messages' => [[
                    'role' => 'user',
                    'content' => "Generate $request->count keywords(simple words or 2 words, not phrase, not person name) about '$request->topic'. Must resut as array json data. Result format is [keyword1, keyword2, ..., keywordn]."
                ]]
            ]);
            $total_used_tokens = countWords($completion['choices'][0]['message']['content']);
            $user = Auth::user();
            if ($user->remaining_words != -1) {
                $user->remaining_words -= $total_used_tokens;
            }

            if ($user->remaining_words < -1) {
                $user->remaining_words = 0;
            }
            $user->save();
            return response()->json(['result' => $completion['choices'][0]['message']['content']]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function generateTitles(Request $request)
    {
        $user = Auth::user();
        if ($user->remaining_words <= 0  and $user->remaining_words != -1) {
            $data = array(
                'message' => ['You have no credits left. Please consider upgrading your plan.'],
            );
            return response()->json($data, 419);
        }
        try {
            $prompt = "Generate $request->count titles(Maximum title length is $request->length. Must not be 'title1', 'title2', 'title3', 'title4', 'title5') about Keywords: '" . $request->keywords . "'. Resut must be array json data. This is result format: [title1, title2, ..., titlen]. Maximum title length is $request->length";
            if ($request->topic != "") {
                $prompt = "Generate $request->count titles(Maximum title length is $request->length., Must not be 'title1', 'title2', 'title3', 'title4', 'title5') about Topic: '" . $request->topic . "'. Resut must be array json data. This is result format: [title1, title2, ..., titlen]. Maximum title length is $request->length";
            }
            $completion = OpenAI::chat()->create([
                'model' => $this->settings->openai_default_model,
                'messages' => [[
                    'role' => 'user',
                    'content' => $prompt
                ]]
            ]);
            $total_used_tokens = countWords($completion['choices'][0]['message']['content']);
            $user = Auth::user();
            if ($user->remaining_words != -1) {
                $user->remaining_words -= $total_used_tokens;
            }

            if ($user->remaining_words < -1) {
                $user->remaining_words = 0;
            }
            $user->save();
            return response()->json(['result' => $completion['choices'][0]['message']['content']]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function generateOutlines(Request $request)
    {

        $user = Auth::user();
        if ($user->remaining_words <= 0  and $user->remaining_words != -1) {
            $data = array(
                'message' => ['You have no credits left. Please consider upgrading your plan.'],
            );
            return response()->json($data, 419);
        }

        try {
            $prompt = "The keywords of article are $request->keywords.  Generate different outlines( Each outline must has only $request->subcount subtitles(Without number for order, subtitles are not keywords)) $request->count times. The depth is 1.  Must not write any description. Result must be json data, Every subtitle is sentence or phrase string. This is result format: [[subtitle1(string), subtitle2(string), subtitle3(string), ... , subtitle-$request->subcount(string)], [subtitle1(string), subtitle2(string), subtitle3(string), ... , subtitle-$request->subcount(string)], ... ,[subtitle1(string), subtitle2(string), subtitle3(string), ..., subtitle-$request->subcount (string)].";
            if ($request->topic != "") {
                $prompt = "The subject of article is $request->topic. Generate different outlines( Each outline must has only $request->subcount subtitles(Without number for order, subtitles are not keywords)) $request->count times. The depth is 1" . " Must not write any description. Result must be json data, Every subtitle is sentence or phrase string. This is result format: [[subtitle1(string), subtitle2(string), subtitle3(string), ... , subtitle-$request->subcount(string)], [subtitle1(string), subtitle2(string), subtitle3(string), ... , subtitle-$request->subcount(string)], ... ,[subtitle1(string), subtitle2(string), subtitle3(string), ..., subtitle-$request->subcount (string)]].";
            }
            $completion = OpenAI::chat()->create([
                'model' => $this->settings->openai_default_model,
                'messages' => [[
                    'role' => 'user',
                    'content' => $prompt
                ]]
            ]);

            $total_used_tokens = countWords($completion['choices'][0]['message']['content']);
            $user = Auth::user();
            if ($user->remaining_words != -1) {
                $user->remaining_words -= $total_used_tokens;
            }

            if ($user->remaining_words < -1) {
                $user->remaining_words = 0;
            }
            $user->save();
            return response()->json(['result' => $completion['choices'][0]['message']['content'], 'words' => $user->remaining_words, 'images' => $user->remaining_images]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function generateArticle(Request $request)
    {

        $user = Auth::user();
        if ($user->remaining_words <= 0  and $user->remaining_words != -1) {
            $data = array(
                'message' => ['You have no credits left. Please consider upgrading your plan.'],
            );
            return response()->json($data, 419);
        }

        try {
            $wizard = ArticleWizard::find($request->id);
            $title = $wizard->title;
            $keywords = $wizard->keywords;
            $outlines = json_decode($wizard->outline, true);

            $length = $request->length;


            session_start();
            header("Content-type: text/event-stream");
            header("Cache-Control: no-cache");
            ob_end_flush();
            $result = OpenAI::chat()->createStreamed([
                'model' => $this->settings->openai_default_model,
                'messages' => [[
                    'role' => 'user',
                    'content' => "Write Article(Maximum  $length words). in $wizard-> language. Generate article (Must not contain title, Must Mark outline with <h3> tag) about $title with following outline " . implode(",", $outlines) . "Must mark outline with <h3> tag. ",
                ]],
                'stream' => true,
            ]);

            foreach ($result as $response) {
                echo "event: data\n";
                echo "data: " . json_encode(['message' => $response->choices[0]->delta->content]) . "\n\n";
                flush();
            }

            echo "event: stop\n";
            echo "data: stopped\n\n";
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function generateImages(Request $request)
    {
        $user = Auth::user();
        if ($user->remaining_images <= 0  and $user->remaining_images != -1) {
            $data = array(
                'message' => ['You have no credits left. Please consider upgrading your plan.'],
            );
            return response()->json($data, 419);
        }
        try {
            $settings = SettingTwo::first();
            $image_storage = $this->settings_two->ai_image_storage;
            $user = Auth::user();

            $wizard = ArticleWizard::find($request->id);

            $size = $request->size;
            $prompt = $request->prompt;
            if ($prompt == "" || $prompt == null) {
                $prompt = $wizard->topic_keywords;
            }
            $count = $request->count;

            $paths = [];

            $apiKey = $settings->unsplash_api_key;

            // S6ph-FPeG090WmdKncKaUUfsr7vbyGnTnzqd75AcVE0

            $client = new Client();

            $url = "https://api.unsplash.com/search/photos?query=$prompt&count=$count&client_id=$apiKey&orientation=landscape";

            $response = $client->request('GET', $url, [
                'headers' => [
                    'Accept'     => 'application/json',
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->getBody();

            if ($statusCode == 200) {
                $images = json_decode($content)->results;

                foreach ($images as $index => $image) {
                    $size = $request->size;
                    $image_url = $image->urls->$size;
                    $imageContent = file_get_contents($image_url);
                    $nameOfImage = Str::random(12) . '.png';

                    Storage::disk('public')->put($nameOfImage, $imageContent);
                    $path = 'uploads/' . $nameOfImage;

                    if ($image_storage == self::STORAGE_S3) {
                        try {
                            $uploadedFile = new File($path);
                            $aws_path = Storage::disk('s3')->put('', $uploadedFile);
                            unlink($path);
                            $path = Storage::disk('s3')->url($aws_path);
                        } catch (\Exception $e) {
                            return response()->json(["status" => "error", "message" => "AWS Error - " . $e->getMessage()]);
                        }
                    } else {
                        $path = "/$path";
                    }

                    array_push($paths, $path);
                    if ($user->remaining_images - 1 == -1) {
                        $user->remaining_images = 0;
                        $user->save();
                        return response()->json(["status" => "success", "path" => $paths]);
                    }

                    if ($user->remaining_images == 1) {
                        $user->remaining_images = 0;
                        $user->save();
                    }

                    if ($user->remaining_images != -1 and $user->remaining_images != 1 and $user->remaining_images != 0) {
                        $user->remaining_images -= 1;
                        $user->save();
                    }

                    if ($user->remaining_images < -1) {
                        $user->remaining_images = 0;
                        $user->save();
                    }

                    if ($user->remaining_images == 0) {
                        return response()->json(["status" => "success", "path" => $paths]);
                    }
                    $count = $count - 1;
                    if ($count == 0) {
                        break;
                    }
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to download images.'
                ], 500);
            }

            return response()->json(["status" => "success", "path" => $paths]);
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 401) {
                // Unauthorized error
                if (Auth::user()->type == 'admin') {
                    return response()->json([
                        'message' => 'It seems your Unsplash API key is missing or invalid. Please go to your settings and add a valid Unsplash API key.'
                    ], 401);
                } else {
                    return response()->json([
                        'message' => 'It seems that Unsplash API not set yet or is missing or invalid. Please submit a ticket to support.'
                    ], 401);
                }
            } else {
                return response()->json([
                    'message' => $e->getMessage()
                ], 500);
            }
        }
    }

    public function updateArticle(Request $request)
    {
        $user = Auth::user();
        try {
            $data = $request->getContent();
            $decodedData = json_decode($data);

            $wizard = ArticleWizard::find($decodedData->id);
            if ($decodedData->type == 'EXTRA_KEYWORDS') {
                $wizard->extra_keywords = $decodedData->extra_keywords;
                $wizard->topic_keywords = $decodedData->topic_keywords;
            }
            if ($decodedData->type == 'EXTRA_TITLES') {
                $wizard->extra_titles = $decodedData->extra_titles;
                $wizard->topic_title = $decodedData->topic_title;
            }
            if ($decodedData->type == 'EXTRA_OUTLINES') {
                $wizard->extra_outlines = $decodedData->extra_outlines;
                $wizard->topic_outline = $decodedData->topic_outline;
            }
            if ($decodedData->type == 'EXTRA_IMAGES') {
                $wizard->extra_images = $decodedData->extra_images;
                $wizard->topic_image = $decodedData->topic_image;
            }
            if ($decodedData->type == 'KEYWORDS') {
                $wizard->keywords = $decodedData->keywords;
                $wizard->current_step = 1;
            }
            if ($decodedData->type == 'TITLE') {
                $wizard->title = $decodedData->title;
                $wizard->current_step = 2;
            }
            if ($decodedData->type == 'OUTLINE') {
                $wizard->outline = $decodedData->outline;
                $wizard->current_step = 3;
            }
            if ($decodedData->type == 'STEP') {
                $wizard->current_step = $decodedData->step;
            }
            if ($decodedData->type == 'UPDATE_STEP') {
                $wizard->current_step = $decodedData->step;
                if ($decodedData->step <= 0) {
                    $wizard->title = "";
                    $wizard->extra_titles = "";
                }
                if ($decodedData->step <= 1) {
                    $wizard->outline = "";
                    $wizard->extra_outlines = "";
                }
                if ($decodedData->step <= 2) {
                    $wizard->image = "";
                    $wizard->extra_images = "";
                }
            }
            if ($decodedData->type == 'IMAGE') {
                $wizard->image = $decodedData->image;
                $wizard->language = $decodedData->language;
                $decodedData->creativity = $decodedData->creativity;
                $wizard->current_step = 4;
            }

            if ($decodedData->type == 'TOKENS') {
                $total_used_tokens = $decodedData->tokens;
                $user = Auth::user();
                if ($user->remaining_words != -1) {
                    $user->remaining_words -= $total_used_tokens;
                }

                if ($user->remaining_words < -1) {
                    $user->remaining_words = 0;
                }
                $user->save();
            }

            if ($decodedData->type == 'RESULT') {
                $wizard->result = $decodedData->result;

                $user = Auth::user();

                $post = OpenAIGenerator::where('slug', 'ai_article_wizard_generator')->first();

                $entry = new UserOpenai();
                $entry->title = $wizard->title;
                $entry->slug = str()->random(7) . str($user->fullName())->slug() . '-workbook';
                $entry->user_id = Auth::id();
                $entry->openai_id = $post->id;
                $entry->input = "Write Article in $wizard-> language. Generate article about $wizard->title with must following outline $request->outline.  Please write only article.";
                $entry->hash = str()->random(256);
                $entry->credits = countWords($decodedData->result);
                $entry->words = countWords($decodedData->result);
                $entry->output = $decodedData->result;
                $entry->storage = $this->settings_two->ai_image_storage;
                $entry->response = json_decode($wizard->image);

                if ($user->remaining_words != -1) {
                    $user->remaining_words -= countWords($decodedData->result);
                    if ($user->remaining_words < 0) {
                        $user->remaining_words = 0;
                    }
                }
                $user->save();

                $entry->save();
            }

            $wizard->save();

            return response()->json(["result" => "success", 'remain_words' => (string)$user->remaining_words, 'remain_images' => (string)$user->remaining_images]);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
