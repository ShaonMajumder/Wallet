@extends('layouts.app')

@section('content')
<script>
    $(document).ready(function(){
        var toggleExclude = false;
        var toggleCleanEmptyRow = false;

        $("#exclude_column").select2({
            tags: true,
            tokenSeparators: [',', ' ']
        });

        
        @if(request()->input('exclude_column'))
            
            values = eval( {!!  "['". implode("','",request()->post('exclude_column')) ."']"  !!} ) ;
            values.forEach(function(valu){
                $("#exclude_column").append("<option value='"+valu+"' selected>"+valu+"</option>");
            });
        @endif
        
        @if(request()->input('clean_empty_row') == 'true')
            var toggleCleanEmptyRow = true;
            let color = 'red';
        @else
            var toggleCleanEmptyRow = false;
            let color = '';
        @endif

        $('#clean_empty_row_button').css('background-color', color);
        
        $('.table-responsive').doubleScroll();
        
        $(".rowname" ).click(function() {
            $('#start_row').val( $(this).attr("data-value") );
        });

        $(".colname" ).click(function() {
            if(toggleExclude){
                let values = $("#exclude_column").val();
                if( ! values.includes(this.textContent) ){
                    $("#exclude_column").append("<option value='"+this.textContent+"' selected>"+this.textContent+"</option>");
                }
            }else{
                $('#start_column').val( this.textContent );
            }
        });

        $("#toggleExclude" ).click(function() {
            toggleExclude = !toggleExclude;
            let color = toggleExclude ? 'red' : '';
            $('#toggleExclude').css('background-color', color);
        });

        $("#clean_empty_row_button" ).click(function() {
            toggleCleanEmptyRow = !toggleCleanEmptyRow;
            let color = toggleCleanEmptyRow ? 'red' : '';
            $('#clean_empty_row_button').css('background-color', color);
            $('#clean_empty_row').val(toggleCleanEmptyRow);
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

                    <form method="POST" action="{{ route('import') }}">
                        @csrf
                        <input type="button" name="clean_empty_row_button" id="clean_empty_row_button" value="Clean Empty Row">
                        <input type="hidden" name="clean_empty_row" id="clean_empty_row" id="clean_empty_row" value="{{ request()->input('clean_empty_row') ?? 'false'}}">
                        <div class="row">
                            <div class="col">
                                <label for="start_row">Starting Row</label>
                                <input type="text" style="color: green;  font-weight: bold;" class="form-control" placeholder="Starting Row"  name="start_row" id="start_row" value="{{ request()->input('start_row') ?? 0 }}">
                            </div>
                            <div class="col">
                                <label for="start_column">Starting Column</label>
                                <input type="text" style="color: green;  font-weight: bold;" class="form-control" placeholder="Starting Column"  name="start_column" id="start_column" value="{{ request()->input('start_column') ?? 'A'  }}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="button" name="toggleExclude" id="toggleExclude" value="Exclude Toggle">
                            <label for="exclude_column">Exclude Column</label>
                            <select name="exclude_column[]" id="exclude_column" class="form-control"  multiple> </select>
                        </div>
                        <input type="submit" value="Show View">
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
                                        <th scope="row" class="rowname" data-value="{{ $key }}">{{ $key }} </th>
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
