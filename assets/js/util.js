function tonum(mynum, digit = 2){
	mynum = parseFloat(mynum.replace(/\,/g,'')).toFixed(digit)
	if(isNaN(mynum)) return 0.00;
	return mynum;
}