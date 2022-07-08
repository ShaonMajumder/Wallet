@extends('layouts.app')

@section('content')
<script>
    $(document).ready(function(){
        var toggleExclude = false;
        $("#exclude_column").select2({
            tags: true,
            tokenSeparators: [',', ' ']
        });

        @if(request()->input('exclude_column'))
            values = Array.from( '{{ implode("",request()->input('exclude_column')) }}' );
            values.forEach(function(valu){
                $("#exclude_column").append("<option value='"+valu+"' selected>"+valu+"</option>");
            });
        @endif
        
        
        
        
        $('.table-responsive').doubleScroll();
        
        $(".rowname" ).click(function() {
            $('#start_row').val( $(this).attr("data-value") );
        });

        $(".colname" ).click(function() {
            $('#start_column').val( this.textContent );
            if(toggleExclude){
                let values = $("#exclude_column").val();
                if( ! values.includes(this.textContent) ){
                    $("#exclude_column").append("<option value='"+this.textContent+"' selected>"+this.textContent+"</option>");
                }
            }
        });

        $("#toggleExclude" ).click(function() {
            toggleExclude = !toggleExclude;
            let color = toggleExclude ? 'red' : '';
            $('#toggleExclude').css('background-color', color);
        });
        
    });
</script>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ __('You are logged in!') }}


                    <form method="POST" action="{{ route('import') }}">
                        @csrf
                        <input type='hidden' name="tempfile" value="{{ $tempfile }}" >
                        <input type="hidden" name="column_range" value="">
                        <div class="row">
                          <div class="col">
                            <label for="start_row">Starting Row</label>
                            <input type="text" class="form-control" placeholder="Starting Row"  name="start_row" id="start_row" value="{{ old('start_row') }}">
                          </div>
                          <div class="col">
                            <label for="start_column">Starting Column</label>
                            <input type="text" class="form-control" placeholder="Starting Column"  name="start_column" id="start_column" value="{{ old('start_column') }}">
                          </div>
                        </div>
                        <div class="form-group">
                            <input type="button" name="toggleExclude" id="toggleExclude" value="Exclude Toggle">
                            <label for="exclude_column">Exclude Column</label>
                            <select name="exclude_column[]" id="exclude_column" class="form-control"  multiple="multiple"> </select>
                        </div>
                        <input type="submit">
                    </form>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover table-sm">
                            <caption>View File before Import</caption>
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    @foreach ($column_range as $item)
                                        <th scope="col" class="colname">{{$item}}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($data as $key=>$row)
                                    <tr>
                                        <th scope="row" class="rowname" data-value="{{$key}}">{{$key}}</th>
                                        @foreach($row as $cell)
                                            <td>{{$cell}}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
