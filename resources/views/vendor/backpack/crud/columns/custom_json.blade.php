@php
$value = is_string($entry->{$column['name']}) ?
json_decode($entry->{$column['name']}, true) :
$entry->{$column['name']};

$column['text'] = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
$column['escaped'] = $column['escaped'] ?? true;
$column['prefix'] = $column['prefix'] ?? '';
$column['suffix'] = $column['suffix'] ?? '';
$column['wrapper']['element'] = $column['wrapper']['element'] ?? 'pre';

if(!empty($column['text'])) {
$column['text'] = $column['prefix'].$column['text'].$column['suffix'];
}
@endphp

@includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_start')
@if($column['escaped'])
@if(json_decode($column['text']))
    @foreach(json_decode($column['text']) as $image)
        <div style="float:left;margin: 10px;">
        @php
        $extension=explode('.',$image);
        @endphp
        @if(in_array(end($extension),['mp4','webm']))
            <video controls style="max-height: 300px;width: 300px;">
                <source src="{{env('app_url').'/'.$image}}" type="video/mp4">
                <source src="{{env('app_url').'/'.$image}}" type="video/webm">
            </video>
        @else
            <img src="{{env('app_url').'/'. $image }}" style="max-height: 100px;width: 100px;" />
        @endif
        </div>
    @endforeach
@endif
@else
{!! $column['text'] !!}
@endif
@includeWhen(!empty($column['wrapper']), 'crud::columns.inc.wrapper_end')