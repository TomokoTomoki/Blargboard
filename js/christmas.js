var xcolors = [ 0, 120, 56, 240 ];
var xoff = xcolors.length - 1;
var xoff2 = 0;
var xcount = 0;

function christmaslight1()
{
	var e;
	for (var i = 0; (e = document.getElementById('xmas'+i)) != undefined; i++)
	{
		e.style.color = 'hsl('+xcolors[(i + xoff) % xcolors.length]+',100%,50%)';
	}
	
	xoff--;
	if (xoff < 0) xoff = xcolors.length - 1;
	xcount++;
	if (xcount < 10)
		setTimeout('christmaslight1();', 500);
	else
	{
		xoff2 = 0;
		xcount = 0;
		setTimeout('christmaslight2();', 500);
	}
}

function christmaslight2()
{
	var e;
	for (var i = 0; (e = document.getElementById('xmas'+i)) != undefined; i++)
	{
		e.style.color = 'hsl('+xcolors[(i + xoff) % xcolors.length]+',100%,'+(50-xoff2)+'%)';
	}

	if ((xcount % 100) < 50) xoff2++;
	else xoff2--;
	xcount++;
	
	if (xcount < 500)
		setTimeout('christmaslight2();', 10);
	else
	{
		xcount = 0;
		setTimeout('christmaslight1();', 10);
	}
}

christmaslight1();