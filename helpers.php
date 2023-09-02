<?php

/**
 * Movos = The php Script for Saksake
 */

use GeoIp2\Database\Reader;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;

define('APPEND_TO_RESPONSE', 'videos,credits,images,external_ids,release_dates,recommendations,similar,keywords');
define('PEOPLE_APPEND_TO_RESPONSE', 'movie_credits,tv_credits,combined_credits,images,tagged_images');

if (!function_exists('currentTheme')) {
    function currentTheme() {
        return env('MOVOS_THEME');
    }
}

if (!function_exists('theme_path')) {
    function theme_path() {
        return realpath(public_path('themes/'.currentTheme()));
    }
}

if (! function_exists('asset_theme')) {
    function asset_theme($path, $secure = null) {
        return asset('/themes/'.currentTheme().'/'.$path, $secure);
    }
}

if (! function_exists('genre_comma')) {
    function genre_comma(array $genres) {
        $genres_list = [];

        foreach ($genres as $data) {
            $genres_list[] = '<a class="link-theme" href="'.route('genre', ['id' => $data->id, 'slug' => Str::slug($data->name)]).'" title="'.$data->name.'">'.$data->name.'</a>';
        }

        return implode(', ',$genres_list);
    }
}

if (! function_exists('star_comma')) {
    function star_comma(array $star, int $count = 5) {
        $stars = [];

        foreach (collect($star)->take($count) as $data) {
            $stars[] = '<a class="link-theme" href="'.route('people.single', ['id' => $data->id]).'" title="'.$data->name.'">'.$data->name.'</a>';
        }

        return implode(', ',$stars);
    }
}

if (! function_exists('director_comma')) {
    function director_comma(array $director, int $count = 5) {
        $directors = [];

        foreach (collect($director)->take($count) as $data) {
            $directors[] = '<a class="link-theme" href="'.route('people.single', ['id' => $data->id]).'" title="'.$data->name.'">'.$data->name.'</a>';
        }

        return implode(', ',$directors);
    }
}

if (! function_exists('view_path')) {
    function view_path() {
        if (request()->is('admin*') || app()->runningInConsole()) {
            return resource_path('views');
        }else{
            return theme_path();
        }
    }
}

if (! function_exists('to_year')) {
    function to_year($release_date) {
        if (! empty($release_date)) {
            return Str::before($release_date, '-');
        }

        return '';
    }
}

if (! function_exists('img_backdrop')) {
    function img_backdrop($path, $size = 'w780')
    {
        if (empty($path)) {
            return asset('assets/no-backdrop.png');
        }
        return '//image.tmdb.org/t/p/'.$size.$path;
    }
}

if (! function_exists('img_poster')) {
    function img_poster($path, $size = 'w300') {
        if (empty($path) || is_null($path)) {
            return asset('assets/no-poster.png');
        }

        return '//image.tmdb.org/t/p/'.$size.$path;
    }
}

if (! function_exists('getTvShows')) {
    function getTvShows($query, $page = 1) {
        if (conf('is_cache')) {
            $key = 'tv'.$query.$page.app()->getLocale();
            $results = Cache::remember($key, conf('cache_exp'), function () use ($query, $page) {
                return fetch("/tv/{$query}", ['page' => $page]);
            });
        }else{
            $results = fetch("/tv/{$query}", ['page' => $page]);
        }

        return block_id($results);
    }
}

if (! function_exists('getTvSeasonEpisode')) {
    function getTvSeasonEpisode($id, $season_number, $episode)
    {
        if (conf('is_cache')) {
            $key = $id.$season_number.$episode.app()->getLocale();
            $results = Cache::remember($key, conf('cache_exp'), function () use ($id, $season_number, $episode) {
                return fetch("/tv/{$id}/season/{$season_number}/episode/{$episode}", [
                'append_to_response' => APPEND_TO_RESPONSE,
                'include_image_language' => 'en'
            ]);
            });
        }else{
            $results = fetch("/tv/{$id}/season/{$season_number}/episode/{$episode}", [
                'append_to_response' => APPEND_TO_RESPONSE,
                'include_image_language' => 'en'
            ]);
        }

        return $results;
    }
}

if (! function_exists('getTvSeason')) {
    function getTvSeason($id, $season_number)
    {
        if (conf('is_cache')) {
            $key = $id.$season_number.app()->getLocale();
            $results = Cache::remember($key, conf('cache_exp'), function () use ($id, $season_number) {
                return fetch("/tv/{$id}/season/{$season_number}", [
                'append_to_response' => APPEND_TO_RESPONSE,
                'include_image_language' => 'en'
            ]);
            });
        }else{
            $results = fetch("/tv/{$id}/season/{$season_number}", [
                'append_to_response' => APPEND_TO_RESPONSE,
                'include_image_language' => 'en'
            ]);
        }

        return $results;
    }
}

if (! function_exists('getTvShow')) {
    function getTvShow($id)
    {
        if (conf('is_cache')) {
            $key = $id.app()->getLocale();
            $results = Cache::remember($key, conf('cache_exp'), function () use ($id) {
                return fetch("/tv/{$id}", [
                'append_to_response' => APPEND_TO_RESPONSE,
                'include_image_language' => 'en'
            ]);
            });
        }else{
            $results = fetch("/tv/{$id}", [
                'append_to_response' => APPEND_TO_RESPONSE,
                'include_image_language' => 'en'
            ]);
        }

        return $results;
    }
}

if (! function_exists('getMovie')) {
    function getMovie($id)
    {
        if (conf('is_cache')) {
            $key = $id.app()->getLocale();
            $results = Cache::remember($key, conf('cache_exp'), function () use ($id) {
                return fetch("/movie/{$id}", [
                'append_to_response' => APPEND_TO_RESPONSE,
                'include_image_language' => 'en'
            ]);
            });
        }else{
            $results = fetch("/movie/{$id}", [
                'append_to_response' => APPEND_TO_RESPONSE,
                'include_image_language' => 'en'
            ]);
        }

        return $results;
    }
}

if (! function_exists('getMovies')) {
    function getMovies($query, $page = 1)
    {
        if (conf('is_cache')) {
            $key = $query.$page.app()->getLocale();
            $results = Cache::remember($key, conf('cache_exp'), function () use ($query, $page) {
                return fetch("/movie/{$query}", ['page' => $page]);
            });
        }else{
            $results = fetch("/movie/{$query}", ['page' => $page]);
        }

        return block_id($results);
    }
}

if (! function_exists('getSearch')) {
    function getSearch($query, $page = 1)
    {
        return block_id(fetch("/search/multi", ['query' => $query, 'page' => $page]));
    }
}

if (! function_exists('getMovieByGenre') ) {
    function getMovieByGenre($genre_id, $page = 1)
    {
        if (conf('is_cache')) {
            $key = 'discover_movie'.$genre_id.$page.app()->getLocale();
            $results = Cache::remember($key, conf('cache_exp'), function () use ($page, $genre_id) {
                return fetch("/discover/movie", ['page' => $page, 'with_genres' => $genre_id]);
            });
        }else{
            $results = fetch("/discover/movie", ['page' => $page, 'with_genres' => $genre_id]);
        }

        return block_id($results);
    }
}

if (! function_exists('getPeoplePopular') ) {
    function getPeoplePopular($page = 1)
    {
        if (conf('is_cache')) {
            $key = 'people-popular'.$page.app()->getLocale();
            $results = Cache::remember($key, conf('cache_exp'), function () use ($page) {
                return fetch("/person/popular", ['page' => $page]);
            });
        }else{
            $results = fetch("/person/popular", ['page' => $page]);
        }

        return block_id($results);
    }
}

if (! function_exists('getPeople') ) {
    function getPeople($id)
    {
        if (conf('is_cache')) {
            $key = 'people'.$id.app()->getLocale();
            $results = Cache::remember($key, conf('cache_exp'), function () use ($id) {
                return fetch("/person/{$id}", [
                    'append_to_response' => PEOPLE_APPEND_TO_RESPONSE,
                    'include_image_language' => 'en'
                ]);
            });
        }else{
            $results = fetch("/person/{$id}", [
                'append_to_response' => PEOPLE_APPEND_TO_RESPONSE,
                'include_image_language' => 'en'
            ]);
        }

        return $results;
    }
}

if (! function_exists('block_id')) {
    function block_id($results) {
        $results->results = collect($results->results)->whereNotIn('id', conf('block_id'))->all();

        return $results;
    }
}

if (! function_exists('getGenreLists')) {
    function getGenreLists($media) {
        if (conf('is_cache')) {
            $key = $media.app()->getLocale();
            $results = Cache::remember($key, conf('cache_exp'), function () use ($media) {
                return fetch("/genre/{$media}/list");
            });
        }else{
            $results = fetch("/genre/{$media}/list");
        }

        return $results->genres;
    }
}

if (! function_exists('fetch')) {
    function fetch($url, $params = []) {
        $query = [
            'api_key' => conf('tmdb_key'),
            'language' => app()->getLocale(),
            'include_adult' => false
        ];

        if(!empty($params)) {
            $query = Arr::collapse([$query, $params]);
        }

        return Http::get('https://api.themoviedb.org/3'.$url, $query)->object();
    }
}

if (! function_exists('get_country_code')) {
    function get_country_code() {
        $ip = request()->ip();

        if ( $ip !== '127.0.0.1' ) {
            $reader = new Reader(__DIR__.'/geo-country.mmdb');
            $record = $reader->country($ip);

            return strtolower($record->country->isoCode);
        }

        return false;
    }
}

if (! function_exists('conf')) {
    function conf($key)
    {
        return config('nanosia.'.$key);
    }
}
