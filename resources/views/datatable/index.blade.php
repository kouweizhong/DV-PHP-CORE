
@extends('layout')

@section('header')
  <div class="page-head">
    <h3>
      Data Table
    </h3>
    <span class="sub-title">Data Table/</span>
  </div>
@endsection

@section('content')
  <div class="wrapper">
    <div class="row">
      <div id="datatable">
        <label for="service" class="col-lg-2 col-sm-2 control-label">Service </label>
        <div class="col-md-3">
          <select id="service" name="service" class="form-control m-b-10">
            @if(\Request::has('service_name'))
              <option value="{{$service->id}}">{{$service->name}}</option>
            @else
              <option disabled selected value> -- select a service -- </option>
              @foreach($services as $service)
                <option value="{{$service->id}}">{{$service->name}}</option>
              @endforeach
            @endif
          </select>
        </div>
        <label for="table_name" class="col-sm-2 control-label col-md-offset-1">Table </label>
        <div class="col-md-3">
          <select id="table_name" name="table_name" class="form-control m-b-10">
            @if(\Request::has('table_name'))
              @foreach($tables as $table)
                <option value="{{$table->id}}">{{$table->table_name}}</option>
              @endforeach
            @else
              <option disabled selected value> -- select a table -- </option>
            @endif
          </select>
        </div>
      </div>

      <div class="col-sm-12">
        <section class="panel">
          <table id="excelDataTable" class="schema-table table" cellspacing="0" width="100%">
          </table>
          <h3 id="empty_handler" class="text-center alert alert-info" style="margin: -5px;">Empty!</h3>
        </section>
        <nav id="page-nav">
          <ul class="pager">
            <li><a id="previous">Previous</a></li>
            <li><a id="next">Next</a></li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
  <script charset="utf-8">
  window.onload(function() {

    document.getElementById('empty_handler').style.display = 'none';
    document.getElementById('page-nav').style.display = 'none';

    $(window).load(function() {
      $('#page-nav').hide();
      $('.header-control').hide();
    });

    var entries;
    var nextUrl;
    var prevUrl;

    function tableCall(table_entries) {
      $.get('/datatable/'+table_entries+'/entries', function(data) {
        $('#excelDataTable').html(' ');
        if (data.data.length == 0){
          $('#empty_handler').show('fast', function(){
            $('.header-control').hide();
            $('#page-nav').hide();
          });
        } else {
            $(window).load(function() {
            $('#page-nav').show();
          });
        }

        entries = data.data;
        nextUrl = data.next_page_url;
        prevUrl = data.prev_page_url;
        buildHtmlTable();
        $('#page-nav').show();
        if(data.current_page == data.from ) {
          $('#previous').addClass('disabled');
        }
      });
    }

    if ($('#service option:selected').val() != '' && $('#table_name option:selected').val() != '') {
      var tb_name = $('#service option:selected').text() + '_' + $('#table_name option:selected').text();
      tableCall(tb_name);
    }

    var service_id;
    $('#service').change(function() {
      service_id = $('#service').val();

      $.get('/datatable/'+service_id, function(data) {
        var tables = data;
        for (var i = 0; i < tables.length; i++) {
          $('#table_name').append('<option value="'+JSON.parse(tables[i].id)+'">'+JSON.parse(tables[i].schema).name+'</option>');
        }
      });
    });

    $('#table_name').change(function(data) {
      var table_entries = $('#service option:selected').text() + '_' + $('#table_name option:selected').text();
      tableCall(table_entries);
    });

    function buildHtmlTable() {
      var columns = addAllColumnHeaders(entries);

      for (var i = 0 ; i < entries.length ; i++) {
        var row$ = $('<tr/>').addClass('details');
        row$.append($('<td/>').addClass('details-control'));
        for (var colIndex = 0 ; colIndex < columns.length ; colIndex++) {
          var cellValue = entries[i][columns[colIndex]];

          row$.append($('<td/>').html(cellValue));
        }
        $("#excelDataTable").append(row$);
      }
    }

    function addAllColumnHeaders(entries) {
      var columnSet = [];
      var headerTr$ = $('<tr/>');
      headerTr$.append($('<th/>').addClass('header-control'));

      for (var i = 0 ; i < entries.length ; i++) {
        var rowHash = entries[i];
        for (var key in rowHash) {
          if ($.inArray(key, columnSet) == -1){
            columnSet.push(key);
            headerTr$.append($('<th/>').html(key));
          }
        }
      }
      $("#excelDataTable").append(headerTr$);

      return columnSet;
    }

    $('#previous').click(function(){
      $.get(prevUrl, function(data) {
        $('#excelDataTable').html(' ');
        entries = data.data;
        prevUrl = data.prev_page_url;
        nextUrl = data.next_page_url;
        buildHtmlTable();
        checkPage(data);
      });
    });

    $('#next').click(function() {
      $.get(nextUrl, function(data) {
        $('#excelDataTable').html(' ');
        entries = data.data;
        prevUrl = data.prev_page_url;
        nextUrl = data.next_page_url;
        buildHtmlTable();
        checkPage(data);
      });
    })

    function checkPage(data) {
      if(data.current_page == data.from ) {
        $('#previous').addClass('disabled');
        $('#next').removeClass('disabled');
      } else if (data.current_page == data.last_page) {
        $('#previous').removeClass('disabled');
        $('#next').addClass('disabled');
      } else {
        $('#next').removeClass('disabled');
        $('#previous').removeClass('disabled');
      }
    }

  }());

  </script>
@endsection
