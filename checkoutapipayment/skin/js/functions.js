/**
*	functions.js
*	Creates necessary JavaScript functions to aide in the processing of
*	credit card sales via the Offline Credit Card module produced for Prestashop.
*/

/**
 * object converter for var in array
 */
function oc(a)
{
	var o={};
	for(vari=1;i<a.length;i++)
		o[a[i]] = "";
	return 0;
}

/**
*	modTenValue( cardNumber )
*	Returns true if the cardNumber has a valid mod10 check digit
*/
function modTenValid( cardNumber ){
	var clen = new Array(cardNumber.length);
	var n = 0, sum = 0;

	for(n = 0; n < cardNumber.length; n++ ){
		clen[n] = parseInt(cardNumber.charAt(n));
	}

	for(n = clen.length - 2; n >= 0; n -= 2){
		clen[n] *= 2;
		if(clen[n] > 9){
			clen[n] -= 9;}
	}

	for(n = 0; n < clen.length; n++){
		sum += clen[n];
	}

	return(((sum%10)===0)? true : false);
}

/**
*	makePopups()
*	Creates a centered popup window of a certian size (360x380) for every 
*	anchor in the document that has a class of 'popup'.
*/
function makePopups(){
	if (!document.getElementsByTagName){
		return false;}

	var links = document.getElementsByTagName("a");
	for (var i=0; i < links.length; i++) {
		if (links[i].className.match("popup")) {
			links[i].onclick = function() {
				window.open (this.href, this.name + "_window", "menubar=0,resizable=0,width=360,height=380,left=" + ((screen.availWidth - 360)/2) + ",top="+ ((screen.availHeight - 380)/2));
				return false;
			};
		}
	}
}

/**
*	doCreditCardValidation(cardnumber)
*	Returns card type name if everything is valid, else false
*/
function doCreditCardValidation(cardnumber, returnImg) {
  	cardnumber = cardnumber.replace (/\s/g, "");

	var cards = new Array();

  	//Assign Card Types: Names, Length, Prefixes, and check digit
		cards [0] = {name: "Visa",
			imgName: "CHECKOUTAPI_CARD_TYPE_VISA",
			length: "13,16", 
			prefixes: "4",
			checkdigit: true};
		cards [1] = {name: "MasterCard",
			imgName: "CHECKOUTAPI_CARD_TYPE_MASTERCARD",
			length: "16", 
			prefixes: "51,52,53,54,55",
			checkdigit: true};
		cards [2] = {name: "DinersClub",
			imgName: "CHECKOUTAPI_CARD_TYPE_DINERS",
			length: "14,16", 
			prefixes: "300,301,302,303,304,305,36,38,55",
			checkdigit: true};
		cards [3] = {name: "CarteBlanche", 
			imgName: "unknown.gif",
			length: "14", 
			prefixes: "300,301,302,303,304,305,36,38",
			checkdigit: true};
		cards [4] = {name: "AmEx", 
			imgName: "CHECKOUTAPI_CARD_TYPE_AMEX",
			length: "15", 
			prefixes: "34,37",
			checkdigit: true};
		cards [5] = {name: "Discover", 
			imgName: "CHECKOUTAPI_CARD_TYPE_DISCOVER",
			length: "16", 
			prefixes: "6011,650",
			checkdigit: true};
		cards [6] = {name: "JCB", 
			imgName: "unknown.gif",
			length: "15,16", 
			prefixes: "3,1800,2131",
			checkdigit: true};
		cards [7] = {name: "enRoute", 
			imgName: "unknown.gif",
			length: "15", 
			prefixes: "2014,2149",
			checkdigit: true};
		cards [8] = {name: "Solo", 
			imgName: "solo.gif",
			length: "16,18,19", 
			prefixes: "6334, 6767",
			checkdigit: true};
		cards [9] = {name: "Switch", 
			imgName: "unknown.gif",
			length: "16,18,19", 
			prefixes: "4903,4905,4911,4936,564182,633110,6333,6759",
			checkdigit: true};
		cards [10] = {name: "Maestro", 
			imgName: "maestro.gif",
			length: "16,18", 
			prefixes: "5020,6",
			checkdigit: true};
		cards [11] = {name: "VisaElectron", 
			imgName: "visa.gif",
			length: "16", 
			prefixes: "417500,4917,4913",
			checkdigit: true};

	var cardexp = /^[0-9]{13,19}$/;

	if(cardnumber.length >0 && cardexp.exec(cardnumber))
	{
		for(var currentType in cards){  
			var prefix = new Array ();
			var lengths = new Array ();
			if(cards[currentType] && cards[currentType].prefixes){
				prefix = cards[currentType].prefixes.split(",");

				for (i=0; i<prefix.length; i++){
					var exp = new RegExp("^" + prefix[i]);
					if (exp.test(cardnumber)){
					  lengths = cards[currentType].length.split(",");
					  for (j=0; j<lengths.length; j++){
						if (cardnumber.length == lengths[j]){
							if (cards[currentType].checkdigit){
								if(modTenValid(cardnumber)){
									if(returnImg){
										return cards[currentType].imgName;
									}else{

										return true;}
								}
							}
						}
					  }
					}
				  }	
			}
		}
		if(returnImg){
			return "unknown.gif";
		}else{
			return false;}
	}else{
		if(returnImg){
			return "unknown.gif";
		}else{
			return false;}
	}
}

/**
*	checkForm()
*	Checks validity of entered data
*/
function addListener(elem,eventHandle,functionCall) {
	if ( elem.addEventListener ) {
		elem.addEventListener( eventHandle, functionCall, false );

	} else if ( elem.attachEvent ) {
		elem.attachEvent( "on" + eventHandle, functionCall );
	}
}

function addClass( classname, element ) {
	var cn = element.className;

	//test for existance
	if( cn.indexOf( classname ) != -1 ) {
		return;
	}

	//add a space if the element already has class
	if( cn != '' ) {
		classname = ' '+classname;
	}
	element.className = cn+classname;
}

function removeClass( classname, element ) {
	var cn = element.className;
	var rxp = new RegExp( "\\s?\\b"+classname+"\\b", "g" );
	cn = cn.replace( rxp, '' );
	element.className = cn;
}

function formSubmit(){
	var ccHolder = document.getElementById('creditcardpic_cc_owner'),
		ccType   = document.getElementById('creditcardpic_cc_type'),
		ccNumber = document.getElementById('creditcardpic_cc_number'),
		ccMonth  = document.getElementById('creditcardpic_expiration'),
		ccYear   = document.getElementById('creditcardpic_expiration_yr'),
		ccCCv    = document.getElementById('creditcardpic_cc_cid'),
		ccCardToken = document.getElementById('cko-card-token'),
		error 	 = false;

console.log('formSubmit');

		if(ccCardToken){
			return error;
		}

		if(!jQuery('#uniform-checkoutapipayment-saved-card span').hasClass('checked')){

			if(!validateRequired(ccHolder.value)) {
				error = true;
				removeClass('successCKO',ccHolder);
				addClass('errorCKO',ccHolder);
			}else {
				removeClass('errorCKO',ccHolder);
				addClass('successCKO',ccHolder);
			}

			if(!validateRequired(ccType.value)) {
				error = true;
				removeClass('successCKO',document.getElementById('uniform-creditcardpic_cc_type'));
				addClass('errorCKO',document.getElementById('uniform-creditcardpic_cc_type'));
			}else {
				removeClass('errorCKO',document.getElementById('uniform-creditcardpic_cc_type'));
				addClass('successCKO',document.getElementById('uniform-creditcardpic_cc_type'));
			}

			if(!validateRequired(ccNumber.value)) {
				error = true;
				removeClass('successCKO',ccNumber);
				addClass('errorCKO',ccNumber);
			}else {
				removeClass('errorCKO',ccNumber);
				addClass('successCKO',ccNumber);
			}

			if(!doCreditCardValidation(ccNumber.value,false)) {
				error = true;
				removeClass('successCKO',ccNumber);
				addClass('errorCKO',ccNumber);
			}else {
				removeClass('errorCKO',ccNumber);
				addClass('successCKO',ccNumber);
				var type = doCreditCardValidation(ccNumber.value,true);
				if(type !=ccType.value){
					error = true;
					removeClass('successCKO',document.getElementById('uniform-creditcardpic_cc_type'));
					addClass('errorCKO',document.getElementById('uniform-creditcardpic_cc_type'));
				}else {
					removeClass('errorCKO',document.getElementById('uniform-creditcardpic_cc_type'));
					addClass('successCKO',document.getElementById('uniform-creditcardpic_cc_type'));
				}
			}

			if(!validateRequired(ccMonth.value)) {
				error = true;
				removeClass('successCKO',document.getElementById('uniform-creditcardpic_expiration'));
				addClass('errorCKO',document.getElementById('uniform-creditcardpic_expiration'));
			}else {
				removeClass('errorCKO',document.getElementById('uniform-creditcardpic_expiration'));
				addClass('successCKO',document.getElementById('uniform-creditcardpic_expiration'));
			}

			if(!validateRequired(ccYear.value)) {
				error = true;
				removeClass('successCKO',document.getElementById('uniform-creditcardpic_expiration_yr'));
				addClass('errorCKO',document.getElementById('uniform-creditcardpic_expiration_yr'));
			}else {
				removeClass('errorCKO',document.getElementById('uniform-creditcardpic_expiration_yr'));
				addClass('successCKO',document.getElementById('uniform-creditcardpic_expiration_yr'));
			}

			if(!validateRequired(ccCCv.value)) {
				error = true;
				removeClass('successCKO',ccCCv);
				addClass('errorCKO',ccCCv);
			}else {
				removeClass('errorCKO',ccCCv);
				addClass('successCKO',ccCCv);
			}

			document.getElementById('isSavedCard').value = false;

		} else {
			return error;
		}

	return error;
}

function validateRequired(value){
	if(!value || value.length<1 || value.match(/^\s+$/)) {
		return false;
	}
	return true;
}

/**
*	doSetup()
*	Sets up javascript functions
*/
function doSetup() {

    $('#checkoutapipayment_form .button').click(function (e) {
        e.preventDefault();

        // if(document.getElementById('cko-card-token').value == ""){
        // 	return false;
        // }

        if (!formSubmit()) {
            $(this).attr('disabled','disabled');
            $('#checkoutapipayment_form').submit();
        }
        return false;
    });
}

addListener(window,'load',doSetup);