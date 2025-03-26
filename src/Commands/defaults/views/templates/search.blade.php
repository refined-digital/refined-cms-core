@extends('layouts.index')

@section('template')

    {!! $page->content !!}

    @if (isset($page->search))
        <section class="page__block page__block--search page__block--light">
            <div class="holder">
                <div class="search__results">
                    <div class="search__header">
                        <h3>You searched for "{{ search()->getKeyword() }}"</h3>
                        <div class="search__form">
                            <form action="{{ config('app.url') }}/search" method="get">
                                <input type="text" name="q" value="{{ search()->getKeyword() }}" class="form__control"/>
                                <button class="button">
                                    @svg('search')
                                </button>
                            </form>
                        </div>
                    </div>

                    @foreach ($page->search as $result)
                        <article class="search__result">
                            <h3 class="search__heading"><a href="{{ $result->link }}">{!! $result->name !!}</a></h3>
                            <blockquote>
                                {!! $result->content->longest !!}
                            </blockquote>
                            <footer><a href="{{ $result->link }}">Read more</a></footer>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

@stop


