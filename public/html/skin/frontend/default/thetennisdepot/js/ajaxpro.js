/* <!-- AjaxPro --> */

var AjaxPro = function (){

function _getPosition()
{   
    // 'bottom', 'center'
    switch (ajaxproLocationMessage) {
        case 'top':
            var left = document.viewport.getWidth() / 2 - 100;
            var top = 10;
            
            break;
        case 'bottom':
            left = document.viewport.getWidth() / 2 - 100;
            top = document.viewport.getHeight() - 150;
            break;
        case 'center':default:
            left = document.viewport.getWidth() / 2 - 100;
            top = document.viewport.getHeight() / 2 - 150;
            break;
    }
    //fix IE6 position:fixed
    Prototype.Browser.IE6 = Prototype.Browser.IE && 
        parseInt(navigator.userAgent.substring(navigator.userAgent.indexOf("MSIE") + 5)) == 6;
    if (Prototype.Browser.IE6) {
        top = parseInt(document.documentElement.clientHeight / 2 - 25);
    }
    return {'top': top + 'px', 'left':left + 'px'};
}

function _hideProgress()
{
    $('ajaxpro-spinner').hide();
}

function _showProgress()
{
    _hideProgress();
    $('ajaxpro-spinner').setStyle(_getPosition()).show();
}

function _showMessage(id, message)
{
    var element = $(id);
    element.setStyle(_getPosition());
    element.select('.ajaxpro-message').invoke('update', message);
//  element.removeClassName('no-display')
    element.setStyle('');
    element.setOpacity(ajaxproOpacity);
    element.show();
}
function _parseResponse(response)
{
    if (response == null) {
        window.location.reload();
    }

    if (response.redirectUrl) { //redirectUrl use like error flag :(((
        if (ajaxproEnabledNoticeForm && response.message) {
            _showMessage('ajaxpro-notice-form', response.message);
//            $('ajaxpro-notice-form').select('.ajaxpro-action-button').each(function(element){
//                element.setAttribute('href', response.redirectUrl);
//            });
        } else {
            alert(response.message);
            window.location.href = response.redirectUrl;
        }
    }
    //shopping cart blocks
    if (response.topLinkCart) {
        $$('.top-link-cart').invoke('replace', response.topLinkCart);
        if (response.message && ajaxproEnabledShoppingCartForm) {
            _showMessage('ajaxpro-addtocart-form', response.message);
        }

    }
    if (response.miniCart || response.miniCart == "") {
        $$('.block-cart').invoke('replace', response.miniCart);
    }
    if (response.checkoutCart) {
        $$('.col-main').invoke('update', response.checkoutCart);
    }
    //compare blocks
    if (response.compareSideBar || response.compareSideBar == "") {
        $$('.block-compare').invoke('replace', response.compareSideBar);
        if (response.message && ajaxproEnabledCompareForm) {
            _showMessage('ajaxpro-addtocompare-form', response.message);
        }
    }
    if (response.rightReportsProductCompared || response.rightReportsProductCompared == "") {
        $$('.block-compared').invoke('replace', response.rightReportsProductCompared);
    }
    // wishlist blocks
    if (response.topLinkWishlist) {
        $$('.top-link-wishlist').invoke('replace', response.topLinkWishlist);
        if (response.message && ajaxproEnabledWishlistForm) {
            _showMessage('ajaxpro-addtowishlist-form', response.message);
        }
    }

    if (response.wishlistSideBar || response.wishlistSideBar == "") {

        $$('.block-wishlist').invoke('replace', response.wishlistSideBar);
    }
    if (response.customerWishlist) {
        $$('.col-main').invoke('update', response.customerWishlist);
    }
}
function _showEffect(url) {
    var cssClass = false;
    if (url.search('/checkout/cart') != -1) {
        cssClass = '.block-cart';
    }
    if (url.search('/catalog/product_compare') != -1) {
        cssClass = '.block-compare';
        $$('.block-compared').each(function(element){
            element.setOpacity(0.5);
        });
    }
    if (url.search('/wishlist/index') != -1) {
        cssClass = '.block-wishlist';
    }
    if (cssClass) {
        $$(cssClass).each(function(element){
            element.setOpacity(0.5);
        });
    }
}
function _hideEffect(url) {
    var cssClass = false;
    if (url.search('/checkout/cart') != -1) {
        cssClass = '.block-cart';
    }
    if (url.search('/catalog/product_compare') != -1) {
        cssClass = '.mini-compare-products';
    }
    if (url.search('/wishlist/index') != -1) {
        cssClass = '.mini-wishlist';
    }
    if (cssClass) {
        $$(cssClass).each(function(element){
            element.setOpacity(1);
        });
    }
}
return {
    /**
     * Core function
     */
    core :function (url, method)
    {
        var responseStatus = true;
        var requestMethod = method || 'post';

        new Ajax.Request(url, {
            parameters: {'handles': ajaxproHandles.toJSON()}, // see page/head.phtml
            method: requestMethod,
            onLoading: function(transport) {
                _showProgress();
                
                _showEffect(url);
            },

            onComplete: function(transport) {
                var response = transport.responseJSON;
                if (200 != transport.status) {
                    responseStatus = false;
                    _hideProgress();
                    return false;
                }
                _parseResponse(response);
                _hideProgress();

                AjaxPro().redefineEvent();

                _hideEffect(url);
                return false;
            }
        });
        return responseStatus;
    },
    redefineEvent: function()
    {

        /**
         * redeclare submit form function on product page
         *  add product action checkout/cart/add
         */
        if(typeof productAddToCartForm != 'undefined' && ajaxproEnabledShoppingCart) {
            productAddToCartForm.submit = function(){
                var params = this.form.serialize();
                var url = this.form.action;
                if(this.validator && this.validator.validate()){
                    if (!AjaxPro().core(url + '?' + params  + '&ajaxpro=1')){
                        this.form.submit();
                    }
                }
                return false;
            }
        }

        /**
         * redeclare submit form function on shopping cart page
         * update action checkout/cart/updatePost
         */
        var shoppingCartTable = $('shopping-cart-table');
        if (shoppingCartTable && ajaxproEnabledShoppingCart) {
            var shoppingCartForm = shoppingCartTable.up('form');
            if(typeof shoppingCartForm != 'undefined'){
                shoppingCartForm.observe('submit', function(event) {
                    Event.stop(event);
                    var params = Event.element(event).serialize();
                    var url = Event.element(event).action;
                    if (!AjaxPro().core(url + '?' + params  + '&ajaxpro=1')){
                        Event.element(event).submit();
                    }
                    return false;
                });
            }
        }
        /**
         * get all page link and add redeclare his onclick event
         *
         */
        $$('a').each(function(element){

            var url = element.getAttribute('href');
//            var onclickHandler = element.getAttribute('onclick');

            if (url == '#' && ajaxproEnabledShoppingCart)
            {
                var onclickHandler = element.getAttribute('onclick');
                if (
                    onclickHandler
                    && typeof onclickHandler == 'string'
                    && onclickHandler != ''
                    && onclickHandler.search('setLocation') != -1
                    && onclickHandler.search('checkout/cart/add') != -1
                ) {
                    
                    element.stopObserving('click');
                    element.observe('click', function(event) {
                        Event.stop(event);
                    });
                }
            }
            confirmation = true;
            if (url && ajaxproEnabledShoppingCart
                && url.search('checkout/cart/delete') != -1)
            {
                element.stopObserving('click');
                element.setAttribute('onclick', '');
                element.observe('click', function(event) {
                    Event.stop(event);
                    if (enabledDeleteCartConfirm) {
                        confirmation = confirm(removeCartItemMessage);
                        if(!confirmation) {return false;}
                    }
                    AjaxPro().core(url + 'ajaxpro/1', 'get');
                    return false;
                });
                return false;
            }
            //if enabled compare
            if (url && ajaxproEnabledCompare &&
                ((url.search('catalog/product_compare/add') != -1)
                || (url.search('catalog/product_compare/remove') != -1)
                || (url.search('catalog/product_compare/clear') != -1)
                ))
            {
                element.stopObserving('click');

                if (url.search('catalog/product_compare/remove') != -1) {
                    element.setAttribute('onclick', '');
                }

                if (url.search('catalog/product_compare/clear') != -1) {
                    element.setAttribute('onclick', '');
                }

                element.observe('click', function(event) {
                    Event.stop(event);
                    if (enabledDeleteCompareConfirm) {
                        if (url.search('catalog/product_compare/remove') != -1) {
                            confirmation = confirm(removeCompareItemMessage);
                        }
                        if (url.search('catalog/product_compare/clear') != -1) {
                            confirmation = confirm(removeCompareClearMessage);
                        }
                        if(!confirmation) {return false;}
                    }
                    AjaxPro().core(url + 'ajaxpro/1', 'get');
                    return false;
                });
                return false;
            }
            //if enabled wishlist
            if (url && ajaxproEnabledWishlist &&
                ((url.search('wishlist/index/add') != -1)
                ||(url.search('wishlist/index/remove') != -1)
//                ||(url.search('wishlist/index/cart') != -1)
                ))
            {
                element.stopObserving('click');
                if (url.search('wishlist/index/remove') != -1) {
                    element.setAttribute('onclick', '');
                }
                element.observe('click', function(event) {
                    Event.stop(event);
                    if (enabledDeleteWishlistConfirm) {
                        if (url.search('wishlist/index/remove') != -1) {
                            confirmation = confirm(removeWishlistItemMessage);
                        }
                        if(!confirmation) {return false;}
                    }
                    if (!customerLoggedId) {
                        window.location.href = baseUrl + 'customer/account/login';
                    }

                    AjaxPro().core(url + 'ajaxpro/1', 'get');
                    return false;
                });
                return false;
            }

        });
    }
}
};



function ajaxproload() {
    //add loading spinner
    var spinner = new Element('div', {'id': 'ajaxpro-spinner'});
    document.body.appendChild(spinner.hide().setOpacity(ajaxproOpacity));

    /**
     * redefine setLocation function
     */
    setLocation = function (url)
    {
        if (url.search('checkout/cart/add') != -1
            && AjaxPro().core(url + 'ajaxpro/1', 'get')) {
            //Event.stop();
            return false;
        }
        if (url.search('wishlist/index/cart') != -1
            && AjaxPro().core(url + 'ajaxpro/1', 'get')) {
            //Event.stop();
            return false;
        }

        window.location.href = url;
    }

    AjaxPro().redefineEvent();

    //add event hide form message
    $$('.ajaxpro-continue-button').each(function(element){
        element.observe('click', function(event) {
            Event.stop(event);
			var el = Event.element(event);
			// while(el.tagName != 'A') {
			// 	el = el.up();
			//}
            el.up('div').hide();
//          Event.element(event).up().addClassName('no-display');
            return false;
        });
    });
    $$('.ajaxpro-action-button').each(function(element){
        element.observe('click', function(event) {
            Event.element(event).up().hide();
            return false;
        });
    });
}

//onReady
if (Prototype.Browser.IE) {
    Event.observe(window, 'load', function(){ //KB927917 fix
        ajaxproload();
    });
} else {
    document.observe("dom:loaded", function(){
        ajaxproload();
    });
}