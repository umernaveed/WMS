@extends('admin.template')
@section('content')
<div class="main-content">
    @if(Session::has('success_msg'))
        <div class="alert alert-success" style="margin:10px 15px;">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <p class="text-center" style="margin-bottom:0;">{{ Session::get('success_msg') }}</p>
        </div>
    @endif

    @include('common.errors')

    <div class="container-fluid">
        <div class="portal-page-title">
            <div>
                <h1>{{$config['title']}}</h1>
                <p>Create, update, search, and manage {{$config['title']}} records.</p>
            </div>
            @if($editItem)
                <a href="{{URL::to('admin/portal/'.$module)}}" class="portal-action-btn">Cancel Edit</a>
            @endif
        </div>

        <div class="row portal-grid-gap">
            <div class="col-lg-4">
                <div class="portal-card portal-section">
                    <div class="portal-section-header">
                        <h3>{{$editItem ? 'Edit '.$config['singular'] : 'Add '.$config['singular']}}</h3>
                    </div>
                    <form method="post" action="{{$editItem ? URL::to('admin/portal/'.$module.'/'.$editItem->id) : URL::to('admin/portal/'.$module)}}">
                        {!! csrf_field() !!}
                        @if($editItem)
                            <input type="hidden" name="_method" value="POST">
                        @endif

                        @foreach($config['fields'] as $field)
                            @php
                                $name = $field['name'];
                                $value = old($name, $editItem ? ($editItem->$name ?? '') : '');
                                if (($field['type'] ?? 'text') === 'datetime-local' && $value) {
                                    $value = date('Y-m-d\TH:i', strtotime($value));
                                }
                            @endphp
                            <div class="form-group">
                                <label>{{$field['label']}} @if(!empty($field['required']))<span class="text-danger">*</span>@endif</label>
                                @if(($field['type'] ?? 'text') === 'textarea')
                                    <textarea name="{{$name}}" class="form-control" rows="3" @if(!empty($field['required'])) required @endif>{{$value}}</textarea>
                                @elseif(($field['type'] ?? 'text') === 'select')
                                    <select name="{{$name}}" class="form-control" @if(!empty($field['required'])) required @endif>
                                        @foreach($field['options'] as $option)
                                            <option value="{{$option}}" @if($value == $option) selected @endif>{{$option}}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="{{$field['type'] ?? 'text'}}" name="{{$name}}" value="{{$value}}" class="form-control" @if(($field['type'] ?? '') === 'number') step="0.01" @endif @if(!empty($field['required'])) required @endif>
                                @endif
                            </div>
                        @endforeach

                        <button type="submit" class="btn btn-primary">{{$editItem ? 'Update' : 'Save'}}</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="portal-card portal-section">
                    <div class="portal-section-header">
                        <h3>Records</h3>
                        <form method="get" action="{{URL::to('admin/portal/'.$module)}}" style="display:flex;gap:8px;">
                            <input type="search" name="search" value="{{$search}}" class="form-control" placeholder="Search..." style="min-width:220px;">
                            <button class="btn btn-default" type="submit">Search</button>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="portal-table">
                            <thead>
                                <tr>
                                    @foreach($config['columns'] as $label)
                                        <th>{{$label}}</th>
                                    @endforeach
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($items as $item)
                                    <tr>
                                        @foreach($config['columns'] as $column => $label)
                                            <td>
                                                @if($column === 'amount' || $column === 'value')
                                                    {{number_format((float) $item->$column, 2)}}
                                                @else
                                                    {{$item->$column}}
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="text-right">
                                            <a href="{{URL::to('admin/portal/'.$module.'/'.$item->id.'/edit')}}" class="btn btn-sm btn-info">Edit</a>
                                            <form method="post" action="{{URL::to('admin/portal/'.$module.'/'.$item->id.'/delete')}}" style="display:inline;" onsubmit="return confirm('Delete this record?');">
                                                {!! csrf_field() !!}
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{count($config['columns']) + 1}}" class="text-center">No records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div style="margin-top:16px;">
                        {{ $items->appends(['search' => $search])->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
