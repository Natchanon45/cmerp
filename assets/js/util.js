function todate(d){
	/*alert(1);
	alert(d.getDate());
	alert(2);*/
	return ("0" + d.getDate()).slice(-2)+"/"+("0"+(d.getMonth()+1)).slice(-2)+"/"+d.getFullYear();
}

function tonum(mynum, digit = 2){
	mynum = parseFloat(mynum.replace(/\,/g,'')).toFixed(digit)
	if(isNaN(mynum)) return 0.00;
	return mynum;
}