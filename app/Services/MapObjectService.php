<?php

namespace App\Services;

use App\Models\MapObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use AllowDynamicProperties;
use DOMDocument;
use DOMXPath;
use stdClass;

#[AllowDynamicProperties]
class MapObjectService {
    protected string $url = "";
    protected int $user_id = 0;
    private string $yandex_domen = "https://yandex.ru";
    private string $api_request_header = "https://yandex.ru/maps/api/business/fetchReviews";
    private string $widget_request_header = "https://yandex.ru/maps-reviews-widget/";
    private string $widget_request_suffix = "?size=x";
    private string $api_request_parameters = "";
    private string $api_request_full = "";
    private string $widget_request_full = "";
    protected string $locale = "ru_RU";
    protected int $page_id = 1;
    protected int $page_size = 50;
    protected string $api_csrf_token = "";
    private int $yandex_parameters_hash = 0;
    protected $response = null;
    private $cookies = null;

    public function __construct()
    {
        $this->cookies = new \GuzzleHttp\Cookie\CookieJar();
        $this->object_result = new stdClass();
        $this->object_result->success = false;
        $this->object_result->business_id = 0;
        $this->object_result->business_title = '';
        $this->object_result->rating = "";
        $this->object_result->reviews_count = 0;
        $this->object_result->ratings_count = 0;
        $this->object_result->errors = null;
    }

    // Получаем отзывы из API Яндекса
    public function get_reviews(Request $request)
    {
        $this->object_result->business_id = $request->business_id;
        $this->page_id = $request->page_id;

        if (!$this->http_get_widget()) {
            return response()->json($this->object_result);
        }

        if(!$this->http_get_csrf_token()) {
            return response()->json($this->object_result);
        }

        $this->http_get_api_reviews();

        return response()->json($this->object_result);
    }

    // Проверка URL-ссылки и получение ID объекта на карте (business_id)
    public function check_url(Request $request)
    {
        //Log::debug("request = " . $request);
        //Log::debug("request->reviews_url = " . $request->reviews_url);

        $validator = Validator::make($request->all(), [ 'reviews_url' => 'required|url|active_url' ]);
        if ($validator->fails()) {
            $this->object_result->errors = $validator->errors();
            return response()->json($this->object_result);
        }

        $this->url = urldecode($request->reviews_url);
        $this->user_id = $request->user_id;

        $this->response = Http::withOptions([
            'verify' => false,
            'allow_redirects' => false,
            'crypto_method' => null,
        ])->get($this->url);

        if ($this->response->redirect()) {
            $this->redirect_processing();
        }

        $this->parse_business_id();
        if ($this->object_result->business_id < 1) {
            $this->object_result->errors = ['reviews_url' => ['Not valid Yandex map URL!']];
            return response()->json($this->object_result);
        }

        if ($this->http_get_widget()) {
            $this->store();
            $this->object_result->success = true;
        } else {
            $this->store();
        }
        return response()->json($this->object_result);
    }
    // Добываем business_id объекта из URL-ссылки
    private function parse_business_id(): void
    {
        // ссылка, в которой business_id задан как параметр (oid=11111111111&)
        $matches = [];
        preg_match("/oid=(\d+)&/", $this->url, $matches);
        if (count($matches) > 1) {
            $this->object_result->business_id = (int)$matches[1];
            return;
        }
        // ссылка, в которой business_id задан как часть адреса (yandex.ru/maps/org/nazvanie_mesta/11111111/)
        $matches = [];
        preg_match("/\/(\d+)\//", $this->url, $matches);
        if (count($matches) > 1) {
            $this->object_result->business_id = (int)$matches[1];
            return;
        }

        //Log::alert($this->object_result->business_id);
    }

    // Короткая ссылка вида https://yandex.ru/maps/-/CTE6rP62
    // При редиректе узнаем конечный адрес, запросив информацию из заголовка
    private function redirect_processing()
    {
        $this->url = $this->response->header('Location');
        $this->url = urldecode($this->url);
        // Если в начале нет названия домена (у коротких ссылок он отсутствует), то добавляем
        if (!(stripos($this->url, $this->yandex_domen) === 0)) {
            $this->url = $this->yandex_domen . $this->url;
        }
//    Log::debug("redirectUrl_new = " . $this->url);
    }
    // Основную информацию об объекте (название, рейтинг, количество отзывов и оценок) узнаём методом парсинга HTML виджета объекта
    // https://yandex.ru/maps-reviews-widget/1111111111111?size=x
    private function http_get_widget(): bool
    {
        $this->widget_request_full = "{$this->widget_request_header}{$this->object_result->business_id}{$this->widget_request_suffix}";
//        Log::alert($this->widget_request_full);

        $this->response = Http::withOptions([
            'verify' => false,
            'allow_redirects' => false,
            'crypto_method' => null,
        ])->get($this->widget_request_full);

//        Log::error($this->response->status());

        if (!$this->response->ok()) {
            $this->object_result->errors = ['reviews_url' => ['Error load Yandex map URL!']];
            return false;
        }

        $dom = new DomDocument();
        if (!@$dom->loadHTML($this->response)) {
            $this->object_result->errors = ['reviews_url' => ['Error parse Yandex map HTML!']];
            return false;
        }

        $xpath = new DomXPath($dom);
        $this->object_result->business_title = $xpath->query(".//a[@class='mini-badge__org-name']")->item(0)->textContent ?? null;
        // Название
        if (is_null($this->object_result->business_title)) {
            $this->object_result->errors = ['reviews_url' => ['Error parse Yandex map object title!']];
            return false;
        }
        $rating = $xpath->query(".//p[@class='mini-badge__stars-count']")->item(0)->textContent ?? null;
        // Рейтинг (может отсутствовать, если никто не голосовал)
        if (!is_null($rating)) {
            $this->object_result->rating = $rating;
        }

        $info_string = $xpath->query("..//a[@class='mini-badge__rating']")->item(0)->textContent ?? "";
        $matches = [];
        preg_match_all('/\d+/', $info_string, $matches);
        if (count($matches) > 0) {
            $matches = $matches[0];
            if (count($matches) === 1) {
                $this->object_result->ratings_count = $matches[0];
                // Количество голосов (может отсутствовать)
            } else if (count($matches) === 2) {
                $this->object_result->reviews_count = $matches[0];
                // Количество отзывов (может отсутствовать)
                $this->object_result->ratings_count = $matches[1];
            }
        }
//        Log::alert(json_encode($this->object_result, JSON_FORCE_OBJECT | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return true;
    }
    // Сохраняем данные объекта в БД (для нераспознанной ссылки - только её URL)
    private function store(): void
    {
        $update_fields = ['reviews_url' => $this->url];
        if ($this->object_result->business_id > 0) {
            $update_fields['business_id'] = $this->object_result->business_id;
            $update_fields['business_title'] = $this->object_result->business_title;
            $update_fields['rating'] = $this->object_result->rating;
            $update_fields['ratings_count'] = $this->object_result->ratings_count;
            $update_fields['reviews_count'] = $this->object_result->reviews_count;
        }

        MapObject::updateOrCreate(
            ['user_id' => $this->user_id],
            $update_fields
        );
    }
    // Для формирования запроса к API запрашиваем CSRF-токен сеанса
    private function http_get_csrf_token(): bool {
        $this->response = Http::withOptions([
            'verify' => false,
            'allow_redirects' => true,
            'cookies' => $this->cookies,
            'crypto_method' => null,
        ])->get($this->api_request_header);

        if (!$this->response->ok()) {
            $this->object_result->errors = ['http_get_csrf_token' => ['Error get CSRF token!']];
            return false;
        }

        $this->api_csrf_token = $this->response->json('csrfToken') ?? '';
//        Log::debug("csrf_token = " . $this->api_csrf_token);
        if ($this->api_csrf_token !== '') {
            return true;
        } else {
            $this->object_result->errors = ['http_get_csrf_token()' => ['Error found CSRF node!']];
            return false;
        }
    }
    // Получаем отзывы с определённой страницы (не больше 50, ограничение Яндекса)
    private function http_get_api_reviews(): bool
    {
        $this->api_csrf_token = urlencode($this->api_csrf_token);
        $this->api_request_parameters = "ajax=1&businessId={$this->object_result->business_id}&csrfToken={$this->api_csrf_token}&locale={$this->locale}&page={$this->page_id}&pageSize={$this->page_size}";
//        Log::alert($this->api_request_parameters);
        $this->yandex_parameters_hash = $this->hashFunction($this->api_request_parameters);
        $this->api_request_full = "{$this->api_request_header}?{$this->api_request_parameters}&s={$this->yandex_parameters_hash}";
//        Log::alert($this->api_request_full);
        $this->response = Http::withOptions([
            'verify' => false,
            'allow_redirects' => false,
            'cookies' => $this->cookies,
            'crypto_method' => null,
        ])->get($this->api_request_full);
        if (!$this->response->ok()) {
            $this->object_result->errors = ['http_get_api_reviews()' => ['Request does not return a code of 200']];
            return false;
        }

        $json = json_decode($this->response);
        $this->object_result->params = $json->data->params;
        $this->object_result->aspects = $json->data->aspects;

        $reviews = [];
        foreach ($json->data->reviews as $k=>$v) {
            $reviews[$k] = array(
                "text" => $json->data->reviews[$k]->text,
                "rating" => $json->data->reviews[$k]->rating,
                "updatedTime" => $json->data->reviews[$k]->updatedTime,
                "author_name" => $json->data->reviews[$k]->author->name ?? '',
                "author_level" => $json->data->reviews[$k]->author->professionLevel ?? ''
            );
        }
        $this->object_result->reviews = $reviews;
        $this->object_result->success = true;
        return true;
//        Log::alert($this->response);
    }

// Контрольная сумма строки всех параметров запроса Yandex-ApI
    private function hashFunction($e): int {
        $t = mb_strlen($e);
        $n = 5381;
        for ($r = 0; $r < $t; $r++) {
            $n = (33 * $n) ^ ord($e[$r]);
            if ($n > 4294967295) {
                $n = $n % 4294967296;
            }
        }
        return $n;
    }
}
