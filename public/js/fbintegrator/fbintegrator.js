function login(){
    FB.getLoginStatus(function(response){
        if(response.status == 'connected'){
            $('fb-loader').setStyle({display: 'block'});
            $('fb-connect').submit();
        }
    })
}