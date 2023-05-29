function todate(d){
	return ("0" + d.getDate()).slice(-2)+"/"+("0"+(d.getMonth()+1)).slice(-2)+"/"+d.getFullYear();
}

function tonum(mynum, digit = 2){
	let div = 1;

	if(digit == 3) div = 1000;
	else if(digit == 4) div = 10000;
	else div = 100;

	mynum = parseFloat(mynum.replace(/\,/g,''));

	mynum = Math.round(mynum * div) / div;

	if(isNaN(mynum)) return 0.00;

	//mynum = parseFloat(mynum.replace(/\,/g,'')).toFixed(digit)
	return mynum;
}