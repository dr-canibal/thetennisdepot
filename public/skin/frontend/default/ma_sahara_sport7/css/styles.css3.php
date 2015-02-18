<?php
    header('Content-type: text/css; charset: UTF-8');
    header('Cache-Control: must-revalidate');
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT');
    $url = $_REQUEST['url'];
?>

.mobilemenu>li.first {
	-webkit-border-radius: 4px 4px 0 0;
	-moz-border-radius: 4px 4px 0 0;
	border-radius: 4px 4px 0 0;
	behavior: url(<?php echo $url; ?>css/css3.htc);
	position: relative;
}
.mobilemenu>li.last {
	-webkit-border-radius:0 0 4px 4px;
	-moz-border-radius: 0 0 4px 4px;
	border-radius: 0 0 4px 4px;
	behavior: url(<?php echo $url; ?>css/css3.htc);
	position: relative;
}
#Example_F {
	-moz-box-shadow: 0 0 5px 5px #888;
	-webkit-box-shadow: 0 0 5px 5px #888;
	box-shadow: 0 0 5px 5px #888;
}
.header .currency-header ul li a {
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
    behavior: url(<?php echo $url; ?>css/css3.htc);
}

.drop-lang li a,.drop-currency li a,.header .links a,button.button span span,.top-link .links li a,.drop-currency .sub-currency li a{
    -moz-transition: all 0.3s ease-in-out 0s;
    transition: all 0.3s ease-in-out;
    -webkit-transition: all 0.3s ease-in-out 0s;
}
.drop-lang li a:hover,.product-name a,.drop-currency li a:hover,.header .links a:hover,button.button:hover span span,
.top-link .links li a:hover,.drop-currency .sub-currency li a:hover{
    -moz-transition: all 0.3s ease 0s;
    -webkit-transition: all 0.3s ease-in-out 0s;
}
.custom_banner img,.block-subscribe button.button span,.product-name a:hover{
    -moz-transition: all 0.3s ease-in-out 0s;
    transition: all 0.3s ease-in-out;
    -webkit-transition: all 0.3s ease-in-out 0s;
}
.custom_banner img:hover, .f-one a:hover,.block-subscribe button.button:hover span{
    -moz-transition: all 0.5s ease 0s;
    -webkit-transition: all 0.3s ease-in-out 0s;
}
.footer-icon a,.block-tags .block-content a,.pt_custommenu div.pt_menu.act a, .pt_custommenu div.pt_menu.active .parentMenu a,.pager .view-mode strong.grid, .pager .view-mode a.grid:hover
,.pager .view-mode a.list,.pager .view-mode strong.list, .pager .view-mode a.list:hover,.pager .view-mode a.grid,.bx-wrapper .bx-controls a,
.banner-static-contain .banner-link a,.banner-one2 .banner-one-link a,.banner-left .link-banner-left a,.nivo-controlNav a
,.products-list button.btn-cart span,.products-list .link-wishlist,.products-list .link-compare,.product-view .link-wishlist,.product-view .link-compare,
.product-view .email-friend a,.top-cart-wrapper .btn-edit,.top-cart-wrapper .btn-remove,button.button span,.catslider button.btn-cart span,.newsletter-right .n-one a{
    -moz-transition: background 0.3s ease-in-out 0s;
    transition: background 0.3s ease-in-out;
    -webkit-transition: all 0.3s ease-in-out 0s;
}
.footer-icon a:hover,.block-tags .block-content a:hover,.pager .view-mode strong.grid, .pager .view-mode a.grid:hover
,.pager .view-mode a.list,.pager .view-mode strong.list, .pager .view-mode a.list:hover,.pager .view-mode a.grid
,.bx-wrapper .bx-controls a:hover,.banner-static-contain .banner-link a:hover,.banner-one2 .banner-one-link a:hover,.banner-left .link-banner-left a:hover,
.nivo-controlNav a:hover,.products-list button.btn-cart:hover span,.products-list .link-wishlist:hover,.products-list .link-compare:hover,.product-view .link-wishlist:hover,
.product-view .link-compare:hover,.product-view .email-friend a:hover,.top-cart-wrapper .btn-edit:hover,.top-cart-wrapper .btn-remove:hover,button.button:hover span,
.catslider button.btn-cart:hover span,.newsletter-right .n-one a:hover{
     -moz-transition: background 0.3s ease 0s;
     -webkit-transition: all 0.3s ease-in-out 0s;
}
.products-grid button.btn-cart:hover span {
    -moz-transition: background 0s ease 0s;
    -webkit-transition: all 0.3s ease-in-out 0s;
}
.header .search-container button.button span {
    transition:background 0s ease-in-out 0s;
    -webkit-transition: all 0.3s ease-in-out 0s;
}
.footer-static-container ul li a,.products-list .desc .link-learn {
    -moz-transition: color 0.3s ease-in-out 0s;
    transition: color 0.3s ease-in-out;
    -webkit-transition: all 0.3s ease-in-out 0s;
}
.footer-static-container ul li a:hover,.products-list .desc .link-learn:hover {
    transition:color 0.3s ease-in-out 0s;
    -webkit-transition: all 0.3s ease-in-out 0s;
}
.tab_container .item-inner .ratings,.category-products .products-grid .item-inner .ratings{
    -moz-transition: top 0.5s ease-in-out 0s;
    transition: top 0.5s ease-in-out;
    -webkit-transition: all 0.3s ease-in-out 0s;
}
.tab_container .item-inner:hover .ratings,.category-products .products-grid .item-inner:hover .ratings{
     -moz-transition: top 0.5s ease 0s;
     -webkit-transition: all 0.3s ease-in-out 0s;
}


.tab_container .item-inner .actions .box-cart{
    -moz-transition: bottom 0.5s ease-in-out 0s;
    transition: bottom 0.5s ease-in-out;
    -webkit-transition: all 0.3s ease-in-out 0s;
}
.tab_container .item-inner:hover .actions .box-cart{
     -moz-transition: bottom 0.5s ease 0s;
     -webkit-transition: all 0.3s ease-in-out 0s;
}

.footer-static-content ul li{
    -moz-transition: padding 0.2s ease-in-out 0s;
    transition: padding 0.2s ease-in-out;
    -webkit-transition: all 0.3s ease-in-out 0s;
}
.footer-static-content ul li:hover{
     -moz-transition: padding 0.3s ease 0s;
     -webkit-transition: all 0.3s ease-in-out 0s;
}

.tab_container .bx-wrapper .bx-controls a,.ma-brand-slider-contain .bx-wrapper .bx-controls a,.ma-thumbnail-container .bx-wrapper .bx-controls-direction a,
.ma-upsellslider-container .bx-wrapper .bx-controls a,.drop-currency .currency-trigger .sub-currency{
    -moz-transition: opacity 0.2s ease-in-out 0s;
    transition: opacity 0.2s ease-in-out;
    -webkit-transition: all 0.3s ease-in-out 0s;
}
.tab_container:hover .bx-wrapper .bx-controls a,.ma-brand-slider-contain:hover .bx-wrapper .bx-controls a,.ma-thumbnail-container .bx-wrapper:hover .bx-controls-direction a,
.ma-upsellslider-container .bx-wrapper:hover .bx-controls a,.drop-currency .currency-trigger:hover .sub-currency {
     -moz-transition: opacity 0.2s ease 0s;
     -webkit-transition: all 0.3s ease-in-out 0s;
}

.item-inner .add-to-links {
    -webkit-transition-duration: 0.4s;
    -moz-transition-duration: 0.4s;
    -ms-transition-duration: 0.4s;
    -o-transition-duration: 0.4s;
    transition-duration: 0.4s;
    transform: scale(0);
    opacity:0;
    left: 16px;
}
.item-inner:hover .add-to-links {
    transform: scale(1);
    -ms-transform: scale(1); 
    -moz-transform: scale(1);
    -webkit-transform: scale(1);
    -o-transform: scale(1);
    opacity:1;
}
.banner-one a:before,.banner-center-one a:before,.banner-right a:before{
    -moz-box-sizing: border-box; box-sizing:border-box;
    border: 0px solid rgba(255, 255, 255, 0.5);
    bottom: 0;
    content: "";
    left: 0;
    overflow: visible;
    position: absolute;
    z-index: 9;
    right: 0;
    top: 0;
    opacity: 0;
     -moz-transition: all 0.3s ease-in-out 0s;
    transition: all 0.3s ease-in-out;
}
.banner-one a:hover:before,.banner-center-one a:hover:before,.banner-right a:hover:before{
    -moz-box-sizing: border-box; box-sizing:border-box;
    border: 8px solid rgba(255, 255, 255, 0.5);		
    opacity:1;
    -moz-transition: all 0.3s ease 0s;
}
.banner-one a,.banner-center-one a,.banner-right a {
    z-index: 9;
    position:relative;
    overflow:hidden;
    display:block;
    -moz-transition: all 0.3s ease-in-out 0s;
    transition: all 0.3s ease-in-out;
}