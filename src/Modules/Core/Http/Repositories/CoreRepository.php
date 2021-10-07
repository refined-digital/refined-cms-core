<?php

namespace RefinedDigital\CMS\Modules\Core\Http\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RefinedDigital\CMS\Modules\Media\Http\Repositories\MediaRepository;
use RefinedDigital\CMS\Modules\Tags\Models\Tag;

class CoreRepository {

    protected $model;

    private $dates = ['date'];
    private $dateTimes = [];

    public function setModel($model)
    {
        $this->model = $model;
    }

	public function getAll()
    {
        return $this->model::
            keywords()
            ->order()
            ->paging()
        ;
    }

    public function getForFront()
    {
        $data = $this->model::whereActive(1)
            ->order()
            ->get();

        return $data;
    }

    public function find($id)
    {
        return $this->model::find($id);
    }

    public function destroy($id)
    {
        $model = $this->model;
        $item = $model::find($id);
        if(isset($item->id)) {
            $item->delete();

            // log
            activity()
                ->performedOn($item)
                ->causedBy(auth()->check() ? auth()->user()->id : null)
                ->withProperties(['Deleted item' => $id])
                ->log('An item was deleted')
            ;

            // send back a note to say all good
            return true;
        }

        // return false as we failed
        return false;

    }

    public function update($id, $request, $extra = [])
	  {
		$model = $this->model;
        $item = $model::findOrFail($id);

        if(is_array($request)) {
            $data = $request;
        } else {
            $data = $request->all();
        }

        $basename = class_basename($item);

        if(is_array($extra) && sizeof($extra)) {
            $data = array_merge($data, $extra);
        }

        // format the data
        $data = $this->formatData($data);

        // save the item
        $item->fill($data);
        $item->save();

        // now log the item
        activity()
            ->performedOn($item)
            ->causedBy(auth()->check() ? auth()->user()->id : null)
            ->withProperties([$basename.' has been updated' => $id])
            ->log($basename.' has been updated')
        ;

        return $item;
    }

    public function store($request, $extra = [])
	  {
		$model = $this->model;

        if(is_array($request)) {
            $data = $request;
        } else {
            $data = $request->all();
        }

        if(is_array($extra) && sizeof($extra)) {
            $data = array_merge($data, $extra);
        }

        // format the data
        $data = $this->formatData($data);

        // save the item
        $item = $model::create($data);

        // now log the item
        $basename = class_basename($item);
        activity()
            ->performedOn($item)
            ->causedBy(auth()->check() ? auth()->user()->id : null)
            ->withProperties([$basename.' has been created' => $item->id])
            ->log($basename.' has been created')
        ;

        return $item;
    }


    public function formatData($data)
    {

        // force the first name
        if(isset($data['name']))            $data['name']           = ucfirst($data['name']);
        if(isset($data['first_name']))      $data['first_name']     = ucfirst($data['first_name']);
        if(isset($data['password'])) {
            if ($data['password']) {
                $data['password'] = bcrypt($data['password']);
            } else {
                unset($data['password']);
            }
        } else {
            unset($data['password']);
        }

        if (isset($data['parent_id']) && isset($data['page_holder_id'])) {
            if ($data['parent_id'] < 0) {
                $data['page_holder_id'] = abs($data['parent_id']);
                $data['parent_id'] = 0;
            }
        }

        // convert the date strings
        if(sizeof($this->dates)) {
            foreach($this->dates as $date) {
                if(isset($data[$date]) && $data[$date] != '') {

                    $key = 'd/m/Y';
                    if(strlen($data[$date]) == 19 || is_numeric(strpos($data[$date], '-'))) {
                        $key = 'Y-m-d H:i:s';
                    }
                    if(strlen($data[$date]) == 11 || is_numeric(strpos($data[$date], '-'))) {
                        $key = 'Y-m-d';
                    }
                    $data[$date] = Carbon::createFromFormat($key, $data[$date]);
                }
            }
        }

        // convert the date time strings
        if(sizeof($this->dateTimes)) {
            foreach($this->dateTimes as $date) {
                if(isset($data[$date]) && $data[$date] != '') {
                    $key = 'd/m/Y H:i:s';
                    if(is_numeric(strpos($data[$date], '-'))) {
                        $key = 'Y-m-d H:i:s';
                    }
                    $data[$date] = Carbon::createFromFormat($key, $data[$date]);
                }
            }
        }

        if (isset($data['reply_to_type']) && $data['reply_to_type'] != 'text') {
            $data['reply_to'] = $data['reply_to_type'];
        }

        // check for extra data fields
        if (sizeof($data)) {
            $extraData = [];
            foreach ($data as $key => $value) {
                if (is_numeric(strpos($key, 'data__'))) {
                    $newKey = str_replace('data__', '', $key);
                    $extraData[$newKey] = $value;
                    unset($data[$key]);
                }
            }

            if (sizeof($extraData)) {
                $data['data'] = $extraData;
            }
        }

        return $data;
    }



    protected function getTagCollection($type, $model = false)
    {
        if ($model) {
            $tags = DB::table('taggables')
                        ->select('tag_id')
                        ->whereTaggableType($model)
                        ->groupBy('tag_id')
                        ->get();
        }

        $tag = Tag::with('meta')->whereType($type);
        if (isset($tags) && $tags) {
            $tag->whereIn('id', $tags->pluck('tag_id'));
        }
        $data = $tag->orderBy('position')->get();

        if ($data && $data->count()) {
            foreach ($data as $tag) {
                if (isset($tag->meta->original_uri)) {
                    continue;
                }

                $tag->meta->original_uri = $tag->meta->uri;
                $tag->meta->uri = str_slug($type).'/'.$tag->meta->uri;
            }
        }

        return $data;
    }


    public function count()
    {
        return $this->model::count();
    }
}
