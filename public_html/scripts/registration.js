'use strict';

(function(){
	// LIVE VALIDATION
	var recentQuery=false;
	var pending=false;
	var validated={};
	var validators={
		username:validate_username,
		password:validate_password,
		password2:validate_password2,
		fname:validate_fname,
		lname:validate_lname,
		email:validate_email
	}
	function inputFeedback(queryString,state,message){
		//creates feedback element in third column of table containing the input described with querystring.
		//state: 0=>failure,1=>success,2=>working
		let cssClass=['.failure','.success','.working']
		let unInput=document.querySelector(queryString);
		if(!unInput){
			console.log("Error finding "+queryString)
			return false;
		}
		let parent=unInput.closest("tr");
		let oldMsg=parent.querySelector(".formFeedback")
		if(oldMsg){
			oldMsg.parentElement.removeChild(oldMsg);
		}
		let feedback=TPR_GEN.newElement('td',{'className':'formFeedback '+cssClass[state],'innerText':message});
		parent.appendChild(feedback);
		
		
	}
	function validateUserNameOkHandler(xhttp){
		let obj = JSON.parse(xhttp.responseText);
		let input=document.querySelector("#username");
		if(input && input.value==obj.username){
			inputFeedback('#username',1,'Looks Good!');
			validated.username=true;
			submitManager();
		}

	}
	function validateUserNameFailureHandler(xhttp){
		let obj = JSON.parse(xhttp.responseText);
		let input=document.querySelector("#username");
		if(input && input.value==obj.error){
			inputFeedback('#username',0,'Username is already taken');
			validated.username=false;
			submitManager();
		}
	}
	function errorHandler(xhttp){
		CP_POPUP.makePopup('Sorry, there appears to be an error with the server. Please try again later.','Server Error',0);
	}

	function timeManagedValidation(){
		
		function resetQuery(){
			recentQuery=false;
			if(pending){
				timeManagedValidation();
				pending=false;
			}
		}
		if(recentQuery){
			inputFeedback('#username',2,'Working...');
			pending=true;
		}else{
			recentQuery=true;
			TPR_GEN.postWrapper(
				document.getElementById('reg-form'),
				validateUserNameOkHandler,
				validateUserNameFailureHandler,
				errorHandler,
				false
				);
			setTimeout(resetQuery,1000);
		}
	}
	
	function validate_username(){
		let input=document.getElementById('username');
		let justAN= /^[a-zA-z0-9]+$/;
		if(input.value==''){
			validated.username=false;
			inputFeedback('#username',0,'Required');
		}else if(input.value.length<5){
			validated.username=false;
			inputFeedback('#username',0,'Must be at least 5 characters.');
		}else if(input.value.length>25){
			validated.username=false;
			inputFeedback('#username',0,'Must be less than 25 characters.');
		}else if(!justAN.test(input.value)){
			validated.username=false;
			inputFeedback('#username',0,'Only letters and numbers please.');
		}else{//its ok to send to the db wew
			timeManagedValidation();
		}
	}
	function validate_password(){
		let input=document.getElementById('password');
		if(input.value==''){
			validated.password=false;
			inputFeedback('#password',0,'Required');
		}else if(input.value.length<7){
			validated.password=false;
			inputFeedback('#password',0,'Must be at least 7 characters.');
		}else{
			validated.password=true;
			inputFeedback('#password',1,"You're doing great.");
		}
		validate_password2();
	}
	function validate_password2(){
		let input=document.getElementById('password2');
		let password=document.getElementById('password');
		if(input.value==''){
			validated.password2=false;
			inputFeedback('#password2',0,'Required');
		}else if(input.value!=password.value){
			validated.password2=false;
			inputFeedback('#password2',0,"Passwords don't match, keep trying.");
		}else{
			validated.password2=true;
			inputFeedback('#password2',1,"Huzzah!");
		}
	}
	function validate_fname(){
		validate_name('fname');
	}
	function validate_lname(){
		validate_name('lname')
	}
	function validate_name(id){
		let input=document.getElementById(id);
		if(input.value==''){
			validated[id]=false
			inputFeedback('#'+id,0,'Required');
		}else if(input.value.length<1){
			validated[id]=false
			inputFeedback('#'+id,0,"I need more characters than that")
		}else{
			validated[id]=true
			inputFeedback('#'+id,1,"EZ-PZ");
		}
	}
	function validate_email(){
		let input=document.getElementById('email');
		if(input.value==''){
			validated.email=false;
			inputFeedback('#email',0,'Required');
		}else if(input.value.length<1){
			validated.email=false;
			inputFeedback('#email',0,"A valid email if you dont mind")
		}else{
			validated.email=true;
			inputFeedback('#email',1,"Look how far you've come!");
		}
	}
	function submitManager(){
		let register=document.querySelector('input[type="submit"]');
		if(!register){
			return false;
		}
		for(var property in validated){
			if(!validated[property]){
				register.disabled=true;
				return false;
			}
		}
		register.disabled=false;
		return true;
	}
	
	// SUBMISSION AND REDIRECT HANDLING
	function createOK(xhttp){
		let obj=JSON.parse(xhttp.responseText);
		CP_POPUP.makePopup(obj.msg+' Redirecting to login...','Success',1);
		setTimeout(function(){
			let url=document.querySelector('[name="homedir"]').getAttribute('content');
			window.location.href=url;
		},3000);
	}
	function createFail(xhttp){
		let obj = JSON.parse(xhttp.responseText);
		CP_POPUP.makePopup('Could not create account. '+obj.error,'Error',0);

	}
	function initValidate(){
		let inputs=document.querySelectorAll('input[type="text"]');
		inputs.forEach(function(e){
			console.log(e.name)
			validated[e.name]=false;
		})
		for(var property in validated){
			validators[property]();
		}
		
	}
	document.addEventListener('keyup',function(e){
		let target=e.target;
		if(target.id=='username'){
			validate_username();
		}
		if(target.id=='password'){
			validate_password();
		}
		if(target.id=='password2'){
			validate_password2();
		}
		if(target.id=='fname'){
			validate_fname();
		}
		if(target.id=='lname'){
			validate_lname();
		}
		if(target.id=='email'){
			validate_email();
		}
		submitManager();
	});
	document.addEventListener('submit',function(e){
		console.log(e);
		e.preventDefault();
		if(e.target.id='reg-form'){
			//Async submit doesnt include the submit button, so create a temporary input.
			let hidden=TPR_GEN.newElement('input',{'type':'hidden','name':'submit','value':'true','id':'submit'});
			e.target.appendChild(hidden);
			TPR_GEN.postWrapper(e.target,
				createOK,
				createFail,
				errorHandler,
				true);
			e.target.removeChild(hidden);
		}

	});
	initValidate();
	CP_POPUP.initPopupHandler();

})();