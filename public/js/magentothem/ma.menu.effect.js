
$jq(document).ready(function(){	
         (function($jq){
          //show subnav on hover
          $jq('.search-contain').mouseenter(function() {
           $jq(this).find(".search-content").stop(true, true).slideDown();
          });
          //hide submenus on exit
          $jq('.search-contain').mouseleave(function() {
           $jq(this).find(".search-content").stop(true, true).slideUp();
          });
         })($jq);
	
});
