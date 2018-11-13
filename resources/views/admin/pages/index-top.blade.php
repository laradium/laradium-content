<div class="row" id="channel-select">
    <div class="col-md-2">
        Create new page
        <select v-model="selectedPage" class="form-control">
            @foreach($channels as $channel)
                <option value="{{ $channel['name'] }}" @if($loop->iteration == 1) selected @endif>{{ ucfirst($channel['name']) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-10">
        <br>
        <button class="btn btn-primary" @click="redirectToCreatePage" :disabled="!selectedPage">
            <i class="fa fa-plus"></i> Create
        </button>
    </div>
</div>