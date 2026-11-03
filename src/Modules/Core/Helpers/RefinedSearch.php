<?php

namespace RefinedDigital\CMS\Modules\Core\Helpers;

use Illuminate\Http\Response;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use RefinedDigital\CMS\Modules\Content\Models\Content;
use RefinedDigital\CMS\Modules\Core\Aggregates\CustomModuleAggregate;
use RefinedDigital\CMS\Modules\Pages\Aggregates\PageAggregate;
use RefinedDigital\CMS\Modules\Pages\Models\Page;
use RefinedDigital\CMS\Modules\Pages\Traits\HasContentBlocks;
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

        // paged after the content block matches are merged in, not by the searcher
        if (request()->has('perPage')) {
            $this->hasPaging = true;
        }

        $searchableModels = array_map('unserialize', array_unique(array_map('serialize', config('searchable-models'))));
        $blockModels = [];
        foreach ($searchableModels as $model) {
            $terms = $model['terms'];

            // content block models keep their content in the contents table, not a column, so it is searched there
            if ($this->usesContentBlocks($model['class'])) {
                $blockModels[] = $model['class'];
                $terms = array_values(array_diff($terms, ['content'])) ?: ['name'];
            }

            $c = $model['class']::query();
            $c->whereActive(1);
            $search->add($c, $terms);
            $this->searchTerms = array_merge($terms, $this->searchTerms);
        }

        $this->searchTerms = array_unique($this->searchTerms);

        if (str_starts_with($this->keyword, '"') && str_ends_with($this->keyword, '"')) {
            $this->singleWord = true;
        }

        $results = collect($search->search($this->keyword))
            ->merge($this->searchContentBlocks($blockModels))
            ->unique(fn ($result) => $result::class.':'.$result->id)
            ->values();

        $data = collect([]);
        if ($results->count()) {
            $baseUrl = rtrim(config('app.url'), '/');
            $modules = app(CustomModuleAggregate::class);
            foreach ($results as $index => $result){
                $item = new \stdClass();
                $item->type = class_basename($result);
                $item->name = $this->highlight($result->name);

                // module records (team, blog...) live under their module's base page
                $uri = trim(($modules->getSitemapBasePage($result::class) ?? '').'/'.$result->meta->uri, '/');
                $item->link = $uri === '' ? $baseUrl : $baseUrl.'/'.$uri;

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

        if ($this->usesContentBlocks($result::class)) {
            foreach ($this->contentBlockStrings($result) as $value) {
                if (str_contains(strtolower($value), strtolower($this->keyword))) {
                    $d = $this->highlight($this->createSnippet($value));
                    if (!in_array($d, $data)) {
                        $data[] = $d;
                    }
                }
            }
        }

        // help()->trace($this->searchTerms);
        foreach ($this->searchTerms as $key) {
            // content on a block model is rendered html, its text was read from the contents rows above
            if ($key === 'content' && $this->usesContentBlocks($result::class)) {
                continue;
            }

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

    /**
     * Whether a model keeps its content in the contents table.
     */
    private function usesContentBlocks(string $class): bool
    {
        return in_array(HasContentBlocks::class, class_uses_recursive($class));
    }

    /**
     * Active records of the content block models whose blocks contain the keyword.
     *
     * @param array<int, class-string> $classes
     */
    private function searchContentBlocks(array $classes): Collection
    {
        $found = collect();
        $keyword = '%'.strtolower(trim($this->keyword, '"')).'%';

        foreach ($classes as $class) {
            // the json column compares as binary, so lower both sides for a case insensitive match
            $ids = Content::whereContentableType($class)
                ->whereRaw('LOWER(CAST(data AS CHAR)) LIKE ?', [$keyword])
                ->distinct()
                ->pluck('contentable_id');

            if ($ids->count()) {
                $found = $found->merge($class::whereActive(1)->whereIn('id', $ids)->get());
            }
        }

        return $found;
    }

    /**
     * Every string stored in a record's content block fields, repeatable items included.
     *
     * @return array<int, string>
     */
    private function contentBlockStrings($result): array
    {
        $strings = [];

        $rows = Content::whereContentableType($result::class)->whereContentableId($result->id)->pluck('data');

        foreach ($rows as $row) {
            array_walk_recursive($row, function ($value) use (&$strings) {
                if (is_string($value) && trim(strip_tags($value)) !== '') {
                    $strings[] = $value;
                }
            });
        }

        return $strings;
    }

    public function getKeyword()
    {
        return $this->keyword;
    }

}
