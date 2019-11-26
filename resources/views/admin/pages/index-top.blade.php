<div class="row mb-4">
    <div class="col-md-2">
        Create new page
        <select class="form-control js-channel-select">
            @foreach($channels as $channel)
                <option value="{{ $channel['name'] }}" @if($loop->iteration == 1) selected @endif>{{ ucfirst($channel['name']) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-10">
        <br>
        <button class="btn btn-primary js-channel-select-btn">
            <i class="fa fa-plus"></i> Create
        </button>
    </div>
</div>
