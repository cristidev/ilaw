<div class="row">
    <div class="col-xs-12 col-md-12">

        <!-- header first -->

        <div class="row">
            <div class="col-xs-12 col-md-4">
                <div class="form-group clearfix">
                    <label>Select a section:</label>
                    <select class="form-control"  name="section" id="section">
                       <option value="news/local-news/">news/local-news/</option>
                        <option value="news/latest-news/">news/latest-news/</option>
                        <option value="sport/local-sport/">sport/local-sport/</option>
                    </select>
                </div>
            </div>
            <div class="col-xs-12 col-md-4">
                <div class="form-group clearfix">
                    <label>Number of pages:</label>
                    <select class="form-control"  name="nopages" id="nopages">
                        <option value="1">1 (recommended)</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </div>
            </div>
            <div class="col-xs-12 col-md-2">
                <label><br /></label>
                <button type="button" class="btn btn-primary btn-block" id="scrape">SCRAPE</button>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-12">
                <table class="table table-bordered" id="response">
                    <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>URL</th>
                        <th>Category</th>
                        <th>Body</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>


    </div>
</div>
<script>
    $(document).ready( function(){
        $('#scrape').on( 'click', function(){
            $(this).attr('disabled','disabled').html('Scraping. This takes a while');
            doScraping();
        } )
    } );

    function doScraping(){
        var section = $('#section').val();
        var nopages = $('#nopages').val();

        querylist = {'section':section, 'nopages':nopages};

        $.ajax({
            type: "POST",
            dataType: 'json',
            url: "scrape.php",
            data: querylist
        }).done(function(response) {
            doWriteResponse( response );
        });
    }

    function doWriteResponse( response ){
        console.log( response );
        $('#scrape').removeAttr('disabled').html('Scrape');

        var $table = $('#response > tbody');
        $("#response > tbody").html("");
        if( response.error ){
            $table.append( '<thead><tr><th>There was an error scraping</th></tr></thead>' );
        } else {
//            $table.append( '<tr><td>Title</td><td>Url</td><td>Category</td><td>Body</td>' )
            $.each(response.content, function(index,value){
                var $tr = $('<tr>');
                var img = $('<img />', {
                    src: value.image.src,
                    alt: value.image.alt,
                    class: 'img-m'
                });
                var newtd = $('<td>').appendTo($tr);
                img.appendTo(newtd);

                console.log(value.image.src);
//                $tr.append( '<td>'+img+'</td>' );
                $tr.append( '<td>'+value.title+'</td>' );
                $tr.append( '<td>'+value.url+'</td>' );
                var item_category = '';
                $.each( value.categories, function(i, v){
                    item_category += v.name;
                    if( i < value.categories.length - 1 ){
                        item_category += ' - ';
                    }
                } )
                $tr.append( '<td>'+item_category+'</td>' );
                $tr.append( '<td><div class="bodyclass">'+value.body+'</div></td>' );
                $table.append($tr);
//                console.log(value);
            });

            $('.bodyclass').on( 'click', function(){
                if( $(this).css('height') == '150px' ){
                    $(this).css('height', 'auto');
                } else {
                    $(this).css('height', '150px')
                }
            } );
            }
        }
</script>