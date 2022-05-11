<?php

namespace RefinedDigital\CMS\Modules\Core\Helpers;

use Illuminate\Http\Response;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use RefinedDigital\CMS\Modules\Pages\Aggregates\PageAggregate;
use RefinedDigital\CMS\Modules\Pages\Models\Page;
use \ProtoneMedia\LaravelCrossEloquentSearch\Search;

class RefinedSearch {
    protected $keyword;
    protected $singleWord = false;
    protected $pregStart = '/';
    protected $pregEnd = '/i';
    protected $snippetLength = 300;
    protected $snippetOffset = 10;
    protected $hasPaging = false;
    protected $searchTerms = [];
    protected $terms = ['q', 'query', 'keywords'];

    public function __construct()
    {
        $term = null;
        foreach ($this->terms as $t) {
            if (!$term && request()->has($t)) {
                $term = request()->get($t);
            }
        }
        $this->keyword = $term;
    }


    public function getResults($highlightType = 'keyword')
    {
        if (!$this->keyword) {
            return collect([]);
        }


        if ($highlightType === 'full') {
            $this->pregStart = '/\w*?';
            $this->pregEnd = '\w*/i';
        }

        $search = Search::new()
             ->beginWithWildcard()
             ->orderByRelevance()
        ;

        // multi word search
        if (request()->has('singleWord')) {
            $search->dontParseTerm();
            $this->singleWord = true;
        }

        if (request()->has('perPage')) {
            $search->paginate((int) request()->get('perPage'));
            $this->hasPaging = true;
        }

        $searchableModels = array_map('unserialize', array_unique(array_map('serialize', config('searchable-models'))));
        foreach ($searchableModels as $model) {
            $c = $model['class']::query();
            $c->whereActive(1);
            $search->add($c, $model['terms']);
            $this->searchTerms = array_merge($model['terms'], $this->searchTerms);
        }

        $this->searchTerms = array_unique($this->searchTerms);

        if (str_starts_with($this->keyword, '"') && str_ends_with($this->keyword, '"')) {
            $this->singleWord = true;
        }

        $results = $search->search($this->keyword);

        $data = collect([]);
        if ($results->count()) {
            $baseUrl = rtrim(config('app.url'), '/');
            foreach ($results as $index => $result){
                $item = new \stdClass();
                $item->type = class_basename($result);
                $item->name = $this->highlight($result->name);
                $item->link = $result->meta->uri === '/' ? $baseUrl : $baseUrl.'/'.$result->meta->uri;

                $content = new \stdClass();
                $content->all = $this->formatContent($result);
                $lengths = $this->getLongestAndShortestContent($content->all);
                $content->longest = $lengths->longest;
                $content->shortest = $lengths->shortest;

                $item->content = $content;

                $data->push($item);
            };
        }

        if ($this->hasPaging) {
            return $this->paginate($data);
        }


        return $data;
    }

    private function highlight($content)
    {
        $keywords = strtolower($this->keyword);
        // todo: update this puppy
        if (!$this->singleWord) {
            $keywords = explode(' ', $this->keyword);
        }
        // end todo;
        return preg_replace($this->pregStart.$this->keyword.$this->pregEnd, "<span class=\"search__highlight\">$0</span>", $content);
    }

    private function formatContent($result)
    {
        $data = [];

        // help()->trace($this->searchTerms);
        foreach ($this->searchTerms as $key) {
            if (isset($result->{$key}) && $result->{$key}) {
                if(is_array($result->{$key}) && sizeof($result->{$key})) {
                    foreach($result->{$key} as $content) {
                        if(isset($content->fields)) {
                            foreach($content->fields as $field) {
                                if($field->content) {
                                    if(is_array($field->content)) {
                                        foreach($field->content as $subField) {
                                            foreach($subField as $key => $value) {
                                                $hasKeywords = str_contains(strtolower($value), $this->keyword);
                                                if($hasKeywords) {
                                                    $d = $this->highlight($this->createSnippet($value));
                                                    if (!in_array($d, $data)) {
                                                        $data[] = $d;
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                        $hasKeywords = str_contains(strtolower($field->content), $this->keyword);
                                        if($hasKeywords) {
                                            $d = $this->highlight($this->createSnippet($field->content));
                                            if (!in_array($d, $data)) {
                                                $data[] = $d;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else if (is_object($result->{$key})) {
                    // todo: do this one
                } else {
                    $hasKeywords = str_contains(strtolower($result->{$key}), $this->keyword);
                    if($hasKeywords) {
                        $d = $this->highlight($this->createSnippet($result->{$key}));
                        if (!in_array($d, $data)) {
                            $data[] = $d;
                        }
                    }
                }
            }
        }

        return $data;
    }

    private function createSnippet($content)
    {
        $strippedContent = strip_tags($content);

        // find the first occurance of the text
        $match = strpos($strippedContent, $this->keyword);

        // find the starting index;
        if ($match > $this->snippetOffset) {
            $match = $match - $this->snippetOffset;
        }

        $newContent = substr($strippedContent, $match, $this->snippetLength);
        if (strlen($strippedContent) > strlen($newContent) && !substr_compare($strippedContent, $newContent, -1, 30)) {
            $newContent .= '...';
        }

        return $newContent;
    }

    private function paginate($items, $perPage = 5, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        $paging = new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
        $paging->setPath(request()->url());
        return $paging;
    }

    private function getLongestAndShortestContent($content)
    {
        $longest = null;
        $shortest = null;

        foreach ($content as $cont) {
            $len = strlen($cont);
            $longLen = strlen($longest);
            $shortLen = strlen($shortest);

            if ($len > $longLen || !$longest) {
                $longest = $cont;
            }
            if ($len < $shortLen || !$shortest) {
                $shortest = $cont;
            }
        }

        $return = new \stdClass();
        $return->longest = $longest;
        $return->shortest = $shortest;

        return $return;
    }

    public function getKeyword()
    {
        return $this->keyword;
    }

}
