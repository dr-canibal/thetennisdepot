//set image paths
imgArray =  ["http://www.woksfordinner.com/media/catalog/category/SushiSake1.jpg", "http://www.woksfordinner.com/media/catalog/category/RiceSteamers2.jpg", "http://www.woksfordinner.com/media/catalog/category/TeaHouse3.jpg", "http://www.woksfordinner.com/media/catalog/category/Woks4.jpg"];

url = ["http://www.woksfordinner.com/index.php/sushi-sake.html", "http://www.woksfordinner.com/index.php/rice-steamers-cookers.html", "http://www.woksfordinner.com/index.php/teas.html","http://www.woksfordinner.com/index.php/woks.html"]
/*imgArray =  ["SushiSake1.jpg", "RiceSteamers2.jpg", "TeaHouse3.jpg", "Woks4.jpg"]*/

//Please do not edit below
ads=[];
ct = -1;
duration = 5;
var opc = 100;
var t, waitTimer;
var start = true;
function fadeout()
{
	if(opc > 1)
	{
		opc = opc - 5;
	}
	else
	{
		clearTimeout(t);
		var n=(ct+1)%imgArray.length;
		if (ads[n] && (ads[n].complete || ads[n].complete==null))
		{	
			document["Ad_Image"].src = ads[ct=n].src;
		}
		if(n==0)
		{
			document.getElementById("Ad_Image_href").href = url[0];
		}
		else
		{
			document.getElementById("Ad_Image_href").href = url[ct];
		}

		ads[n=(ct+1)%imgArray.length] = new Image;
		ads[n].src = imgArray[n];
		
		opc = 100;
		return;
	}

	document["Ad_Image"].style.filter = 'alpha(opacity='+opc+')';
	document["Ad_Image"].style.MozOpacity = opc/100;
	document["Ad_Image"].style.opacity = opc/100;
	t = setTimeout("fadeout()",50);
}

function clickhotspot(img)
{
	ct = img - 2;
	switchAd();
}

function switchAd()
{
	var n=(ct+1)%imgArray.length;
	clearTimeout(waitTimer);
	document.getElementById("Ad_Image_bg").style.backgroundImage = "url("+ imgArray[n] +")";
	document.getElementById("Ad_Image_bg").style.backgroundRepeat = 'no-repeat';
	fadeout();

	waitTimer = setTimeout("switchAd()",duration*1000);
}

onload = function(){
	//waitTimer = setTimeout("switchAd()",1);
	switchAd();
}