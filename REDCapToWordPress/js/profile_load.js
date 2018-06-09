function loading_profile(record_id){
	$(document).ready(function(){
    $.ajax({        // call php script
        url: '/content/plugins/RedCap/get_profile.php?recordID=' + record_id,
        type:'GET',
        timeout: 15000,
        contentType: 'html'
    }).success(function(data){
            // remove loading image and add content received from php 
        $('div#content').html(data);

    }).error(function(jqXHR, textStatus, errorThrown){
            // in case something went wrong, show error
        $('div#content').html('Sorry, something went wrong: ' + textStatus + ' (' + errorThrown + ')');
    });
});
}